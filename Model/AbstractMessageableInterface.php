<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Model;

/**
 * Interface AbstractMessageableInterface
 * @package Mopa\Bundle\FeedBundle\Entity
 */
interface AbstractMessageableInterface
{
    /**
     * Get its id
     *
     * @return int
     */
    public function getId();

    /**
     * Get the final Message Data for "this" object
     *
     * @return Message
     */
    public function toMessage();

    /**
     * Set Updated on emission
     *
     * @param  \DateTime $dateTime
     */
    public function setUpdated(\DateTime $dateTime);
}
