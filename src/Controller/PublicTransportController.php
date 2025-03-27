<?php
// File: src/Controller/PublicTransportController.php
namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\MoyenDeTransport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PublicTransportController extends AbstractController
{
    #[Route('/events/{eventId}/transports', name: 'client_transport_index', methods: ['GET'])]
    public function listTransports(int $eventId, EntityManagerInterface $entityManager): Response
    {
        $evenement = $entityManager->getRepository(Evenement::class)->find($eventId);
        if (!$evenement) {
            throw $this->createNotFoundException('Event not found');
        }
        $transports = $evenement->getMoyenDeTransports();

        return $this->render('public/transports.html.twig', [
            'event' => $evenement,
            'transports' => $transports,
        ]);
    }

    #[Route('/transports/{id}/reserve', name: 'client_transport_reserve', methods: ['POST'])]
    public function reserve(MoyenDeTransport $transport, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        // Check if user has already reserved this transport
        if ($user->getReservedTransports()->contains($transport)) {
            $this->addFlash('error', 'You have already reserved a seat in this transport.');
        } elseif ($transport->getNbrePlaces() > 0) {
            $transport->setNbrePlaces($transport->getNbrePlaces() - 1);
            $user->addReservedTransport($transport);
            $entityManager->flush();
            $this->addFlash('success', 'Reservation confirmed.');
        } else {
            $this->addFlash('error', 'The ride is already full.');
        }
        $eventId = $transport->getEvenement() ? $transport->getEvenement()->getId() : 0;
        return $this->redirectToRoute('client_transport_index', ['eventId' => $eventId]);
    }
}