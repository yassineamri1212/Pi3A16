<?php

                                               namespace App\Repository;

                                               use App\Entity\ResetPasswordRequest;
                                               use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
                                               use Doctrine\Persistence\ManagerRegistry;
                                               use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
                                               use SymfonyCasts\Bundle\ResetPassword\Persistence\ResetPasswordRequestRepositoryInterface;

                                               class ResetPasswordRequestRepository extends ServiceEntityRepository implements ResetPasswordRequestRepositoryInterface
                                               {
                                                   public function __construct(ManagerRegistry $registry)
                                                   {
                                                       parent::__construct($registry, ResetPasswordRequest::class);
                                                   }

                                                   public function add(ResetPasswordRequestInterface $resetPasswordRequest): void
                                                   {
                                                       $em = $this->getEntityManager();
                                                       $em->persist($resetPasswordRequest);
                                                       $em->flush();
                                                   }

                                                   public function remove(ResetPasswordRequestInterface $resetPasswordRequest): void
                                                   {
                                                       $em = $this->getEntityManager();
                                                       $em->remove($resetPasswordRequest);
                                                       $em->flush();
                                                   }

                                                   public function findLatestNonExpiredRequest(object $user, \DateTimeInterface $expiresAt): ?ResetPasswordRequest
                                                   {
                                                       return $this->createQueryBuilder('r')
                                                           ->andWhere('r.user = :user')
                                                           ->andWhere('r.expiresAt > :now')
                                                           ->setParameter('user', $user)
                                                           ->setParameter('now', new \DateTime())
                                                           ->orderBy('r.expiresAt', 'DESC')
                                                           ->setMaxResults(1)
                                                           ->getQuery()
                                                           ->getOneOrNullResult();
                                                   }

                                                   public function persistResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
                                                   {
                                                       $this->add($resetPasswordRequest);
                                                   }

                                                   public function removeResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
                                                   {
                                                       $this->remove($resetPasswordRequest);
                                                   }

                                                   public function removeExpiredResetPasswordRequests(): int
                                                   {
                                                       $now = new \DateTime();
                                                       $qb = $this->createQueryBuilder('r')
                                                           ->delete()
                                                           ->where('r.expiresAt <= :now')
                                                           ->setParameter('now', $now);
                                                       return (int) $qb->getQuery()->execute();
                                                   }

                                                   public function createResetPasswordRequest(object $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken): ResetPasswordRequestInterface
                                                   {
                                                       return new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);
                                                   }

                                                   public function findResetPasswordRequest(string $selector): ?ResetPasswordRequestInterface
                                                   {
                                                       return $this->findOneBy(['selector' => $selector]);
                                                   }

                                                   public function getMostRecentNonExpiredRequestDate(object $user): ?\DateTimeInterface
                                                   {
                                                       $result = $this->createQueryBuilder('r')
                                                           ->select('r.expiresAt')
                                                           ->andWhere('r.user = :user')
                                                           ->andWhere('r.expiresAt > :now')
                                                           ->setParameter('user', $user)
                                                           ->setParameter('now', new \DateTime())
                                                           ->orderBy('r.expiresAt', 'DESC')
                                                           ->setMaxResults(1)
                                                           ->getQuery()
                                                           ->getOneOrNullResult();

                                                       return $result ? $result['expiresAt'] : null;
                                                   }

                                                   // Add this method to implement the interface
                                                   public function getUserIdentifier(object $user): string
                                                   {
                                                       if (method_exists($user, 'getUserIdentifier')) {
                                                           return $user->getUserIdentifier();
                                                       }
                                                       if (method_exists($user, 'getUsername')) {
                                                           return $user->getUsername();
                                                       }
                                                       throw new \LogicException('User identifier not found.');
                                                   }
                                               }