<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\AvailabilityRepository;
use App\Repository\RentalRepository;
use App\Repository\ReservationRepository;
use App\Repository\SeasonRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReservationController extends AbstractController
{
    // Constantes pour les statuts des réservations
    const STATUS_REFUSED = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_PENDING = 2;

    /**
     * Méthode qui affiche la liste des réservations
     * @Route("/client/reservation", name="app_reservation_index", methods={"GET"})
     * @param ReservationRepository $reservationRepository
     * @return Response
     */
    #[Route('/admin/reservation', name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        // Récupération de toutes les réservations
        $reservations = $reservationRepository->getAllInfos();

        return $this->render('reservation/admin/index.html.twig', [
            'reservations' => $reservations
        ]);
    }

    /**
     * Méthode qui permet de créer une nouvelle réservation
     * @Route("/client/reservation/new", name="app_reservation_new", methods={"GET", "POST"})
     * @param Request $request
     * @param RentalRepository $rentalRepository
     * @param SeasonRepository $seasonRepository
     * @param AvailabilityRepository $availabilityRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/admin/reservation/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, RentalRepository $rentalRepository, SeasonRepository $seasonRepository, AvailabilityRepository $availabilityRepository, EntityManagerInterface $entityManager): Response
    {
        // Création d'une nouvelle réservation
        $reservation = new Reservation();

        // Création du formulaire de réservation et traitement de la requête
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        // Vérification de la soumission du formulaire
        if ($form->isSubmitted()) {
            // Vérification du bouton cliqué
            $clickedButton = $form->getClickedButton();
            // Récupération du nom du bouton cliqué
            $action = $clickedButton ? $clickedButton->getName() : null;

            // Récupération des données du formulaire
            $dateStart = $form->get('dateStart')->getData();
            $dateEnd = $form->get('dateEnd')->getData();
            $rental = $form->get('rental')->getData();
            $isActive = $form->get('isActive')->getData();
            $seasonsClosed = $seasonRepository->findSeasonsClosed();
            $availabilities = $availabilityRepository->findAvailabilitiesByRental($rental->getId());

            // Vérification de si le locatif est actif
            if (!$isActive) {
                // Message d'erreur
                $this->addFlash('danger', 'Le locatif est inactif !');

                return $this->redirectToRoute('app_reservation_new');
            }

            // vérification de si le camping est ouvert à ces dates
            foreach ($seasonsClosed as $season) {
                if (($dateStart >= $season->getDateStart() && $dateStart <= $season->getDateEnd()) ||
                    ($dateEnd <= $season->getDateEnd()  && $dateEnd >= $season->getDateStart())
                ) {
                    // Message d'erreur
                    $this->addFlash('danger', 'Le camping est fermé du ' . $season->getDateStart()->format('d/m/Y') . ' au ' . $season->getDateEnd()->format('d/m/Y') . ' !');

                    return $this->redirectToRoute('app_reservation_new');
                }
            }

            // Vérification de si la date sélectionnée est supérieure à la date du jour
            $now = new \DateTime('now');
            $now->setTime(0, 0, 0);

            // Mise à zéro de l'heure pour la comparaison pour éviter les problèmes d'heure
            $dateSZT = $dateStart->setTime(0, 0, 0);
            $dateEZT = $dateEnd->setTime(0, 0, 0);
            if ($dateSZT < $now) {
                // Message d'erreur
                $this->addFlash('danger', 'La date de début doit être supérieure à la date du jour !');

                return $this->redirectToRoute('app_reservation_new');
            }

            // Vérification de si la date de fin est supérieure à la date de début
            if ($dateEZT <= $dateSZT) {
                // Message d'erreur
                $this->addFlash('danger', 'La date de fin doit être supérieure à la date de début !');

                return $this->redirectToRoute('app_reservation_new');
            }

            // Vérification du nombre de personnes
            $nbAdults = $form->get('adultsNumber')->getData();
            $nbKids = $form->get('kidsNumber')->getData();
            // Vérification de si le nombre d'adultes et d'enfants rentré est valide
            if ($nbAdults <= 0 || $nbKids < 0) {
                // Message d'erreur
                $this->addFlash('danger', 'Le nombre de personnes doit être supérieur à 0 pour les adultes et supérieur ou égal à 0 pour les enfants !');

                return $this->redirectToRoute('app_reservation_new');
            }

            $nbPersons = $form->get('adultsNumber')->getData() + $form->get('kidsNumber')->getData();
            // Vérification de si le nombre de personnes est supérieur à la capacité du locatif
            if ($nbPersons > $rental->getBedding()) {
                // Message d'erreur
                $this->addFlash('danger', 'Le nombre de personnes est supérieur à la capacité du locatif !');

                return $this->redirectToRoute('app_reservation_new');
            }

            // Vérification de si le locatif est disponible à ces dates (disponibilités)
            if (!empty($availabilities)) {
                foreach ($availabilities as $availability) {
                    if ((($dateStart >= $availability['dateStart']) && $dateStart <= $availability['dateEnd']) ||
                        ($dateEnd >= $availability['dateStart'] && $dateEnd <= $availability['dateEnd'])
                    ) {
                        // Message d'erreur
                        $this->addFlash('danger', 'Les dates sélectionnées ne sont pas disponibles !');

                        return $this->redirectToRoute('app_reservation_new');
                    }
                }
            }

            // Vérification de si le locatif est disponible à ces dates (réservations)
            $reservations = $rentalRepository->findReservationsByRental($rental->getId());
            foreach ($reservations as $res) {
                if (($dateStart >= $res->getDateStart() && $dateStart <= $res->getDateEnd()) ||
                    ($dateEnd >= $res->getDateStart() && $dateEnd <= $res->getDateEnd())
                ) {
                    // Message d'erreur
                    $this->addFlash('danger', 'Les dates sélectionnées ne sont pas disponibles !');

                    return $this->redirectToRoute('app_reservation_new');
                }
            }

            // Calcul du prix
            $totalPrice = $this->calculateTotalPrice($reservation, $seasonRepository);
            $reservation->setPrice($totalPrice);
            $reservation->setStatus(self::STATUS_CONFIRMED);

            // Vérification de si l'action est de calculer le prix ou de confirmer la réservation
            if ($action === 'calculate') {
                // Message de succès
                $this->addFlash('success', 'Prix calculé avec succès !');

                // Affichage du prix sans enregistrement
                return $this->render('reservation/admin/new.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form->createView(),
                    'price' => $totalPrice // Envoi du prix au template
                ]);
            }

            // Enregistrement de la réservation
            if ($action === 'confirm' && $form->isValid()) {
                // Enregistrement de la réservation
                $entityManager->persist($reservation);
                $entityManager->flush();

                // Message de succès
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
     * @param ReservationRepository $reservationRepository
     * @param int $id
     * @return Response
     */
    #[Route('/admin/reservation/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(ReservationRepository $reservationRepository, int $id, UserRepository $userRepository): Response
    {
        // Vérification de l'existence de la réservation
        $reservation = $reservationRepository->find($id);
        if (!$reservation) {
            // Si la réservation n'existe pas, redirection vers la liste des réservations
            $this->addFlash('danger', 'La réservation n\'existe pas !');

            return $this->redirectToRoute('app_reservation_client_index');
        }

        // Récupération du client
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
     * @param int $id
     * @param ReservationRepository $reservationRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/admin/reservation/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, ReservationRepository $reservationRepository, EntityManagerInterface $entityManager): Response
    {
        // Vérification de l'existence de la réservation
        $reservation = $reservationRepository->find($id);
        if (!$reservation) {
            // Si la réservation n'existe pas, redirection vers la liste des réservations
            $this->addFlash('danger', 'La réservation n\'existe pas !');

            return $this->redirectToRoute('app_reservation_client_index');
        }

        // Création du formulaire de réservation et traitement de la requête
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        // Vérification de la soumission du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrement de la modification de la réservation
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Réservation modifiée avec succès !');

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/admin/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    /**
     * Méthode qui permet d'annuler une réservation
     * @Route("/admin/{id}", name="app_reservation_cancel", methods={"POST"})
     * @param Request $request
     * @param int $id
     * @param ReservationRepository $reservationRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/admin/reservation/{id}', name: 'app_reservation_cancel', methods: ['POST'])]
    public function cancel(Request $request, int $id, ReservationRepository $reservationRepository, EntityManagerInterface $entityManager): Response
    {
        // Vérification de l'existence de la réservation
        $reservation = $reservationRepository->find($id);
        if (!$reservation) {
            // Si la réservation n'existe pas, redirection vers la liste des réservations
            $this->addFlash('danger', 'La réservation n\'existe pas !');

            return $this->redirectToRoute('app_reservation_client_index');
        }

        // Vérification du jeton CSRF
        if ($this->isCsrfTokenValid('cancel' . $reservation->getId(), $request->getPayload()->getString('_token'))) {
            // Annulation de la réservation
            $reservation->setStatus(self::STATUS_REFUSED);
            $entityManager->flush();
        }

        // Message de succès
        $this->addFlash('success', 'Réservation annulée avec succès !');

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Méthode permettant d'afficher la liste des réservations par filtre
     * @Route("/filter/{filter}", name="app_reservation_filter", methods={"GET"})
     * @param ReservationRepository $reservationRepository
     * @param string $filter
     * @return Response
     */
    #[Route('/admin/reservation/filter/{filter}', name: 'app_reservation_filter', methods: ['GET'])]
    public function filter(ReservationRepository $reservationRepository, string $filter): Response
    {
        // Récupération des réservations par filtre
        $reservations = $reservationRepository->getReservationsByFilter($filter);

        return $this->render('reservation/admin/index.html.twig', [
            'title' => 'Liste des réservations',
            'reservations' => $reservations
        ]);
    }

    /**
     * Méthode permettant d'envoyer des données en JSON
     * @Route("/js", name="app_reservation_json", methods={"GET"})
     * @param ReservationRepository $reservationRepository
     * @return JsonResponse
     */
    #[Route('/reservation/api/js', name: 'app_reservation_json', methods: ['GET'])]
    public function getTodayReservations(ReservationRepository $reservationRepository): JsonResponse
    {
        // Récupération de la date du jour et des réservations correspondantes
        //$date = new \DateTime();
        $date = new \DateTime('2025-05-15');
        $reservationStart = $reservationRepository->findByDateStart($date);
        $reservationEnd = $reservationRepository->findByDateEnd($date);

        return new JsonResponse([
            'reservationsStart' => $reservationStart,
            'reservationsEnd' => $reservationEnd
        ]);
    }

    /**
     * Méthode permettant de récupérer les données envoyées par l'appli JS
     * @Route("/js/{id}", name="app_reservation_json_update", methods={"PATCH"})
     * @param Request $request
     * @param int $id
     * @param ReservationRepository $reservationRepository
     * @return JsonResponse
     */
    #[Route('/reservation/api/js/{id}', name: 'update_reservation_update', methods: ['PATCH'])]
    public function patchIsClean(int $id, Request $request, RentalRepository $rentalRepository): JsonResponse
    {
        // Trouver la location à partir de l'id
        $rental = $rentalRepository->find($id);

        // Vérifier si la location existe
        if (!$rental) {
            return new JsonResponse(["message" => "Réservation non trouvée"], 404);
        }

        // Récupérer les données de la requête (ici, on ne prend que le champ isClean)
        $data = json_decode($request->getContent(), true);

        // Vérifier que isClean est bien présent dans les données
        if (!isset($data['isClean'])) {
            return new JsonResponse(["message" => "Le champ 'isClean' est requis"], 400);
        }

        // Mettre à jour le champ isClean
        $rental->setIsClean($data['isClean']);

        // Sauvegarder les changements en base de données
        $entityManager = $rentalRepository->getEntityManager();
        $entityManager->flush();

        // Retourner la réponse avec un message de succès
        return new JsonResponse(["message" => "Propreté mise à jour"], 200);
    }

    /**
     * Méthode pour calculer le prix total d'une réservation
     * @param Reservation $reservation
     * @param SeasonRepository $seasonRepository
     * @return int
     */
    public function calculateTotalPrice(Reservation $reservation, SeasonRepository $seasonRepository): int
    {
        $total = 0;

        // Mise à zéro de l'heure pour la comparaison pour éviter les problèmes d'heure
        $dateStart = $reservation->getDateStart()->setTime(0, 0, 0);
        $dateEnd = $reservation->getDateEnd()->setTime(0, 0, 0);

        // Récupération des saisons qui chevauchent la réservation
        $seasons = $seasonRepository->findSeasonsBetweenDates($dateStart, $dateEnd);

        // Si pas de saison trouvée, calcul du prix sans saison
        if (empty($seasons)) {
            $days = $dateStart->diff($dateEnd)->days + 1;
            $total = ($days * $reservation->getRental()->getType()->getPrice());
        } else {
            foreach ($seasons as $season) {
                // Détermination de la période de la saison qui chevauche la réservation
                // Pour avoir le nombre de jours correct de la période mise du temps à 0
                $start = $dateStart > $season->getDateStart()->setTime(0, 0, 0) ? $dateStart : $season->getDateStart()->setTime(0, 0, 0);
                $end = $dateEnd < $season->getDateEnd()->setTime(0, 0, 0) ? $dateEnd : $season->getDateEnd()->setTime(0, 0, 0);

                // Calcul du nombre de jours de la période
                $days = $start->diff($end)->days + 1;

                // Calcul du prix total de la réservation
                $total += ($days * $season->getPercentage() * $reservation->getRental()->getType()->getPrice()) / 100;
            }
        }

        return $total;
    }
}
