<?php

namespace App\Controller; // Keep admin namespace

use App\Entity\Offre;
// --- Ensure correct use statements ---
use App\Repository\OffreRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
// --- End use statements ---

#[Route('/admin/offre')] // Keep admin base route
#[IsGranted('ROLE_ADMIN')] // Secure for Admin only
class OffreController extends AbstractController
{
    // --- ADMIN VIEW ALL OFFRES ---
    #[Route('/', name: 'app_offre_index', methods: ['GET'])]
    public function index(
        Request $request,
        OffreRepository $offreRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $searchTerm = $request->query->get('q');
        $sort = $request->query->get('sort', 'o.dateDepart');
        $direction = $request->query->get('direction', 'DESC');

        $queryBuilder = $offreRepository->findBySearchQueryBuilder($searchTerm, $sort, $direction);

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            15
        );

        // <<< Render ADMIN offre index template >>>
        return $this->render('offre/index.html.twig', [
            'pagination' => $pagination,
            'searchTerm' => $searchTerm,
            'is_admin_view' => true // Flag for template
        ]);
    }

    // --- ADMIN VIEW SINGLE OFFRE ---
    #[Route('/{idOffre}', name: 'app_offre_show', requirements: ['idOffre' => '\d+'], methods: ['GET'])]
    public function show(Offre $offre): Response
    {
        // <<< Render ADMIN offre show template >>>
        return $this->render('offre/show.html.twig', [
            'offre' => $offre,
            'is_admin_view' => true // Flag for template
        ]);
    }

    // Modification/Deletion actions remain commented out or removed
}