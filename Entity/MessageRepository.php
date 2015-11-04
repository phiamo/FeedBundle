<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Entity;

use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Model\UserInterface;
use Mopa\Bundle\BooksyncBundle\Entity\ProtectedBookmark;
use Mopa\Bundle\BooksyncUserBundle\Entity\ProtectedGroup;

/**
 * Class MessageRepository
 * @package Mopa\Bundle\FeedBundle\Entity
 */
class MessageRepository extends EntityRepository
{
    /**
     * @param UserInterface $user
     * @param string $alias
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getByOwnerQueryBuilder(UserInterface $user, $alias = "m")
    {
        $qb = $this->createQueryBuilder($alias)
            ->andWhere($alias . '.user = :user')
            ->setParameter("user", $user)
        ;

        return $qb;
    }

    /**
     * @param ProtectedBookmark $bookmark
     * @param string $alias
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getByBookmarkQueryBuilder(ProtectedBookmark $bookmark, $alias = "m")
    {
        $qb = $this->createQueryBuilder($alias)
            ->leftJoin($alias . '.protectedBookmark', 'b')
            ->andWhere($alias . '.protectedBookmark = :bookmark')
            ->setParameter("bookmark", $bookmark)
        ;

        return $qb;
    }
}
