<?php

namespace App\Controller;

use App\Entity\Availability;
use App\Entity\Reservation;
use App\Form\ConfirmType;
use App\Form\ReservationType;
use App\Repository\AvailabilityRepository;
use App\Repository\ReservationRepository;
use App\Repository\SeasonRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/reservation')]
final class ReservationController extends AbstractController
{
    /**
     * Méthode qui affiche la liste des réservations
     * @Route("/client/reservation", name="app_reservation_index", methods={"GET"})
     * @param ReservationRepository $reservationRepository
     * @return Response
     */
    #[Route(name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $reservationsOld = $reservationRepository->getAllInfosOld();
        $reservationsFuture = $reservationRepository->getAllInfosFuture();
        $reservationsNow = $reservationRepository->getAllInfosNow();

        return $this->render('reservation/admin/index.html.twig', [
            'reservationsOld' => $reservationsOld,
            'reservationsFuture' => $reservationsFuture,
            'reservationsNow' => $reservationsNow
        ]);
    }

    /**
     * Méthode qui permet de créer une nouvelle réservation
     * @Route("/client/reservation/new", name="app_reservation_new", methods={"GET", "POST"})
     * @param Request $request
     * @param SeasonRepository $seasonRepository
     * @param AvailabilityRepository $availabilityRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SeasonRepository $seasonRepository, AvailabilityRepository $availabilityRepository,EntityManagerInterface $entityManager): Response  
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Vérification du bouton cliqué
            $clickedButton = $form->getClickedButton();
            $action = $clickedButton ? $clickedButton->getName() : null;

            // Récupération des dates
            $dateStart = $form->get('dateStart')->getData();
            $dateEnd = $form->get('dateEnd')->getData();
            $rental = $form->get('rental')->getData();
            $seasonsClosed = $seasonRepository->findSeasonsClosed();
            $availabilities = $availabilityRepository->findAvailabilitiesByRental($rental->getId());
            
            // On vérifie si le locatif est disponible à ces dates
            if(!empty($availabilities)){
                foreach ($availabilities as $availability) {
                    if (($dateStart >= $availability->getDateStart() && $dateStart <= $availability->getDateEnd()) || 
                        ($dateEnd >= $availability->getDateStart() && $dateEnd <= $availability->getDateEnd())) {
                        $this->addFlash('danger', 'Les dates sélectionnées ne sont pas disponibles !');
                        return $this->redirectToRoute('app_reservation_new');
                    }
                }
            }

            // On vérifie si le camping est ouvert à ces dates
            foreach ($seasonsClosed as $season) {
                if ($dateStart >= $season->getDateStart() && $dateEnd <= $season->getDateEnd()) {
                    $this->addFlash('danger', 'Le camping est fermé du ' . $season->getDateStart()->format('d/m/Y') . ' au ' . $season->getDateEnd()->format('d/m/Y') . ' !');
                    return $this->redirectToRoute('app_reservation_new');
                }
            }

            // On vérifie si la date sélectionnée est supérieure à la date du jour
            if ($dateStart < new \DateTime('now')) {
                $this->addFlash('danger', 'La date de début doit être supérieure à la date du jour !');
                return $this->redirectToRoute('app_reservation_new');
            }

            // On vérifie si la date de fin est supérieure à la date de début
            if ($dateEnd < $dateStart) {
                $this->addFlash('danger', 'La date de fin doit être supérieure à la date de début !');
                return $this->redirectToRoute('app_reservation_new');
            }

            // On vérifie si le locatif est disponible à ces dates
            

            // On vérifie si le nombre de personnes est supérieur à la capacité du locatif
            $nbPersons = $form->get('adultsNumber')->getData() + $form->get('kidsNumber')->getData();
            if ($nbPersons > $rental->getBedding()) {
                $this->addFlash('danger', 'Le nombre de personnes est supérieur à la capacité du locatif !');
                return $this->redirectToRoute('app_reservation_new');
            }

            // On vérifie si le locatif est disponible à ces dates
            $reservations = $rental->getReservations();
            foreach ($reservations as $res) {
                if (($dateStart >= $res->getDateStart() && $dateStart <= $res->getDateEnd()) || 
                    ($dateEnd >= $res->getDateStart() && $dateEnd <= $res->getDateEnd())) {
                    $this->addFlash('danger', 'Les dates sélectionnées ne sont pas disponibles !');
                    return $this->redirectToRoute('app_reservation_new');
                }
            }

            // Calcul du prix
            $totalPrice = $this->calculateTotalPrice($reservation, $seasonRepository);
            $reservation->setPrice($totalPrice);

            if ($action === 'calculate') {
                // Affichage du prix sans enregistrement
                return $this->render('reservation/admin/new.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form->createView(),
                    'price' => $totalPrice // Envoi du prix au template
                ]);
            }

            if ($action === 'confirm' && $form->isValid()) {
                // Enregistrement de la réservation
                $entityManager->persist($reservation);
                $entityManager->flush();

                $this->addFlash('success', 'Réservation confirmée avec succès !');
                return $this->redirectToRoute('app_reservation_index');
            }
        }

        return $this->render('reservation/admin/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);
    }


    /**
     * Méthode qui affiche les détails d'une réservation
     * @Route("/client/{id}", name="app_reservation_show", methods={"GET"})
     * @param Reservation $reservation
     * @return Response
     */
    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation, UserRepository $userRepository): Response
    {
        $client = $userRepository->find($reservation->getUser());

        return $this->render('reservation/admin/show.html.twig', [
            'reservation' => $reservation,
            'client' => $client
        ]);
    }

    /**
     * Méthode qui permet de modifier une réservation
     * @Route("/admin/{id}/edit", name="app_reservation_edit", methods={"GET", "POST"})
     * @param Request $request
     * @param Reservation $reservation
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/admin/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    /**
     * Méthode qui permet de supprimer une réservation
     * @Route("/admin/{id}", name="app_reservation_delete", methods={"POST"})
     * @param Request $request
     * @param Reservation $reservation
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Méthode pour calculer le prix totla d'une réservation
     * @param Reservation $reservation
     * @param SeasonRepository $seasonRepository
     * @return int
     */
    public function calculateTotalPrice(Reservation $reservation, SeasonRepository $seasonRepository): int
    {
        $total = 0;

        $dateStart = $reservation->getDateStart();
        $dateEnd = $reservation->getDateEnd();

        $seasons = $seasonRepository->findSeasonsBetweenDates($dateStart, $dateEnd);

        foreach ($seasons as $season) {
            // On détermine la période de la saison qui chevauche la réservation
            // Pour avoir le nombre de jours correct de la période on met le temps à 0
            $start = $dateStart->setTime(0,0,0) > $season->getDateStart()->setTime(0,0,0) ? $dateStart->setTime(0,0,0) : $season->getDateStart()->setTime(0,0,0);
            $end = $dateEnd->setTime(0,0,0) < $season->getDateEnd()->setTime(0,0,0) ? $dateEnd->setTime(0,0,0) : $season->getDateEnd()->setTime(0,0,0);

            // On calcule le nombre de jours de la période
            $days = $start->diff($end)->days + 1;

            // On calcule le prix total de la réservation
            $total += ($days * $season->getPercentage() * $reservation->getRental()->getType()->getPrice())/100;
        }

        return $total;
    }
}
