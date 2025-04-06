<?php
// Language: php
// File: src/Controller/Admin/DashboardController.php

namespace App\Controller\Admin;

use App\Repository\EvenementRepository;
use App\Repository\MoyenDeTransportRepository; // Import Transport Repo
use App\Repository\ReclamationRepository;    // Import Reclamation Repo (Make sure this exists)
use App\Repository\UserRepository;          // Import User Repo
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')] // Keep this attribute
#[Route('/admin')]         // Keep this attribute
class DashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')] // Keep this route for the main dashboard
    public function index(
        EvenementRepository $evenementRepository,       // Keep EvenementRepo (used for 'events')
        UserRepository $userRepository,                     // Inject UserRepository
        MoyenDeTransportRepository $moyenDeTransportRepository, // Inject MoyenDeTransportRepository
        ReclamationRepository $reclamationRepository         // Inject ReclamationRepository (Ensure this repo exists)
    ): Response {
        // --- Fetch Data ---
        // Fetch all events once and keep the original variable name for compatibility
        $allEvents = $evenementRepository->findAll(); // Fetch all events
        $allUsers = $userRepository->findAll();
        $allTransports = $moyenDeTransportRepository->findAll();
        $allReclamations = $reclamationRepository->findAll(); // Fetch all reclamations

        // --- Calculate Statistics ---

        // Event calculations using $allEvents
        $now = new \DateTime();
        $todayStart = new \DateTime('today');
        $upcomingEvents = [];
        $todaysEvents = [];

        foreach ($allEvents as $event) {
            $eventDate = $event->getDateEvenement();
            if ($eventDate) {
                if ($eventDate > $now) {
                    $upcomingEvents[] = $event;
                }
                if ($eventDate->format('Y-m-d') === $todayStart->format('Y-m-d')) {
                    $todaysEvents[] = $event;
                }
            }
        }

        // Reclamation calculation (Example: assumes a 'status' property or method)
        // TODO: Replace 'getStatus' and 'Pending' with your actual Reclamation entity logic
        $pendingReclamations = [];
        foreach ($allReclamations as $reclamation) {
            if (method_exists($reclamation, 'getStatus') && $reclamation->getStatus() === 'Pending') {
                $pendingReclamations[] = $reclamation;
            }
        }

        // --- Prepare Latest Items Lists ---

        // Use a copy for sorting to not modify $allEvents order if needed elsewhere (though not currently)
        $eventsToSort = $allEvents;
        usort($eventsToSort, function($a, $b) {
            $dateA = $a->getDateEvenement();
            $dateB = $b->getDateEvenement();
            if ($dateA === $dateB) return 0;
            if ($dateA === null) return 1;
            if ($dateB === null) return -1;
            return $dateB <=> $dateA; // Descending order
        });
        $latestEvents = array_slice($eventsToSort, 0, 5);

        // Latest users (using copy for sorting)
        $usersToSort = $allUsers;
        usort($usersToSort, function($a, $b) {
            // TODO: Replace 'getId' with a relevant sorting field if available (e.g., 'getRegisteredAt')
            return $b->getId() <=> $a->getId();
        });
        $latestUsers = array_slice($usersToSort, 0, 5);


        // --- Render Template with Data ---
        return $this->render('admin/dashboard/index.html.twig', [
            // KEEP ORIGINAL VARIABLE FOR CURRENT TEMPLATE
            'events' => $allEvents, // Pass the original 'events' variable

            // Pass NEW counts for the enhanced template
            'totalEventsCount' => count($allEvents),
            'upcomingEventsCount' => count($upcomingEvents),
            'todaysEventsCount' => count($todaysEvents),
            'totalUsersCount' => count($allUsers),
            'totalTransportsCount' => count($allTransports),
            'totalReclamationsCount' => count($allReclamations),
            'pendingReclamationsCount' => count($pendingReclamations),

            // Pass NEW lists of latest items for the enhanced template
            'latestEvents' => $latestEvents,
            'latestUsers' => $latestUsers,
        ]);
    }

    /**
     * Keep the separate eventStats action untouched.
     */
    #[Route('/event-stats', name: 'admin_event_stats')]
    public function eventStats(EvenementRepository $evenementRepository): Response
    {
        // Original logic or placeholder
        return new Response('Event Stats page placeholder');
    }
}