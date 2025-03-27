<?php
// File: src/Service/WeatherService.php
namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class WeatherService
{
    private string $apiKey;
    private Client $client;
    private string $apiUrl = 'https://api.openweathermap.org/data/2.5/weather';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new Client();
    }

    public function getWeather(string $location): ?array
    {
        try {
            $response = $this->client->request('GET', $this->apiUrl, [
                'query' => [
                    'q' => $location,
                    'appid' => $this->apiKey,
                    'units' => 'metric'
                ]
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            return null;
        }
    }
}