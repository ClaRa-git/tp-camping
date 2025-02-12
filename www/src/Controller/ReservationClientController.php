<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReservationClientController extends AbstractController
{
    /**
     * Méthode permettant d'afficher la page de réservation pour un client
     * @Route("/reservation/client", name="app_reservation_client_index")
     * @return Response
     */
    #[Route('/reservation/client', name: 'app_reservation_client_index')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        // On récupère les réservations du client
        $reservations = $reservationRepository->getAllInfosByIdUser($this->getUser()->getId());

        return $this->render('reservation_client/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }
}
