<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationClientType;
use App\Repository\AvailabilityRepository;
use App\Repository\RentalRepository;
use App\Repository\ReservationRepository;
use App\Repository\SeasonRepository;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/reservation/client')]
class ReservationClientController extends AbstractController
{
    // Constantes pour les statuts des réservations
    const STATUS_REFUSED = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_PENDING = 2;

    /**
     * Méthode permettant d'afficher la page de réservation pour un client
     * @Route("/reservation/client", name="app_reservation_client_index")
     * @return Response
     */
    #[Route(name: 'app_reservation_client_index')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        // On récupère les réservations du client
        $reservations = $reservationRepository->getAllInfosByIdUser($this->getUser()->getId());

        return $this->render('reservation/client/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    /**
     * Méthode qui permet de créer une nouvelle réservation
     * @Route("/client/reservation/new", name="app_reservation_new", methods={"GET", "POST"})
     * @param Request $request
     * @param SeasonRepository $seasonRepository
     * @param RentalRepository $rentalRepository
     * @param TypeRepository $typeRepository
     * @param AvailabilityRepository $availabilityRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/new/{id}', name: 'app_reservation_client_new', methods: ['GET', 'POST'])]
    public function new(int $id, Request $request, SeasonRepository $seasonRepository, RentalRepository $rentalRepository, TypeRepository $typeRepository, AvailabilityRepository $availabilityRepository,EntityManagerInterface $entityManager): Response  
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationClientType::class, $reservation);
        $form->handleRequest($request);

        // On récupère l'utilisateur connecté
        $reservation->setUser($this->getUser());

        // On affecte la location à la réservation
        $rental = $rentalRepository->find($id);

        if ($form->isSubmitted()) {
            // Vérification du bouton cliqué
            $clickedButton = $form->getClickedButton();
            $action = $clickedButton ? $clickedButton->getName() : null;

            // Récupération des dates
            $dateStart = $form->get('dateStart')->getData();
            $dateEnd = $form->get('dateEnd')->getData();

            // On récupère la location grâce à l'id
            $rental = $rentalRepository->find($id);

            // On récupère les saisons fermées
            $seasonsClosed = $seasonRepository->findSeasonsClosed();

            // On récupère les non disponibilités du locatif
            $availabilities = $availabilityRepository->findAvailabilitiesByRental($rental->getId());

            // On vérifie si le camping est ouvert à ces dates
            dd($dateStart, $dateEnd);

            foreach ($seasonsClosed as $season) {
                if (($dateStart >= $season->getDateStart() && $dateStart <= $season->getDateStart()) ||  ($dateEnd <= $season->getDateEnd()  && $dateEnd >= $season->getDateEnd())) {
                    $this->addFlash('danger', 'Le camping est fermé du ' . $season->getDateStart()->format('d/m/Y') . ' au ' . $season->getDateEnd()->format('d/m/Y') . ' !');
                    return $this->redirectToRoute('app_reservation_client_new', ['id' => $id]);
                }
            }

            // On vérifie si la date sélectionnée est supérieure à la date du jour
            $now = new \DateTime('now');
            $now->setTime(0,0,0);
            $dateSZT = $dateStart->setTime(0,0,0);
            $dateEZT = $dateEnd->setTime(0,0,0);
            if ($dateSZT < $now) {
                $this->addFlash('danger', 'La date de début doit être supérieure à la date du jour !');
                return $this->redirectToRoute('app_reservation_client_new', ['id' => $id]);
            }

            // On vérifie si la date de fin est supérieure à la date de début
            if ($dateEZT <= $dateSZT) {
                $this->addFlash('danger', 'La date de fin doit être supérieure à la date de début !');
                return $this->redirectToRoute('app_reservation_client_new', ['id' => $id]);
            }            

            $nbAdults = $form->get('adultsNumber')->getData();
            $nbKids = $form->get('kidsNumber')->getData();
            // On vérifie si le nombre d'adultes et d'enfants rentré est valide
            if ($nbAdults <= 0 || $nbKids < 0) {
                $this->addFlash('danger', 'Le nombre de personnes doit être supérieur à 0 pour les adultes et supérieur ou égal à 0 pour les enfants !');
                return $this->redirectToRoute('app_reservation_client_new', ['id' => $id]);
            }

            $nbPersons = $form->get('adultsNumber')->getData() + $form->get('kidsNumber')->getData();

            // On vérifie si le nombre de personnes est supérieur à la capacité du locatif
            if ($nbPersons > $rental->getBedding()) {
                $this->addFlash('danger', 'Le nombre de personnes est supérieur à la capacité du locatif !');
                return $this->redirectToRoute('app_reservation_client_new', ['id' => $id]);
            }

            // On vérifie si le locatif est disponible à ces dates (disponibilités)
            if(!empty($availabilities)){
                foreach ($availabilities as $availability) {
                    if (($dateStart >= $availability['dateStart']->setTime(0,0,0) && $dateStart <= $availability['dateEnd']->setTime(0,0,0)) || 
                        ($dateEnd >= $availability['dateStart']->setTime(0,0,0) && $dateEnd <= $availability['dateEnd']->setTime(0,0,0))) {
                        $this->addFlash('danger', 'Les dates sélectionnées ne sont pas disponibles !');
                        return $this->redirectToRoute('app_reservation_client_new', ['id' => $id]);
                    }
                }
            }

            // On vérifie si le locatif est disponible à ces dates (réservations)
            $reservations = $rental->getReservations();
            foreach ($reservations as $res) {
                if (($dateStart >= $res->getDateStart()->setTime(0,0,0) && $dateStart <= $res->getDateEnd()->setTime(0,0,0)) || 
                    ($dateEnd >= $res->getDateStart()->setTime(0,0,0) && $dateEnd <= $res->getDateEnd()->setTime(0,0,0))) {
                    $this->addFlash('danger', 'Les dates sélectionnées ne sont pas disponibles !');
                    return $this->redirectToRoute('app_reservation_client_new', ['id' => $id]);
                }
            }

            // On remplit la réservation
            $reservation->setRental($rental);
            $reservation->setDateStart($form->get('dateStart')->getData());
            $reservation->setDateEnd($form->get('dateEnd')->getData());
            $rental = $reservation->getRental();
            $type = $typeRepository->getTypeForRental($rental->getId());
            $rental->setType($type);
            $reservation->setRental($rental);
            $reservation->setStatus(self::STATUS_CONFIRMED);
            
            // Calcul du prix
            $totalPrice = $this->calculateTotalPrice($reservation, $seasonRepository);
            $reservation->setPrice($totalPrice);

            // Si le bouton cliqué est "Calculer"
            if ($action === 'calculate') {
                // Affichage du prix sans enregistrement
                return $this->render('reservation/client/new.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form->createView(),
                    'price' => $totalPrice // Envoi du prix au template
                ]);
            }

