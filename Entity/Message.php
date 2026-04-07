<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Mopa\Bundle\FeedBundle\Model\Message as BaseMessage;

#[ORM\MappedSuperclass]
abstract class Message extends BaseMessage
{
    /**
     * Override this with e.g. `protected $class = self::class;`
     *
     * @var string
     */
    protected $class;

    /**
     * @var \DateTime $created
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected $created;

    /**
     * might be set to false for certain types e.g. settings update etc, and no need to save them
     *
     * @var boolean
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    protected $save = true;

    /**
     * might be set to false for certain types e.g. settings update etc, and no need to decorate them
     *
     * @var boolean
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    protected $decorate = true;

    /**
     * @var integer
     */
    #[ORM\Column(type: Types::INTEGER)]
    protected $ttl = -1;

    /**
     * @var integer
     */
    #[ORM\Column(type: Types::INTEGER)]
    protected $hideAfter = -1;

    /**
     * @var string
     */
    #[ORM\Column(type: Types::STRING)]
    protected $event;

    /**
     * @var string|array
     */
    #[ORM\Column(type: Types::STRING)]
    protected $serializerGroup = "mopa_feed_websockets.internal";
}
