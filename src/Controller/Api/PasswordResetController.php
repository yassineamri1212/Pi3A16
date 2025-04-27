<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface; // For logging errors
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface; // Specific mailer exception
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/password')] // Base route for these API endpoints
class PasswordResetController extends AbstractController
{
    // Define token lifetime (e.g., 1 hour = 3600 seconds)
    private const TOKEN_LIFETIME_SECONDS = 3600;

    // Constructor remains the same, injecting necessary services
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly MailerInterface $mailer,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger,
        private readonly string $appMailerSender,
        private readonly string $appMailerSenderName,
        private readonly string $frontendResetUrl
    ) {}

    /**
     * Handles the request to initiate a password reset.
     * Expects JSON body: {"email": "user@example.com"}
     * MODIFIED: Returns different messages based on email existence (Less Secure).
     */
    #[Route('/request-reset', name: 'api_password_request_reset', methods: ['POST'])]
    public function requestPasswordReset(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $email = $data['email'] ?? null;
        } catch (\JsonException $e) {
            $this->logger->warning('Invalid JSON received for password reset request.', ['exception' => $e]);
            return $this->json(['error' => 'Invalid request format.'], Response::HTTP_BAD_REQUEST);
        }

        // Basic validation
        $violations = $this->validator->validate($email, [
            new Assert\NotBlank(),
            new Assert\Email(),
        ]);

        if (count($violations) > 0) {
            return $this->json(['error' => 'Invalid email address provided.'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        // --- MODIFIED LOGIC ---
        if ($user instanceof User) {
            // --- User FOUND ---
            try {
                // Generate secure token & expiry
                $token = bin2hex(random_bytes(32));
                $expiresAt = (new \DateTimeImmutable())->modify('+' . self::TOKEN_LIFETIME_SECONDS . ' seconds');

                $user->setResetToken($token);
                $user->setResetTokenExpiresAt($expiresAt);

                $this->entityManager->persist($user);
                $this->entityManager->flush(); // Save token to DB

                $resetUrl = rtrim($this->frontendResetUrl, '/') . '?token=' . urlencode($token);

                // Send email
                $emailMessage = (new Email())
                    ->from(new Address($this->appMailerSender, $this->appMailerSenderName))
                    ->to($user->getEmail())
                    ->subject('Your GoMobility Password Reset Request')
                    ->html($this->renderView(
                        'email/password_reset.html.twig',
                        [
                            'reset_url' => $resetUrl,
                            'token_lifetime_minutes' => self::TOKEN_LIFETIME_SECONDS / 60
                        ]
                    ));

                $this->mailer->send($emailMessage);

                // Return specific success message
                return $this->json(['message' => 'Password reset link sent successfully to your email.'], Response::HTTP_OK);

            } catch (TransportExceptionInterface $e) {
                $this->logger->error('Password reset email sending failed.', ['error' => $e->getMessage(), 'email' => $email]);
                // Return an error even if sending failed in this less secure version
                return $this->json(['error' => 'Failed to send password reset email. Please try again later or contact support.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            } catch (\Exception $e) {
                $this->logger->error('Error processing password reset request.', ['error' => $e->getMessage(), 'email' => $email]);
                // Return an error even if other exception occurred
                return $this->json(['error' => 'An internal error occurred while processing your request.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            // --- End User FOUND ---
        } else {
            // --- User NOT FOUND ---
            // Return a specific message indicating the email wasn't found (LESS SECURE)
            return $this->json(['error' => 'No account found with that email address.'], Response::HTTP_NOT_FOUND); // Use 404 Not Found
        }
        // --- END MODIFIED LOGIC ---

        // NOTE: The generic return statement that was here previously is removed
        // because the if/else now covers all cases.
    }


    /**
     * Handles setting the new password using a valid token.
     * Expects JSON body: {"password": "newSecurePassword123"}
     * Token is passed in the URL.
     * (This method remains unchanged from the previous version)
     */
    #[Route('/reset/{token}', name: 'api_password_reset', methods: ['POST'])]
    public function resetPassword(Request $request, string $token): JsonResponse
    {
        // Find user by VALID token
        $user = $this->userRepository->findOneByValidResetToken($token);

        // Handle invalid/expired token
        if (!$user instanceof User) {
            return $this->json(['error' => 'Invalid or expired password reset token.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $newPassword = $data['password'] ?? null;
        } catch (\JsonException $e) {
            $this->logger->warning('Invalid JSON received for password reset submission.', ['exception' => $e, 'token' => $token]);
            return $this->json(['error' => 'Invalid request format.'], Response::HTTP_BAD_REQUEST);
        }

        // Validate the new password
        $violations = $this->validator->validate($newPassword, [
            new Assert\NotBlank(['message' => 'Password cannot be empty.']),
            new Assert\Length([
                'min' => 8,
                'minMessage' => 'Password must be at least {{ limit }} characters long.'
                // Add other constraints if needed
            ]),
            // new Assert\NotCompromisedPassword(), // Consider adding this
        ]);

        if (count($violations) > 0) {
            $errorMessages = [];
            foreach ($violations as $violation) {
                $errorMessages[] = $violation->getMessage();
            }
            return $this->json(['error' => 'Invalid password provided.', 'details' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Hash the new password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        // Invalidate the token
        $user->setResetToken(null);
        $user->setResetTokenExpiresAt(null);

        // Save changes
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Return success
        return $this->json(['message' => 'Password has been successfully reset.'], Response::HTTP_OK);
    }
}