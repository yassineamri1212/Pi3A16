<?php

    namespace App\Controller;

    use App\Service\GeminiChatService;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;
    use Psr\Log\LoggerInterface;

    class ChatbotController extends AbstractController
    {
        private GeminiChatService $geminiChatService;
        private LoggerInterface $logger;

        public function __construct(GeminiChatService $geminiChatService, LoggerInterface $logger)
        {
            $this->geminiChatService = $geminiChatService;
            $this->logger = $logger;
        }

        #[Route('/api/chatbot/message', name: 'api_chatbot_message', methods: ['POST'])]
        public function handleMessage(Request $request): JsonResponse
        {
            $data = json_decode($request->getContent(), true);
            $userMessage = $data['message'] ?? null;
            $history = $data['history'] ?? [];

            if (!$userMessage) {
                return new JsonResponse(['error' => 'No message provided'], Response::HTTP_BAD_REQUEST);
            }

            try {
                $response = $this->geminiChatService->sendMessage($userMessage, $history);
                return new JsonResponse(['reply' => $response]);
            } catch (\Exception $e) {
                $this->logger->error('Gemini API error: ' . $e->getMessage(), ['exception' => $e]);
                return new JsonResponse(
                    ['error' => 'Sorry, I encountered an error. Please try again later.'],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }
        }
    }