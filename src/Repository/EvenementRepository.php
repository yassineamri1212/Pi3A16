<?php
namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    public function findBySearchAndSort(?string $search, string $sort = 'asc'): array
    {
        $qb = $this->createQueryBuilder('e');

        if ($search) {
            $qb->andWhere('e.nom LIKE :search OR e.description LIKE :search OR e.lieu LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Sorting by name (or change the field as needed)
        $qb->orderBy('e.nom', $sort);

        return $qb->getQuery()->getResult();
    }
}