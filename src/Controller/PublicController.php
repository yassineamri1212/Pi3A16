<?php

namespace App\Controller;

// Necessary Use Statements for the moved actions
use App\Repository\LivraisonRepository;
use App\Repository\OffreRepository; // If you move offer action here too
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

// NO Security constraints at the class level for public access
class PublicController extends AbstractController
{
    // --- DELIVERY ACTIONS ---

    /**
     * Displays the public list of active deliveries.
     */
    #[Route('/deliveries', name: 'app_public_deliveries_index', methods: ['GET'])]
    public function publicDeliveriesIndex(
        Request $request,
        LivraisonRepository $livraisonRepository,
        PaginatorInterface $paginator
    ): Response {
        $queryBuilder = $livraisonRepository->createQueryBuilder('l')
            ->where('l.isDelivered = :status')
            ->setParameter('status', false)
            ->orderBy('l.id', 'DESC');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('public/deliveries/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * Displays the public form to enter a Delivery ID for tracking.
     */
    #[Route('/deliveries/track', name: 'app_public_delivery_track_form', methods: ['GET'])]
    public function trackDeliveryForm(): Response
    {
        return $this->render('public/deliveries/track_form.html.twig');
    }

    /**
     * Processes the tracking form submission and shows delivery status.
     */
    #[Route('/deliveries/status', name: 'app_public_delivery_status', methods: ['GET', 'POST'])]
    public function showDeliveryStatus(Request $request, LivraisonRepository $livraisonRepository): Response
    {
        $deliveryId = null;
        $livraison = null;
        $error = null;

        if ($request->isMethod('POST')) {
            $deliveryId = filter_var($request->request->get('delivery_id'), FILTER_VALIDATE_INT);
        } elseif ($request->query->has('id')) {
            $deliveryId = filter_var($request->query->get('id'), FILTER_VALIDATE_INT);
        }

        if ($deliveryId) {
            $livraison = $livraisonRepository->find($deliveryId);
            if (!$livraison) {
                $error = "Delivery with ID '{$deliveryId}' not found.";
            }
        } elseif ($request->isMethod('POST')) {
            $error = "Please enter a valid Delivery ID.";
        }

        return $this->render('public/deliveries/status.html.twig', [
            'livraison' => $livraison,
            'submitted_id' => $deliveryId,
            'error' => $error,
        ]);
    }

    // --- You could move the Public OFFERS action here too ---
    /*
    #[Route('/offers', name: 'app_public_offers_index', methods: ['GET'])]
    public function publicOffersIndex(
        Request $request,
        OffreRepository $offreRepository, // Inject OffreRepository here
        PaginatorInterface $paginator
    ): Response {
        // ... logic from OffreController::publicIndex ...
         return $this->render('public/offers/index.html.twig', [ ... ]);
    }
    */

}