<?php

namespace App\Controller;

use App\Entity\MoyenDeTransport;
use App\Form\MoyenDeTransportType;
use App\Repository\MoyenDeTransportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/transport')]
#[IsGranted('ROLE_ADMIN')]
final class MoyenDeTransportController extends AbstractController
{
    #[Route('/', name: 'admin_moyen_de_transport_index', methods: ['GET'])]
    public function index(MoyenDeTransportRepository $moyenDeTransportRepository): Response
    {
        return $this->render('admin/moyen_de_transport/index.html.twig', [
            'moyen_de_transports' => $moyenDeTransportRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_moyen_de_transport_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $moyenDeTransport = new MoyenDeTransport();
        $form = $this->createForm(MoyenDeTransportType::class, $moyenDeTransport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($moyenDeTransport);
            $entityManager->flush();

            return $this->redirectToRoute('admin_moyen_de_transport_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/moyen_de_transport/new.html.twig', [
            'moyen_de_transport' => $moyenDeTransport,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_moyen_de_transport_show', methods: ['GET'])]
    public function show(MoyenDeTransport $transport): Response
    {
        return $this->render('admin/moyen_de_transport/show.html.twig', [
            'transport' => $transport,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_moyen_de_transport_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MoyenDeTransport $moyenDeTransport, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MoyenDeTransportType::class, $moyenDeTransport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_moyen_de_transport_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/moyen_de_transport/edit.html.twig', [
            'moyen_de_transport' => $moyenDeTransport,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_moyen_de_transport_delete', methods: ['POST'])]
    public function delete(Request $request, MoyenDeTransport $moyenDeTransport, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$moyenDeTransport->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($moyenDeTransport);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_moyen_de_transport_index', [], Response::HTTP_SEE_OTHER);
    }
}