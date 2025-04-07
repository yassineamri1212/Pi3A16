<?php

namespace App\Controller;

use App\Entity\Livraison;
use App\Form\LivraisonType; // Make sure PackageType is used in LivraisonType
use App\Repository\LivraisonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/livraison')] // All routes here start with /admin/livraison
#[IsGranted('ROLE_ADMIN')] // Secure the whole controller for admin access
class LivraisonController extends AbstractController
{
    // --- ADMIN ACTIONS ---

    #[Route('/', name: 'app_admin_livraison_index', methods: ['GET'])] // Renamed route
    public function index(Request $request, LivraisonRepository $livraisonRepository, PaginatorInterface $paginator): Response
    {
        // Use the admin search query builder
        $searchTerm = $request->query->get('q');
        $sort = $request->query->get('sort', 'l.id');
        $direction = $request->query->get('direction', 'DESC');

        $queryBuilder = $livraisonRepository->findByAdminSearchQueryBuilder($searchTerm, $sort, $direction);

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            15 // Items per page
        );

        return $this->render('livraison/index.html.twig', [ // Renders admin template
            'pagination' => $pagination,
            'searchTerm' => $searchTerm, // Pass search term for admin search bar
        ]);
    }

    #[Route('/new', name: 'app_admin_livraison_new', methods: ['GET', 'POST'])] // Renamed route
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $livraison = new Livraison();
        $form = $this->createForm(LivraisonType::class, $livraison);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($livraison);
            $entityManager->flush();
            $this->addFlash('success', 'Delivery created successfully.');
            return $this->redirectToRoute('app_admin_livraison_index', [], Response::HTTP_SEE_OTHER); // Redirect to admin index
        }

        return $this->render('livraison/new.html.twig', [ // Renders admin template
            'livraison' => $livraison,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_livraison_show', requirements: ['id' => '\d+'], methods: ['GET'])] // Renamed route
    public function show(Livraison $livraison): Response
    {
        // Parameter converter finds Livraison by id
        return $this->render('livraison/show.html.twig', [ // Renders admin template
            'livraison' => $livraison,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_livraison_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])] // Renamed route
    public function edit(Request $request, Livraison $livraison, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LivraisonType::class, $livraison);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Delivery updated successfully.');
            return $this->redirectToRoute('app_admin_livraison_index', [], Response::HTTP_SEE_OTHER); // Redirect to admin index
        }

        return $this->render('livraison/edit.html.twig', [ // Renders admin template
            'livraison' => $livraison,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_livraison_delete', requirements: ['id' => '\d+'], methods: ['POST'])] // Renamed route
    public function delete(Request $request, Livraison $livraison, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$livraison->getId(), $request->request->get('_token'))) {
            $entityManager->remove($livraison);
            $entityManager->flush();
            $this->addFlash('success', 'Delivery and associated packages deleted.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('app_admin_livraison_index', [], Response::HTTP_SEE_OTHER); // Redirect to admin index
    }

    // --- NO PUBLIC ACTIONS IN THIS CONTROLLER ---

}