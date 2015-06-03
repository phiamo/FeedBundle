<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */


namespace Mopa\Bundle\FeedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FilterRule
 *
 * @ORM\Entity()
 * @ORM\Table(name="mopa_feed_filter_rule")
 */
class FilterRule
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    protected $field = "";

    /**
     * @var string
     * @ORM\Column(type="string", length=10)
     */
    protected $operator = "";

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $value = "";

    /**
     * @var Filter
     * @ORM\ManyToOne(targetEntity="Mopa\Bundle\FeedBundle\Entity\Filter", inversedBy="rules")
     */
    protected $filter;

    /**
     * @param Filter $filter
     * @param string $field
     * @param string $operator
     * @param string $value
     */
    public function __construct(Filter $filter, $field = "", $operator = "", $value = "")
    {
        $this->filter = $filter;
        $this->field = $field;
        $this->operator = $operator;
        $this->value = $value;
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
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
