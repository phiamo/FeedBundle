<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Mopa\Bundle\FeedBundle\Model\Message as BaseMessage;

#[MongoDB\MappedSuperclass]
abstract class Message extends BaseMessage
{
    /**
     * @var \DateTime $created
     */
    #[MongoDB\Field(type: 'date')]
    protected $created;

    /**
     * might be set to false for certain types e.g. settings update etc, and no need to save them
     *
     * @var boolean
     */
    #[MongoDB\Field(type: 'bool')]
    protected $save = true;

    /**
     * might be set to false for certain types e.g. settings update etc, and no need to decorate them
     *
     * @var boolean
     */
    #[MongoDB\Field(type: 'bool')]
    protected $decorate = true;

    /**
     * @var integer
     */
    #[MongoDB\Field(type: 'int')]
    protected $ttl = -1;

    /**
     * @var integer
     */
    #[MongoDB\Field(type: 'int')]
    protected $hideAfter = -1;

    /**
     * @var string
     */
    #[MongoDB\Field(type: 'string')]
    protected $event;

    /**
     * @var string|array
     */
    #[MongoDB\Field(type: 'string')]
    protected $serializerGroup = "mopa_feed_websockets.internal";
}
