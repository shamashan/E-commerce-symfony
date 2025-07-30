<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/admin/user', name: 'app_user')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            "users" => $userRepository->findAll(),
        ]);
    }


    #[Route('/admin/user/{id}/delete', name: 'app_user_delete')]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash("success", 'The user was deleted!');
        return $this->redirectToRoute("app_user");
    }

    #[Route('/admin/user/{id}/handleRole/editor', name: 'app_user_handleRole_editor')]
    public function handleRoleEditor(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $roles = $user->getRoles();
        // var_dump($roles);
        if (in_array("ROLE_EDITOR", $roles)) {
            $roles = [];
        } else {
            $roles = array_merge($roles, ["ROLE_EDITOR", "ROLE_USER"]);
        }
        $user->setRoles($roles);
        $entityManager->flush();

        $this->addFlash("success", 'The user state was changed successfully!');
        return $this->redirectToRoute("app_user");
    }
}
