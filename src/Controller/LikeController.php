<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

//méthode pour ajouter ou supprimer un like pour un article spécifique

class LikeController extends AbstractController
{
    #[Route('/like/article/{id}', name: 'like.article')]
    public function like(Article $article, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        if ($article->isLikedByUser($user)) {
            $article->removeLike($user);
            $manager->flush();
            return $this->json(['message' => 'le like a été supprimé']);
        }

        $article->addLike($user);
        $manager->flush();
        return $this->json(['message' => 'le like a été ajouté']);
    }
}
