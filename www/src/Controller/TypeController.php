<?php

namespace App\Controller;

use App\Entity\Type;
use App\Form\TypeType;
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
    // Constantes pour définir si le type de bien est actif ou non
    private const ACTIVE = 1;
    private const INACTIVE = 0;

    /**
     * Méthode permettant d'afficher la liste des types de bien
     * @Route("/", name="app_type_index", methods={"GET"})
     * @param TypeRepository $typeRepository
     * @return Response
     */
    #[Route(name: 'app_type_index', methods: ['GET'])]
    public function index(TypeRepository $typeRepository): Response
    {
        // Récupération de tous les types de bien
        $types = $typeRepository->findAll();

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
        // Création d'une nouvelle instance de Type
        $type = new Type();

        // Création du formulaire avec option is_edit à false et gestion de la requête  
        $form = $this->createForm(TypeType::class, $type, ['is_edit' => false]);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            // Gestion de l'image uploadée
            $imageFile = $form->get('imagePath')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Génération d'un nom de fichier unique
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                // Déplacement du fichier dans le dossier public/images
                try {
                    $imageFile->move(
                        $this->getParameter('types_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Message d'erreur si l'upload de l'image a échoué
                    $this->addFlash('danger', 'Une erreur est survenue lors de l\'upload de l\'image');
                }

                // Set du chemin de l'image dans l'entité
                $type->setImagePath($newFilename);
            }

            // Enregistrement du type de bien
            $typeRepository->save($type, true);

            // Message de succès
            $this->addFlash('success', 'Le type de bien a bien été ajouté');

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
     * @param TypeRepository $typeRepository
     * @param int $id
     * @return Response
     */
    #[Route('/{id}', name: 'app_type_show', methods: ['GET'])]
    public function show(TypeRepository $typeRepository, int $id): Response
    {
        // Vérification de l'existence du type
        $type = $typeRepository->find($id);
        if (!$type) {
            // Si le type n'existe pas, redirection vers la liste des types
            $this->addFlash('danger', 'Le type demandé n\'existe pas.');

            return $this->redirectToRoute('app_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type/show.html.twig', [
            'type' => $type
        ]);
    }

    /**
     * Méthode permettant de modifier un type de bien
     * @Route("/{id}/edit", name="app_type_edit", methods={"GET", "POST"})
     * @param Request $request
     * @param TypeRepository $typeRepository
     * @param int $id
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, TypeRepository $typeRepository): Response
    {
        // Vérification de l'existence du type
        $type = $typeRepository->find($id);
        if (!$type) {
            // Si le type n'existe pas, redirection vers la liste des types
            $this->addFlash('danger', 'Le type demandé n\'existe pas.');

            return $this->redirectToRoute('app_type_index', [], Response::HTTP_SEE_OTHER);
        }

        // Création du formulaire avec option is_edit à true
        $form = $this->createForm(TypeType::class, $type, ['is_edit' => true]);
        // Préremplissage du champ caché avec l'image actuelle
        $form->get('currentImage')->setData($type->getImagePath());

        // Gestion de la requête
        $form->handleRequest($request);

        // Vérification de la soumission du formulaire et de sa validité
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'image uploadée
            $imageFile = $form->get('imagePath')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Génération d'un nom de fichier unique
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    // Déplacement du fichier dans le dossier configuré
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

            // Message de succès
            $this->addFlash('success', 'Le type de bien a bien été modifié');

            return $this->redirectToRoute('app_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type/edit.html.twig', [
            'type' => $type,
            'form' => $form,
        ]);
    }

    /**
     * Méthode permettant de supprimer un type de bien
     * @Route("/{id}", name="app_type_desactivate", methods={"POST"})
     * @param Request $request
     * @param TypeRepository $typeRepository
     * @param int $id
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}', name: 'app_type_desactivate', methods: ['POST'])]
    public function desactivate(Request $request, TypeRepository $typeRepository, int $id, EntityManagerInterface $entityManager): Response
    {
        // Vérification de l'existence du type
        $type = $typeRepository->find($id);
        if (!$type) {
            // Si le type n'existe pas, redirection vers la liste des types
            $this->addFlash('danger', 'Le type demandé n\'existe pas.');

            return $this->redirectToRoute('app_type_index', [], Response::HTTP_SEE_OTHER);
        }

        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('desactivate'.$type->getId(), $request->getPayload()->getString('_token'))) {
            // Désactivation du type de bien
            $type->setIsActive(self::INACTIVE);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_type_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Méthode permettant d'activer un type de bien
     * @Route("/{id}/activate", name="app_type_activate", methods={"POST"})
     * @param Request $request
     * @param TypeRepository $typeRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}/activate', name: 'app_type_activate', methods: ['POST'])]
    public function activate(Request $request, TypeRepository $typeRepository, int $id, EntityManagerInterface $entityManager): Response
    {
        // Vérification de l'existence du type
        $type = $typeRepository->find($id);
        if (!$type) {
            // Si le type n'existe pas, redirection vers la liste des types
            $this->addFlash('danger', 'Le type demandé n\'existe pas.');

            return $this->redirectToRoute('app_type_index', [], Response::HTTP_SEE_OTHER);
        }

        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('activate'.$type->getId(), $request->getPayload()->getString('_token'))) {
            // Activation du type de bien
            $type->setIsActive(self::ACTIVE);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_type_index', [], Response::HTTP_SEE_OTHER);
    }
}
