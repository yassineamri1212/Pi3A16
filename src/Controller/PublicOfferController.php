<?php

namespace App\Controller;

use App\Repository\OffreRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PublicOfferController extends AbstractController
{
    #[Route('/carpool-offers', name: 'app_public_offers_index', methods: ['GET'])]
    public function index(
        Request $request,
        OffreRepository $offreRepository,
        PaginatorInterface $paginator
    ): Response
    {
        // Get search/sort parameters from the request query string
        $searchTerm = $request->query->get('q'); // Use 'q' for search
        $sort = $request->query->get('sort', 'o.dateDepart'); // Default sort by departure date (Ascending)
        $direction = $request->query->get('direction', 'ASC');

        // Use the NEW repository method to get the QueryBuilder for *public* offers
        $queryBuilder = $offreRepository->findPublicOffersQueryBuilder($searchTerm, $sort, $direction);

        // Paginate the results
        $pagination = $paginator->paginate(
            $queryBuilder, // QueryBuilder instance
            $request->query->getInt('page', 1), // Page number
            9 // Items per page (e.g., 9 for a 3-column layout)
        );

        // Render the public listing template
        return $this->render('public/offers/index.html.twig', [
            'pagination' => $pagination,
            'searchTerm' => $searchTerm,
            'currentSort' => $sort,       // Pass sort/direction for search form state
            'currentDirection' => $direction,
        ]);
    }

    // Optional: Add a route for showing a single offer details page later
    // #[Route('/carpool-offers/{idOffre}', name: 'app_public_offers_show', requirements: ['idOffre' => '\d+'], methods: ['GET'])]
    // public function show(Offre $offre): Response { ... render a details template ... }

}