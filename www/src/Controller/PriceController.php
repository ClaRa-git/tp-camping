<?php

namespace App\Controller;

use App\Entity\Price;
use App\Form\PriceType;
use App\Repository\PriceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('admin/price')]
final class PriceController extends AbstractController
{
    /**
     * Méthode permettant d'afficher la liste des prix
     * @Route("/", name="app_price_index", methods={"GET"})
     * @param PriceRepository $priceRepository
     * @return Response
     */
    #[Route(name: 'app_price_index', methods: ['GET'])]
    public function index(PriceRepository $priceRepository): Response
    {
        return $this->render('price/index.html.twig', [
            'prices' => $priceRepository->findAll(),
        ]);
    }

    /**
     * Méthode permettant de créer un nouveau prix
     * @Route("/new", name="app_price_new", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/new', name: 'app_price_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $price = new Price();
        $form = $this->createForm(PriceType::class, $price);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($price);
            $entityManager->flush();

            return $this->redirectToRoute('app_price_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('price/new.html.twig', [
            'price' => $price,
            'form' => $form,
        ]);
    }

    /**
     * Méthode permettant d'afficher un prix
     * @Route("/{id}", name="app_price_show", methods={"GET"})
     * @param Price $price
     * @return Response
     */
    #[Route('/{id}', name: 'app_price_show', methods: ['GET'])]
    public function show(Price $price): Response
    {
        return $this->render('price/show.html.twig', [
            'price' => $price,
        ]);
    }

    /**
     * Méthode permettant de modifier un prix
     * @Route("/{id}/edit", name="app_price_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Price $price
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_price_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Price $price, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PriceType::class, $price);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_price_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('price/edit.html.twig', [
            'price' => $price,
            'form' => $form,
        ]);
    }

    /**
     * Méthode permettant de supprimer un prix
     * @Route("/{id}", name="app_price_delete", methods={"POST"})
     * @param Request $request
     * @param Price $price
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}', name: 'app_price_delete', methods: ['POST'])]
    public function delete(Request $request, Price $price, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$price->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($price);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_price_index', [], Response::HTTP_SEE_OTHER);
    }
}
