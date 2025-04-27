<?php
// src/Controller/ReponseController.php

namespace App\Controller;

use App\Entity\Reponse;
use App\Entity\Reclamation;
use App\Form\ReponseType;
use App\Repository\ReclamationRepository; // Assuming you use this
use App\Service\MailingApiService; // Keep if you use this
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException; // For authorization checks

// Consider adding security to the whole controller if only admins access this
// use Symfony\Component\Security\Http\Attribute\IsGranted;
// #[IsGranted('ROLE_ADMIN')]

// Changed base route for clarity - assumes this is ADMIN response handling
#[Route('/admin/reponse')]
final class ReponseController extends AbstractController
{
    // --- NEW RESPONSE Action (typically called from Reclamation show page) ---
    // Note: This action might be better placed within AdminReclamationController
    // as it's tightly coupled to showing/adding a response TO a reclamation.
    // If kept separate, the route name should reflect admin context.

    #[Route('/new/for/{reclamationId}', name: 'admin_reponse_new', requirements: ['reclamationId' => '\d+'], methods: ['POST'])]
    public function new(
        Request $request,
        int $reclamationId, // Get ID directly from route
        ReclamationRepository $reclamationRepository,
        EntityManagerInterface $entityManager,
        MailingApiService $mailingApiService // Optional service
    ): Response {
        // Check if user is allowed to respond (e.g., is admin)
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); // Or your appropriate role

        $reclamation = $reclamationRepository->find($reclamationId);
        if (!$reclamation) {
            throw $this->createNotFoundException('Reclamation not found.');
        }

        $reponse = new Reponse();
        // Create form WITH the response object
        $form = $this->createForm(ReponseType::class, $reponse);

        // Set properties NOT coming from the form BEFORE handling request
        $reponse->setReclamation($reclamation); // Link to the parent reclamation
        // Date is set by constructor

        // Set user ID and username based on logged-in admin user
        $adminUser = $this->getUser();
        if ($adminUser) {
            // Adjust getUserIdentifier() if you use a different method for admin username
            $reponse->setUsername(method_exists($adminUser,'getUserIdentifier') ? $adminUser->getUserIdentifier() : 'Admin');
            // Assuming admin user has getId()
            $reponse->setUtilisateurId($adminUser->getId());
        } else {
            // Should not happen if secured, but set defaults if needed
            $reponse->setUsername('System');
            $reponse->setUtilisateurId(0); // Or handle as error
            throw new AccessDeniedException('Admin user not found for response.');
        }

        // Handle the request AFTER setting non-form properties
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 'reponse' text is set by form binding
            $reclamation->setEtat('handled'); // or your desired status
            $entityManager->persist($reclamation);
            $entityManager->persist($reponse);
            $entityManager->flush();

            // Attempt to send notification email
            try {
                // Ensure $reclamation->getEmail() and ->getNom() are correct
                $mailingApiService->sendReponseNotification($reclamation->getEmail(), $reclamation->getNom());
                $this->addFlash('success', 'Response submitted successfully and user notified.');
            } catch (\Exception $e) {
                error_log('Mail notification failed: ' . $e->getMessage()); // Log the actual error
                $this->addFlash('warning', 'Response submitted, but notification email could not be sent.');
            }

            // Redirect back to the reclamation's show page
            return $this->redirectToRoute('admin_reclamations_show', ['id' => $reclamation->getId()], Response::HTTP_SEE_OTHER);

        } elseif ($form->isSubmitted() && !$form->isValid()) {
            // If form is invalid, add errors as flash messages to be displayed on the reclamation show page
            $errorMessages = [];
            foreach ($form->getErrors(true, true) as $error) {
                $errorMessages[] = $error->getMessage();
            }
            $this->addFlash('error', 'Failed to submit response: ' . implode(' ', $errorMessages));
            // It's better to redirect back to the show page where the form was,
            // rather than trying to re-render a dedicated 'new' template here.
            return $this->redirectToRoute('admin_reclamations_show', ['id' => $reclamation->getId()]);

        } else {
            // If accessed directly or not submitted properly
            $this->addFlash('error', 'Invalid request for submitting a response.');
            return $this->redirectToRoute('admin_reclamations_show', ['id' => $reclamation->getId()]);
        }

        // This part is usually not reached if the form is submitted via POST from the show page.
        // If you have a dedicated GET route/page for '/admin/reponse/new', render it here.
        // return $this->render('admin/reponse/new.html.twig', [
        //     'reponse' => $reponse,
        //     'form' => $form->createView(),
        //     'reclamation' => $reclamation,
        // ]);
    }


    // --- EDIT RESPONSE Action ---
    // Make sure route name reflects admin context
    #[Route('/{id}/edit', name: 'admin_reponse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        // Check if user is allowed to edit (e.g., is admin)
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); // Or check if user owns response if needed

        $reclamation = $reponse->getReclamation(); // Get parent reclamation for context/redirect

        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Maybe update date? $reponse->setDate(new \DateTime());
            $entityManager->flush(); // No persist needed for edit
            $this->addFlash('success', 'Response updated successfully.');

            // Redirect back to the reclamation show page
            if ($reclamation) {
                return $this->redirectToRoute('admin_reclamations_show', ['id' => $reclamation->getId()], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('warning', 'Response updated, but associated reclamation not found.');
                return $this->redirectToRoute('admin_reclamations_index'); // Fallback to admin reclamation index
            }
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Please check the form for errors.');
        }

        // Render the dedicated edit page
        return $this->render('admin/reponse/edit.html.twig', [ // Ensure this template exists
            'reponse' => $reponse,
            'form' => $form->createView(),
            'reclamation' => $reclamation, // Pass reclamation for context (e.g., cancel link)
        ]);
    }

    // --- DELETE RESPONSE Action ---
    // Make sure route name reflects admin context
    #[Route('/{id}/delete', name: 'admin_reponse_delete', methods: ['POST'])]
    public function delete(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        // Check if user is allowed to delete (e.g., is admin)
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $reclamation = $reponse->getReclamation(); // Get parent reclamation for redirect
        $reclamationId = $reclamation ? $reclamation->getId() : null;

        // Use request->request->get() for POST data
        if ($this->isCsrfTokenValid('delete' . $reponse->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reponse);
            $entityManager->flush();
            $this->addFlash('success', 'Response deleted successfully.');
        } else {
            $this->addFlash('error', 'Failed to delete response: Invalid security token.');
        }

        // Redirect back to the reclamation show page
        if ($reclamationId) {
            return $this->redirectToRoute('admin_reclamations_show', ['id' => $reclamationId], Response::HTTP_SEE_OTHER);
        } else {
            $this->addFlash('info', 'Response deleted. Could not determine original reclamation page.');
            return $this->redirectToRoute('admin_reclamations_index'); // Fallback to admin reclamation index
        }
    }

    // Removed index/show for individual responses as they are usually viewed
    // within the context of the reclamation
}