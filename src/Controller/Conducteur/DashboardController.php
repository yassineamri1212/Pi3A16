<?php

namespace App\Controller\Conducteur; // Correct namespace

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/conducteur')] // Base path for conducteur area
#[IsGranted('ROLE_CONDUCTEUR')] // Secure the entire controller
class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_conducteur_dashboard', methods: ['GET'])]
    public function index(): Response
    {
        $conducteur = $this->getUser();
        // Fetch some stats later if needed (e.g., number of offers, upcoming departures)
        return $this->render('conducteur/dashboard/index.html.twig', [
            'conducteur' => $conducteur,
        ]);
    }
}