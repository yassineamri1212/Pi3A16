<?php

namespace App\Controller\Admin;

use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class DashboardController extends AbstractController
{
#[Route('/', name: 'admin_dashboard')]
public function index(EvenementRepository $eventRepo)
{
return $this->render('admin/dashboard/index.html.twig', [
'events' => $eventRepo->findAll(),
]);
}
}