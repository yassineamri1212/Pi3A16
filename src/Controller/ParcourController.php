<?php

// src/Controller/ParcourController.php
namespace App\Controller;

use App\Entity\Parcour;
use App\Form\ParcourType;
use App\Repository\ParcourRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/parcour')]
#[IsGranted('ROLE_ADMIN')] // <<< REVERTED: ROLE_ADMIN only >>>
class ParcourController extends AbstractController
{
    // --- ADMIN ACTIONS ONLY ---

    #[Route('/', name: 'app_admin_parcour_index', methods: ['GET'])]
    public function index(
        Request $request,
        ParcourRepository $parcourRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $queryBuilder = $parcourRepository->createQueryBuilder('p')
            ->orderBy('p.trajet', 'ASC');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            15
        );

        // Render ADMIN template
        return $this->render('parcour/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_admin_parcour_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $parcour = new Parcour();
        $form = $this->createForm(ParcourType::class, $parcour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($parcour);
            $entityManager->flush();
            $this->addFlash('success', 'Parcour created successfully.');
            // Redirect to ADMIN index
            return $this->redirectToRoute('app_admin_parcour_index', [], Response::HTTP_SEE_OTHER);
        }

        // Render ADMIN template
        return $this->render('parcour/new.html.twig', [
            'parcour' => $parcour,
            'form' => $form,
        ]);
    }

    #[Route('/{idParcours}', name: 'app_admin_parcour_show', requirements: ['idParcours' => '\d+'], methods: ['GET'])]
    public function show(Parcour $parcour): Response
    {
        // Render ADMIN template
        return $this->render('parcour/show.html.twig', [
            'parcour' => $parcour,
        ]);
    }

    #[Route('/{idParcours}/edit', name: 'app_admin_parcour_edit', requirements: ['idParcours' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Parcour $parcour, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParcourType::class, $parcour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Parcour updated successfully.');
            // Redirect to ADMIN index
            return $this->redirectToRoute('app_admin_parcour_index', [], Response::HTTP_SEE_OTHER);
        }

        // Render ADMIN template
        return $this->render('parcour/edit.html.twig', [
            'parcour' => $parcour,
            'form' => $form,
        ]);
    }

    #[Route('/{idParcours}', name: 'app_admin_parcour_delete', requirements: ['idParcours' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Parcour $parcour, EntityManagerInterface $entityManager): Response
    {
        if (!$parcour->getOffres()->isEmpty()) {
            $this->addFlash('error', sprintf(
                    'Cannot delete Parcour "%s". It is used by %d Offre(s).',
                    $parcour->getTrajet(),
                    $parcour->getOffres()->count()
                )
            );
            return $this->redirectToRoute('app_admin_parcour_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete'.$parcour->getIdParcours(), $request->request->get('_token'))) {
            $entityManager->remove($parcour);
            $entityManager->flush();
            $this->addFlash('success', 'Parcour deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token. Deletion failed.');
        }

        // Redirect to ADMIN index
        return $this->redirectToRoute('app_admin_parcour_index', [], Response::HTTP_SEE_OTHER);
    }
}