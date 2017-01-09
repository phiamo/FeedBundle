<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FeedFilter
 *
 * @ORM\MappedSuperclass()
 */
abstract class Filter
{
    const TYPE_EXCLUDE = 0;
    const TYPE_INCLUDE = 1;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $active = false;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $type = self::TYPE_EXCLUDE;

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
