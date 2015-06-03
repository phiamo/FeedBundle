<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Model;

use FOS\UserBundle\Model\UserInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Message
 * @package Mopa\Bundle\FeedBundle\Message
 */
abstract class Message {

    /**
     * @var int
     *
     * @Serializer\Accessor(getter="getId")
     * @Serializer\Groups({"websockets", "websockets.internal"})
     * @Serializer\Type("integer")
     */
    protected $id;

    /**
     * might be set to false for certain types e.g. settings update etc, and no need to save them
     *
     * @var boolean
     *
     * @Serializer\Groups({"websockets.internal"})
     * @Serializer\Type("boolean")
     */
    protected $save = true;


    /**
     * might be set to false for certain types e.g. settings update etc, and no need to decorate them
     *
     * @var boolean
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"websockets.internal"})
     */
    protected $decorate = true;

    /**
     * @var \DateTime $created

     * @Serializer\Groups({"websockets", "websockets.internal"})
     * @Gedmo\Timestampable(on="create")
     * @Serializer\Type("DateTime")
     */
    protected $created;

    /**
     * @var integer
     * @Serializer\Type("integer")
     * @Serializer\Groups({"websockets", "websockets.internal"})
     */
    protected $ttl = -1;

    /**
     * @var integer
     * @Serializer\Type("integer")
     * @Serializer\Groups({"websockets", "websockets.internal"})
     */
    protected $hideAfter = -1;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Groups({"websockets", "websockets.internal"})
     */
    protected $event;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var UserInterface
     */
    protected $emittingUser;

    /**
     * @Serializer\Type("array")
     * @Serializer\Groups({"websockets", "websockets.internal"})
     */
    protected $data = array();

    /**
     * @var $feedItem FeedItem
     */
    protected $feedItem;

    /**
     * @param UserInterface $user
     * @param $event
     * @param UserInterface $emittingUser
     * @param null $save
     * @param null $decorate
     */
    public function __construct(UserInterface $user, $event, UserInterface $emittingUser = null, $save = null, $decorate = null)
    {
        $this->created = new \DateTime();
        $this->setUser($user);
        if (null === $emittingUser) {
            $emittingUser = $user;
        }
        $this->setEmittingUser($emittingUser);
        $this->setEvent($event);
        if (null !== $save) {
            $this->setSave($save);
        }
        if (null !== $decorate) {
            $this->setDecorate($decorate);
        }
    }

    /**
     * @param  UserInterface    $user
     * @return Message
     */
    public static function createReloadFeed(UserInterface $user)
    {
        return new static($user, "reload_feed", null, false, false);
    }
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        if ($this->id == null && $this->getSave() == false) { // enable serializing of a unique identifier even if message isnt saved
            $this->id = uniqid();
        }

        return $this->id;
    }

    /**
     * Set ttl
     *
     * @param  integer $ttl
     * @return Message
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * Get ttl
     *
     * @return integer
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Set hideAfter
     *
     * @param  integer $hideAfter
     * @return Message
     */
    public function setHideAfter($hideAfter)
    {
        $this->hideAfter = $hideAfter;

        return $this;
    }

    /**
     * Get hideAfter
     *
     * @return integer
     */
    public function getHideAfter()
    {
        return $this->hideAfter;
    }
    /**
     * Set user
     *
     * @param  UserInterface    $user
     * @return Message
     */
    public function setUser(UserInterface $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set emittingUser
     *
     * @param  UserInterface    $emittingUser
     * @return Message
     */
    public function setEmittingUser(UserInterface $emittingUser = null)
    {
        $this->emittingUser = $emittingUser;

        return $this;
    }

    /**
     * Get emittingUser
     *
     * @return UserInterface
     */
    public function getEmittingUser()
    {
        return $this->emittingUser;
    }

    /**
     * Set created
     *
     * @param  \DateTime $created
     * @return Message
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set data
     *
     * @param  array|SerializableMessageableInterface $data
     * @return Message
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return array|SerializableMessageableInterface
     */
    public function getData()
    {
        if (count($this->data) == 0 && $this->getFeedItem()) {
            $this->setData($this->getFeedItem()->getMessageData());
        }

        return $this->data;
    }
    /**
     * Set event
     *
     * @param  string  $event
     * @return Message
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }
    /**
     * Set feedItem
     *
     * @param  FeedItem $feedItem
     * @return Message
     */
    public function setFeedItem(FeedItem $feedItem)
    {
        $this->feedItem = $feedItem;

        return $this;
    }

    /**
     * Get message
     *
     * @return FeedItem
     */
    public function getFeedItem()
    {
        return $this->feedItem;
    }

    /**
     * @return bool
     */
    public function getSave()
    {
        return $this->save;
    }

    /**
     * @param $save
     * @return $this
     */
    public function setSave($save)
    {
        $this->save = $save;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDecorate()
    {
        return $this->decorate;
    }

    /**
     * @param $decorate
     * @return $this
     */
    public function setDecorate($decorate)
    {
        $this->decorate = $decorate;

        return $this;
    }
}