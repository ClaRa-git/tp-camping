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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        // Récupèration toutes les non disponibilités
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
        // Création une nouvelle non disponibilité
        $availability = new Availability();

        // Création du formulaire et traitement de la requête
        $form = $this->createForm(AvailabilityType::class, $availability);
        $form->handleRequest($request);

        // Vérification de la soumission du formulaire et de sa validité
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrement de la non disponibilité
            $entityManager->persist($availability);
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Non disponibilité crée avec succès !');

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
        // Vérification de l'existence de la non disponibilité
        if (!$availability) {
            throw new NotFoundHttpException('Non disponibilité non trouvée');
        }

        // Récupération de la location par son ID
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
        // Vérification de l'existence de la non disponibilité
        if (!$availability) {
            throw new NotFoundHttpException('Non disponibilité non trouvée');
        }

        // Création du formulaire et traitement de la requête
        $form = $this->createForm(AvailabilityType::class, $availability);
        $form->handleRequest($request);

        // Vérification de la soumission du formulaire et de sa validité
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrement de la modification de la non disponibilité
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Non disponibilité modifiée avec succès !');

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
        // Vérification de l'existence de la non disponibilité
        if (!$availability) {
            throw new NotFoundHttpException('Non disponibilité non trouvée');
        }

        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('delete'.$availability->getId(), $request->getPayload()->getString('_token'))) {
            // Suppression de la non disponibilité
            $entityManager->remove($availability);
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Non disponibilité supprimée avec succès !');
        }

        return $this->redirectToRoute('app_availability_index', [], Response::HTTP_SEE_OTHER);
    }
}
