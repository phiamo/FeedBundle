<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Model;

/**
 * Implementing this means we use the serializer meta data of our object itself instead of getMessage()
 *
 * Interface SerializableMessageableInterface
 * @package Mopa\Bundle\FeedBundle\Model
 */
interface SerializableMessageableInterface extends AbstractMessageableInterface{}
