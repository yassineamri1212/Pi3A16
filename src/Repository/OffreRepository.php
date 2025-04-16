<?php

namespace App\Repository;

use App\Entity\Offre;
use App\Entity\User; // <<< ADDED MISSING USE STATEMENT >>>
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Offre>
 */
class OffreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offre::class);
    }

    /**
     * Finds Offres based on search term, sort field, and direction (FOR ADMIN OR PUBLIC VIEW)
     * Returns a QueryBuilder instance, suitable for pagination.
     */
    public function findBySearchQueryBuilder(?string $term, string $sort = 'o.dateDepart', string $direction = 'ASC'): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.parcour', 'p')
            ->addSelect('p')
            ->leftJoin('o.conducteur', 'c')
            ->addSelect('c');

        if ($term) {
            $qb->where($qb->expr()->orX(
                $qb->expr()->like('p.trajet', ':term'),
                $qb->expr()->like('o.typeFuel', ':term'),
                $qb->expr()->like('o.prix', ':term'),
                $qb->expr()->like('c.userName', ':term') // Search by conducteur username
            ))
                ->setParameter('term', '%' . trim($term) . '%');
        }

        // Validate Sort Parameters
        $allowedSortFields = ['o.idOffre', 'p.trajet', 'o.typeFuel', 'o.nombrePlaces', 'o.prix', 'o.dateDepart', 'o.climatisee', 'c.userName'];
        if (!in_array($sort, $allowedSortFields)) { $sort = 'o.dateDepart'; }
        if (!in_array(strtoupper($direction), ['ASC', 'DESC'])) { $direction = 'ASC'; }

        $qb->orderBy($sort, $direction);
        return $qb;
    }

    /**
     * Finds Offres for a specific Conducteur, with search/sort.
     * Returns a QueryBuilder instance.
     */
    public function findConducteurOffresQueryBuilder(
        int $conducteurId,
        ?string $term,
        string $sort = 'o.dateDepart',
        string $direction = 'DESC'
    ): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o')
            ->innerJoin('o.conducteur', 'c')
            ->leftJoin('o.parcour', 'p')
            ->addSelect('p')
            ->where('c.id = :conducteurId')
            ->setParameter('conducteurId', $conducteurId);

        if ($term) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('p.trajet', ':term'),
                $qb->expr()->like('o.typeFuel', ':term'),
                $qb->expr()->like('o.prix', ':term')
            ))
                ->setParameter('term', '%' . trim($term) . '%');
        }

        // Validate Sort Parameters
        $allowedSortFields = ['o.idOffre', 'p.trajet', 'o.typeFuel', 'o.nombrePlaces', 'o.prix', 'o.dateDepart', 'o.climatisee'];
        if (!in_array($sort, $allowedSortFields)) { $sort = 'o.dateDepart'; }
        if (!in_array(strtoupper($direction), ['ASC', 'DESC'])) { $direction = 'DESC'; }

        $qb->orderBy($sort, $direction);
        return $qb;
    }
}