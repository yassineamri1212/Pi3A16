<?php
// php
namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\HttpFoundation\File\UploadedFile; // Unused, can be removed
use App\Entity\MoyenDeTransport; // Import the transport entity

#[Route('/admin/evenement')]
class EvenementController extends AbstractController
{
    #[Route('/', name: 'admin_evenement_index', methods: ['GET'])]
    public function index(EvenementRepository $evenementRepository): Response
    {
        // This action remains unchanged
        return $this->render('admin/evenement/index.html.twig', [
            'evenements' => $evenementRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // This action remains unchanged
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($evenement);
                $entityManager->flush();

                $this->addFlash('success', 'Event created successfully.');
                return $this->redirectToRoute('admin_evenement_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                error_log('Error saving new event: ' . $e->getMessage());
                $this->addFlash('error', 'An error occurred while saving the event.');
            }
        }

        return $this->render('admin/evenement/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement, UserRepository $userRepository): Response
    {
        // --- Start: Corrected logic for transport stats ---

        // Fetch users who reserved *any* transport for this event
        // Eager load the transports to avoid extra queries in the loop
        $users = $userRepository->createQueryBuilder('u')
            ->select('u, t') // Select user AND their transport data
            ->leftJoin('u.reservedTransports', 't') // Assumes User entity has 'reservedTransports' relation
            ->where('t.evenement = :evenement')  // Assumes MoyenDeTransport 't' has an 'evenement' relation
            ->setParameter('evenement', $evenement)
            ->getQuery()
            ->getResult();

        // Calculate Actual Transport Statistics
        // Use an associative array, normalizing the type key (e.g., to Title Case) for consistency
        $statsByType = [];

        foreach ($users as $user) {
            if ($user->getReservedTransports() instanceof \Doctrine\Common\Collections\Collection) {
                foreach ($user->getReservedTransports() as $transport) {
                    // Assumes MoyenDeTransport entity has getEvenement() and getType() methods
                    if ($transport instanceof MoyenDeTransport && $transport->getEvenement() && $transport->getEvenement()->getId() === $evenement->getId()) {
                        $typeFromDb = $transport->getType(); // e.g., 'BUS', 'lawaj', 'Car'
                        if ($typeFromDb) {
                            // Normalize the key: Convert to lower case then capitalize first letter (Title Case)
                            $normalizedType = ucfirst(strtolower($typeFromDb)); // 'BUS' -> 'Bus', 'lawaj' -> 'Lawaj', 'Car' -> 'Car'

                            if (!isset($statsByType[$normalizedType])) {
                                $statsByType[$normalizedType] = 0;
                            }
                            $statsByType[$normalizedType]++;
                        }
                    }
                }
            }
        }

        // Format for Chart.js, Ensuring Default Types Exist
        // Define the types you ALWAYS want to see on the chart axis (use the SAME normalized case)
        $defaultTypes = ['Bus', 'Train', 'Car']; // Use Title Case matching normalization above

        // Ensure default types exist in the calculated stats, adding them with 0 count if missing
        foreach ($defaultTypes as $defaultType) {
            if (!array_key_exists($defaultType, $statsByType)) {
                $statsByType[$defaultType] = 0;
            }
        }

        // Convert the final associative array ($statsByType) to the indexed array format needed by Chart.js
        $transportStats = [];
        foreach ($statsByType as $type => $count) {
            $transportStats[] = ['type' => $type, 'userCount' => $count];
        }

        // Optional: Sort the final array alphabetically by type for consistent chart order
        usort($transportStats, fn($a, $b) => strcmp($a['type'], $b['type']));

        // --- End Statistics Calculation & Formatting ---


        // Render the template
        return $this->render('admin/evenement/show.html.twig', [
            'evenement'     => $evenement,
            'users'         => $users, // Pass the fetched users list
            'transportStats'=> $transportStats, // Pass the correctly formatted and de-duplicated stats
        ]);
    }

    // language: php
    #[Route('/{id}/edit', name: 'admin_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        // This action remains unchanged
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', 'Event updated successfully.');
                return $this->redirectToRoute('admin_evenement_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                error_log('Error updating event ID ' . $evenement->getId() . ': ' . $e->getMessage());
                $this->addFlash('error', 'An error occurred while updating the event.');
                // $this->addFlash('error', 'Update Error: ' . $e->getMessage()); // Optional for more detail in dev
            }
        }

        return $this->render('admin/evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form'      => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        // This action remains unchanged
        if ($this->isCsrfTokenValid('delete' . $evenement->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($evenement);
                $entityManager->flush();
                $this->addFlash('success', 'Event deleted successfully.');
            } catch (\Exception $e) {
                error_log('Error deleting event ID ' . $evenement->getId() . ': ' . $e->getMessage());
                $this->addFlash('error', 'An error occurred while deleting the event.');
            }
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('admin_evenement_index', [], Response::HTTP_SEE_OTHER);
    }
}