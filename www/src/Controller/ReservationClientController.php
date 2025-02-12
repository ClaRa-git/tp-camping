<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReservationClientController extends AbstractController
{
    #[Route('/reservation/client', name: 'app_reservation_client')]
    public function index(): Response
    {
        return $this->render('reservation_client/index.html.twig', [
            'controller_name' => 'ReservationClientController',
        ]);
    }
}
