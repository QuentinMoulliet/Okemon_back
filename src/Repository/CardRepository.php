<?php

namespace App\Repository;

use App\Entity\Card;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Card>
 *
 * @method Card|null find($id, $lockMode = null, $lockVersion = null)
 * @method Card|null findOneBy(array $criteria, array $orderBy = null)
 * @method Card[]    findAll()
 * @method Card[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    public function add(Card $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Card $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Method to find cards by user id & api_id in collection
     * @param string $apiId
     * @param int $userId
     * @return Card[] Returns an array of Card objects
     */
    public function findCardByApiIdAndUserIdInCollection(string $apiId,int $userId)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('c.id')
            ->from(User::class, 'u')
            ->join('u.card','c')
            ->where('c.api_id = :apiId')
            ->andWhere('u.id = :userId')
            ->andWhere('c.own = 1')
            ->setParameter('userId', $userId)
            ->setParameter('apiId', $apiId);
        $result = $queryBuilder->getQuery()->getResult();
        return $result;
    }

    /**
     * Method to find cards by user id & api_id in wishlist
     * @param string $apiId
     * @param int $userId
     * @return Card[] Returns an array of Card objects
     */
    public function findCardByApiIdAndUserIdInWishlist(string $apiId,int $userId)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('c.id')
            ->from(User::class, 'u')
            ->join('u.card','c')
            ->where('c.api_id = :apiId')
            ->andWhere('u.id = :userId')
            ->andWhere('c.wish = 1')
            ->setParameter('userId', $userId)
            ->setParameter('apiId', $apiId);
        $result = $queryBuilder->getQuery()->getResult();
        return $result;
    }
}
