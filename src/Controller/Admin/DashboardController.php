<?php

namespace App\Controller\Admin;

use App\Repository\EvenementRepository;
use App\Repository\MoyenDeTransportRepository;
use App\Repository\ReclamationRepository;
use App\Repository\UserRepository;
// --- ADD Use statement for the ReservationOffer repository ---
use App\Repository\ReservationOfferRepository;
// --- END ADD ---
use Psr\Log\LoggerInterface; // For logging errors
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(
        EvenementRepository $evenementRepository,
        UserRepository $userRepository,
        MoyenDeTransportRepository $moyenDeTransportRepository,
        ReclamationRepository $reclamationRepository,
        // --- Inject the ReservationOffer Repository ---
        ReservationOfferRepository $reservationOfferRepository,
        // --- END Inject ---
        LoggerInterface $logger // Inject logger for error handling
    ): Response {
        // --- Fetch Basic Data ---
        try {
            $allEvents = $evenementRepository->findAll();
            $allUsers = $userRepository->findAll();
            $allTransports = $moyenDeTransportRepository->findAll();
            $allReclamations = $reclamationRepository->findAll();
        } catch (\Exception $e) {
            $logger->error('Failed fetching basic dashboard data: '. $e->getMessage());
            $this->addFlash('error', 'Could not load all dashboard data.');
            // Initialize arrays to prevent errors in template if loading fails
            $allEvents = $allUsers = $allTransports = $allReclamations = [];
        }

        // --- Calculate Basic Statistics ---
        $upcomingEvents = [];
        $todaysEvents = [];
        $pendingReclamations = [];
        try {
            $now = new \DateTimeImmutable();
            $todayStart = $now->setTime(0, 0, 0); // Start of today

            foreach ($allEvents as $event) {
                $eventDate = $event->getDateEvenement();
                if ($eventDate) {
                    if ($eventDate > $now) {
                        $upcomingEvents[] = $event;
                    }
                    if ($eventDate >= $todayStart && $eventDate < $todayStart->modify('+1 day')) {
                        $todaysEvents[] = $event;
                    }
                }
            }

            // Example Reclamation logic (ADJUST TO YOUR ENTITY)
            foreach ($allReclamations as $reclamation) {
                if (method_exists($reclamation, 'getStatus') && $reclamation->getStatus() === 'Pending') {
                    $pendingReclamations[] = $reclamation;
                }
            }

        } catch (\Exception $e) {
            $logger->error('Error calculating basic dashboard stats: '. $e->getMessage());
            $this->addFlash('warning', 'Could not calculate all dashboard statistics.');
        }


        // --- Calculate Latest Items ---
        $latestEvents = [];
        $latestUsers = [];
        try {
            // Latest Events
            $eventsToSort = $allEvents;
            usort($eventsToSort, function($a, $b) {
                $dateA = $a->getDateEvenement(); $dateB = $b->getDateEvenement();
                if ($dateA === $dateB) return 0; if ($dateA === null) return 1; if ($dateB === null) return -1;
                return $dateB <=> $dateA; // Descending
            });
            $latestEvents = array_slice($eventsToSort, 0, 5);

            // Latest Users
            $usersToSort = $allUsers;
            usort($usersToSort, fn($a, $b) => $b->getId() <=> $a->getId());
            $latestUsers = array_slice($usersToSort, 0, 5);
        } catch (\Exception $e) {
            $logger->error('Error sorting latest items for dashboard: '. $e->getMessage());
            $this->addFlash('warning', 'Could not display all latest items.');
        }


        // --- Fetch User Stats for Offer Reservations ---
        $topOfferReservingUsers = [];
        $statsStartDate = null;
        $statsEndDate = null;
        try {
            // Define the date range for "this week" (e.g., Monday to Sunday)
            // Ensure consistent timezone handling (e.g., UTC or your application's default)
            $timezone = new \DateTimeZone(date_default_timezone_get() ?: 'UTC');
            $statsStartDate = new \DateTimeImmutable('monday this week', $timezone);
            // Set time to beginning of the day
            $statsStartDate = $statsStartDate->setTime(0, 0, 0);
            // End date is start date + 6 days, time end of the day
            $statsEndDate = $statsStartDate->modify('+6 days')->setTime(23, 59, 59);

            // Fetch the data using the repository method
            $topOfferReservingUsers = $reservationOfferRepository->findTopReservingOfferUsers(
                $statsStartDate,
                $statsEndDate,
                5 // Get top 5 users
            );
        } catch (\Exception $e) {
            $logger->error('Failed fetching user reservation statistics: '. $e->getMessage());
            $this->addFlash('warning', 'Could not fetch user reservation statistics.');
        }
        // --- END Fetch User Stats ---


        // --- Render Template with ALL Data ---
        return $this->render('admin/dashboard/index.html.twig', [
            // Original event data (might still be needed by parts of template)
            'events' => $allEvents,

            // Basic counts
            'totalEventsCount' => count($allEvents),
            'upcomingEventsCount' => count($upcomingEvents),
            'todaysEventsCount' => count($todaysEvents),
            'totalUsersCount' => count($allUsers),
            'totalTransportsCount' => count($allTransports),
            'totalReclamationsCount' => count($allReclamations),
            'pendingReclamationsCount' => count($pendingReclamations),

            // Latest items lists
            'latestEvents' => $latestEvents,
            'latestUsers' => $latestUsers,

            // NEW User Stats Data
            'topOfferReservingUsers' => $topOfferReservingUsers,
            'statsStartDate' => $statsStartDate,
            'statsEndDate' => $statsEndDate,
        ]);
    }

    // Keep the separate eventStats action untouched for now
    // If you want EVENT booking stats PER EVENT, implement the query in its repository
    #[Route('/event-stats', name: 'admin_event_stats')]
    public function eventStats(EvenementRepository $evenementRepository): Response
    {
        // Example: Assumes EvenementRepository has this method
        // $stats = $evenementRepository->findEventBookingStats();
        $stats = []; // Placeholder
        return $this->render('admin/event_stats.html.twig', [
            'stats' => $stats
        ]);
    }
}