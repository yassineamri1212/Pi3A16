<?php
// File: src/Service/FunFactService.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class FunFactService
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getFunFact(string $lieu): ?string
    {
        // Update the API URL with a real endpoint if available
        $apiUrl = 'https://api.example.com/funfact?location=' . urlencode($lieu);

        try {
            $response = $this->client->request('GET', $apiUrl);
            if (200 === $response->getStatusCode()) {
                $data = $response->toArray();
                return $data['fact'] ?? null;
            }
        } catch (\Exception $e) {
            // Handle or log error as needed
        }

        return null;
    }
}