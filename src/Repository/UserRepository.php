<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping\Driver\DatabaseDriver;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function add(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->add($user, true);
    }

    /**
     * Method to find one user by his nickname
     * @param string $search
     * @return User[] Returns an array of User objects
     */
    public function findByNickname(string $search)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.nickname LIKE :search')
            ->setParameter('search', '%'.$search.'%');
        $result = $queryBuilder->getQuery()->getResult();
        return $result;
    }

    /**
     * Method to find all user, order by DESC and max result 5 for backoffice home page
     * @return User[] Returns an array of User objects
     */
    public function findAllWithMaxResult()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('u')
            ->from(User::class, 'u')
            ->orderBy('u.createdAt', 'DESC')
            ->setMaxResults(5);
        $result = $queryBuilder->getQuery()->getResult();
        return $result;
    }

    /**
     * Method to find all user, order by DESC for backoffice user index
     * @return User[] Returns an array of User objects
     */
    public function findAllOrderByDesc()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('u')
            ->from(User::class, 'u')
            ->orderBy('u.createdAt', 'DESC');

        $result = $queryBuilder->getQuery()->getResult();
        return $result;
    }

    /**
     * Method for ranking the best collector based on the number of cards. Sorted alphabetically
     * @return User[] Returns an array of User objects
     */
    public function findAllUserSortByNumberOfCards()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder
            ->select('u.id', 'u.nickname', 'u.image', 'COUNT(c.id) AS card_number')
            ->from(User::class, 'u')
            ->join('u.card', 'c')
            ->groupBy('u.id', 'u.nickname')
            ->orderBy('card_number', 'DESC')
            ->addOrderBy('u.nickname', 'ASC')
            ->where('c.own = 1')
            ->setMaxResults(10);

        $result = $queryBuilder->getQuery()->getResult();

        return $result;
    }

    /**
     * Method to display a specific user's card wishlist
     * @param int $userId
     * @return User[] Returns an array of User objects
     */
    public function findWishlistById(int $userId)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder
        ->select('c.api_id', 'c.id AS card_id')
        ->from(User::class, 'u')
        ->join('u.card', 'c')
        ->where('c.wish = 1')
        ->andWhere('u.id = :userId')
        ->setParameter('userId', $userId);

        $result = $queryBuilder->getQuery()->getResult();

        return $result;
    }

    /**
     * Method to display a specific user's card collection
     * @param int $userId
     * @return User[] Returns an array of User objects
     */
    public function findCollectionById(int $userId)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder
        ->select('c.api_id', 'c.id AS card_id')
        ->from(User::class, 'u')
        ->join('u.card', 'c')
        ->where('c.own = 1')
        ->andWhere('u.id = :userId')
        ->setParameter('userId', $userId);

        $result = $queryBuilder->getQuery()->getResult();

        return $result;
    }


    /**
     * Method to count how many cards has a user in collection
     * @param int $userId
     * @return User[] Returns an array of User objects
     */
    public function findUserNumberCardsInCollection($userId)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder
        ->select('COUNT(c.api_id)')
        ->from(User::class, 'u')
        ->join('u.card', 'c')
        ->where('c.own = 1')
        ->andWhere('u.id = :userId')
        ->setParameter('userId', $userId);

        $result = $queryBuilder->getQuery()->getResult();

        return $result;
    }

    /**
     * Method to count how many cards has a user in wishlist
     * @param int $userId
     * @return User[] Returns an array of User objects
     */
    public function findUserNumberCardsInWishlist($userId)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder
        ->select('COUNT(c.api_id)')
        ->from(User::class, 'u')
        ->join('u.card', 'c')
        ->where('c.wish = 1')
        ->andWhere('u.id = :userId')
        ->setParameter('userId', $userId);

        $result = $queryBuilder->getQuery()->getResult();

        return $result;
    }

    /**
     * Method to list the owners of card by api_id
     * @param string $apiId
     * @return User[] Returns an array of User objects
     */
    public function findOwnerFromApiId(string $apiId)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder
        ->select('u.id, u.nickname')
        ->from(User::class, 'u')
        ->join('u.card', 'c')
        ->where('c.own = 1')
        ->andWhere('c.api_id = :apiId')
        ->setParameter('apiId', $apiId);

        $result = $queryBuilder->getQuery()->getResult();

        return $result;
    }
    
    /**
     * Method to list the wishers of card by api_id
     * @param string $apiId
     * @return User[] Returns an array of User objects
     */
    public function findWisherFromApiId(string $apiId)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder
        ->select('u.id, u.nickname')
        ->from(User::class, 'u')
        ->join('u.card', 'c')
        ->where('c.wish = 1')
        ->andWhere('c.api_id = :apiId')
        ->setParameter('apiId', $apiId);

        $result = $queryBuilder->getQuery()->getResult();

        return $result;
    }

    /**
     * Method to count the owners of card by api_id
     * @param string $apiId
     * @return User[] Returns an array of User objects
     */
    public function countOwnerFromApiId(string $apiId)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder
        ->select('COUNT(u.id)')
        ->from(User::class, 'u')
        ->join('u.card', 'c')
        ->where('c.own = 1')
        ->andWhere('c.api_id = :apiId')
        ->setParameter('apiId', $apiId);

        $result = $queryBuilder->getQuery()->getResult();

        return $result;
    }

    /**
     * Method to count the owners of card by api_id
     * @param string $apiId
     * @return User[] Returns an array of User objects
     */
    public function countWisherFromApiId(string $apiId)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder
        ->select('COUNT(u.id)')
        ->from(User::class, 'u')
        ->join('u.card', 'c')
        ->where('c.wish = 1')
        ->andWhere('c.api_id = :apiId')
        ->setParameter('apiId', $apiId);

        $result = $queryBuilder->getQuery()->getResult();

        return $result;
    }
}
