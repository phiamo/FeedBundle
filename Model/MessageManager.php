<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Psr\Log\LoggerInterface;
use ReflectionClass;

/**
 * Class MessageManager
 * @package Mopa\Bundle\FeedBundle\Entity
 */
class MessageManager
{
    /**
     * @var Producer
     */
    protected $websocketProducer;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var MessageHelper
     */
    protected $messageHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Producer $websocketProducer
     * @param ObjectManager $objectManager
     * @param Serializer $serializer
     * @param MessageHelper $messageHelper
     * @param LoggerInterface $logger
     */
    public function __construct(Producer $websocketProducer, ObjectManager $objectManager, Serializer $serializer, MessageHelper $messageHelper, LoggerInterface $logger)
    {
        $this->websocketProducer = $websocketProducer;
        $this->objectManager = $objectManager;
        $this->serializer = $serializer;
        $this->messageHelper = $messageHelper;
        $this->logger = $logger;
    }

    /**
     * Emit a Messageable
     *
     * @param  AbstractMessageableInterface $messageAble
     * @param  boolean $andFlush
     * @return Message
     * @throws \Exception
     */
    public function emit(AbstractMessageableInterface $messageAble, $andFlush = true)
    {
        $this->objectManager->persist($messageAble);
        $message = $messageAble->toMessage();
        if ($messageAble instanceof SerializableMessageableInterface) {
            /** @var $messageAble SerializableMessageableInterface */
            $message->setData($messageAble);
        }

        $messageAble->setUpdated(new \DateTime());

        if ($messageAble instanceof MessageableInterface) {
            /** @var MessageableInterface $messageAble */
            $messageAble->resetReadAt();
        }

        if($andFlush) {
            $this->objectManager->flush();
        }

        if($message->getUser() === null) {
            throw new \Exception("no user for message ".$message->getId());
        }

        if($message->getEmittingUser() === null) {
            throw new \Exception("no emmitting user for message ".$message->getId());
        }

        return $this->send($message, $andFlush);
    }

    /**
     * Send a Message
     *
     * @param  Message $message
     * @param  boolean $andFlush
     * @return Message
     */
    public function send(Message $message, $andFlush = true)
    {
        $serialized = $this->prepareMessage($message, $andFlush);

        $this->logger->debug('Sending data to mopa_feed_websockets', json_decode($serialized, true));

        $this->websocketProducer->publish($serialized);

        return $message;
    }

    /**
     * @param Message $message
     * @param bool|true $andFlush
     * @return string
     */
    public function prepareMessage(Message $message, $andFlush = true)
    {
        $payload = $message->getData();

        if ($payload instanceof SerializableMessageableInterface) {

            $data = $this->serializer->serialize($payload, 'json',
                SerializationContext::create()->setGroups("mopa_feed_websockets.internal")
            );

            $obj = (array) json_decode($data);

            //some objects need further transformation, let em handle this themselves
            if ($payload instanceof MessageObjectTransformer) {
                $obj = $payload->transformMessageObject($obj);
            }

            $message->setData($obj);
        }

        $serialized = $this->serializer->serialize($message, 'json',
            SerializationContext::create()->setGroups("mopa_feed_websockets.internal")
        );

        if ($message->getSave()) {
            $this->objectManager->persist($message);
        }
        else {
            if($message->getFeedItem() instanceof FeedItem) {
                $message->getFeedItem()->setMessage(null);
            }
        }

        if ($message->getSave() && $andFlush) {
            $this->objectManager->flush();
        }

        return $serialized;
    }

    /**
     * Emits a FeedItem beased on the last Part of its class throw it in here we will find it
     *
     * @param string $class, $arguments...
     * @return Message
     * @throws \Exception
     */
    public function emitItem($class)
    {
        if(!is_string($class)){
            throw new \Exception('Class must be a string. '.(string)$class.' given');
        }

        $class = self::findFullClass($class);

        $r = new ReflectionClass($class);

        $args = func_get_args();
        $arrayOfConstructorArgs = array_splice($args, 1);

        /** @var FeedItem $feedItem */
        $feedItem = $r->newInstanceArgs($arrayOfConstructorArgs);
        return $this->emit($feedItem);
    }

    /**
     * @param $class
     * @return mixed
     */
    public static function findFullClass($class)
    {
        if(class_exists($class)){
            return $class;
        }

        $classes = get_declared_classes();
        foreach($classes as $declared){
            if(($temp = strlen($declared) - strlen($class)) >= 0 && strpos($declared, $class, $temp) !== FALSE){
                return $declared;
            }
        }

        return $class;
    }
}
