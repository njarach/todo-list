<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/users', name: 'users_list')]
    #[IsGranted('ROLE_ADMIN')]
    public function listAction(UserRepository $userRepository): Response
    {
        return $this->render('user/list.html.twig', ['users' => $userRepository->findAll()]);
    }

    #[Route('/users/create', name: 'user_create')]
    #[IsGranted('ROLE_ADMIN')]
    public function createAction(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);
            $selectedRole = $form->get('roles')->getData();
            $user->setRoles([$selectedRole]);

            $userRepository->save($user, true);

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            return $this->redirectToRoute('users_list');
        }

        return $this->render('user/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users/{id}/edit', name: 'user_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function editAction(User $user, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, Request $request): \Symfony\Component\HttpFoundation\RedirectResponse|Response
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);
            $selectedRole = $form->get('roles')->getData();
            $user->setRoles([$selectedRole]);

            $userRepository->save($user, true);

            $this->addFlash('success', "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('users_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }

    #[Route('/users/{id}/delete', name: 'user_delete')]
    public function deleteAction(User $user, UserRepository $userRepository): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $userRepository->remove($user, true);

        $this->addFlash('success', "L'utilisateur a bien été supprimé.");

        return $this->redirectToRoute('users_list');
    }
}
