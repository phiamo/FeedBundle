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
     * use decorator service
     * default: templating done via MessagingHelper
     *
     * @var string
     */
    protected $decoratorService = 'templating';

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
     * @var boolean
     */
    protected $broadcast = false;

    /**
     * @var array
     */
    protected $broadcastTopics = [];

    /**
     * @var string
     */
    protected $firewallName = null;

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
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
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
     */
    public function setHideAfter($hideAfter)
    {
        $this->hideAfter = $hideAfter;
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
     */
    public function setUser(UserInterface $user = null)
    {
        $this->user = $user;
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
     */
    public function setEmittingUser(UserInterface $emittingUser = null)
    {
        $this->emittingUser = $emittingUser;
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
     */
    public function setCreated($created)
    {
        $this->created = $created;
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
     * @return string
     */
    public function getDecoratorService()
    {
        return $this->decoratorService;
    }

    /**
     * @param string $decoratorService
     */
    public function setDecoratorService($decoratorService)
    {
        $this->decoratorService = $decoratorService;
    }

    /**
     * @return array
     */
    public function getBroadcastTopics()
    {
        return $this->broadcastTopics;
    }

    /**
     * @param array $broadcastTopics
     */
    public function setBroadcastTopic(array $broadcastTopics)
    {
        $this->broadcastTopics = $broadcastTopics;
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
     */
    public function setEvent($event)
    {
        $this->event = $event;
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
     */
    public function setFeedItem(FeedItem $feedItem)
    {
        $this->feedItem = $feedItem;
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
     */
    public function setSave($save)
    {
        $this->save = $save;
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
     */
    public function setDecorate($decorate)
    {
        $this->decorate = $decorate;
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

    /**
     * @return boolean
     */
    public function isBroadcast()
    {
        return $this->broadcast;
    }

    /**
     * @return string
     */
    public function getFirewallName()
    {
        return $this->firewallName;
    }
}