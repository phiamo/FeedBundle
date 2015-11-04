<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mopa\Bundle\FeedBundle\Model\Message as BaseMessage;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Message
 * @package Mopa\Bundle\FeedBundle\Entity
 *
 * @ORM\MappedSuperclass()
 */
abstract class Message extends BaseMessage
{
    /**
     * @var \DateTime $created
     *
     * @ORM\Column(type="datetime")
     *
     * @Serializer\Type("DateTime")
     * @Serializer\Groups({"websockets", "websockets.internal"})
     */
    protected $created;

    /**
     * might be set to false for certain types e.g. settings update etc, and no need to save them
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     *
     * @Serializer\Groups({"websockets.internal"})
     * @Serializer\Type("boolean")
     */
    protected $save = true;

    /**
     * might be set to false for certain types e.g. settings update etc, and no need to decorate them
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     *
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"websockets.internal"})
     */
    protected $decorate = true;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     *
     * @Serializer\Type("integer")
     * @Serializer\Groups({"websockets", "websockets.internal"})
     */
    protected $ttl = -1;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     *
     * @Serializer\Type("integer")
     * @Serializer\Groups({"websockets", "websockets.internal"})
     */
    protected $hideAfter = -1;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Serializer\Type("string")
     * @Serializer\Groups({"websockets", "websockets.internal"})
     */
    protected $event;

    /**
     * @var string|array
     *
     * @ORM\Column(type="string")
     *
     * @Serializer\Type("string")
     * @Serializer\Groups({"websockets", "websockets.internal"})
     */
    protected $serializerGroup = "websockets.internal";

    /**
     * Internal data used while sending message, should be abled to regenerate this at any time
     *
     * @var array
     *
     * @Serializer\Type("array")
     * @Serializer\Groups({"websockets", "websockets.internal"})
     */
    protected $data = array();

    /**
     * @var $feedItem FeedItem
     */
    protected $feedItem;
}
