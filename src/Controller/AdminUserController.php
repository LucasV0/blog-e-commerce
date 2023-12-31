<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

//méthodes pour l'affichage et la suppression des utilisateurs

#[Route('/admin/user')]
class AdminUserController extends AbstractController
{
    #[Route('/', name: 'app_admin_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin_user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    

    #[Route('/{id}', name: 'app_admin_user_delete', methods: ['POST', 'DELETE'])]
public function delete(Request $request, User $user, UserRepository $userRepository): Response
{
    if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
        $userRepository->remove($user, true);
    }

    return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
}

}
