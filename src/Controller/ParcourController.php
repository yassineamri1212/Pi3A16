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

#[Route('/admin/parcour')] // Base route for admin parcour actions
#[IsGranted('ROLE_ADMIN')] // Apply admin security to all actions in this controller
class ParcourController extends AbstractController
{
    #[Route('/', name: 'app_admin_parcour_index', methods: ['GET'])] // Keep admin-specific route name
    public function index(
        Request $request,
        ParcourRepository $parcourRepository,
        PaginatorInterface $paginator
    ): Response
    {
        // *** CHANGED: Order by 'name' now instead of 'trajet' ***
        $queryBuilder = $parcourRepository->createQueryBuilder('p')
            ->orderBy('p.name', 'ASC'); // Or 'p.pickup', 'p.destination', 'p.idParcours'

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            15 // Items per page
        );

        // Render the ADMIN index template
        return $this->render('parcour/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_admin_parcour_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $parcour = new Parcour();
        $form = $this->createForm(ParcourType::class, $parcour); // Uses the updated ParcourType
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($parcour);
            $entityManager->flush();
            $this->addFlash('success', 'Parcour created successfully.');
            // Redirect to ADMIN index
            return $this->redirectToRoute('app_admin_parcour_index', [], Response::HTTP_SEE_OTHER);
        }

        // Render the ADMIN new template
        return $this->render('parcour/new.html.twig', [
            'parcour' => $parcour,
            'form' => $form,
        ]);
    }

    #[Route('/{idParcours}', name: 'app_admin_parcour_show', requirements: ['idParcours' => '\d+'], methods: ['GET'])]
    public function show(Parcour $parcour): Response // ParamConverter handles finding the Parcour
    {
        // Render the ADMIN show template
        return $this->render('parcour/show.html.twig', [
            'parcour' => $parcour,
        ]);
    }

    #[Route('/{idParcours}/edit', name: 'app_admin_parcour_edit', requirements: ['idParcours' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Parcour $parcour, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParcourType::class, $parcour); // Uses the updated ParcourType
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Parcour updated successfully.');
            // Redirect to ADMIN index
            return $this->redirectToRoute('app_admin_parcour_index', [], Response::HTTP_SEE_OTHER);
        }

        // Render the ADMIN edit template
        return $this->render('parcour/edit.html.twig', [
            'parcour' => $parcour,
            'form' => $form,
        ]);
    }

    #[Route('/{idParcours}', name: 'app_admin_parcour_delete', requirements: ['idParcours' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Parcour $parcour, EntityManagerInterface $entityManager): Response
    {
        // Check if related Offres exist (relationship logic unchanged)
        if (!$parcour->getOffres()->isEmpty()) {
            // *** CHANGED: Update flash message to use name/pickup/destination instead of trajet ***
            $parcourIdentifier = $parcour->getName() ?: sprintf('%s to %s', $parcour->getPickup(), $parcour->getDestination());
            $this->addFlash('error', sprintf(
                    'Cannot delete Parcour "%s". It is used by %d Offre(s). Please delete or reassign the Offres first.',
                    $parcourIdentifier, // Use the new identifier
                    $parcour->getOffres()->count()
                )
            );
            // Redirect to ADMIN index
            return $this->redirectToRoute('app_admin_parcour_index', [], Response::HTTP_SEE_OTHER);
        }

        // CSRF token validation logic unchanged
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