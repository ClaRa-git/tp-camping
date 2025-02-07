<?php

namespace App\Controller;

use App\Repository\TypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index( TypeRepository $typeRepository ): Response
    {
        // Titre de la page d'accueil
        $title = "Bienvenue sur CampingFun !";

        // Récupérations des locations
        $types = $typeRepository->findAll();

        return $this->render('home/index.html.twig', [
            'title' => $title,
            'types' => $types
        ]);
    }
}
