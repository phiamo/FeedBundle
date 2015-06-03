<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use FOS\UserBundle\Model\UserInterface;

/**
 * Class FeedItemRepository
 * @package Mopa\Bundle\FeedBundle\Entity
 */
class FeedItemRepository extends EntityRepository
{
    /**
     *
     * @param  UserInterface $user
     * @param  string        $alias
     * @return QueryBuilder
     */
    public function getByOwnerQueryBuilder(UserInterface $user, $alias = "f")
    {
        $qb = $this->createQueryBuilder($alias)
            ->select($alias . ", m")
            ->leftJoin($alias . ".message", "m")
            ->andWhere($alias . '.owner = :owner')
            ->setParameter("owner", $user)
        ;

        return $qb;
    }
    /**
     * @param  UserInterface $user
     * @param  string        $alias
     * @return QueryBuilder
     */
    public function getFeedQueryBuilder(UserInterface $user, $alias = "f")
    {
        $qb = $this->getByOwnerQueryBuilder($user, $alias)
            ->andWhere("f.message IS NOT NULL") // messages not set yet ...
        ;

        return $qb;
    }
    /**
     * @param  UserInterface $user
     * @param  string        $alias
     * @return QueryBuilder
     */
    public function getUnreadFeedQueryBuilder(UserInterface $user, $alias = "f")
    {
        $qb = $this
            ->getFeedQueryBuilder($user, $alias)
            ->andWhere("f.readAt IS NULL")
            ->orderBy("f.updated", "DESC")
            ;

        return $qb;
    }
}
