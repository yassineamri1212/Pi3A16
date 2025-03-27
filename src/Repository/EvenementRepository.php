<?php
                    namespace App\Repository;

                    use App\Entity\Evenement;
                    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
                    use Doctrine\Persistence\ManagerRegistry;
                    use Doctrine\ORM\Query;

                    class EvenementRepository extends ServiceEntityRepository
                    {
                        public function __construct(ManagerRegistry $registry)
                        {
                            parent::__construct($registry, Evenement::class);
                        }

                        public function findBySearchAndSortQuery(?string $search = '', string $order = 'asc'): Query
                        {
                            $qb = $this->createQueryBuilder('e');

                            if (!empty($search)) {
                                $qb->andWhere('e.nom LIKE :search OR e.description LIKE :search OR e.lieu LIKE :search')
                                   ->setParameter('search', '%' . $search . '%');
                            }

                            $direction = strtolower($order) === 'asc' ? 'ASC' : 'DESC';
                            $qb->orderBy('e.nom', $direction);

                            return $qb->getQuery();
                        }
                    }