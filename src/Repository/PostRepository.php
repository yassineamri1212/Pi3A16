<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 *
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        // The second argument is the Entity class this repository manages
        parent::__construct($registry, Post::class);
    }

    // You can add custom query methods here later if needed.
    // For example, finding posts by a specific user:
    /*
    public function findByAuthor(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.author = :author')
            ->setParameter('author', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
    */

    // Example: Find posts with search term (could be added later for search feature)
    /*
     public function findBySearchQueryBuilder(?string $term): \Doctrine\ORM\QueryBuilder
     {
         $qb = $this->createQueryBuilder('p')
             ->leftJoin('p.author', 'a')
             ->addSelect('a')
             ->orderBy('p.createdAt', 'DESC');

         if ($term) {
             $qb->where($qb->expr()->orX(
                 $qb->expr()->like('p.title', ':term'),
                 $qb->expr()->like('p.content', ':term'),
                 $qb->expr()->like('a.userName', ':term') // Search author username
             ))
             ->setParameter('term', '%' . trim($term) . '%');
         }
         return $qb;
     }
     */
}