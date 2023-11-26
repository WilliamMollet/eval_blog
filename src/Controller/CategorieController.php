<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Categorie;
use App\Entity\Article;
use App\Repository\CategorieRepository;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\CategorieType;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/categorie')]
class CategorieController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private CategorieRepository $categorieRepository, private ArticleRepository $articleRepository)
    {
    }

    #[Route('/', name: 'app_categorie')]
    public function index(Request $request): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', 'La categorie n\'a pas été ajoutée.');
        } elseif ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($categorie);
            $this->em->flush();
            $this->addFlash('success', 'La categorie a bien été ajoutée.');
            return $this->redirectToRoute('app_categorie');
        }

        return $this->render('categorie/index.html.twig', [
            'controller_name' => 'CategorieController',
            'form' => $form->createView(),
            'categories' => $this->categorieRepository->findAll(),
            'articles' => $this->articleRepository->findAll(),
        ]);
    }
    #[Route('/details/{id}', name: 'detail_categorie')]
    public function details(Categorie $categorie = null)
    {
        if($categorie == null){
            return $this->redirectToRoute('app_categorie');
        }
        else{
            return $this->render('categorie/details.html.twig', [
                'categorie' => $categorie,
                'articles' => $this->articleRepository->findBy(['categorie' => $categorie->getId()]),
            ]);
        }
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/delete/{id}', name: 'delete_categorie')]
    public function delete($id)
    {
        $categorie = $this->em->getRepository(Categorie::class)->find($id); // Récupère la catégorie à modifier 

        if($categorie == null){
            return $this->redirectToRoute('app_categorie');
        }
        else{
            $articles = $categorie->getArticles();
            foreach($articles as $article){
                $commentaires = $article->getCommentaires();
                foreach($commentaires as $commentaire){
                    $this->em->remove($commentaire);
                }
                $this->em->remove($article);
            }
            $this->em->remove($categorie);
            $this->em->flush();
            $this->addFlash('success', 'La catégorie a bien été supprimée.');
            return $this->redirectToRoute('app_categorie');
        }
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/update/{id}', name: 'update_categorie')]
    public function editCategorie(Request $request, $id)
    {
        $categorie = $this->em->getRepository(Categorie::class)->find($id); // Récupère la catégorie à modifier
        $form = $this->createForm(CategorieType::class, $categorie); // Création du formulaire
        $form->handleRequest($request); // Gestion de la requête

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Catégorie modifié avec succès');
            return $this->redirectToRoute('app_categorie');
        } 
        return $this->render('categorie/edit.html.twig', [
            'form' => $form->createView() // Envoi du formulaire à la vue
        ]);
    }
}