            // Si le bouton cliqué est "Confirmer" et que le formulaire est valide
            if ($action === 'confirm' && $form->isValid()) {
                // Enregistrement de la réservation
                $entityManager->persist($reservation);
                $entityManager->flush();

                $this->addFlash('success', 'Réservation confirmée avec succès !');
                return $this->redirectToRoute('app_reservation_client_index');
            }
        }

        return $this->render('reservation/client/new.html.twig', [
            'reservation' => $reservation,
            'rental' => $rental,
            'form' => $form->createView(),
        ]);
    }


    /**
     * Méthode qui affiche les détails d'une réservation
     * @Route("/client/{id}", name="app_reservation_show", methods={"GET"})
     * @param Reservation $reservation
     * @return Response
     */
    #[Route('/{id}', name: 'app_reservation_client_show', methods: ['GET'])]
    public function show(Reservation $reservation, UserRepository $userRepository): Response
    {
        $isCancelable = false;

        // On vérifie si la réservation est annulable
        $dateStart = $reservation->getDateStart();
        $now = new \DateTime('now');

        if ($dateStart->diff($now)->days >= 2) {
            $isCancelable = true;
        }

        return $this->render('reservation/client/show.html.twig', [
            'reservation' => $reservation,
            'isCancelable' => $isCancelable,
        ]);
    }

    /**
     * Méthode qui permet d'annuler une réservation
     * @Route("/{id}", name="app_reservation_delete", methods={"POST"})
     * @param Request $request
     * @param Reservation $reservation
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}', name: 'app_reservation_client_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->getPayload()->getString('_token'))) {
            $reservation->setStatus(self::STATUS_REFUSED);
            $entityManager->flush();
        }

        $this->addFlash('success', 'Réservation annulée avec succès !');
        return $this->redirectToRoute('app_reservation_client_index', [], Response::HTTP_SEE_OTHER);
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

        $dateStart = $reservation->getDateStart()->setTime(0,0,0);
        $dateEnd = $reservation->getDateEnd()->setTime(0,0,0);

        // On récupère les saisons qui chevauchent la réservation
        $seasons = $seasonRepository->findSeasonsBetweenDates($dateStart, $dateEnd);

        // Si pas de saison trouvée, on calcule le prix sans saison
        if (empty($seasons)) {
            $days = $dateStart->diff($dateEnd)->days + 1;
            $total = ($days * $reservation->getRental()->getType()->getPrice());
        }
        else
        {
            foreach ($seasons as $season) {
                // On détermine la période de la saison qui chevauche la réservation
                // Pour avoir le nombre de jours correct de la période on met le temps à 0
                $start = $dateStart > $season->getDateStart()->setTime(0,0,0) ? $dateStart : $season->getDateStart()->setTime(0,0,0);
                $end = $dateEnd < $season->getDateEnd()->setTime(0,0,0) ? $dateEnd : $season->getDateEnd()->setTime(0,0,0);
    
                // On calcule le nombre de jours de la période
                $days = $start->diff($end)->days + 1;
    
                // On calcule le prix total de la réservation
                $total += ($days * $season->getPercentage() * $reservation->getRental()->getType()->getPrice())/100;
            }
    
        }
        
        return $total;
    }
}
