<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Model;

/**
 * Interface MessageObjectTransformer
 * @package Mopa\Bundle\FeedBundle\Model
 */
interface MessageObjectTransformer
{
    /**
     * @param array $object
     * @return array
     */
    public function transformMessageObject($object);
}