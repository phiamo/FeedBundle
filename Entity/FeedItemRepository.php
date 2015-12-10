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
     * @param string $alias
     * @param null $indexBy
     * @return QueryBuilder
     */
    public function createQueryBuilder($alias, $indexBy = null)
    {
        $qb = parent::createQueryBuilder($alias, $indexBy)
            ->select($alias . ", m")
            ->leftJoin($alias . ".message", "m")
        ;

        return $qb;
    }

    /**
     *
     * @param  UserInterface $user
     * @param  string        $alias
     * @return QueryBuilder
     */
    public function getByOwnerQueryBuilder(UserInterface $user, $alias = "f")
    {
        $qb = $this->createQueryBuilder($alias)
            ->where($alias . '.owner = :owner')
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
            ->addSelect('bookmarks, e')
            ->leftJoin('f.publicBookmark', 'bookmarks')
            ->leftJoin('f.emitter', 'e')
            ->andWhere("f.message IS NOT NULL") // messages not set yet ...
            ->orderBy("f.updated", "DESC")
        ;

        return $qb;
    }

    /**
     * @param  UserInterface $user
     * @param  string $alias
     * @param null $qb
     * @return QueryBuilder
     */
    public function getUnreadFeedQueryBuilder(UserInterface $user, $alias = "f", $qb = null)
    {
        if(null === $qb){
            $qb = $this->getFeedQueryBuilder($user, $alias);
        }

        $qb = $qb
            ->andWhere("f.readAt IS NULL")
            ->orderBy("f.updated", "DESC")
        ;

        return $qb;
    }

    public function getFeed($get)
    {
    }
}
