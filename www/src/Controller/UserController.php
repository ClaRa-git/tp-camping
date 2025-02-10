<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;


final class UserController extends AbstractController
{
    /**
     * Méthode permettant d'afficher la liste des utilisateurs
     * @Route("/", name="app_user_index", methods={"GET"})
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/admin/profile', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $roleAdmin = $userRepository->findAllAdmins();
        $roleUser = $userRepository->findAllUsers();

        return $this->render('user/index.html.twig', [
            'roleAdmin' => $roleAdmin,
            'roleUser' => $roleUser
        ]);
    }

    /**
     * Méthode permettant de créer un nouvel utilisateur
     * @Route("/new", name="app_user_new", methods={"GET","POST"})
     * @param Request $request
     * @param UserPasswordHasherInterface $userPasswordHasherInterface
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/admin/profile/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserPasswordHasherInterface $userPasswordHasherInterface, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roles = $form->get('roles')->getData();
            // Si le tableau contient ROLE_ADMIN et ROLE_USER, on retourne un tableau avec uniquement ROLE_ADMIN
            if (in_array('ROLE_ADMIN', $user->getRoles()) && in_array('ROLE_USER', $user->getRoles())) {
                $roles = ['ROLE_ADMIN'];
            }
            $user->setRoles($roles);

            // On récupère la valeur du champ plainPassword du formulaire
            $plainPassword = $form->get('plainPassword')->getData();
            // On hash le mot de passe
            $user->setPassword($userPasswordHasherInterface->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * Méthode permettant d'afficher un utilisateur
     * @Route("/{id}", name="app_user_show", methods={"GET"})
     * @param User $user
     * @return Response
     */
    #[Route('/profile/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Méthode permettant de modifier un utilisateur
     * @Route("/{id}/edit", name="app_user_edit", methods={"GET","POST"})
     * @param Request $request
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/profile/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * Méthode permettant de supprimer un utilisateur
     * @Route("/{id}", name="app_user_delete", methods={"POST"})
     * @param Request $request
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('admin/profile/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
