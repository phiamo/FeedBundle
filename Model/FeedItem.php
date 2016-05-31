<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Model;

use FOS\UserBundle\Model\UserInterface;

/**
 * Class FeedItem
 * @package Mopa\Bundle\FeedBundle\Model
 */
abstract class FeedItem implements MessageableInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var $owner UserInterface
     */
    protected $owner;

    /**
     * @var $emitter UserInterface
     */
    protected $emitter;

    /**
     * @var \DateTime
     */
    protected $created;

    /**
     * @var \DateTime
     */
    protected $updated;

    /**
     * When a Feeditem was published it gets a message, feedItems not having a message will not be displayed
     * See BookmarkImport, its async and cant be displayed until the message is added
     *
     * @var $message Message
     */
    protected $message;

    /**
     * @var \DateTime
     */
    protected $readAt;

    /**
     * @param UserInterface $owner
     * @param UserInterface|null $emitter
     */
    public function __construct(UserInterface $owner, UserInterface $emitter = null)
    {
        $this->setOwner($owner);
        $this->setEmitter($emitter);
    }

    /**
     * FQDN of the Messaging Class
     *
     * @return string
     */
    abstract protected function getMessageClass();

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set owner
     *
     * @param UserInterface $owner
     */
    public function setOwner(UserInterface $owner)
    {
        $this->owner = $owner;
    }

    /**
     * Get Owner
     *
     * @return UserInterface
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set emitter
     *
     * @param  UserInterface $emitter
     */
    public function setEmitter(UserInterface $emitter = null)
    {
        $this->emitter = $emitter;
    }

    /**
     * Get emitter
     *
     * @return UserInterface
     */
    public function getEmitter()
    {
        return $this->emitter;
    }

    /**
     * Set readAt
     *
     * @param \DateTime $readAt
     */
    public function setReadAt(\DateTime $readAt)
    {
        $this->readAt = $readAt;
    }

    /**
     * Set readAt
     */
    public function setRead()
    {
        $this->readAt = new \DateTime("now");
    }

    /**
     * Get readAt
     *
     * @return \DateTime
     */
    public function getReadAt()
    {
        return $this->readAt;
    }

    /**
     * Reset $readAt
     */
    public function resetReadAt()
    {
        $this->readAt = null;
    }

    /**
     * Set created
     *
     * @param  \DateTime $created
     */
    public function setCreated(\DateTime $created)
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
     * Set updated
     *
     * @param  \DateTime $updated
     * @return AbstractMessageableInterface|void
     */
    public function setUpdated(\DateTime $updated)
    {
        $this->updated = $updated;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set message
     *
     * @param  Message  $message
     */
    public function setMessage($message = null)
    {
        $this->message = $message;
    }

    /**
     * Get message
     *
     * @return Message
     */
    public function getMessage()
    {
        if ($this->message == null) {
            $this->toMessage();
        }

        return $this->message;
    }

    /**
     * Get a data for Message for "this" object
     *
     * callback will be called as js callback maybe deprecated ?!
     * route and route_parameters make e.g. chrome notifications windows clickable
     *
     * @return array
     */
    public function getMessageData()
    {
        return [
            "callback" => [],
        ];
    }

    /**
     * @return Message
     */
    public function toMessage()
    {
        $messageClass = $this->getMessageClass();
        /** @type $message Message */
        $message = new $messageClass($this->getEvent(), $this->getOwner(), $this->getEmitter());
        $message->setData($this->getMessageData());
        $message->setFeedItem($this);
        $this->message = $message;

        return $message;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        $class = get_class($this);
        $classPart = substr($class, 0, strlen($class));
        $classPart = explode("\\", $classPart);
        $classPart = $classPart[count($classPart)-1];
        $event = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $classPart));
        return $event;
    }
}
