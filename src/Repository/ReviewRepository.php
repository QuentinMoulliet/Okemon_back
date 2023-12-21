<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 *
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function add(Review $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Review $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Method to find all reviews, order by DESC and max result 5 for backoffice home page
     * @return Review[] Returns an array of Review objects
     */
    public function findAllWithMaxResult(){
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('r')
            ->from(Review::class, 'r')
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults(5);
            
        $result = $queryBuilder->getQuery()->getResult();
        return $result;
    }

    /**
     * Method to find all reviews, order by DESC for backoffice review index
     * @return Review[] Returns an array of Review objects
     *
     */
    public function findAllOrderByDesc(){
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('r')
            ->from(Review::class, 'r')
            ->orderBy('r.createdAt', 'DESC');
                    
        $result = $queryBuilder->getQuery()->getResult();
        return $result;
    }

    /**
     * Method to find all reviews by card api_id
     * @return Review[] Returns an array of Review objects
     *
     */
    public function findAllReviewsByCardApiID(string $apiId)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('u.id AS user_id', 'u.nickname','u.image', 'c.id AS card_id', 'c.api_id AS card_api_id', 'r.id AS review_id', 'r.title', 'r.content', 'r.createdAt')
            ->from(Review::class, 'r')
            ->join('r.card','c')
            ->join('r.user','u')
            ->where('c.api_id = :apiId')
            ->setParameter('apiId', $apiId);
        $result = $queryBuilder->getQuery()->getResult();
        return $result;
    }
}
