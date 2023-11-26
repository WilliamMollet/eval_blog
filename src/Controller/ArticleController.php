<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Form\CommentaireType;
use App\Entity\Commentaire;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/article')]
class ArticleController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private ArticleRepository $ArticleRepository, private CategorieRepository $categorieRepository, private Security $security)
    {
    }

    #[Route('/', name: 'app_article')]
    public function index(Request $request): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        $auteur = $this->security->getUser();
        $article->setAuteur($auteur);

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', "Le formulaire contient des erreurs");
        } elseif ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('submit')->isClicked()) {
                $article->setEtat(1);
                $article->setPostedAt(new \DateTimeImmutable());
            } elseif ($form->get('draft')->isClicked()) {
                $article->setEtat(0);
                $article->setPostedAt(null);
            }
            if (!$auteur){
                $this->addFlash('danger', "Vous devez être connecté pour ajouter un article");
                return $this->redirectToRoute('app_article');
            } else{
                $this->em->persist($article);
                $this->em->flush();
                $this->addFlash('success', "L'article a bien été ajouté");
                return $this->redirectToRoute('app_article');

            }
        }

        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
            'form' => $form->createView(),
            'articles' => $this->ArticleRepository->findAll()
        ]);
    }

    #[Route('/details/{id}', name: 'detail_article')]
    public function details(int $id, Article $article = null, Request $request, CommentaireController $commentaireController)
    {
        if ($article == null) {
            return $this->redirectToRoute('app_article');
        }
        $article = $this->ArticleRepository->find($id); 
        $categorie = $article->getCategorie();
        $auteur = $article->getAuteur();
        $auteurComm = $this->getUser(); 
        $commentaire = new Commentaire();
        $commentaire->setAuteur($auteurComm);
        $commentaire->setArticle($article);
        $commentaires = $article->getCommentaires();
        
        $commentaireForm = $commentaireController->createForm(CommentaireType::class, $commentaire);
        $commentaireForm->handleRequest($request);
    
        if ($commentaireForm->isSubmitted() && $commentaireForm->isValid()) {
            $this->em->persist($commentaire);
            $this->em->flush();
            $this->addFlash('success', 'Le commentaire a bien été ajouté.');
            return $this->redirectToRoute('detail_article', ['id' => $id]);
        } elseif ($commentaireForm->isSubmitted()) {
            $this->addFlash('danger', 'Le commentaire n\'a pas été ajouté.');
        }
    
        return $this->render('article/show.html.twig', [
            'article' => $article,
            'commentaireForm' => $commentaireForm->createView(),
            'commentaires' => $commentaires,
            'categorie' => $categorie
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/edit/{id}', name: 'edit_article')]
    public function edit(Request $request, Article $article = null)
    {
        if ($article == null) {
            return $this->redirectToRoute('app_article');
        } else {
            $form = $this->createForm(ArticleType::class, $article);
            $form->handleRequest($request);

            if ($form->isSubmitted() && !$form->isValid()) {
                $this->addFlash('danger', "Le formulaire contient des erreurs");
            } elseif ($form->isSubmitted() && $form->isValid()) {
                if ($article->isEtat() == 1) {
                    $article->setPostedAt(new \DateTimeImmutable());
                } else {
                    $article->setPostedAt(null);
                }

                $this->em->persist($article);
                $this->em->flush();
                $this->addFlash('success', "L'article a bien été modifié");
                return $this->redirectToRoute('app_article');
            }

            return $this->render('article/edit.html.twig', [
                'form' => $form->createView(),
                'article' => $article
            ]);
        }
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/delete/{id}', name: 'delete_article')]
    public function delete(Article $article = null)
    {
        if ($article == null) {
            return $this->redirectToRoute('app_article');
        } else {
            foreach ($article->getCommentaires() as $commentaire){
                $article->removeCommentaire($commentaire);
            }
            $this->em->remove($article);
            $this->em->flush();
            $this->addFlash('success', "L'article a bien été supprimé");
            return $this->redirectToRoute('app_article');
        }
    }
}
