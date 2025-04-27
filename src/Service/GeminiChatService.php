<?php

                namespace App\Service;

                use Symfony\Component\HttpClient\HttpClient;
                use Symfony\Contracts\HttpClient\HttpClientInterface;
                use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
                use Psr\Log\LoggerInterface;

                class GeminiChatService
                {
                    private HttpClientInterface $httpClient;
                    private string $apiKey;
                    private string $apiUrl;
                    private LoggerInterface $logger;

                    public function __construct(
                        ParameterBagInterface $params,
                        LoggerInterface $logger,
                        ?HttpClientInterface $httpClient = null
                    ) {
                        $this->apiKey = $params->get('gemini_api_key');
                        if (empty($this->apiKey)) {
                            throw new \InvalidArgumentException('Gemini API key is not configured in environment variables (GEMINI_API_KEY).');
                        }
                        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $this->apiKey;
                        $this->httpClient = $httpClient ?? HttpClient::create();
                        $this->logger = $logger;
                    }

                    public function sendMessage(string $userMessage, array $history = []): string
                    {
                        $payload = [
                            'contents' => [],
                        ];

                        foreach($history as $entry) {
                            if(isset($entry['role']) && isset($entry['parts'])) {
                                $payload['contents'][] = $entry;
                            }
                        }

                        $payload['contents'][] = [
                            'role' => 'user',
                            'parts' => [['text' => $userMessage]]
                        ];

                        $this->logger->debug('Sending payload to Gemini', ['payload' => $payload]);

                        try {
                            $response = $this->httpClient->request('POST', $this->apiUrl, [
                                'json' => $payload,
                                'headers' => [
                                    'Content-Type' => 'application/json',
                                ]
                            ]);

                            $statusCode = $response->getStatusCode();
                            $content = $response->toArray();

                            $this->logger->debug('Received response from Gemini', ['statusCode' => $statusCode, 'content' => $content]);

                            // You may want to handle the response here and return the reply text
                            if (isset($content['candidates'][0]['content']['parts'][0]['text'])) {
                                return $content['candidates'][0]['content']['parts'][0]['text'];
                            } else {
                                throw new \RuntimeException('Unexpected response from Gemini API');
                            }
                        } catch (\Exception $e) {
                            $this->logger->error('Error communicating with Gemini API: ' . $e->getMessage(), ['exception' => $e]);
                            throw $e;
                        }
                    }
                }