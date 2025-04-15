<?php
namespace App\Security;

use App\Entity\User as AppUser;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Psr\Log\LoggerInterface; // <-- Import Logger

class UserChecker implements UserCheckerInterface
{
    private LoggerInterface $logger; // <-- Add logger property

    // Inject the logger service
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function checkPreAuth(UserInterface $user): void
    {
        // Log that the checker is running
        $this->logger->info('UserChecker::checkPreAuth running for user: ' . $user->getUserIdentifier());

        if (!$user instanceof AppUser) {
            $this->logger->info('User is not an instance of AppUser, skipping checks.');
            return;
        }

        // Log Roles for Debugging
        $this->logger->info('User roles being checked: ' . implode(', ', $user->getRoles()));

        // Bypass block check for ADMIN
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $this->logger->info('User ' . $user->getUserIdentifier() . ' has ROLE_ADMIN, bypassing block check.');
            return;
        }

        // Log the isBlocked status for non-admins
        $isBlockedStatus = $user->isBlocked() ? 'BLOCKED' : 'NOT BLOCKED';
        $this->logger->info('Checking isBlocked status for NON-ADMIN user ' . $user->getUserIdentifier() . ': ' . $isBlockedStatus);

        // Check if the NON-ADMIN user is blocked
        if ($user->isBlocked()) {
            $this->logger->warning('Attempted login by BLOCKED non-admin user: ' . $user->getUserIdentifier());
            throw new CustomUserMessageAccountStatusException('Your account has been blocked. Please contact support.');
        }

        $this->logger->info('User ' . $user->getUserIdentifier() . ' passed pre-auth checks.');
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Post-auth checks (if any) can go here
    }
}