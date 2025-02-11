<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/client')]
class ClientController extends AbstractController
{
    /**
     * Méthode qui renvoie la page d'accueil de l'utilisateur
     * @Route("/dashboard", name="app_client_dashboard")
     * @return Response
     */
    #[Route('/dashboard', name: 'app_client_dashboard')]
    public function index(): Response
    {
        return $this->render('client/dashboard.html.twig');
    }
}
