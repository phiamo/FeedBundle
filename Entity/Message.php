<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mopa\Bundle\FeedBundle\Model\Message as BaseMessage;

/**
 * Class Message
 * @package Mopa\Bundle\FeedBundle\Entity
 *
 * @ORM\MappedSuperclass()
 */
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
     *
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * might be set to false for certain types e.g. settings update etc, and no need to save them
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $save = true;

    /**
     * might be set to false for certain types e.g. settings update etc, and no need to decorate them
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $decorate = true;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    protected $ttl = -1;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    protected $hideAfter = -1;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $event;

    /**
     * @var string|array
     *
     * @ORM\Column(type="string")
     */
    protected $serializerGroup = "mopa_feed_websockets.internal";
}
