<?php

namespace App\Controller;

use App\Entity\Type;
use App\Form\TypeType;
use App\Repository\PriceRepository;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('admin/type')]
final class TypeController extends AbstractController
{
    /**
     * Méthode permettant d'afficher la liste des types de bien
     * @Route("/", name="app_type_index", methods={"GET"})
     * @param TypeRepository $typeRepository
     * @return Response
     */
    #[Route(name: 'app_type_index', methods: ['GET'])]
    public function index(TypeRepository $typeRepository): Response
    {
        $types = $typeRepository->getAllInfos();

        return $this->render('type/index.html.twig', [
            'types' => $types,
        ]);
    }

    /**
     * Méthode permettant de créer un nouveau type de bien
     * @Route("/new", name="app_type_new", methods={"GET", "POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/new', name: 'app_type_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TypeRepository $typeRepository): Response
    {
        $type = new Type();

        // Création du formulaire avec option is_edit à false
        $form = $this->createForm(TypeType::class, $type, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Gestion de l'image uploadée
            $imageFile = $form->get('imagePath')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // On génère un nom de fichier unique
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                // On déplace le fichier dans le dossier public/images
                try {
                    $imageFile->move(
                        $this->getParameter('types_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Une erreur est survenue lors de l\'upload de l\'image');
                }

                // On set le chemin de l'image dans l'entité
                $type->setImagePath($newFilename);
            }

            $typeRepository->save($type, true);

            return $this->redirectToRoute('app_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type/new.html.twig', [
            'type' => $type,
            'form' => $form,
        ]);
    }

    /**
     * Méthode permettant d'afficher un type de bien
     * @Route("/{id}", name="app_type_show", methods={"GET"})
     * @param Type $type
     * @return Response
     */
    #[Route('/{id}', name: 'app_type_show', methods: ['GET'])]
    public function show(Type $type, PriceRepository $priceRepository): Response
    {
        $prices = $priceRepository->getPricebyType($type->getId());

        return $this->render('type/show.html.twig', [
            'type' => $type,
            'prices' => $prices
        ]);
    }

    /**
     * Méthode permettant de modifier un type de bien
     * @Route("/{id}/edit", name="app_type_edit", methods={"GET", "POST"})
     * @param Request $request
     * @param Type $type
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Type $type, TypeRepository $typeRepository): Response
    {
        // Création du formulaire avec option is_edit à true
        $form = $this->createForm(TypeType::class, $type, ['is_edit' => true]);
        // Préremplit le champ caché avec l'image actuelle
        $form->get('currentImage')->setData($type->getImagePath());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'image uploadée
            $imageFile = $form->get('imagePath')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // On génère un nom de fichier unique
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    // On déplace le fichier dans le dossier configuré
                    $imageFile->move(
                        $this->getParameter('types_images_directory'),
                        $newFilename
                    );
                    $type->setImagePath($newFilename); // Met à jour le chemin de l'image
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Une erreur est survenue lors de l\'upload de l\'image');
                }
            } else {
                // Conserver l'image actuelle si aucune nouvelle image n'est uploadée
                $type->setImagePath($form->get('currentImage')->getData());
            }

            // Enregistrement des modifications
            $typeRepository->save($type, true);

            return $this->redirectToRoute('app_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type/edit.html.twig', [
            'type' => $type,
            'form' => $form,
        ]);
    }

    /**
     * Méthode permettant de supprimer un type de bien
     * @Route("/{id}", name="app_type_delete", methods={"POST"})
     * @param Request $request
     * @param Type $type
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}', name: 'app_type_delete', methods: ['POST'])]
    public function delete(Request $request, Type $type, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$type->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($type);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_type_index', [], Response::HTTP_SEE_OTHER);
    }
}
