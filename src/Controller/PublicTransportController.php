<?php
            // File: src/Controller/PublicTransportController.php
            namespace App\Controller;

            use App\Entity\Evenement;
            use App\Repository\EvenementRepository;
            use App\Repository\MoyenDeTransportRepository;
            use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
            use Symfony\Component\HttpFoundation\Request;
            use Symfony\Component\HttpFoundation\Response;
            use Symfony\Component\Routing\Annotation\Route;

            class PublicTransportController extends AbstractController
            {
                #[Route('/transports/event/{eventId}', name: 'client_transport_index', methods: ['GET'])]
                public function index(
                    int $eventId,
                    EvenementRepository $evenementRepository,
                    MoyenDeTransportRepository $moyenDeTransportRepository,
                    Request $request
                ): Response {
                    $evenement = $evenementRepository->find($eventId);
                    if (!$evenement) {
                        throw $this->createNotFoundException('Event not found');
                    }

                    $transports = $moyenDeTransportRepository->findBy(['evenement' => $evenement]);

                    return $this->render('public/transports.html.twig', [
                        'event' => $evenement,
                        'transports' => $transports,
                    ]);
                }

                #[Route('/transports/{id}/reserve', name: 'client_transport_reserve', methods: ['POST'])]
                public function reserve(
                    \App\Entity\MoyenDeTransport $transport,
                    \Doctrine\ORM\EntityManagerInterface $entityManager,
                    Request $request
                ): Response {
                    $user = $this->getUser();
                    if (!$user) {
                        return $this->redirectToRoute('app_login');
                    }

                    if ($user->getReservedTransports()->contains($transport)) {
                        $this->addFlash('error', 'You have already reserved a seat in this transport.');
                    } elseif ($transport->getNbrePlaces() > 0) {
                        $transport->setNbrePlaces($transport->getNbrePlaces() - 1);
                        $user->addReservedTransport($transport);
                        $entityManager->flush();
                        $this->addFlash('success', 'Reservation confirmed.');

                        $eventLieu = $transport->getEvenement() ? $transport->getEvenement()->getLieu() : null;
                        if ($eventLieu) {
                            $funFact = "Did you know? " . $eventLieu . " is known for its hidden gems!";
                            $this->addFlash('fun_fact', $funFact);
                        }
                    } else {
                        $this->addFlash('error', 'The ride is already full.');
                    }
                    $eventId = $transport->getEvenement() ? $transport->getEvenement()->getId() : 0;
                    return $this->redirectToRoute('client_transport_index', ['eventId' => $eventId]);
                }
            }