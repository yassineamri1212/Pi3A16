<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Entity\ReservationOffer; // Correct entity
use App\Entity\User;
use App\Repository\ReservationOfferRepository; // Correct repository
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reserve')] // Base path for reservation actions
class ReservationController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ReservationOfferRepository $reservationOfferRepository;

    // Inject services via constructor for easier access throughout the class
    public function __construct(EntityManagerInterface $entityManager, ReservationOfferRepository $reservationOfferRepository)
    {
        $this->entityManager = $entityManager;
        $this->reservationOfferRepository = $reservationOfferRepository;
    }

    #[Route('/offre/{idOffre}', name: 'app_reservation_create', requirements: ['idOffre' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')] // Must be logged in user
    public function create(
        Request $request,
        Offre $offre // ParamConverter finds the Offre
        // EntityManagerInterface and ReservationOfferRepository are now available via $this->
    ): Response {
        /** @var User $passenger */
        $passenger = $this->getUser();
        if (!$passenger) {
            $this->addFlash('error', 'You must be logged in to make a reservation.');
            return $this->redirectToRoute('app_login'); // Adjust route name if needed
        }

        // CSRF Protection FIRST
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('reserve' . $offre->getIdOffre(), $submittedToken)) {
            $this->addFlash('error', 'Invalid security token. Please try again.');
            return $this->redirectToRoute('app_public_offre_show', ['idOffre' => $offre->getIdOffre()]); // Adjust route name
        }

        // --- Basic Validation Checks ---
        if ($offre->getConducteur() === $passenger) {
            $this->addFlash('warning', 'You cannot reserve your own offer.');
            return $this->redirectToRoute('app_public_offre_show', ['idOffre' => $offre->getIdOffre()]);
        }

        // Assuming Offre entity has a method getRemainingPlaces()
        if ($offre->getRemainingPlaces() <= 0) {
            $this->addFlash('warning', 'Sorry, no more seats available for this offer.');
            return $this->redirectToRoute('app_public_offre_show', ['idOffre' => $offre->getIdOffre()]);
        }
        // --- End Basic Validation Checks ---


        // --- Core Logic: Check Existing Reservation & Decide Action ---
        $reservationToSave = null;

        // Use the repository (available via $this->) to find ANY existing reservation
        $existingReservation = $this->reservationOfferRepository->findExistingReservation(
            $offre->getIdOffre(),
            $passenger->getId() // Ensure getId() is correct for your User entity
        );

        if ($existingReservation) {
            // A record already exists
            $currentStatus = $existingReservation->getStatus();

            if ($currentStatus === 'cancelled_by_user') { // Ensure status string matches
                // Reactivate the reservation
                $existingReservation->setStatus('confirmed'); // Set to active status
                // *** REMOVED: $existingReservation->setReservationDate(new \DateTime()); ***
                $reservationToSave = $existingReservation; // Mark for update
                $this->addFlash('success', 'Your previous reservation for this offer has been reactivated.');

            } elseif ($currentStatus === 'confirmed' || $currentStatus === 'pending') {
                $this->addFlash('info', 'You already have an active reservation for this offer.');
                return $this->redirectToRoute('app_public_offre_show', ['idOffre' => $offre->getIdOffre()]);

            } else {
                $this->addFlash('warning', 'Cannot re-book this offer due to its current reservation status (' . $currentStatus . ').');
                return $this->redirectToRoute('app_public_offre_show', ['idOffre' => $offre->getIdOffre()]);
            }
        } else {
            // No existing record found - create a new one
            $newReservation = new ReservationOffer();
            $newReservation->setOffre($offre);
            $newReservation->setPassenger($passenger);
            $newReservation->setStatus('confirmed'); // Set initial active status
            // *** REMOVED: $newReservation->setReservationDate(new \DateTime()); ***

            $reservationToSave = $newReservation; // Mark for insertion
            $this->addFlash('success', 'Reservation successful!');
        }
        // --- End Core Logic ---


        // --- Perform Database Operation (if needed) ---
        if ($reservationToSave !== null) {
            try {
                // Use entity manager injected via constructor
                $this->entityManager->persist($reservationToSave);
                $this->entityManager->flush();

            } catch (\Exception $e) {
                // Log error if needed (inject LoggerInterface)
                $this->addFlash('error', 'An unexpected error occurred while saving your reservation. Please try again.');
                return $this->redirectToRoute('app_public_offre_show', ['idOffre' => $offre->getIdOffre()]);
            }
        }

        // Redirect after success
        return $this->redirectToRoute('app_public_offre_show', ['idOffre' => $offre->getIdOffre()]);
    }

    // --- Other controller actions (myReservations, cancel, etc.) ---

}