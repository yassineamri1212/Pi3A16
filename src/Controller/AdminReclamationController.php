<?php
            namespace App\Controller;

            use App\Entity\Reponse;
            use App\Repository\ReclamationRepository;
            use App\Service\MailingApiService;
            use Doctrine\ORM\EntityManagerInterface;
            use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
            use Symfony\Component\HttpFoundation\Request;
            use Symfony\Component\HttpFoundation\Response;
            use Symfony\Component\Routing\Annotation\Route;
            use Symfony\Bundle\SecurityBundle\Security;

            #[Route('/admin/reclamations')]
            final class AdminReclamationController extends AbstractController
            {
                #[Route('/', name: 'admin_reclamations_index', methods: ['GET'])]
                public function index(ReclamationRepository $reclamationRepository): Response
                {
                    return $this->render('admin/reclamations/index.html.twig', [
                        'reclamations' => $reclamationRepository->findAllWithResponses(),
                    ]);
                }

                #[Route('/{id}', name: 'admin_reclamations_show', methods: ['GET', 'POST'])]
                public function show(
                    Request $request,
                    $id,
                    EntityManagerInterface $entityManager,
                    Security $security,
                    ReclamationRepository $reclamationRepository,
                    MailingApiService $mailingApiService
                ): Response {
                    $reclamation = $reclamationRepository->find($id);
                    if (!$reclamation) {
                        throw $this->createNotFoundException('Reclamation not found.');
                    }

                    $reponse = new Reponse();
                    $form = $this->createForm(\App\Form\ReponseType::class, $reponse);
                    $form->handleRequest($request);

                    if ($form->isSubmitted() && $form->isValid()) {
                        $reponse->setReclamation($reclamation);
                        $reponse->setDate(new \DateTime());

                        $user = $security->getUser();
                        $reponse->setUsername($user->getUserIdentifier());
                        $reponse->setUtilisateurId($user->getId());

                        $reclamation->setEtat('answered');

                        $entityManager->persist($reponse);
                        $entityManager->flush();

                        // Send email notification to the user who submitted the reclamation.
                        $mailingApiService->sendReponseNotification($reclamation->getEmail(), $reclamation->getNom());

                        $this->addFlash('success', 'Response added successfully!');
                        return $this->redirectToRoute('admin_reclamations_show', ['id' => $reclamation->getId()]);
                    }

                    return $this->render('admin/reclamations/show.html.twig', [
                        'reclamation' => $reclamation,
                        'form' => $form->createView(),
                    ]);
                }

                #[Route('/{id}/delete', name: 'admin_reclamations_delete', methods: ['POST'])]
                public function delete(
                    Request $request,
                    \App\Entity\Reclamation $reclamation,
                    EntityManagerInterface $entityManager
                ): Response {
                    if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
                        $entityManager->remove($reclamation);
                        $entityManager->flush();
                        $this->addFlash('success', 'Reclamation deleted successfully!');
                    }

                    return $this->redirectToRoute('admin_reclamations_index');
                }
            }