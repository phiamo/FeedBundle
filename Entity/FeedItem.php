<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Mopa\Bundle\FeedBundle\Model\FeedItem as BaseFeedItem;

#[ORM\MappedSuperclass]
abstract class FeedItem extends BaseFeedItem
{
    /**
     * @var \DateTime
     */
    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected $created;

    /**
     * Only update on create automatically, otherwise this will lead to unexpected results on setting e.g. readat or adding a message
     *
     * @var \DateTime
     */
    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected $updated;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected $readAt;
}
