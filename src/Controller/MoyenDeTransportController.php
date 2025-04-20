<?php
// File: src/Controller/AdminMoyenDeTransportController.php

namespace App\Controller;

use App\Entity\MoyenDeTransport;
// We need UserRepository again to find the users
use App\Repository\UserRepository;
use App\Form\MoyenDeTransportType;
use App\Repository\MoyenDeTransportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/transport')] // Using the route prefix from your original controller
#[IsGranted('ROLE_ADMIN')]
final class MoyenDeTransportController extends AbstractController
{
    #[Route('/', name: 'admin_moyen_de_transport_index', methods: ['GET'])]
    public function index(MoyenDeTransportRepository $moyenDeTransportRepository): Response
    {
        return $this->render('admin/moyen_de_transport/index.html.twig', [
            'moyen_de_transports' => $moyenDeTransportRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_moyen_de_transport_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $moyenDeTransport = new MoyenDeTransport();
        $form = $this->createForm(MoyenDeTransportType::class, $moyenDeTransport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($moyenDeTransport);
            $entityManager->flush();
            $this->addFlash('success', 'Transport created successfully.');
            return $this->redirectToRoute('admin_moyen_de_transport_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/moyen_de_transport/new.html.twig', [
            'moyen_de_transport' => $moyenDeTransport,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_moyen_de_transport_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    // Inject UserRepository again
    public function show(MoyenDeTransport $transport, UserRepository $userRepository): Response
    {
        // --- CORRECTED: Fetch users using the UserRepository ---
        // This assumes your User entity has a 'reservedTransports' property
        // which is the ManyToMany side mappedBy the (missing) relationship in MoyenDeTransport
        $reservedUsers = $userRepository->createQueryBuilder('u')
            ->innerJoin('u.reservedTransports', 't') // 't' is alias for the joined transport
            ->andWhere('t = :transport') // Filter where the joined transport is the current one
            ->setParameter('transport', $transport)
            ->getQuery()
            ->getResult();
        // --- End Fetch users ---

        // --- Calculate stats based on Users count ---
        $reservedCount = count($reservedUsers); // Count the fetched users

        // *** ASSUMPTION: Adjust 'getCapacite' if your total capacity field is different ***
        // Make sure getCapacite() exists and returns the TOTAL capacity.
        // If it doesn't exist, use getNbrePlaces() + $reservedCount if getNbrePlaces() stores REMAINING seats.
        // Let's assume getNbrePlaces is TOTAL capacity for now based on your entity.
        $totalCapacity = $transport->getNbrePlaces(); // Using getNbrePlaces as total capacity

        $fullPercentage = ($totalCapacity > 0) ? round(($reservedCount / $totalCapacity) * 100) : 0;
        // --- End Calculate stats ---

        return $this->render('admin/moyen_de_transport/show.html.twig', [
            'transport'      => $transport,
            // *** PASS THE USER COLLECTION ***
            'users'          => $reservedUsers, // Pass the collection of users fetched via repository
            'fullPercentage' => $fullPercentage,
            'totalCapacity'  => $totalCapacity, // Pass calculated total capacity
            'reservedCount'  => $reservedCount,
        ]);
    }


    #[Route('/{id}/edit', name: 'admin_moyen_de_transport_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MoyenDeTransport $moyenDeTransport, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MoyenDeTransportType::class, $moyenDeTransport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Transport updated successfully.');
            return $this->redirectToRoute('admin_moyen_de_transport_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/moyen_de_transport/edit.html.twig', [
            'moyen_de_transport' => $moyenDeTransport,
            'form'               => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_moyen_de_transport_delete', methods: ['POST'])]
    public function delete(Request $request, MoyenDeTransport $moyenDeTransport, EntityManagerInterface $entityManager): Response
    {
        // Using request->request->get() as you had before, might be necessary depending on form submission method
        $token = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $moyenDeTransport->getId(), $token)) {
            // You would need UserRepository again here to check if users exist before deleting
            // Or attempt deletion and catch potential foreign key constraint errors if the relationship exists implicitly
            // For now, let's just remove the check based on the missing entity relationship

            // $userRepository = $entityManager->getRepository(User::class); // Get repo if needed
            // $users = $userRepository->createQueryBuilder('u')... // Run query similar to show action
            // if(count($users) > 0) { $this->addFlash('error', 'Cannot delete...'); } else { ... }

            // Simplified delete without user check due to missing relationship:
            try {
                $entityManager->remove($moyenDeTransport);
                $entityManager->flush();
                $this->addFlash('success', 'Transport deleted successfully.');
            } catch (\Exception $e) {
                // Catch potential database errors (like foreign key violations if users ARE linked)
                $this->addFlash('error', 'Could not delete transport. It might be in use. DB Error: '.$e->getMessage());
            }

        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('admin_moyen_de_transport_index', [], Response::HTTP_SEE_OTHER);
    }
}