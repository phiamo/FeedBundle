<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Mopa\Bundle\FeedBundle\Model\FeedItem as BaseFeedItem;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class FeedItem
 * @package Mopa\Bundle\FeedBundle\Entity
 * @ORM\MappedSuperclass()
 */
abstract class FeedItem extends BaseFeedItem
{
    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * Only update on create automatically, otherwise this will lead to unexpected results on setting e.g. readat or adding a message
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updated;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $readAt;
}