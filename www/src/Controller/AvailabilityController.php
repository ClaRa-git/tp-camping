<?php

namespace App\Controller;

use App\Entity\Availability;
use App\Form\AvailabilityType;
use App\Repository\AvailabilityRepository;
use App\Repository\RentalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('admin/availability')]
final class AvailabilityController extends AbstractController
{
    /**
     * Méthode permettant d'afficher la liste des disponibilités
     * @Route("/", name="app_availability_index", methods={"GET"})
     * @param AvailabilityRepository $availabilityRepository
     * @return Response
     */
    #[Route(name: 'app_availability_index', methods: ['GET'])]
    public function index(AvailabilityRepository $availabilityRepository): Response
    {
        // On récupère toutes les non disponibilités
        $availabilities = $availabilityRepository->getAllInfos();

        return $this->render('availability/index.html.twig', [
            'availabilities' => $availabilities,
        ]);
    }

    /**
     * Méthode permettant de créer une nouvelle disponibilité
     * @Route("/new", name="app_availability_new", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/new', name: 'app_availability_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $availability = new Availability();
        $form = $this->createForm(AvailabilityType::class, $availability);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($availability);
            $entityManager->flush();

            return $this->redirectToRoute('app_availability_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('availability/new.html.twig', [
            'availability' => $availability,
            'form' => $form,
        ]);
    }

    /**
     * Méthode permettant d'afficher une disponibilité
     * @Route("/{id}", name="app_availability_show", methods={"GET"})
     * @param Availability $availability
     * @return Response
     */
    #[Route('/{id}', name: 'app_availability_show', methods: ['GET'])]
    public function show(Availability $availability, RentalRepository $rentalRepository): Response
    {
        // On récupère le bien associé à la disponibilité
        $rental = $rentalRepository->find($availability->getRental()->getId());

        return $this->render('availability/show.html.twig', [
            'availability' => $availability,
            'rental' => $rental,
        ]);
    }

    /**
     * Méthode permettant de modifier une disponibilité
     * @Route("/{id}/edit", name="app_availability_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Availability $availability
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_availability_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Availability $availability, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AvailabilityType::class, $availability);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_availability_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('availability/edit.html.twig', [
            'availability' => $availability,
            'form' => $form,
        ]);
    }

    /**
     * Méthode permettant de supprimer une disponibilité
     * @Route("/{id}", name="app_availability_delete", methods={"POST"})
     * @param Request $request
     * @param Availability $availability
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}', name: 'app_availability_delete', methods: ['POST'])]
    public function delete(Request $request, Availability $availability, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$availability->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($availability);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_availability_index', [], Response::HTTP_SEE_OTHER);
    }
}
