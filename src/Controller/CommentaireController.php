<?php

namespace App\Controller;

use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/commentaire')]
class CommentaireController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private CommentaireRepository $commentaireRepository, private Security $security)
    {
    }

    #[Route('/', name: 'app_commentaire')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request): Response
    {
        $commentaire = new Commentaire();
        $auteur = $this->security->getUser();

        $commentaire->setAuteur($auteur);
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($commentaire);
            $this->em->flush();
            $this->addFlash('success', 'Le commentaire a bien été ajouté.');
            return $this->redirectToRoute('app_commentaire');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('danger', 'Le commentaire n\'a pas été ajouté.');
        }

        return $this->render('commentaire/index.html.twig', [
            'controller_name' => 'CommentaireController',
            'form' => $form->createView(),
            'commentaires' => $this->commentaireRepository->findAll(),
        ]);
    }
    
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/edit/{id}', name: 'app_commentaire_etat')]
    public function etat(Commentaire $commentaire = null): Response
    {
        if ($commentaire == null) {
            $this->addFlash('danger', 'Le commentaire n\'existe pas.');
        } else {
            $commentaire->setEtat(!$commentaire->isEtat());
            $this->em->persist($commentaire);
            $this->em->flush();
            $this->addFlash('success', 'Le commentaire a bien été modifié.');
        }
        return $this->redirectToRoute('detail_article', ['id' => $commentaire->getArticle()->getId()]);
    }
}
