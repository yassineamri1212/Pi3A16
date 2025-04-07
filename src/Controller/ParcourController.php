<?php

namespace App\Controller;

use App\Entity\Parcour;
use App\Form\ParcourType;
use App\Repository\ParcourRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/parcour')] // Base route prefix for admin actions
#[IsGranted('ROLE_ADMIN')] // Secure the whole controller
class ParcourController extends AbstractController
{
    // --- ADMIN ACTIONS ---

    #[Route('/', name: 'app_admin_parcour_index', methods: ['GET'])] // <<< RENAMED ROUTE
    public function index(
        Request $request,
        ParcourRepository $parcourRepository,
        PaginatorInterface $paginator
    ): Response
    {
        // Using simple QueryBuilder for now, add search later if needed
        $queryBuilder = $parcourRepository->createQueryBuilder('p')
            ->orderBy('p.trajet', 'ASC'); // Default order

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            15 // Items per page
        );

        return $this->render('parcour/index.html.twig', [ // Renders admin template
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_admin_parcour_new', methods: ['GET', 'POST'])] // <<< RENAMED ROUTE
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $parcour = new Parcour();
        $form = $this->createForm(ParcourType::class, $parcour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($parcour);
            $entityManager->flush();
            $this->addFlash('success', 'Parcour created successfully.');
            // Redirect to the RENAMED admin index route
            return $this->redirectToRoute('app_admin_parcour_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('parcour/new.html.twig', [ // Renders admin template
            'parcour' => $parcour,
            'form' => $form,
        ]);
    }

    #[Route('/{idParcours}', name: 'app_admin_parcour_show', requirements: ['idParcours' => '\d+'], methods: ['GET'])] // <<< RENAMED ROUTE
    public function show(Parcour $parcour): Response // ParamConverter finds Parcour by ID
    {
        return $this->render('parcour/show.html.twig', [ // Renders admin template
            'parcour' => $parcour,
        ]);
    }

    #[Route('/{idParcours}/edit', name: 'app_admin_parcour_edit', requirements: ['idParcours' => '\d+'], methods: ['GET', 'POST'])] // <<< RENAMED ROUTE
    public function edit(Request $request, Parcour $parcour, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParcourType::class, $parcour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Parcour updated successfully.');
            // Redirect to the RENAMED admin index route
            return $this->redirectToRoute('app_admin_parcour_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('parcour/edit.html.twig', [ // Renders admin template
            'parcour' => $parcour,
            'form' => $form,
        ]);
    }

    #[Route('/{idParcours}', name: 'app_admin_parcour_delete', requirements: ['idParcours' => '\d+'], methods: ['POST'])] // <<< RENAMED ROUTE
    public function delete(Request $request, Parcour $parcour, EntityManagerInterface $entityManager): Response
    {
        // Check if used by Offres
        if (!$parcour->getOffres()->isEmpty()) {
            $this->addFlash('error', sprintf(
                    'Cannot delete Parcour "%s". It is used by %d Offre(s).',
                    $parcour->getTrajet(),
                    $parcour->getOffres()->count()
                )
            );
            // Redirect to the RENAMED admin index route
            return $this->redirectToRoute('app_admin_parcour_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete'.$parcour->getIdParcours(), $request->request->get('_token'))) {
            $entityManager->remove($parcour);
            $entityManager->flush();
            $this->addFlash('success', 'Parcour deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token. Deletion failed.');
        }

        // Redirect to the RENAMED admin index route
        return $this->redirectToRoute('app_admin_parcour_index', [], Response::HTTP_SEE_OTHER);
    }
}