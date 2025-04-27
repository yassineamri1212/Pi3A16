<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
// Add PasswordUpgraderInterface if you use it for security.yaml
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface // Implement if needed
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    // --- NEW: Method to find user by valid reset token ---
    /**
     * Finds a user by a non-expired reset token.
     */
    public function findOneByValidResetToken(string $token): ?User
    {
        return $this->createQueryBuilder('u')
            // Condition 1: Token must match
            ->where('u.resetToken = :token')
            // Condition 2: Expiry time must be in the future
            ->andWhere('u.resetTokenExpiresAt > :now')
            // Set parameters
            ->setParameter('token', $token)
            ->setParameter('now', new \DateTimeImmutable()) // Use current time
            // Get the query and expect one result or null
            ->getQuery()
            ->getOneOrNullResult();
    }
    // --- END NEW METHOD ---

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    { ... } // Keep your existing custom methods if any

    //    public function findOneBySomeField($value): ?User
    //    { ... } // Keep your existing custom methods if any
}