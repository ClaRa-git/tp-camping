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
     * @param int $id
     * @param Request $request
     * @param SeasonRepository $seasonRepository
     * @param RentalRepository $rentalRepository
     * @param TypeRepository $typeRepository
     * @param AvailabilityRepository $availabilityRepository
     * @param ReservationRepository $reservationRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/new/{id}', name: 'app_reservation_client_new', methods: ['GET', 'POST'])]
    public function new(int $id, Request $request, SeasonRepository $seasonRepository, RentalRepository $rentalRepository, TypeRepository $typeRepository, AvailabilityRepository $availabilityRepository, ReservationRepository $reservationRepository,EntityManagerInterface $entityManager): Response  
    {
        // Création d'une nouvelle réservation
        $reservation = new Reservation();

        // Création du formulaire et traitement de la requête
        $form = $this->createForm(ReservationClientType::class, $reservation);
        $form->handleRequest($request);

        // Récupération de l'utilisateur connecté
        $reservation->setUser($this->getUser());

        // Affectation de la location à la réservation
        $rental = $rentalRepository->find($id);

        // Si le locatif n'existe pas, redirection vers la page d'accueil
        if (!$rental) {
            // Message d'erreur
            $this->addFlash('danger', 'Le locatif n\'existe pas !');

            return $this->redirectToRoute('app_home');
        }

        // Vérification de l'activation du locatif
        if (!$rental->isActive()) {
            // Message d'erreur
            $this->addFlash('danger', 'Le locatif n\'est pas activé !');

            return $this->redirectToRoute('app_home');
        }

        // Vérification de l'activation du type
        $type = $typeRepository->getTypeForRental($rental->getId());
        if (!$type->isActive()) {
            // Message d'erreur
            $this->addFlash('danger', 'Le type de locatif n\'est pas activé !');

            return $this->redirectToRoute('app_home');
        }

        // Vérification du formulaire
        if ($form->isSubmitted()) {
            // Vérification du bouton cliqué et récupération du nom du bouton
            $clickedButton = $form->getClickedButton();
            $action = $clickedButton ? $clickedButton->getName() : null;

            // Récupération des dates
            $dateStart = $form->get('dateStart')->getData();
            $dateEnd = $form->get('dateEnd')->getData();

            // Récupération de la location grâce à son id
            $rental = $rentalRepository->find($id);

            // Récupération des saisons fermées
            $seasonsClosed = $seasonRepository->findSeasonsClosed();

            // Récupération des non disponibilités du locatif
            $availabilities = $availabilityRepository->findAvailabilitiesByRental($rental->getId());

            // Vérification de la fermeture du camping à ces dates
            foreach ($seasonsClosed as $season) {
                if (($dateStart >= $season->getDateStart() && $dateStart <= $season->getDateEnd()) ||  ($dateEnd <= $season->getDateEnd()  && $dateEnd >= $season->getDateStart())) {
                    // Message d'erreur
                    $this->addFlash('danger', 'Le camping est fermé du ' . $season->getDateStart()->format('d/m/Y') . ' au ' . $season->getDateEnd()->format('d/m/Y') . ' !');
                    
                    return $this->redirectToRoute('app_reservation_client_new', ['id' => $id]);
                }
            }

            // Vérification de si la date sélectionnée est supérieure à la date du jour
            $now = new \DateTime('now');
            $now->setTime(0,0,0);

            // Mise à zéro de l'heure pour la comparaison pour éviter les problèmes d'heure
            $dateSZT = $dateStart->setTime(0,0,0);
            $dateEZT = $dateEnd->setTime(0,0,0);

            // Vérification de si la date de début est supérieure à la date du jour
            if ($dateSZT < $now) {
                $this->addFlash('danger', 'La date de début doit être supérieure à la date du jour !');
                return $this->redirectToRoute('app_reservation_client_new', ['id' => $id]);
            }

            // Vérification de si la date de fin est supérieure à la date de début
            if ($dateEZT <= $dateSZT) {
                $this->addFlash('danger', 'La date de fin doit être supérieure à la date de début !');
                return $this->redirectToRoute('app_reservation_client_new', ['id' => $id]);
            }            

            // Vérification du nombre de personnes
            $nbAdults = $form->get('adultsNumber')->getData();
            $nbKids = $form->get('kidsNumber')->getData();

            // On vérifie si le nombre d'adultes et d'enfants rentré est valide
            if ($nbAdults <= 0 || $nbKids < 0) {
                // Message d'erreur
                $this->addFlash('danger', 'Le nombre de personnes doit être supérieur à 0 pour les adultes et supérieur ou égal à 0 pour les enfants !');
                
                return $this->redirectToRoute('app_reservation_client_new', ['id' => $id]);
            }

            $nbPersons = $form->get('adultsNumber')->getData() + $form->get('kidsNumber')->getData();

            // Vérification de si le nombre de personnes est supérieur à la capacité du locatif
            if ($nbPersons > $rental->getBedding()) {
                // Message d'erreur
                $this->addFlash('danger', 'Le nombre de personnes est supérieur à la capacité du locatif !');
                
                return $this->redirectToRoute('app_reservation_client_new', ['id' => $id]);
            }

            // Vérification de si le locatif est disponible à ces dates (disponibilités)
            if(!empty($availabilities)){
                foreach ($availabilities as $availability) {
                    if (($dateStart >= $availability['dateStart'] && $dateStart <= $availability['dateEnd']) || 
                        ($dateEnd >= $availability['dateStart'] && $dateEnd <= $availability['dateEnd'])) {
                            // Message d'erreur
                            $this->addFlash('danger', 'Les dates sélectionnées ne sont pas disponibles !');
                        
                            return $this->redirectToRoute('app_reservation_client_new', ['id' => $id]);
                    }
                }
            }

            // Vérification de si le locatif est disponible à ces dates (réservations)
            $reservations = $reservationRepository->getReservationsByRentalId($rental->getId());
            foreach ($reservations as $res) {
                if (($dateStart >= $res->getDateStart()->setTime(0,0,0) && $dateStart <= $res->getDateEnd()->setTime(0,0,0)) || 
                    ($dateEnd >= $res->getDateStart()->setTime(0,0,0) && $dateEnd <= $res->getDateEnd()->setTime(0,0,0))) {
                        // Message d'erreur
                        $this->addFlash('danger', 'Les dates sélectionnées ne sont pas disponibles !');
                        
                        return $this->redirectToRoute('app_reservation_client_new', ['id' => $id]);
                }
            }

            // Remplissage de la réservation
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
                // Message de succès
                $this->addFlash('success', 'Prix calculé avec succès !');

                // Affichage du prix sans enregistrement
                return $this->render('reservation/client/new.html.twig', [
                    'reservation' => $reservation,
                    'rental' => $rental,
                    'form' => $form->createView(),
                    'price' => $totalPrice // Envoi du prix au template
                ]);
            }

            // Si le bouton cliqué est "Confirmer" et que le formulaire est valide
            if ($action === 'confirm' && $form->isValid()) {
                // Enregistrement de la réservation
                $entityManager->persist($reservation);
                $entityManager->flush();

                // Message de succès
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
     * @param ReservationRepository $reservationRepository
     * @param int $id
     * @return Response
     */
    #[Route('/{id}', name: 'app_reservation_client_show', methods: ['GET'])]
    public function show(ReservationRepository $reservationRepository, int $id, UserRepository $userRepository): Response
    {
        // Vérification de l'existence de la réservation
        $reservation = $reservationRepository->find($id);
        if (!$reservation) {
            // Si la réservation n'existe pas, redirection vers la liste des réservations
            $this->addFlash('danger', 'La réservation n\'existe pas !');

            return $this->redirectToRoute('app_reservation_client_index');
        }

        // Vérification de si la réservation est annulable
        // Dans la vue, on n'affichera le bouton d'annulation que si la réservation est pas annulable
        $isCancelable = false;
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
     * @Route("/{id}", name="app_reservation_cancel", methods={"POST"})
     * @param Request $request
     * @param int $id
     * @param ReservationRepository $reservationRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}', name: 'app_reservation_client_cancel', methods: ['POST'])]
    public function cancel(Request $request, int $id, ReservationRepository $reservationRepository, EntityManagerInterface $entityManager): Response
    {
        // Vérification de l'existence de la réservation
        $reservation = $reservationRepository->find($id);
        if (!$reservation) {
            // Si la réservation n'existe pas, redirection vers la liste des réservations
            $this->addFlash('danger', 'La réservation n\'existe pas !');

            return $this->redirectToRoute('app_reservation_client_index');
        }

        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('cancel'.$reservation->getId(), $request->getPayload()->getString('_token'))) {
            // Annulation de la réservation
            $reservation->setStatus(self::STATUS_REFUSED);
            $entityManager->flush();
        }

        // Message de succès
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

        // Mise à zéro de l'heure pour la comparaison pour éviter les problèmes d'heure
        $dateStart = $reservation->getDateStart()->setTime(0,0,0);
        $dateEnd = $reservation->getDateEnd()->setTime(0,0,0);

        // récupération des saisons qui chevauchent la réservation
        $seasons = $seasonRepository->findSeasonsBetweenDates($dateStart, $dateEnd);

        // Si pas de saison trouvée, calcul du prix sans saison
        if (empty($seasons)) {
            $days = $dateStart->diff($dateEnd)->days + 1;
            $total = ($days * $reservation->getRental()->getType()->getPrice());
        }
        else
        {
            foreach ($seasons as $season) {
                // Détermination de la période de la saison qui chevauche la réservation
                // Pour avoir le nombre de jours correct de la période mise du temps à 0
                $start = $dateStart > $season->getDateStart()->setTime(0,0,0) ? $dateStart : $season->getDateStart()->setTime(0,0,0);
                $end = $dateEnd < $season->getDateEnd()->setTime(0,0,0) ? $dateEnd : $season->getDateEnd()->setTime(0,0,0);
    
                // Calcul du nombre de jours de la période
                $days = $start->diff($end)->days + 1;
    
                // Calcul du prix total de la réservation
                $total += ($days * $season->getPercentage() * $reservation->getRental()->getType()->getPrice())/100;
            }
    
        }
        
        return $total;
    }
}
