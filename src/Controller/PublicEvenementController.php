<?php
// File: src/Controller/PublicEvenementController.php
namespace App\Controller;

use App\Repository\EvenementRepository;
use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PublicEvenementController extends AbstractController
{
    #[Route('/events', name: 'client_evenement_index', methods: ['GET'])]
    public function index(Request $request, EvenementRepository $evenementRepository, WeatherService $weatherService): Response
    {
        // Get search and sort parameters from the query string (default values are empty string and 'asc' respectively)
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'asc');

        // Find events based on search and sort criteria (make sure to implement the 'findBySearchAndSort' method in the repository)
        $events = $evenementRepository->findBySearchAndSort($search, $sort);

        // Get weather data for each event that has a location (lieu)
        $weathers = [];
        foreach ($events as $event) {
            if ($event->getLieu()) {
                $weathers[$event->getId()] = $weatherService->getWeather($event->getLieu());
            }
        }

        // Render the events with weather information, search, and sort parameters
        return $this->render('public/events.html.twig', [
            'events'  => $events,
            'weathers' => $weathers,
            'search'  => $search,
            'sort'    => $sort
        ]);
    }
}
