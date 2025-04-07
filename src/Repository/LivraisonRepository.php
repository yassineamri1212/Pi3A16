<?php

namespace App\Repository;

use App\Entity\Livraison;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder; // Import QueryBuilder if using custom methods later
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Livraison>
 *
 * @method Livraison|null find($id, $lockMode = null, $lockVersion = null)
 * @method Livraison|null findOneBy(array $criteria, array $orderBy = null)
 * @method Livraison[]    findAll()
 * @method Livraison[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LivraisonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livraison::class);
    }

    /**
     * Example of a custom QueryBuilder method (adapt as needed for search/filter)
     * Returns a QueryBuilder instance, suitable for pagination.
     */
    public function findBySearchQueryBuilder(?string $term, string $sort = 'l.id', string $direction = 'DESC'): QueryBuilder
    {
        // Start building the query, 'l' is the alias for the Livraison entity
        $qb = $this->createQueryBuilder('l');

        // If a search term was provided, add WHERE conditions
        if ($term) {
            $qb->where($qb->expr()->orX( // Combine multiple conditions with OR
                $qb->expr()->like('l.startLocation', ':term'),
                $qb->expr()->like('l.deliveryLocation', ':term')
            // Add more fields to search here if needed, e.g., join with packages
            ))
                // Set the parameter for the search term, adding wildcards
                ->setParameter('term', '%' . trim($term) . '%');
        }

        // --- Validate Sort Parameters ---
        $allowedSortFields = ['l.id', 'l.startLocation', 'l.deliveryLocation', 'l.isDelivered'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'l.id'; // Default sort
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
    // src/Repository/LivraisonRepository.php

    // Add this use statement at the top if it's missing
    // use Doctrine\ORM\QueryBuilder;

    // Add this method inside the class LivraisonRepository extends ServiceEntityRepository { ... }

    /**
     * Finds Livraisons for the admin index based on search term, sort field, and direction.
     * Returns a QueryBuilder instance, suitable for pagination.
     *
     * @param string|null $term The search term (customize fields to search)
     * @param string $sort The field to sort by (e.g., 'l.id', 'l.adresseLivraison')
     * @param string $direction Sort direction ('ASC' or 'DESC')
     * @return QueryBuilder
     */
    public function findByAdminSearchQueryBuilder(?string $term, string $sort = 'l.id', string $direction = 'DESC'): QueryBuilder
    {
        // Start building the query, 'l' is the alias for the Livraison entity
        $qb = $this->createQueryBuilder('l');
        // ->leftJoin('l.someRelation', 'r') // Example Join if needed for search/sort
        // ->addSelect('r')                 // Select related entity if joined

        // If a search term was provided, add WHERE conditions
        if ($term) {
            $qb->where($qb->expr()->orX( // Combine multiple conditions with OR
            // --- Customize which fields to search ---
                $qb->expr()->like('l.adresseLivraison', ':term'),
                $qb->expr()->like('l.status', ':term'),
                $qb->expr()->like('l.nomDestinataire', ':term'),
                $qb->expr()->like('l.prenomDestinataire', ':term'),
                $qb->expr()->like('l.numTelephone', ':term')
            // $qb->expr()->like('r.someField', ':term') // Example search on related field
            ))
                // Set the parameter for the search term, adding wildcards
                ->setParameter('term', '%' . trim($term) . '%');
        }

        // --- Validate Sort Parameters ---
        // Define allowed fields for sorting
        $allowedSortFields = ['l.id', 'l.adresseLivraison', 'l.status', 'l.dateLivraisonPrevu', 'l.nomDestinataire']; // Add more as needed
        // If the requested sort field is not allowed, default to 'l.id'
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'l.id';
        }
        // If the requested direction is invalid, default to 'DESC'
        if (!in_array(strtoupper($direction), ['ASC', 'DESC'])) {
            $direction = 'DESC';
        }
        // --- End Validation ---

        // Apply the validated sorting to the query
        $qb->orderBy($sort, $direction);

        // Return the QueryBuilder object
        return $qb;
    }
    // You can add other custom repository methods here...
}