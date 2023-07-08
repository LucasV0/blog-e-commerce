<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\Comment1Type;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

//méthode pour l'affichage des commentaires et une méthode pour la création d'un nouveau commentaire lié à un article spécifique

#[Route('/article/{id}/comment')]
class CommentController extends AbstractController
{
    #[Route('/', name: 'app_comment_index', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render('comment/index.html.twig', [
            'comments' => $commentRepository->findAll(),
        ]);
    }

    public function new(Request $request, Article $article, CommentRepository $commentRepository): Response
{
    $comment = new Comment();
    $comment->addArticle($article); // Ajouter l'article en utilisant la méthode addArticle()
    $form = $this->createForm(Comment1Type::class, $comment);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $commentRepository->save($comment, true);

        return $this->redirectToRoute('app_comment_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('comment/new.html.twig', [
        'comment' => $comment,
        'form' => $form,
    ]);
}

}
