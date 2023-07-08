<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

//méthode pour afficher la page d'accueil

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ArticleRepository $articleRepository): Response
    {
        $latestArticles = $articleRepository->findLatest(3);
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'latestArticles' => $latestArticles,
        ]);
    }
}
