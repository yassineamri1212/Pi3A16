<?php

namespace App\Repository;

use App\Entity\Commentaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commentaire>
 *
 * @method Commentaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commentaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commentaire[]    findAll()
 * @method Commentaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        // The second argument is the Entity class this repository manages
        parent::__construct($registry, Commentaire::class);
    }

    // You can add custom query methods here later if needed.
    // For example, finding comments for a specific post (though often accessed via Post entity):
    /*
    public function findByPost(Post $post): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.post = :post')
            ->setParameter('post', $post)
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    */
}