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
    // Constantes pour définir si l'utilisateur est actif ou non
    private const ACTIVE = 1;
    private const INACTIVE = 0;

    /**
     * Méthode permettant d'afficher la liste des utilisateurs
     * @Route("/", name="app_user_index", methods={"GET"})
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/admin/client', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        // Récupération des utilisateurs ayant le rôle ROLE_ADMIN
        $roleAdmin = $userRepository->findAllAdmins();
        // Récupération des utilisateurs ayant le rôle ROLE_USER
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
    #[Route('/admin/client/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserPasswordHasherInterface $userPasswordHasherInterface, EntityManagerInterface $entityManager): Response
    {
        // Création d'un nouvel utilisateur
        $user = new User();

        // Création du formulaire,ajout des options is_edit et is_admin et traitement de la requête
        $form = $this->createForm(UserType::class, $user, ['is_edit' => false, 'is_admin' => true]);
        $form->handleRequest($request);

        // Vérification de la soumission du formulaire et de sa validité
        if ($form->isSubmitted() && $form->isValid()) {
            $roles = $form->get('roles')->getData();
            // Si le tableau contient ROLE_ADMIN et ROLE_USER, renvoi d'un tableau avec uniquement ROLE_ADMIN
            if (in_array('ROLE_ADMIN', $user->getRoles()) && in_array('ROLE_USER', $user->getRoles())) {
                $roles = ['ROLE_ADMIN'];
            }
            $user->setRoles($roles);

            // Récupération de la valeur du champ plainPassword du formulaire
            $plainPassword = $form->get('plainPassword')->getData();
            // Hash du mot de passe
            $user->setPassword($userPasswordHasherInterface->hashPassword($user, $plainPassword));

            // Sauvegarde de l'utilisateur
            $entityManager->persist($user);
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'L\'utilisateur a bien été créé.');

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
    #[Route('/client/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(int $id, UserRepository $userRepository): Response
    {
        // Vérification de l'existence de l'utilisateur
        $user = $userRepository->find($id);

        if (!$user) {
            return $this->redirectToRoute('app_user_show', ['id' => $this->getUser()->getId()], Response::HTTP_SEE_OTHER);
        }

        // Si l'id ne correspond pas à l'id de l'utilisateur connecté, redirection vers la page de profil de l'utilisateur connecté
        if ($user->getId() !== $this->getUser()->getId() && !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_user_show', ['id' => $this->getUser()->getId()], Response::HTTP_SEE_OTHER);
        }

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
    #[Route('/client/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // Vérification de l'existence de l'utilisateur
        $user = $userRepository->find($id);

        if (!$user) {
            return $this->redirectToRoute('app_user_show', ['id' => $this->getUser()->getId()], Response::HTTP_SEE_OTHER);
        }

        // Si l'id ne correspond pas à l'id de l'utilisateur connecté, redirection vers la page de profil de l'utilisateur connecté
        if ($user->getId() !== $this->getUser()->getId() && !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_user_show', ['id' => $this->getUser()->getId()], Response::HTTP_SEE_OTHER);
        }

        // Vérification si l'utilisateur connecté est un admin
        $is_admin = in_array('ROLE_ADMIN', $this->getUser()->getRoles());

        // Création du formulaire, ajout des options is_edit et is_admin et traitement de la requête
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true, 'is_admin' => $is_admin]);
        $form->handleRequest($request);

        // Récupération des rôles sélectionnés
        $user->setRoles(array_values($form->getData()->getRoles()));

        // Vérification de la soumission du formulaire et de sa validité
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrement de la modification de l'utilisateur
            $entityManager->flush();
            
            // Si l'utilisateur connecté est un admin, redirection vers la liste des utilisateurs
            if ($is_admin) {
                // Message de succès
                $this->addFlash('success', 'L\'utilisateur a bien été modifié.');

                return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
            }
            // Sinon, redirection vers la page de profil de l'utilisateur
            else {
                // Message de succès
                $this->addFlash('success', 'Votre profil a bien été modifié.');
                            
                return $this->redirectToRoute('app_user_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * Méthode permettant de désactiver un utilisateur
     * @Route("/{id}", name="app_user_desactivate", methods={"POST"})
     * @param Request $request
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('admin/client/{id}', name: 'app_user_desactivate', methods: ['POST'])]
    public function desactivate(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Vérification du jeton CSRF
        if ($this->isCsrfTokenValid('desactivate' . $user->getId(), $request->getPayload()->getString('_token'))) {
            // Désactivation de l'utilisateur
            $user->setIsActive(self::INACTIVE);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Méthode permettant d'activer un utilisateur
     * @Route("/{id}/activate", name="app_user_activate", methods={"POST"})
     * @param Request $request
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('admin/client/{id}/activate', name: 'app_user_activate', methods: ['POST'])]
    public function activate(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Vérification du jeton CSRF
        if ($this->isCsrfTokenValid('activate' . $user->getId(), $request->getPayload()->getString('_token'))) {
            // Activation de l'utilisateur
            $user->setIsActive(self::ACTIVE);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
