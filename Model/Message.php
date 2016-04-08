<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Model;

use FOS\UserBundle\Model\UserInterface;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Message
 * @package Mopa\Bundle\FeedBundle\Message
 */
abstract class Message
{
    /**
     * @var int
     *
     * @Serializer\Accessor(getter="getId")
     */
    protected $id;

    /**
     * Set a reciever Id on the Message, which will be used for one to one communication instead of fan out websocketing
     *
     * @var string
     */
    protected $recieverId;

    /**
     * @var string
     */
    protected $class;

    /**
     * might be set to false for certain types e.g. settings update etc, and no need to save them
     *
     * @var boolean
     */
    protected $save = true;

    /**
     * might be set to false for certain types e.g. settings update etc, and no need to decorate them
     *
     * @var boolean
     */
    protected $decorate = true;

    /**
     * @var \DateTime $created
     */
    protected $created;

    /**
     * @var integer
     */
    protected $ttl = -1;

    /**
     * @var integer
     */
    protected $hideAfter = -1;

    /**
     * @var string
     */
    protected $event;

    /**
     * @var string|array
     */
    protected $serializerGroup = "mopa_feed_websockets.internal";

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var UserInterface
     */
    protected $emittingUser;

    /**
     * Internal data used while sending message, should be abled to regenerate this at any time
     *
     * @var array
     */
    protected $data = array();

    /**
     * @var $feedItem FeedItem
     */
    protected $feedItem;

    /**
     * @var $templatePrefix
     */
    protected $templatePrefix;

    /**
     * @param string $event
     * @param UserInterface $user
     * @param UserInterface $emittingUser
     * @param null $save
     * @param null $decorate
     * @param array|SerializableMessageableInterface $data
     * @throws \Exception
     */
    public function __construct($event, UserInterface $user = null, UserInterface $emittingUser = null, $save = null, $decorate = null, $data = array())
    {
        if(!is_array($data) && !($data instanceof SerializableMessageableInterface)) {
            throw new \Exception('Message data must be array or SerializableMessageableInterface');
        }

        if(!$this->class) {
            throw new \Exception('Message class must be set. Override property with e.g. `protected $class = self::class;`');
        }

        $this->created = new \DateTime();

        if (null === $emittingUser && null !== $user) {
            $emittingUser = $user;
        }

        if (null === $user && null !== $emittingUser) {
            $user = $emittingUser;
        }

        $this->setUser($user);
        $this->setEmittingUser($emittingUser);
        $this->setEvent($event);

        if (null !== $save) {
            $this->setSave($save);
        }

        if (null !== $decorate) {
            $this->setDecorate($decorate);
        }

        $this->setData($data);
    }

    /**
     * Set a unique reciever id for e.g. OneToOne Connections like console
     * @param $id
     */
    public function setReciever($id)
    {
        $this->recieverId = $id;
    }

    /**
     * @param  UserInterface    $user
     * @return Message
     */
    public static function createReloadFeed(UserInterface $user)
    {
        return new static("reload_feed", $user, null, false, false);
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
     */
    public function setData($data)
    {

        $this->data = $this->filterResources($data);
    }

    /**
     * @return string
     */
    public function getRecieverId()
    {
        return $this->recieverId;
    }

    /**
     * @param string $recieverId
     */
    public function setRecieverId($recieverId)
    {
        $this->recieverId = $recieverId;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function filterResources(array $data)
    {
        foreach($data as $k => $v){
            if(is_array($v)){
                $data[$k] = $this->filterResources($v);
            }
            if(is_resource($v)){
                unset($data[$k]);
            }
        }
        return $data;
    }

    /**
     * Get data
     *
     * @return array|SerializableMessageableInterface
     */
    public function getData()
    {
        if (null !== $this->data && is_array($this->data) && count($this->data) == 0 && $this->getFeedItem()) {
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

    /**
     * @return array|string
     */
    public function getSerializerGroup()
    {
        return $this->serializerGroup;
    }

    /**
     * @param array|string $serializerGroup
     */
    public function setSerializerGroup($serializerGroup)
    {
        $this->serializerGroup = $serializerGroup;
    }

    /**
     * @return string
     */
    public function getTemplatePrefix()
    {
        return $this->templatePrefix;
    }
}