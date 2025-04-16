<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Repository\OffreRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Class name matches the file name PublicOfferController.php
class PublicOfferController extends AbstractController
{
    #[Route('/carpool-offers', name: 'app_public_offers_index', methods: ['GET'])]
    public function index(
        Request $request,
        OffreRepository $offreRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $searchTerm = $request->query->get('q');
        $sort = $request->query->get('sort', 'o.dateDepart');
        $direction = $request->query->get('direction', 'ASC');

        $queryBuilder = $offreRepository->findBySearchQueryBuilder($searchTerm, $sort, $direction);

        $queryBuilder
            ->andWhere('o.dateDepart >= :now')
            ->setParameter('now', new \DateTimeImmutable());

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            9
        );

        // --- CORRECTED TEMPLATE PATH ---
        // Point to the actual location: templates/public/offers/index.html.twig
        return $this->render('public/offers/index.html.twig', [
            'pagination' => $pagination,
            'searchTerm' => $searchTerm,
            'sort' => $sort,
            'direction' => $direction,
        ]);
        // --- END CORRECTION ---
    }

    #[Route('/carpool-offers/{idOffre}', name: 'app_public_offre_show', requirements: ['idOffre' => '\d+'], methods: ['GET'])]
    public function show(Offre $offre): Response
    {
        if ($offre->getDateDepart() < new \DateTimeImmutable()) {
            $this->addFlash('warning', 'This carpool offer has already departed.');
            return $this->redirectToRoute('app_public_offers_index');
        }

        // --- CORRECTED TEMPLATE PATH ---
        // Point to the actual location: templates/public/offers/show.html.twig
        // NOTE: You still need to CREATE this 'show.html.twig' file inside 'templates/public/offers/'
        return $this->render('public/offers/show.html.twig', [
            'offre' => $offre,
        ]);
        // --- END CORRECTION ---
    }
}