<?php

 namespace App\Repository;

 use App\Entity\Offre;
 use App\Entity\User;
 use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
 use Doctrine\ORM\QueryBuilder;
 use Doctrine\Persistence\ManagerRegistry;

 class OffreRepository extends ServiceEntityRepository
 {
     public function __construct(ManagerRegistry $registry)
     {
         parent::__construct($registry, Offre::class);
     }

     public function findBySearchQueryBuilder(?string $term, string $sort = 'o.dateDepart', string $direction = 'ASC'): QueryBuilder
     {
         $qb = $this->createQueryBuilder('o')
             ->leftJoin('o.parcour', 'p')
             ->addSelect('p')
             ->leftJoin('o.conducteur', 'c')
             ->addSelect('c');

         if ($term) {
             $qb->where($qb->expr()->orX(
                 $qb->expr()->like('p.name', ':term'),
                 $qb->expr()->like('p.pickup', ':term'),
                 $qb->expr()->like('p.destination', ':term'),
                 $qb->expr()->like('o.typeFuel', ':term'),
                 $qb->expr()->like('o.prix', ':term'),
                 $qb->expr()->like('c.userName', ':term')
             ))
                 ->setParameter('term', '%' . trim($term) . '%');
         }

         // Update allowed sort fields to match new Parcour fields
         $allowedSortFields = [
             'o.idOffre', 'p.name', 'p.pickup', 'p.destination',
             'o.typeFuel', 'o.nombrePlaces', 'o.prix', 'o.dateDepart', 'o.climatisee', 'c.userName'
         ];
         if (!in_array($sort, $allowedSortFields)) { $sort = 'o.dateDepart'; }
         if (!in_array(strtoupper($direction), ['ASC', 'DESC'])) { $direction = 'ASC'; }

         $qb->orderBy($sort, $direction);
         return $qb;
     }

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
                 $qb->expr()->like('p.name', ':term'),
                 $qb->expr()->like('p.pickup', ':term'),
                 $qb->expr()->like('p.destination', ':term'),
                 $qb->expr()->like('o.typeFuel', ':term'),
                 $qb->expr()->like('o.prix', ':term')
             ))
                 ->setParameter('term', '%' . trim($term) . '%');
         }

         $allowedSortFields = [
             'o.idOffre', 'p.name', 'p.pickup', 'p.destination',
             'o.typeFuel', 'o.nombrePlaces', 'o.prix', 'o.dateDepart', 'o.climatisee'
         ];
         if (!in_array($sort, $allowedSortFields)) { $sort = 'o.dateDepart'; }
         if (!in_array(strtoupper($direction), ['ASC', 'DESC'])) { $direction = 'DESC'; }

         $qb->orderBy($sort, $direction);
         return $qb;
     }
 }