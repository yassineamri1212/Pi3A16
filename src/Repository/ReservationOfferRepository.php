<?php

namespace App\Repository;

use App\Entity\ReservationOffer; // Correct entity
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
// --- ADD this Use statement ---
use Doctrine\ORM\QueryBuilder;
// --- END ADD ---

/**
 * @extends ServiceEntityRepository<ReservationOffer> // Correct entity
 */
class ReservationOfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationOffer::class); // Correct entity
    }

    /**
     * Find an ACTIVE (non-cancelled) reservation for a specific offer by a specific passenger.
     * Used to prevent duplicate active bookings.
     * @param int $offreId
     * @param int $passengerId
     * @return ReservationOffer|null
     */
    public function findActiveReservation(int $offreId, int $passengerId): ?ReservationOffer
    {
        return $this->createQueryBuilder('ro')
            ->andWhere('ro.offre = :offreId')
            ->andWhere('ro.passenger = :passengerId')
            // Exclude user-cancelled reservations for this specific check
            ->andWhere('ro.status != :statusCancelled')
            ->setParameter('statusCancelled', 'cancelled_by_user') // Assuming this is your cancelled status string
            ->setParameter('offreId', $offreId)
            ->setParameter('passengerId', $passengerId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find ANY existing reservation record for a specific offer and passenger,
     * regardless of its status. Used to check if we should UPDATE or INSERT.
     *
     * @param int $offreId
     * @param int $passengerId
     * @return ReservationOffer|null
     */
    public function findExistingReservation(int $offreId, int $passengerId): ?ReservationOffer
    {
        return $this->createQueryBuilder('ro')
            ->andWhere('ro.offre = :offreId')
            ->andWhere('ro.passenger = :passengerId')
            // No status check here - we want ANY existing record
            ->setParameter('offreId', $offreId)
            ->setParameter('passengerId', $passengerId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }


    // --- NEW METHOD for Stats ---
    /**
     * Finds users ranked by the number of carpooling offer reservations
     * they made within a date range.
     *
     * @param \DateTimeInterface $startDate Start of the period
     * @param \DateTimeInterface $endDate End of the period
     * @param int $limit Max number of users to return
     * @return array<int, array{'userId': int, 'userIdentifier': string, 'reservationCount': int}>
     */
    public function findTopReservingOfferUsers(\DateTimeInterface $startDate, \DateTimeInterface $endDate, int $limit = 5): array
    {
        // NOTE: Aliases: 'ro' = ReservationOffer, 'u' = User (passenger)
        return $this->createQueryBuilder('ro')
            // Select the count of reservations and alias it
            ->select('COUNT(ro.id) as reservationCount')
            // Select user fields needed for display/linking
            ->addSelect('u.id as userId')
            ->addSelect('u.email as userIdentifier') // Using email as identifier
            // Join ReservationOffer to User via the 'passenger' field
            ->innerJoin('ro.passenger', 'u')
            // Filter reservations based on their creation timestamp
            ->where('ro.createdAt BETWEEN :start AND :end') // Use the correct field name
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            // Group results by user to count reservations per user
            ->groupBy('u.id', 'u.email')
            // Order by the reservation count descending (most first)
            ->orderBy('reservationCount', 'DESC')
            // Limit the number of top users returned
            ->setMaxResults($limit)
            // Get the results as an array
            ->getQuery()
            ->getResult();
    }
    // --- END NEW METHOD ---
}