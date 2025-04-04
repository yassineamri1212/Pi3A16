<?php
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

        #[Route('/admin/evenement')]
        class EvenementController extends AbstractController
        {
            #[Route('/', name: 'admin_evenement_index', methods: ['GET'])]
            public function index(EvenementRepository $evenementRepository): Response
            {
                return $this->render('admin/evenement/index.html.twig', [
                    'evenements' => $evenementRepository->findAll(),
                ]);
            }

            #[Route('/new', name: 'admin_evenement_new', methods: ['GET', 'POST'])]
            public function new(Request $request, EntityManagerInterface $entityManager): Response
            {
                $evenement = new Evenement();
                $form = $this->createForm(EvenementType::class, $evenement);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $file = $form->get('imageFile')->getData();
                    if ($file) {
                        $newFilename = uniqid() . '.' . $file->guessExtension();
                        try {
                            $file->move($this->getParameter('event_images_directory'), $newFilename);
                            $evenement->setImageEvenement($newFilename);
                        } catch (\Exception $e) {
                            // Handle file upload exception if needed
                        }
                    }
                    $entityManager->persist($evenement);
                    $entityManager->flush();

                    return $this->redirectToRoute('admin_evenement_index', [], Response::HTTP_SEE_OTHER);
                }

                return $this->render('admin/evenement/new.html.twig', [
                    'evenement' => $evenement,
                    'form' => $form,
                ]);
            }

            #[Route('/{id}', name: 'admin_evenement_show', methods: ['GET'])]
            public function show(Evenement $evenement, UserRepository $userRepository): Response
            {
                $transportStats = [
                    ['type' => 'Bus', 'userCount' => 5],
                    ['type' => 'Train', 'userCount' => 3],
                    ['type' => 'Car', 'userCount' => 2],
                ];

                // Query for all users with a reserved transport linked to the event.
                $users = $userRepository->createQueryBuilder('u')
                    ->join('u.reservedTransports', 't')
                    ->where('t.evenement = :evenement')
                    ->setParameter('evenement', $evenement)
                    ->getQuery()
                    ->getResult();

                return $this->render('admin/evenement/show.html.twig', [
                    'evenement'     => $evenement,
                    'users'         => $users,
                    'transportStats'=> $transportStats,
                ]);
            }

            #[Route('/{id}/edit', name: 'admin_evenement_edit', methods: ['GET', 'POST'])]
            public function edit(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
            {
                $oldImage = $evenement->getImageEvenement();
                $form = $this->createForm(EvenementType::class, $evenement);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $file = $form->get('imageFile')->getData();
                    if ($file) {
                        $newFilename = uniqid() . '.' . $file->guessExtension();
                        try {
                            $file->move($this->getParameter('event_images_directory'), $newFilename);
                            $evenement->setImageEvenement($newFilename);
                        } catch (\Exception $e) {
                            // Handle file upload exception if needed
                        }
                    } else {
                        $evenement->setImageEvenement($oldImage);
                    }
                    $entityManager->flush();

                    return $this->redirectToRoute('admin_evenement_index', [], Response::HTTP_SEE_OTHER);
                }

                return $this->render('admin/evenement/edit.html.twig', [
                    'evenement' => $evenement,
                    'form' => $form,
                ]);
            }

            #[Route('/{id}', name: 'admin_evenement_delete', methods: ['POST'])]
            public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
            {
                if ($this->isCsrfTokenValid('delete' . $evenement->getId(), $request->request->get('_token'))) {
                    $entityManager->remove($evenement);
                    $entityManager->flush();
                }

                return $this->redirectToRoute('admin_evenement_index', [], Response::HTTP_SEE_OTHER);
            }
        }