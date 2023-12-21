<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function add(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Method to find all comments, order by DESC and max result 5 for backoffice home page
     * @return Comment[] Returns an array of Comment objects
     */
    public function findAllWithMaxResult()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('c')
            ->from(Comment::class, 'c')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(5);
            
        $result = $queryBuilder->getQuery()->getResult();
        return $result;
    }

    /**
     * Method to find all comments, order by DESC for backoffice comment index
     * @return Comment[] Returns an array of Comment objects
     */
    public function findAllOrderByDesc()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('c')
            ->from(Comment::class, 'c')
            ->orderBy('c.createdAt', 'DESC');
                    
        $result = $queryBuilder->getQuery()->getResult();
        return $result;
    }

    /**
     * Method to find all comments by card api_id
     * @param string $apiId
     * @return Comment[] Returns an array of Comment objects
     */
    public function findAllCommentsByCardApiID(string $apiId)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder
            ->select('u.id AS user_id', 'u.nickname', 'u.image', 'r.id AS review_id', 'co.id AS comment_id', 'co.content', 'co.createdAt')
            ->from(Comment::class, 'co')
            ->join('co.review', 'r')
            ->join('co.user', 'u')
            ->join('r.card', 'ca')
            ->where('ca.api_id = :apiId')
            ->setParameter('apiId', $apiId);

        $result = $queryBuilder->getQuery()->getResult();
        return $result;
    }
}
