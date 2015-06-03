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
 * @ORM\MappedSuperclass()
 */
class Message extends BaseMessage
{
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
     * Internal data used while sending message, should be abled to regenerate this at any time
     *
     * @var
     */
    protected $data = array();

    /**
     * @var $feedItem FeedItem
     */
    protected $feedItem;
}
