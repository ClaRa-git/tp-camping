<?php

namespace App\Controller;

use App\Repository\EquipmentRepository;
use App\Repository\RentalRepository;
use App\Repository\TypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    /**
     * Méthode permettant d'afficher la page d'accueil
     * @Route("/", name="app_home")
     * @param TypeRepository $typeRepository
     * @return Response
     */
    #[Route('/', name: 'app_home')]
    public function index( TypeRepository $typeRepository ): Response
    {
        // Titre de la page d'accueil
        $title = "Bienvenue sur CampingFun !";

        // Récupérations des locations
        $types = $typeRepository->findAll();

        // Récupération des types actifs
        $typesAvailable = [];
        foreach ($types as $type) {
            if ($type->isActive()) {
                $typesAvailable[] = $type;
            }
        }

        return $this->render('home/index.html.twig', [
            'title' => $title,
            'types' => $typesAvailable
        ]);
    }

    /**
     * Méthode permettant d'afficher tous les biens d'un type
     * @Route("/detail/type/{id}", name="app_detail_type")
     * @param TypeRepository $typeRepository
     * @param RentalRepository $rentalRepository
     * @param int $id
     * @return Response
     */
    #[Route('/detail/type/{id}', name: 'app_detail_type')]
    public function detailType( TypeRepository $typeRepository, RentalRepository $rentalRepository, int $id ): Response
    {
        // Récupération du type
        $type = $typeRepository->find($id);

        // Titre de la page
        $title = "Découvrez nos locations de type " . $type->getLabel();

        // Récupération des locations
        $rentals = $rentalRepository->findBy(['type' => $type]);

        // Récupération des locations actives
        $rentalsAvailable = [];
        foreach ($rentals as $rental) {
            if ($rental->isActive()) {
                $rentalsAvailable[] = $rental;
            }
        }

        return $this->render('home/detail_type.html.twig', [
            'title' => $title,
            'type' => $type,
            'rentals' => $rentalsAvailable
        ]);
    }

    /**
     * Méthode permettant d'afficher le détail d'une location
     * @Route("/detail/rental/{id}", name="app_detail_rental")
     * @param RentalRepository $rentalRepository
     * @param int $id
     * @return Response
     */
    #[Route('/detail/rental/{id}', name: 'app_detail_rental')]
    public function detailRental( RentalRepository $rentalRepository, int $id , EquipmentRepository $equipmentRepository): Response
    {
        // Récupération de la location
        $rental = $rentalRepository->find($id);

        // Récupération du type
        $type = $rental->getType();

        // Récupération des équipements
        $equipments = $equipmentRepository->getEquipmentsForRental($id);

        // Récupération des équipements actifs
        $equipmentsAvailable = [];
        foreach ($equipments as $equipment) {
            if ($equipment['isActive'] == 1) {
                $equipmentsAvailable[] = $equipment;
            }
        }

        // Titre de la page
        $title = "Découvrez notre location " . $rental->getTitle();

        return $this->render('home/detail_rental.html.twig', [
            'title' => $title,
            'equipments' => $equipmentsAvailable,
            'rental' => $rental,
            'type' => $type
        ]);
    
    }
}
