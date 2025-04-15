<?php
// src/Controller/ReclamationController.php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository; // Make sure this exists
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException; // For checking user

// Assuming your User entity is App\Entity\User and has getId(), getEmail(), getUsername()
// use App\Entity\User;

#[Route('/reclamation')]
final class ReclamationController extends AbstractController
{
    #[Route('/my', name: 'app_reclamation_my', methods: ['GET'])]
    public function myReclamations(Request $request, ReclamationRepository $reclamationRepository): Response
    {
        // Use getUser() which returns the User object or null
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Please log in to view your reclamations.');
            return $this->redirectToRoute('app_login'); // Use your actual login route name
        }

        // Assuming your User entity has getId()
        $userId = $user->getId();

        $search = $request->query->get('search');
        $sort = $request->query->get('sort');

        // Ensure this repository method exists and works
        $reclamations = $reclamationRepository->findByUserWithSearchAndSort($userId, $search, $sort);

        return $this->render('reclamation/my.html.twig', [
            'reclamations' => $reclamations,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            // User should be logged in to access this route (consider adding #[IsGranted('ROLE_USER')] to controller/method)
            $this->addFlash('warning', 'Please log in to submit a reclamation.');
            return $this->redirectToRoute('app_login');
        }

        $reclamation = new Reclamation();

        // --- Set properties from User BEFORE creating/handling the form ---
        // Use getUsername() for nom - ADJUST IF YOUR GETTER IS DIFFERENT
        $reclamation->setNom(method_exists($user, 'getUsername') ? $user->getUsername() : 'N/A');
        // Set prenom to null as user entity doesn't have it
        $reclamation->setPrenom(null);
        // Set email from user
        $reclamation->setEmail($user->getEmail());
        // Set numTele to null as user entity doesn't have it and entity allows null now
        $reclamation->setNumTele(null);
        // Set user ID
        $reclamation->setUtilisateurId($user->getId());
        // Etat and Date are set by the constructor

        // Create the form, passing the PRE-POPULATED reclamation object and user option
        $form = $this->createForm(ReclamationType::class, $reclamation, [
            'user' => $user, // Pass user for display fields in the form
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Only sujet and description are set via the form binding

            $entityManager->persist($reclamation);
            $entityManager->flush();

            $this->addFlash('success', 'Reclamation submitted successfully!');

            return $this->redirectToRoute('app_reclamation_my', [], Response::HTTP_SEE_OTHER);

        } elseif ($form->isSubmitted() && !$form->isValid()) {
            // Add a generic error flash if form is submitted but invalid
            // Specific field errors should be displayed by the form theme in Twig
            $this->addFlash('error', 'Please check the form for errors.');
        }

        return $this->render('reclamation/new.html.twig', [ // Your form template path
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        // TODO: Add authorization check: Does the current user own this reclamation OR are they an admin?
        // Example: $this->denyAccessUnlessGranted('VIEW', $reclamation); or manual check
        // if ($this->getUser() !== $reclamation->getUtilisateur() && !$this->isGranted('ROLE_ADMIN')) {
        //    throw new AccessDeniedException('You cannot view this reclamation.');
        // }

        return $this->render('reclamation/show.html.twig', [ // Your show template path
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        // TODO: Add authorization check: Does the current user own this reclamation OR are they an admin?
        // Example: $this->denyAccessUnlessGranted('EDIT', $reclamation);

        // We don't need the 'user' option here if just editing subject/description
        $form = $this->createForm(ReclamationType::class, $reclamation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Only sujet/description potentially updated by form binding
            // You might want to manually set other fields here if admins can edit them
            // e.g., $reclamation->setEtat($form->get('etat')->getData()); if you added an 'etat' field to the form for admins

            $entityManager->flush();

            $this->addFlash('success', 'Reclamation updated successfully!');

            return $this->redirectToRoute('app_reclamation_my', [], Response::HTTP_SEE_OTHER);
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Please check the form for errors.');
        }

        return $this->render('reclamation/edit.html.twig', [ // Your edit template path
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        // TODO: Add authorization check

        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
            $this->addFlash('success', 'Reclamation deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid security token. Deletion failed.');
        }
        return $this->redirectToRoute('app_reclamation_my');
    }
}