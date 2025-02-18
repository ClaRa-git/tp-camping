<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{    
    // Constantes pour définir si l'utilisateur est actif ou non
    private const ACTIVE = 1;
    private const INACTIVE = 0;

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        // Si un utilisateur est déjà connecté, redirection vers la page d'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        // Création d'un nouvel utilisateur
        $user = new User();

        // Création du formulaire d'inscription et traitement de la requête
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Ajout du rôle ROLE_USER à l'utilisateur peut importe son futur rôle
        $user->setRoles(['ROLE_USER']);
        
        // Vérification de la soumission du formulaire et de sa validité
        if ($form->isSubmitted() && $form->isValid()) {

            // Vérification du format de l'email
            if (!$this->isValidEmail($user->getEmail())) {
                // Message d'erreur
                $this->addFlash('danger', 'L\'adresse email n\'est pas valide.');
                
                return $this->redirectToRoute('app_register', [], Response::HTTP_SEE_OTHER);
            }

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            // Vérification du format du mot de passe
            if (!$this->isValidPassword($plainPassword)) {
                // Message d'erreur
                $this->addFlash('danger', 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.');
                
                return $this->redirectToRoute('app_register', [], Response::HTTP_SEE_OTHER);
            }
            // Encodage du mot de passe
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Mise à ACTIVE de l'utilisateur
            $user->setIsActive(self::ACTIVE);

            // Enregistrement de l'utilisateur
            $entityManager->persist($user);
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Votre compte a bien été créé !');

            return $security->login($user, 'form_login', 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    /**
     * Méthode qui vérifie le format de l'email
     * @param string $email
     * @return bool
     */
    function isValidEmail($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Méthode qui vérifie que le mdp contient au moins 8 caractères, une majuscule, une minuscule et un chiffre
     * @param string $password
     * @return bool
     */
    function isValidPassword($password): bool
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/', $password);
    }
}
