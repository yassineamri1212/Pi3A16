<?php
// File: src/Controller/UserReservedTripsController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}