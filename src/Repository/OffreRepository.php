<?php

namespace App\Repository;

use App\Entity\Offre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder; // <-- Important: Use statement for QueryBuilder
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Offre>
 *
 * @method Offre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Offre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Offre[]    findAll()
 * @method Offre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OffreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offre::class);
    }

    /**
     * Finds Offres based on search term, sort field, and direction.
     * Returns a QueryBuilder instance, suitable for pagination.
     *
     * @param string|null $term The search term (searches trajet, fuel, price)
     * @param string $sort The field to sort by (e.g., 'o.dateDepart', 'p.trajet')
     * @param string $direction Sort direction ('ASC' or 'DESC')
     * @return QueryBuilder
     */
    public function findBySearchQueryBuilder(?string $term, string $sort = 'o.dateDepart', string $direction = 'ASC'): QueryBuilder
    {
        // Start building the query, 'o' is the alias for the Offre entity
        $qb = $this->createQueryBuilder('o')
            // Join with the related Parcour entity, using 'p' as its alias
            ->leftJoin('o.parcour', 'p')
            // Select both 'o' and 'p' to ensure Parcour data is loaded efficiently
            ->addSelect('p');

        // If a search term was provided, add WHERE conditions
        if ($term) {
            $qb->where($qb->expr()->orX( // Combine multiple conditions with OR
            // Condition 1: Parcour trajet contains the term (case-insensitive implicitly by DB usually)
                $qb->expr()->like('p.trajet', ':term'),
                // Condition 2: Offre fuel type contains the term
                $qb->expr()->like('o.typeFuel', ':term'),
                // Condition 3: Offre price contains the term (searching string representation)
                $qb->expr()->like('o.prix', ':term')
            ))
                // Set the parameter for the search term, adding wildcards for partial matches
                ->setParameter('term', '%' . trim($term) . '%');
        }

        // --- Validate Sort Parameters ---
        // Define allowed fields for sorting to prevent errors/injection
        $allowedSortFields = ['o.idOffre', 'p.trajet', 'o.typeFuel', 'o.nombrePlaces', 'o.prix', 'o.dateDepart', 'o.climatisee'];
        // If the requested sort field is not in the allowed list, default to 'o.dateDepart'
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'o.dateDepart';
        }
        // If the requested direction is not 'ASC' or 'DESC', default to 'ASC'
        if (!in_array(strtoupper($direction), ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }
        // --- End Validation ---

        // Apply the validated sorting to the query
        $qb->orderBy($sort, $direction);

        // Return the QueryBuilder object (not the results yet)
        // The Paginator will execute this query later.
        return $qb;
    }

    // You can add other custom repository methods here if needed later...
    // Example: Find upcoming offers
    // public function findUpcomingOffers(\DateTimeInterface $date): array
    // {
    //     return $this->createQueryBuilder('o')
    //         ->andWhere('o.dateDepart > :date')
    //         ->setParameter('date', $date)
    //         ->orderBy('o.dateDepart', 'ASC')
    //         ->getQuery()
    //         ->getResult();
    // }

    // Add this USE statement at the top if not already present
    // use Doctrine\ORM\QueryBuilder;

    /**
     * Finds *upcoming* Offres for public display, optionally filtered and sorted.
     * Returns a QueryBuilder for pagination.
     *
     * @param string|null $searchTerm Optional search term
     * @param string $sort Sorting field (e.g., 'o.dateDepart', 'o.prix')
     * @param string $direction Sort direction ('ASC' or 'DESC')
     * @return QueryBuilder
     */
    public function findPublicOffersQueryBuilder(
        ?string $searchTerm = null,
        string $sort = 'o.dateDepart',
        string $direction = 'ASC' // Default to oldest first for upcoming? Or 'ASC' for price?
    ): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.parcour', 'p')
            ->addSelect('p')
            // --- Key difference: Only show offers with future departure dates ---
            ->where('o.dateDepart > :now')
            ->setParameter('now', new \DateTimeImmutable()); // Use immutable for safety

        // Apply search term if provided
        if ($searchTerm) {
            $qb->andWhere($qb->expr()->orX( // Use andWhere since we already have the date condition
                $qb->expr()->like('p.trajet', ':term'),
                $qb->expr()->like('o.typeFuel', ':term'),
                $qb->expr()->like('o.prix', ':term')
            ))
                ->setParameter('term', '%' . trim($searchTerm) . '%');
        }

        // Validate sort parameters (same logic as admin search)
        $allowedSortFields = ['o.dateDepart', 'o.prix', 'o.nombrePlaces', 'p.trajet'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'o.dateDepart'; // Default sort for public page
        }
        if (!in_array(strtoupper($direction), ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }

        $qb->orderBy($sort, $direction);

        return $qb;
    }


}