<?php
// src/Controller/ReponseController.php

namespace App\Controller;

use App\Entity\Reponse;
use App\Entity\Reclamation; // <-- Added use statement if not already present
use App\Form\ReponseType;
use App\Repository\ReclamationRepository;
use App\Service\MailingApiService; // Assuming this exists and works
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException; // Added for better 404

#[Route('/reponse')]
final class ReponseController extends AbstractController
{
    #[Route('/new', name: 'app_reponse_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        ReclamationRepository $reclamationRepository,
        EntityManagerInterface $entityManager,
        MailingApiService $mailingApiService // Keep if used
    ): Response {
        $reclamationId = $request->query->get('reclamation');
        if (!$reclamationId) {
            // Keep original exception or handle differently if preferred
            throw new \Exception('Reclamation ID is missing.');
        }
        $reclamation = $reclamationRepository->find($reclamationId);
        if (!$reclamation) {
            // Use Symfony's standard 404 helper
            throw $this->createNotFoundException('Reclamation not found.');
        }

        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);

        // Keep your logic for setting default username if needed
        // $form->get('user')->setData($reclamation->getNom());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $reponse->setDate(new \DateTime());
            $reponse->setReclamation($reclamation);

            // Set user ID and username based on logged-in user (Example)
            $user = $this->getUser();
            if ($user) {
                // Assuming Reponse entity has these setters
                // $reponse->setUtilisateurId($user->getId());
                $reponse->setUsername($user->getUserIdentifier()); // Or specific field like $user->getFirstName() etc.
            } else {
                // Handle case where no user is logged in, maybe set default?
                $reponse->setUsername('System'); // Example default
            }

            $entityManager->persist($reponse);
            $entityManager->flush();

            // Send mail notification (Keep if needed)
            try {
                $mailingApiService->sendReponseNotification($reclamation->getEmail(), $reclamation->getNom());
                $this->addFlash('success', 'Response submitted successfully.'); // Add user feedback
            } catch (\Exception $e) {
                // Log error: $e->getMessage()
                $this->addFlash('warning', 'Response submitted, but notification email failed.');
            }

            // --- *** CORRECTED REDIRECT for NEW *** ---
            // Redirect back to the show page of THAT specific Reclamation
            return $this->redirectToRoute('admin_reclamations_show', [
                'id' => $reclamation->getId()
            ], Response::HTTP_SEE_OTHER);
            // --- *** END OF CHANGE *** ---
        }

        // Keep your original render logic for the 'new' page if you have one
        return $this->render('reponse/new.html.twig', [
            'reponse' => $reponse,
            'form' => $form->createView(), // Use createView()
            'reclamation' => $reclamation, // Pass reclamation if needed in the template
        ]);
    }

    #[Route('/', name: 'app_reponse_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // This action remains unchanged
        $reponses = $entityManager->getRepository(Reponse::class)->findAll();

        return $this->render('reponse/index.html.twig', [
            'reponses' => $reponses,
        ]);
    }

    #[Route('/{id}', name: 'app_reponse_show', methods: ['GET'])]
    public function show(Reponse $reponse): Response
    {
        // This action remains unchanged
        return $this->render('reponse/show.html.twig', [
            'reponse' => $reponse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reponse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        // Get the parent reclamation *before* handling the form
        $reclamation = $reponse->getReclamation();

        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Response updated successfully.'); // Add user feedback

            // --- *** CORRECTED REDIRECT for EDIT *** ---
            if ($reclamation) {
                // Redirect back to the SHOW page of THAT specific Reclamation
                return $this->redirectToRoute('admin_reclamations_show', [
                    'id' => $reclamation->getId()
                ], Response::HTTP_SEE_OTHER);
            } else {
                // Fallback: If somehow the response isn't linked, go to the general index
                $this->addFlash('warning', 'Response updated, but associated reclamation not found. Redirecting to list.');
                return $this->redirectToRoute('app_reponse_index', [], Response::HTTP_SEE_OTHER); // Fallback to original route
            }
            // --- *** END OF CHANGE *** ---
        }

        // Pass reclamation to the template for context (e.g., cancel button link)
        return $this->render('reponse/edit.html.twig', [
            'reponse' => $reponse,
            'form' => $form->createView(), // Use createView()
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}', name: 'app_reponse_delete', methods: ['POST'])]
    public function delete(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        // Get the Reclamation ID *before* deleting the response
        $reclamation = $reponse->getReclamation();
        $reclamationId = $reclamation ? $reclamation->getId() : null; // Store the ID

        // Use request->request->get() for POST data
        if ($this->isCsrfTokenValid('delete' . $reponse->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reponse);
            $entityManager->flush();
            $this->addFlash('success', 'Response deleted successfully.'); // Add user feedback
        } else {
            $this->addFlash('error', 'Failed to delete response: Invalid security token.');
        }

        // --- *** CORRECTED REDIRECT for DELETE *** ---
        if ($reclamationId) {
            // Redirect back to the SHOW page of THAT specific Reclamation
            return $this->redirectToRoute('admin_reclamations_show', [
                'id' => $reclamationId
            ], Response::HTTP_SEE_OTHER);
        } else {
            // Fallback: If somehow the response wasn't linked, go to the general index
            $this->addFlash('info', 'Response deleted. Could not determine original reclamation page.');
            return $this->redirectToRoute('app_reponse_index', [], Response::HTTP_SEE_OTHER); // Fallback to original route
        }
        // --- *** END OF CHANGE *** ---
    }
}