<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Entity;

use Mopa\Bundle\FeedBundle\Model\FeedItem as BaseFeedItem;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class FeedItem
 * @package Mopa\Bundle\FeedBundle\Entity
 * @ORM\MappedSuperclass()
 */
abstract class FeedItem extends BaseFeedItem {

}