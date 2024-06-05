<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\ChangePasswordType;
use App\Form\Type\TaskType;
use App\Form\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route(path: '/users', name: 'user_list')]
    #[IsGranted('ROLE_ADMIN',null,'Vous ne pouvez pas accéder à la liste des utilisateurs')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        return $this->render('user/list.html.twig', ['users' => $entityManager->getRepository(User::class)->findAll()]);
    }

    #[Route(path: '/users/create', name: 'user_create')]
    #[IsGranted('ROLE_ADMIN',null,'Vous ne pouvez pas accéder à la liste des utilisateurs')]
    public function create(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $userPasswordHasher->hashPassword($user, $user->getPassword());

            $user->setPassword($password);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route(path: '/users/{id}/edit', name: 'user_edit')]
    #[IsGranted('ROLE_ADMIN',null,'Vous ne pouvez pas accéder à la liste des utilisateurs')]
    public function edit(User $user, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->remove("password");
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $userPasswordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($password);

            $entityManager->flush();

            $this->addFlash('success', "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }


    #[Route(path: '/users/{id}/change-password', name: 'user_change_password')]
    #[IsGranted('ROLE_ADMIN',null,'Vous ne pouvez pas accéder à la liste des utilisateurs')]
    public function changePassword(User $user, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();
            $hashedPassword = $userPasswordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);

            $entityManager->flush();

            $this->addFlash('success', 'Le mot de passe a été modifié avec succès.');

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/change_password.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
