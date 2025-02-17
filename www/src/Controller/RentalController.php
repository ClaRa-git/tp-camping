<?php

namespace App\Controller;

use App\Entity\Rental;
use App\Form\RentalType;
use App\Repository\EquipmentRepository;
use App\Repository\RentalRepository;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('admin/rental')]
final class RentalController extends AbstractController
{
    // Constantes pour définir si la location est active ou non
    private const ACTIVE = 1;
    private const INACTIVE = 0;

    /**
     * Méthode permettant d'afficher la liste des locations
     * @Route("/", name="app_rental_index", methods={"GET"})
     * @param RentalRepository $rentalRepository
     * @return Response
     */
    #[Route(name: 'app_rental_index', methods: ['GET'])]
    public function index(RentalRepository $rentalRepository): Response
    {
        // On récupère toutes les locations avec toutes les informations
        $rentals = $rentalRepository->getAllInfos();

        return $this->render('rental/index.html.twig', [
            'rentals' => $rentals,
        ]);
    }

    /**
     * Méthode permettant de créer une nouvelle location
     * @Route("/new", name="app_rental_new", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/new', name: 'app_rental_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $rental = new Rental();
        $form = $this->createForm(RentalType::class, $rental);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // On vérifie si le type de location est activé
            $type = $form->get('type')->getData();
            if ($type->getIsActive() === self::INACTIVE) {
                $this->addFlash('danger', 'Le type de location sélectionné est désactivé');
                return $this->redirectToRoute('app_rental_new');
            }

            // On vérifie si les équipements sont activés
            $equipments = $form->get('equipments')->getData();
            foreach ($equipments as $equipment) {
                if ($equipment->getIsActive() === self::INACTIVE) {
                    $this->addFlash('danger', 'Un des équipements sélectionné est désactivé');
                    return $this->redirectToRoute('app_rental_new');
                }
            }
            
            $entityManager->persist($rental);
            $entityManager->flush();

            $this->addFlash('success', 'La location a bien été créée');
            return $this->redirectToRoute('app_rental_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rental/new.html.twig', [
            'rental' => $rental,
            'form' => $form,
        ]);
    }

    /**
     * Méthode permettant d'afficher une location
     * @Route("/{id}", name="app_rental_show", methods={"GET"})
     * @param Rental $rental
     * @return Response
     */
    #[Route('/{id}', name: 'app_rental_show', methods: ['GET'])]
    public function show(Rental $rental, TypeRepository $typeRepository, EquipmentRepository $equipmentRepository): Response
    {
        if (!$rental) {
            throw $this->createNotFoundException('La location n\'existe pas');
        }

        // On récupère le type de la location
        $type = $typeRepository->findOneBy(['id' => $rental->getType()]);

        // On récupère les équipements de la location
        $equipments = $equipmentRepository->getEquipmentsForRental($rental->getId());

        return $this->render('rental/show.html.twig', [
            'rental' => $rental,
            'type' => $type,
            'equipments' => $equipments,
        ]);
    }

    /**
     * Méthode permettant de modifier une location
     * @Route("/{id}/edit", name="app_rental_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Rental $rental
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_rental_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Rental $rental, EntityManagerInterface $entityManager): Response
    {
        if (!$rental) {
            throw $this->createNotFoundException('La location n\'existe pas');
        }

        $form = $this->createForm(RentalType::class, $rental);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La location a bien été modifiée');
            return $this->redirectToRoute('app_rental_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rental/edit.html.twig', [
            'rental' => $rental,
            'form' => $form,
        ]);
    }

    /**
     * Méthode permettant de supprimer une location
     * @Route("/{id}", name="app_rental_delete", methods={"POST"})
     * @param Request $request
     * @param Rental $rental
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}', name: 'app_rental_desactivate', methods: ['POST'])]
    public function desactivate(Request $request, Rental $rental, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('desactivate'.$rental->getId(), $request->getPayload()->getString('_token'))) {
            $rental->setIsActive(self::INACTIVE);
            $entityManager->flush();

            $this->addFlash('success', 'La location a bien été désactivée');
        }

        return $this->redirectToRoute('app_rental_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Méthode permettant d'activer une location
     * @Route("/{id}/activate", name="app_rental_activate", methods={"POST"})
     * @param Request $request
     * @param Rental $rental
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}/activate', name: 'app_rental_activate', methods: ['POST'])]
    public function activate(Request $request, Rental $rental, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('activate'.$rental->getId(), $request->getPayload()->getString('_token'))) {
            $rental->setIsActive(self::ACTIVE);
            $entityManager->flush();

            $this->addFlash('success', 'La location a bien été activée');
        }

        return $this->redirectToRoute('app_rental_index', [], Response::HTTP_SEE_OTHER);
    }
}
