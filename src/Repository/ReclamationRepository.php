<?php
                // src/Repository/ReclamationRepository.php

                namespace App\Repository;

                use App\Entity\Reclamation;
                use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
                use Doctrine\Persistence\ManagerRegistry;

                class ReclamationRepository extends ServiceEntityRepository
                {
                    public function __construct(ManagerRegistry $registry)
                    {
                        parent::__construct($registry, Reclamation::class);
                    }

                    public function findByUserWithSearchAndSort($userId, $search = null, $sort = null)
                    {
                        $qb = $this->createQueryBuilder('r')
                            ->leftJoin('r.reponses', 'resp')
                            ->addSelect('resp')
                            ->where('r.utilisateur_id = :userId')
                            ->setParameter('userId', $userId);

                        if ($search) {
                            $qb->andWhere('r.sujet LIKE :search OR r.description LIKE :search')
                               ->setParameter('search', '%' . $search . '%');
                        }

                        if ($sort === 'with_responses') {
                            $qb->andWhere('resp.id IS NOT NULL');
                        } elseif ($sort === 'without_responses') {
                            $qb->andWhere('resp.id IS NULL');
                        }

                        return $qb->orderBy('r.date', 'DESC')
                            ->getQuery()
                            ->getResult();
                    }

                    public function findAllWithResponses()
                    {
                        $qb = $this->createQueryBuilder('r')
                            ->leftJoin('r.reponses', 'resp')
                            ->addSelect('resp')
                            ->orderBy('r.date', 'DESC');
                        return $qb->getQuery()->getResult();
                    }
                }