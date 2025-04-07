<?php

namespace App\Repository;

use App\Entity\Package;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder; // Import QueryBuilder if using custom methods later
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Package>
 *
 * @method Package|null find($id, $lockMode = null, $lockVersion = null)
 * @method Package|null findOneBy(array $criteria, array $orderBy = null)
 * @method Package[]    findAll()
 * @method Package[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PackageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Package::class);
    }

    /**
     * Example of a custom QueryBuilder method (adapt as needed for search/filter)
     * Returns a QueryBuilder instance, suitable for pagination.
     */
    public function findBySearchQueryBuilder(?string $term, string $sort = 'p.id', string $direction = 'DESC'): QueryBuilder
    {
        // Start building the query, 'p' is the alias for the Package entity
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.livraison', 'l') // Join with Livraison
            ->addSelect('l'); // Select Livraison data too

        // If a search term was provided, add WHERE conditions
        if ($term) {
            $qb->where($qb->expr()->orX( // Combine multiple conditions with OR
                $qb->expr()->like('p.descriptionPackage', ':term'),
                $qb->expr()->like('l.startLocation', ':term'), // Search in related Livraison
                $qb->expr()->like('l.deliveryLocation', ':term') // Search in related Livraison
            // Add more fields to search here if needed
            ))
                // Set the parameter for the search term, adding wildcards
                ->setParameter('term', '%' . trim($term) . '%');
        }

        // --- Validate Sort Parameters ---
        $allowedSortFields = ['p.id', 'p.weightPackage', 'p.descriptionPackage', 'l.id', 'l.startLocation', 'l.deliveryLocation'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'p.id'; // Default sort
        }
        if (!in_array(strtoupper($direction), ['ASC', 'DESC'])) {
            $direction = 'DESC'; // Default direction
        }
        // --- End Validation ---

        // Apply sorting
        $qb->orderBy($sort, $direction);

        // Return the QueryBuilder object
        return $qb;
    }

    // You can add other custom repository methods here...
    // Example: Find packages for a specific delivery
    // public function findByLivraison(Livraison $livraison): array
    // {
    //     return $this->createQueryBuilder('p')
    //         ->andWhere('p.livraison = :livraison')
    //         ->setParameter('livraison', $livraison)
    //         ->orderBy('p.id', 'ASC')
    //         ->getQuery()
    //         ->getResult();
    // }
}