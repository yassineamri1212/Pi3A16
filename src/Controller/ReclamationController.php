<?php
            // src/Controller/ReclamationController.php

            namespace App\Controller;

            use App\Entity\Reclamation;
            use App\Form\ReclamationType;
            use App\Repository\ReclamationRepository;
            use Doctrine\ORM\EntityManagerInterface;
            use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
            use Symfony\Component\HttpFoundation\Request;
            use Symfony\Component\HttpFoundation\Response;
            use Symfony\Component\Routing\Annotation\Route;

            #[Route('/reclamation')]
            final class ReclamationController extends AbstractController
            {
                #[Route('/my', name: 'app_reclamation_my', methods: ['GET'])]
                public function myReclamations(Request $request, ReclamationRepository $reclamationRepository): Response
                {
                    $user = $this->getUser();
                    if (!$user) {
                        return $this->redirectToRoute('app_login');
                    }

                    $search = $request->query->get('search');
                    $sort = $request->query->get('sort');

                    $reclamations = $reclamationRepository->findByUserWithSearchAndSort($user->getId(), $search, $sort);

                    return $this->render('reclamation/my.html.twig', [
                        'reclamations' => $reclamations,
                        'search' => $search,
                        'sort' => $sort,
                    ]);
                }

                #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
                public function new(Request $request, EntityManagerInterface $entityManager): Response
                {
                    $user = $this->getUser();
                    if (!$user) {
                        return $this->redirectToRoute('app_login');
                    }

                    $reclamation = new Reclamation();
                    $form = $this->createForm(ReclamationType::class, $reclamation, [
                        'user' => $user,
                    ]);
                    $form->handleRequest($request);

                    if ($form->isSubmitted() && $form->isValid()) {
                        $reclamation->setNom($user->getUsername());
                        $reclamation->setPrenom('N/A');
                        $reclamation->setEmail($user->getEmail());
                        $reclamation->setEtat('new');
                        $reclamation->setNumTele(0);
                        $reclamation->setUtilisateurId($user->getId());

                        $entityManager->persist($reclamation);
                        $entityManager->flush();

                        return $this->redirectToRoute('app_reclamation_my');
                    }

                    return $this->render('reclamation/new.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }

                #[Route('/{id}', name: 'app_reclamation_show', methods: ['GET'])]
                public function show(Reclamation $reclamation): Response
                {
                    return $this->render('reclamation/show.html.twig', [
                        'reclamation' => $reclamation,
                    ]);
                }

                #[Route('/{id}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
                public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
                {
                    $form = $this->createForm(ReclamationType::class, $reclamation, [
                        'user' => $this->getUser(),
                    ]);
                    $form->handleRequest($request);

                    if ($form->isSubmitted() && $form->isValid()) {
                        $entityManager->flush();
                        return $this->redirectToRoute('app_reclamation_my');
                    }

                    return $this->render('reclamation/edit.html.twig', [
                        'reclamation' => $reclamation,
                        'form' => $form->createView(),
                    ]);
                }

                #[Route('/{id}', name: 'app_reclamation_delete', methods: ['POST'])]
                public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
                {
                    if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
                        $entityManager->remove($reclamation);
                        $entityManager->flush();
                    }
                    return $this->redirectToRoute('app_reclamation_my');
                }
            }