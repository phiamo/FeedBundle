<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Entity;

use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Model\UserInterface;

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
}
