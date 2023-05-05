<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\UserFormType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin', name: 'admin.users')]
class UsersController extends AbstractController
{
    #[Route('/users', name: '.index')]
    public function index(UserRepository $repo): Response
    {
        $users = $repo->findAll();

        return $this->render('users/index.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/user/create', name: '.create')]
    public function create(Request $request, UserRepository $repo, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();

        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setRoles([
                $form->get("roles")->getData()
            ]);

            $repo->save($user, true);

            return $this->redirectToRoute('admin.users.index');
        }

        return $this->render('users/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/user/{id}/edit', name: '.edit')]
    public function edit(Request $request, User $user, UserRepository $repo, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setRoles([
                $form->get("roles")->getData()
            ]);

            $repo->save($user, true);

            return $this->redirectToRoute('admin.users.index');
        }

        return $this->render('users/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/user/{id}/delete', name: '.delete')]
    public function delete(Request $request, User $user, UserRepository $repo): Response
    {
        $repo->remove($user, true);

        return $this->redirectToRoute('admin.users.index');
    }
}