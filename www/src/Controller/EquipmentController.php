<?php

namespace App\Controller;

use App\Entity\Equipment;
use App\Form\EquipmentType;
use App\Repository\EquipmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('admin/equipment')]
final class EquipmentController extends AbstractController
{
    // Constantes pour définir si l'équipement est actif ou non
    private const ACTIVE = 1;
    private const INACTIVE = 0;

    /**
     * Méthode permettant d'afficher la liste des équipements
     * @Route("/", name="app_equipment_index", methods={"GET"})
     * @param EquipmentRepository $equipmentRepository
     * @return Response
     */
    #[Route(name: 'app_equipment_index', methods: ['GET'])]
    public function index(EquipmentRepository $equipmentRepository): Response
    {
        return $this->render('equipment/index.html.twig', [
            'equipments' => $equipmentRepository->findAll(),
        ]);
    }

    /**
     * Méthode permettant de créer un nouvel équipement
     * @Route("/new", name="app_equipment_new", methods={"GET", "POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/new', name: 'app_equipment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création d'un nouvel équipement
        $equipment = new Equipment();

        // Création du formulaire et traitement de la requête
        $form = $this->createForm(EquipmentType::class, $equipment);
        $form->handleRequest($request);

        // Vérification de la soumission du formulaire et de sa validité
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrement de l'équipement
            $entityManager->persist($equipment);
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'L\'équipement a été ajouté avec succès');

            return $this->redirectToRoute('app_equipment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipment/new.html.twig', [
            'equipment' => $equipment,
            'form' => $form,
        ]);
    }

    /**
     * Méthode permettant d'afficher un équipement
     * @Route("/{id}", name="app_equipment_show", methods={"GET"})
     * @param Equipment $equipment
     * @return Response
     */
    #[Route('/{id}', name: 'app_equipment_show', methods: ['GET'])]
    public function show(Equipment $equipment): Response
    {
        // Vérification de l'existence de l'équipement
        if (!$equipment) {
            throw new NotFoundHttpException('Equipement non trouvée');
        }

        return $this->render('equipment/show.html.twig', [
            'equipment' => $equipment,
        ]);
    }

    /**
     * Méthode permettant de modifier un équipement
     * @Route("/{id}/edit", name="app_equipment_edit", methods={"GET", "POST"})
     * @param Request $request
     * @param Equipment $equipment
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_equipment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Equipment $equipment, EntityManagerInterface $entityManager): Response
    {
        // Vérification de l'existence de l'équipement
        if (!$equipment) {
            throw new NotFoundHttpException('Equipement non trouvée');
        }

        // Création du formulaire et traitement de la requête
        $form = $this->createForm(EquipmentType::class, $equipment);
        $form->handleRequest($request);

        // Vérification de la soumission du formulaire et de sa validité
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrement de la modification de l'équipement
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'L\'équipement a été modifié avec succès');

            return $this->redirectToRoute('app_equipment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipment/edit.html.twig', [
            'equipment' => $equipment,
            'form' => $form,
        ]);
    }

    /**
     * Méthode permettant de désactiver un équipement
     * @Route("/{id}", name="app_equipment_desactivate", methods={"POST"})
     * @param Request $request
     * @param Equipment $equipment
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}', name: 'app_equipment_desactivate', methods: ['POST'])]
    public function desactivate(Request $request, Equipment $equipment, EntityManagerInterface $entityManager): Response
    {
        // Vérification de l'existence de l'équipement
        if (!$equipment) {
            throw new NotFoundHttpException('Equipement non trouvée');
        }

        // Vérification du jeton CSRF
        if ($this->isCsrfTokenValid('desactivate'.$equipment->getId(), $request->getPayload()->getString('_token'))) {
            // Désactivation de l'équipement
            $equipment->setIsActive(self::INACTIVE);
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'L\'équipement a été désactivé avec succès');
        }

        return $this->redirectToRoute('app_equipment_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Méthode permettant d'activer un équipement
     * @Route("/{id}/activate", name="app_equipment_activate", methods={"POST"})
     * @param Request $request
     * @param Equipment $equipment
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}/activate', name: 'app_equipment_activate', methods: ['POST'])]
    public function activate(Request $request, Equipment $equipment, EntityManagerInterface $entityManager): Response
    {
        // Vérification de l'existence de l'équipement
        if (!$equipment) {
            throw new NotFoundHttpException('Non disponibilité non trouvée');
        }

        // Vérification du jeton CSRF
        if ($this->isCsrfTokenValid('activate'.$equipment->getId(), $request->getPayload()->getString('_token'))) {
            // Activation de l'équipement
            $equipment->setIsActive(self::ACTIVE);
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'L\'équipement a été activé avec succès');
        }

        return $this->redirectToRoute('app_equipment_index', [], Response::HTTP_SEE_OTHER);
    }
}
