<?php

// src/Controller/Conducteur/ParcourController.php
namespace App\Controller\Conducteur;

use App\Entity\Parcour;
use App\Form\ParcourType; // Uses the same updated form type as Admin
use App\Repository\ParcourRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/conducteur/parcour')] // Route prefix for conducteur parcours
#[IsGranted('ROLE_CONDUCTEUR')] // Secure for conducteurs
class ParcourController extends AbstractController
{
    #[Route('/', name: 'app_conducteur_parcour_index', methods: ['GET'])]
    public function index(
        Request $request,
        ParcourRepository $parcourRepository,
        PaginatorInterface $paginator
    ): Response
    {
        // *** CHANGED: Order by 'name' instead of 'trajet' ***
        // Optional: Add filter here later if Parcours should be linked to conducteur
        $queryBuilder = $parcourRepository->createQueryBuilder('p')
            ->orderBy('p.name', 'ASC'); // Or 'p.pickup', 'p.idParcours' etc.

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            15 // Items per page
        );

        // Render CONDUCTEUR template
        return $this->render('conducteur/parcour/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_conducteur_parcour_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $parcour = new Parcour();
        // TODO: If needed later, uncomment and add setConducteur to Parcour Entity
        // $conducteur = $this->getUser();
        // if ($conducteur instanceof \App\Entity\User) {
        //     $parcour->setConducteur($conducteur);
        // }

        $form = $this->createForm(ParcourType::class, $parcour); // Uses the updated shared Form Type
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($parcour);
            $entityManager->flush();
            $this->addFlash('success', 'Parcour created successfully.');
            // Redirect to CONDUCTEUR index
            return $this->redirectToRoute('app_conducteur_parcour_index', [], Response::HTTP_SEE_OTHER);
        }

        // Render CONDUCTEUR template
        return $this->render('conducteur/parcour/new.html.twig', [
            'parcour' => $parcour,
            'form' => $form,
        ]);
    }

    #[Route('/{idParcours}', name: 'app_conducteur_parcour_show', requirements: ['idParcours' => '\d+'], methods: ['GET'])]
    public function show(Parcour $parcour): Response
    {
        // TODO: Add ownership check if needed later
        // $this->checkParcourOwnership($parcour);

        // Render CONDUCTEUR template
        return $this->render('conducteur/parcour/show.html.twig', [
            'parcour' => $parcour,
        ]);
    }

    #[Route('/{idParcours}/edit', name: 'app_conducteur_parcour_edit', requirements: ['idParcours' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Parcour $parcour, EntityManagerInterface $entityManager): Response
    {
        // TODO: Add ownership check if needed later
        // $this->checkParcourOwnership($parcour);

        $form = $this->createForm(ParcourType::class, $parcour); // Uses updated shared Form Type
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Parcour updated successfully.');
            // Redirect to CONDUCTEUR index
            return $this->redirectToRoute('app_conducteur_parcour_index', [], Response::HTTP_SEE_OTHER);
        }

        // Render CONDUCTEUR template
        return $this->render('conducteur/parcour/edit.html.twig', [
            'parcour' => $parcour,
            'form' => $form,
        ]);
    }

    #[Route('/{idParcours}', name: 'app_conducteur_parcour_delete', requirements: ['idParcours' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Parcour $parcour, EntityManagerInterface $entityManager): Response
    {
        // TODO: Add ownership check if needed later
        // $this->checkParcourOwnership($parcour);

        // Check if related Offres exist
        if (!$parcour->getOffres()->isEmpty()) {
            // *** CHANGED: Update flash message using name/pickup/destination ***
            $parcourIdentifier = $parcour->getName() ?: sprintf('%s to %s', $parcour->getPickup(), $parcour->getDestination());
            $this->addFlash('error', sprintf(
                    'Cannot delete Parcour "%s". It is used by %d Offre(s).',
                    $parcourIdentifier, // Use new identifier
                    $parcour->getOffres()->count()
                )
            );
            // Redirect to CONDUCTEUR index
            return $this->redirectToRoute('app_conducteur_parcour_index', [], Response::HTTP_SEE_OTHER);
        }

        // CSRF token check
        if ($this->isCsrfTokenValid('delete'.$parcour->getIdParcours(), $request->request->get('_token'))) {
            $entityManager->remove($parcour);
            $entityManager->flush();
            $this->addFlash('success', 'Parcour deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token. Deletion failed.');
        }

        // Redirect to CONDUCTEUR index
        return $this->redirectToRoute('app_conducteur_parcour_index', [], Response::HTTP_SEE_OTHER);
    }

    // TODO: Uncomment and implement if Parcours should be owned by Conducteurs
    /*
    private function checkParcourOwnership(Parcour $parcour): void
    {
        $user = $this->getUser();
        // Add a 'conducteur' relation to Parcour entity first
        if (!$user instanceof \App\Entity\User || $parcour->getConducteur() !== $user) {
             throw $this->createAccessDeniedException('You are not allowed to manage this parcour.');
        }
    }
    */
}