<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Model;

/**
 * Interface MessageableInterface
 *
 * Items Extending this interface are aboed to be thrown in MessageManager->emit() method
 *
 * @package Mopa\Bundle\FeedBundle\Entity
 */
interface MessageableInterface extends AbstractMessageableInterface
{
    /**
     * Reset ReadAt
     *
     * @return self
     */
    public function resetReadAt();

    /**
     * return Message data
     *
     * @return array
     */
    public function getMessageData();
}
