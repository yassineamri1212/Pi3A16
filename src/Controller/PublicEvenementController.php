<?php
                        namespace App\Controller;

                        use App\Repository\EvenementRepository;
                        use App\Service\WeatherService;
                        use Knp\Component\Pager\PaginatorInterface;
                        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
                        use Symfony\Component\HttpFoundation\Request;
                        use Symfony\Component\HttpFoundation\Response;
                        use Symfony\Component\Routing\Annotation\Route;

                        class PublicEvenementController extends AbstractController
                        {
                            #[Route('/events', name: 'client_evenement_index', methods: ['GET'])]
                            public function index(
                                Request $request,
                                EvenementRepository $evenementRepository,
                                WeatherService $weatherService,
                                PaginatorInterface $paginator
                            ): Response {
                                $search = $request->query->get('search', '');
                                $order  = $request->query->get('order', 'asc');

                                $query = $evenementRepository->findBySearchAndSortQuery($search, $order);

                                $pagination = $paginator->paginate(
                                    $query,
                                    $request->query->getInt('page', 1),
                                    3
                                );

                                $weathers = [];
                                foreach ($pagination as $event) {
                                    if ($event->getLieu()) {
                                        $weathers[$event->getId()] = $weatherService->getWeather($event->getLieu());
                                    }
                                }

                                return $this->render('public/events.html.twig', [
                                    'pagination' => $pagination,
                                    'weathers'   => $weathers,
                                    'search'     => $search,
                                    'order'      => $order,
                                ]);
                            }
                        }