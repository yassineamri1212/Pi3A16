<?php

namespace App\Controller\Conducteur;

use App\Entity\Offre;
use App\Form\OffreType;
use App\Repository\OffreRepository;
use App\Repository\ReservationOfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// <<< CORRECTED: Use ReservationOffer entity >>>
// <<< CORRECTED: Use ReservationOfferRepository >>>

#[Route('/conducteur/offre')]
#[IsGranted('ROLE_CONDUCTEUR')]
class OffreController extends AbstractController
{
    private function checkOwnership(Offre $offre): void
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User || $offre->getConducteur() !== $user) {
            throw new AccessDeniedHttpException('You are not allowed to access this offer.');
        }
    }

    #[Route('/', name: 'app_conducteur_offre_index', methods: ['GET'])]
    public function index(
        Request $request,
        OffreRepository $offreRepository,
        PaginatorInterface $paginator
    ): Response
    {
        /** @var \App\Entity\User $conducteur */
        $conducteur = $this->getUser();
        $searchTerm = $request->query->get('q');
        $sort = $request->query->get('sort', 'o.dateDepart');
        $direction = $request->query->get('direction', 'DESC');

        $queryBuilder = $offreRepository->findConducteurOffresQueryBuilder(
            $conducteur->getId(),
            $searchTerm,
            $sort,
            $direction
        );

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            15
        );

        // <<< Render CONDUCTEUR offre index template >>>
        return $this->render('conducteur/offre/index.html.twig', [
            'pagination' => $pagination,
            'searchTerm' => $searchTerm,
        ]);
    }

    #[Route('/new', name: 'app_conducteur_offre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $offre = new Offre();
        /** @var \App\Entity\User $conducteur */
        $conducteur = $this->getUser();
        $offre->setConducteur($conducteur);

        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($offre);
            $entityManager->flush();
            $this->addFlash('success', 'Your offre has been created successfully.');
            return $this->redirectToRoute('app_conducteur_offre_index', [], Response::HTTP_SEE_OTHER);
        }

        // <<< Render CONDUCTEUR offre new template >>>
        return $this->render('conducteur/offre/new.html.twig', [
            'offre' => $offre,
            'form' => $form,
        ]);
    }

    #[Route('/{idOffre}', name: 'app_conducteur_offre_show', requirements: ['idOffre' => '\d+'], methods: ['GET'])]
    public function show(Offre $offre): Response
    {
        $this->checkOwnership($offre);

        // <<< Render CONDUCTEUR offre show template >>>
        return $this->render('conducteur/offre/show.html.twig', [
            'offre' => $offre,
        ]);
    }

    #[Route('/{idOffre}/reservations', name: 'app_conducteur_offre_reservations', requirements: ['idOffre' => '\d+'], methods: ['GET'])]
    // <<< CORRECTED: Inject ReservationOfferRepository >>>
    public function showReservations(Offre $offre, ReservationOfferRepository $reservationOfferRepository): Response
    {
        $this->checkOwnership($offre);

        // Fetch reservations specifically for this offer
        $reservations = $reservationOfferRepository->findBy(['offre' => $offre], ['createdAt' => 'DESC']);

        // <<< Render CONDUCTEUR offre reservations template >>>
        return $this->render('conducteur/offre/reservations.html.twig', [
            'offre' => $offre,
            'reservations' => $reservations,
        ]);
    }


    #[Route('/{idOffre}/edit', name: 'app_conducteur_offre_edit', requirements: ['idOffre' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Offre $offre, EntityManagerInterface $entityManager): Response
    {
        $this->checkOwnership($offre);

        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Your offre has been updated successfully.');
            return $this->redirectToRoute('app_conducteur_offre_index', [], Response::HTTP_SEE_OTHER);
        }

        // <<< Render CONDUCTEUR offre edit template >>>
        return $this->render('conducteur/offre/edit.html.twig', [
            'offre' => $offre,
            'form' => $form,
        ]);
    }

    #[Route('/{idOffre}', name: 'app_conducteur_offre_delete', requirements: ['idOffre' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Offre $offre, EntityManagerInterface $entityManager): Response
    {
        $this->checkOwnership($offre);

        if ($this->isCsrfTokenValid('delete'.$offre->getIdOffre(), $request->request->get('_token'))) {
            $entityManager->remove($offre);
            $entityManager->flush();
            $this->addFlash('success', 'Your offre has been deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token. Deletion failed.');
        }
        return $this->redirectToRoute('app_conducteur_offre_index', [], Response::HTTP_SEE_OTHER);
    }
}