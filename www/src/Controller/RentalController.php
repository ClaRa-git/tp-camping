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
    /**
     * Méthode permettant d'afficher la liste des locations
     * @Route("/", name="app_rental_index", methods={"GET"})
     * @param RentalRepository $rentalRepository
     * @return Response
     */
    #[Route(name: 'app_rental_index', methods: ['GET'])]
    public function index(RentalRepository $rentalRepository): Response
    {
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
            $entityManager->persist($rental);
            $entityManager->flush();

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
        $type = $typeRepository->findOneBy(['id' => $rental->getType()]);

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
        $form = $this->createForm(RentalType::class, $rental);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

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
    #[Route('/{id}', name: 'app_rental_delete', methods: ['POST'])]
    public function delete(Request $request, Rental $rental, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rental->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($rental);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_rental_index', [], Response::HTTP_SEE_OTHER);
    }
}
