<?php

namespace App\Repository;

use App\Entity\PriceHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @method PriceHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method PriceHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method PriceHistory[]    findAll()
 * @method PriceHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriceHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriceHistory::class);
    }

    // /**
    //  * @return PriceHistory[] Returns an array of PriceHistory objects
    //  */
    public function findAllAsArray()
    {
        return $this->createQueryBuilder('p')
            ->select('p.unixCreatedAt')
            ->addSelect('p.closingPrice')
            // ->andWhere('p.exampleField = :val')
            // ->setParameter('val', $value)
            // ->orderBy('p.id', 'ASC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY)
        ;
    }

    /**
     * Repository method for finding the newest inserted
     * entry inside the database. Will return the latest
     * entry when one is existent, otherwise will return
     * null.
     *
     * @return PriceHistory|null
     */
    public function findLastPriceInserted(): ?PriceHistory
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Find a price recorded last week from today.
     *
     *
     * @return PriceHistory|null
     */
    public function findLastWeekPrice(): ?PriceHistory
    {
        $date = new \DateTime();
        $date->modify('-1 week');

        return $this->createQueryBuilder('p')
            ->where("DATE_FORMAT(p.createdAt, '%Y-%m-%d') = :date")
            ->setParameter(':date', $date->format('Y-m-d'))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Find a price recorded the previous day from today.
     *
     * @param DateTime $date The created date of a current PriceHistory entity.
     *
     * @return PriceHistory|null
     */
    public function findYesterdayPrice($date): ?PriceHistory
    {
        $date->modify('-1 day');

        return $this->createQueryBuilder('p')
            ->where("DATE_FORMAT(p.createdAt, '%Y-%m-%d') = :date")
            ->setParameter(':date', $date->format('Y-m-d'))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
