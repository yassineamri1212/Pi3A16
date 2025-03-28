<?php
// Language: php
// File: src/Controller/Admin/DashboardController.php

namespace App\Controller\Admin;

use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(EvenementRepository $eventRepo): Response
    {
        return $this->render('admin/dashboard/index.html.twig', [
            'events' => $eventRepo->findAll(),
        ]);
    }

    #[Route('/event-stats', name: 'admin_event_stats')]
    public function eventStats(EvenementRepository $evenementRepository): Response
    {
        $stats = $evenementRepository->getEventStatistics();
        return $this->render('admin/event_stats.html.twig', [
            'stats' => $stats,
        ]);
    }
}