<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

//méthodes pour l'affichage et l'ajout de commentaires pour les articles

class ArticleController extends AbstractController
{
    #[Route('/article', name: 'app_article')]
    public function index(ArticleRepository $repository): Response
    {
        $articles = $repository->findAll();
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
            'articles' => $articles
        ]);
    }

    #[Route('/article/{id}', name: 'article.show', methods:['GET','POST'])]
    public function show($id, ArticleRepository $repository, Request $request, EntityManagerInterface $manager): Response
    {
        $comment = new Comment();
        $form = $this -> createForm(CommentType::class, $comment);
        $form -> handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
           $manager->persist($comment);
           $manager->flush();

        }

        $article = $repository->find($id);
        return $this->render('article/show.html.twig', [
            'article' => $article,
            'current_menu' => 'article',
            'form' => $form->createView()
        ]);
    }
}
