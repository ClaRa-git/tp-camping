<?php

namespace App\Controller;

use App\Entity\Season;
use App\Form\SeasonType;
use App\Repository\SeasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('admin/season')]
final class SeasonController extends AbstractController
{
    /**
     * Méthode permettant d'afficher la liste des saisons
     * @Route("/", name="app_season_index", methods={"GET"})
     * @param SeasonRepository $seasonRepository
     * @return Response
     */
    #[Route(name: 'app_season_index', methods: ['GET'])]
    public function index(SeasonRepository $seasonRepository): Response
    {
        return $this->render('season/index.html.twig', [
            'seasons' => $seasonRepository->findAll(),
        ]);
    }

    /**
     * Méthode permettant de créer une nouvelle saison
     * @Route("/new", name="app_season_new", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/new', name: 'app_season_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $season = new Season();
        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($season);
            $entityManager->flush();

            return $this->redirectToRoute('app_season_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('season/new.html.twig', [
            'season' => $season,
            'form' => $form,
        ]);
    }

    /**
     * Méthode permettant d'afficher une saison
     * @Route("/{id}", name="app_season_show", methods={"GET"})
     * @param Season $season
     * @return Response
     */
    #[Route('/{id}', name: 'app_season_show', methods: ['GET'])]
    public function show(Season $season): Response
    {
        return $this->render('season/show.html.twig', [
            'season' => $season,
        ]);
    }

    /**
     * Méthode permettant de modifier une saison
     * @Route("/{id}/edit", name="app_season_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Season $season
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_season_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Season $season, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_season_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('season/edit.html.twig', [
            'season' => $season,
            'form' => $form,
        ]);
    }

    /**
     * Méthode permettant de supprimer une saison
     * @Route("/{id}", name="app_season_delete", methods={"POST"})
     * @param Request $request
     * @param Season $season
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}', name: 'app_season_delete', methods: ['POST'])]
    public function delete(Request $request, Season $season, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$season->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($season);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_season_index', [], Response::HTTP_SEE_OTHER);
    }
}
