<?php

namespace AppBundle\Repository;
use AppBundle\Entity\Auction;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * AuctionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AuctionRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function findActiveOrdered()
    {
        return $this->createQueryBuilder("a")
            ->where("a.status = :active")
            ->setParameter("active", Auction::STATUS_ACTIVE)
            ->andWhere("a.expiresAt > :now")
            ->setParameter("now", new \DateTime())
            ->orderBy("a.expiresAt","ASC")
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $owner
     * @return array
     */
    public function findMyOrdered(User $owner)
    {
        return $this
            ->getEntityManager()
            ->createQuery(
                "SELECT a
                FROM AppBundle:Auction a
                WHERE a.owner = :owner
                ORDER BY a.expiresAt ASC"
            )
            ->setParameter("owner", $owner)
            ->getResult();
    }
}
