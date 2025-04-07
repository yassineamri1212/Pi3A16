<?php

namespace App\Controller; // Ensure this namespace is correct

use App\Entity\Offre;
use App\Form\OffreType;
use App\Repository\OffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/offre')] // Base route prefix for all Offre admin actions
// #[IsGranted('ROLE_ADMIN')] // You can uncomment this later if needed for the whole controller
class OffreController extends AbstractController
{
    #[Route('/', name: 'app_offre_index', methods: ['GET'])] // <<< THE ROUTE NAME IS DEFINED HERE
    #[IsGranted('ROLE_ADMIN')] // Secure this specific action
    public function index(
        Request $request,
        OffreRepository $offreRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $searchTerm = $request->query->get('q');
        $sort = $request->query->get('sort', 'o.dateDepart');
        $direction = $request->query->get('direction', 'DESC');

        $queryBuilder = $offreRepository->findBySearchQueryBuilder($searchTerm, $sort, $direction);

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            15 // Items per page
        );

        return $this->render('offre/index.html.twig', [ // Renders admin template
            'pagination' => $pagination,
            'searchTerm' => $searchTerm,
        ]);
    }

    #[Route('/new', name: 'app_offre_new', methods: ['GET', 'POST'])] // Other route name
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $offre = new Offre();
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($offre);
            $entityManager->flush();
            $this->addFlash('success', 'Offre created successfully.');
            // Redirect to the CORRECT admin index route
            return $this->redirectToRoute('app_offre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('offre/new.html.twig', [ // Renders admin template
            'offre' => $offre,
            'form' => $form,
        ]);
    }

    #[Route('/{idOffre}', name: 'app_offre_show', requirements: ['idOffre' => '\d+'], methods: ['GET'])] // Other route name
    #[IsGranted('ROLE_ADMIN')]
    public function show(Offre $offre): Response
    {
        return $this->render('offre/show.html.twig', [ // Renders admin template
            'offre' => $offre,
        ]);
    }

    #[Route('/{idOffre}/edit', name: 'app_offre_edit', requirements: ['idOffre' => '\d+'], methods: ['GET', 'POST'])] // Other route name
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Offre $offre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Offre updated successfully.');
            // Redirect to the CORRECT admin index route
            return $this->redirectToRoute('app_offre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('offre/edit.html.twig', [ // Renders admin template
            'offre' => $offre,
            'form' => $form,
        ]);
    }

    #[Route('/{idOffre}', name: 'app_offre_delete', requirements: ['idOffre' => '\d+'], methods: ['POST'])] // Other route name
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Offre $offre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$offre->getIdOffre(), $request->request->get('_token'))) {
            $entityManager->remove($offre);
            $entityManager->flush();
            $this->addFlash('success', 'Offre deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token. Deletion failed.');
        }
        // Redirect to the CORRECT admin index route
        return $this->redirectToRoute('app_offre_index', [], Response::HTTP_SEE_OTHER);
    }
}