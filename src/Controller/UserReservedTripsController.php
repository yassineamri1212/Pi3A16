<?php
            // File: src/Controller/UserReservedTripsController.php
            namespace App\Controller;

            use App\Entity\MoyenDeTransport;
            use Doctrine\ORM\EntityManagerInterface;
            use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
            use Symfony\Component\HttpFoundation\Request;
            use Symfony\Component\HttpFoundation\Response;
            use Symfony\Component\Routing\Annotation\Route;

            class UserReservedTripsController extends AbstractController
            {
                #[Route('/my-reserved-trips', name: 'client_reserved_trips', methods: ['GET'])]
                public function index(): Response
                {
                    $user = $this->getUser();
                    if (!$user) {
                        return $this->redirectToRoute('app_login');
                    }
                    $reservedTransports = $user->getReservedTransports();

                    return $this->render('public/reserved_trips.html.twig', [
                        'reservedTransports' => $reservedTransports,
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
            }