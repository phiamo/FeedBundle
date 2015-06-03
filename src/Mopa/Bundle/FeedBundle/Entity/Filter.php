<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * FeedFilter
 *
 * @ORM\Table(name="mopa_feed_filter")
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 */
class Filter
{
    const TYPE_EXCLUDE = 0;
    const TYPE_INCLUDE = 1;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $active = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $hideTags = false;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $type = self::TYPE_EXCLUDE;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Mopa\Bundle\FeedBundle\Entity\FilterRule", cascade={"all"}, mappedBy="filter")
     */
    protected $rules;

    public function __construct()
    {
        $this->rules = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param ArrayCollection $rules
     */
    public function setRules($rules)
    {
        $this->rules = $rules;
    }

    /**
     * @param FilterRule $rule
     */
    public function addRule(FilterRule $rule)
    {
        $this->rules->add($rule);
    }

    /**
     * @param FilterRule $rule
     */
    public function removeRule(FilterRule $rule)
    {
        $this->rules->removeElement($rule);
    }
}
