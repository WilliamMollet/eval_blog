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

class HomeController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private CategorieRepository $categorieRepository, private ArticleRepository $articleRepository)
    {
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $troisDerCategories = $this->categorieRepository->findBy([], ['id' => 'DESC'], 3);

        return $this->render('categorie/home.html.twig', [
            'categories' => $troisDerCategories,
            'articles' => $this->articleRepository->findAll(),
        ]);
    }
}
