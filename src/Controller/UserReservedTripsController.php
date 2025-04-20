<?php
// File: src/Controller/UserReservedTripsController.php
namespace App\Controller;

use App\Entity\MoyenDeTransport;
use App\Entity\ReservationOffer;
use App\Repository\ReservationOfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserReservedTripsController extends AbstractController
{
    #[Route('/my-reserved-trips', name: 'client_reserved_trips', methods: ['GET'])]
    public function index(ReservationOfferRepository $reservationOfferRepo): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Get the user's reserved transports for events
        $reservedTransports = $user->getReservedTransports();

        // Get the user's reserved carpool offers
        $reservedOffers = $reservationOfferRepo->findBy([
            'passenger' => $user->getId(),
            'status' => 'confirmed'
        ]);

        return $this->render('public/reserved_trips.html.twig', [
            'reservedTransports' => $reservedTransports,
            'reservedOffers' => $reservedOffers,
        ]);
    }

    #[Route('/my-reserved-trips/cancel/{id}', name: 'client_transport_cancel', methods: ['POST'])]
    public function cancelReservation(
        MoyenDeTransport $transport,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Check if the transport is actually reserved by the user.
        if (!$user->getReservedTransports()->contains($transport)) {
            $this->addFlash('error', 'Reservation not found.');
        } else {
            // Remove reserved transport and update available seats.
            $user->removeReservedTransport($transport);
            $transport->setNbrePlaces($transport->getNbrePlaces() + 1);
            $entityManager->flush();
            $this->addFlash('success', 'Reservation cancelled successfully.');
        }

        return $this->redirectToRoute('client_reserved_trips');
    }

    #[Route('/my-reserved-trips/cancel-offer/{id}', name: 'client_offer_cancel', methods: ['POST'])]
    public function cancelOfferReservation(
        ReservationOffer $reservation,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Check if the reservation actually belongs to this user
        if ($reservation->getPassenger()->getId() !== $user->getId()) {
            $this->addFlash('error', 'Reservation not found or not authorized.');
            return $this->redirectToRoute('client_reserved_trips');
        }

        // Get the offer to update remaining places
        $offer = $reservation->getOffre();

        // Set status to cancelled
        $reservation->setStatus('cancelled_by_user');
        $entityManager->flush();

        $this->addFlash('success', 'Carpool reservation cancelled successfully.');
        return $this->redirectToRoute('client_reserved_trips');
    }
}