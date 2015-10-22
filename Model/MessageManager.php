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
     * @param Producer $websocketProducer
     * @param ObjectManager $objectManager
     * @param Serializer $serializer
     * @param MessageHelper $messageHelper
     */
    public function __construct(Producer $websocketProducer, ObjectManager $objectManager, Serializer $serializer, MessageHelper $messageHelper)
    {
        $this->websocketProducer = $websocketProducer;
        $this->objectManager = $objectManager;
        $this->serializer = $serializer;
        $this->messageHelper = $messageHelper;
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

        $this->objectManager->flush();

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
        $payload = $message->getData();

        if ($payload instanceof SerializableMessageableInterface) {
            $data = $this->serializer->serialize($payload, 'json',
                SerializationContext::create()->setGroups("websockets.internal")
            );

            $obj = (array) json_decode($data);

            //some objects need further transformation, let em handle this themselves
            if ($payload instanceof MessageObjectTransformer) {
                $obj = $payload->transformMessageObject($obj);
            }
            $message->setData($obj);
        }

        $this->messageHelper->decorate($message); // decorating messages so templates know them

        $serialized = $this->serializer->serialize($message, 'json',
                SerializationContext::create()->setGroups("websockets.internal")
        );


        if ($message->getSave()) {
            $this->objectManager->persist($message);
        }

        if ($andFlush) {
            $this->objectManager->flush();
        }

        $this->websocketProducer->publish($serialized);

        return $message;
    }

    /**
     * Emits a FeedItem beased on the last Part of its class throw it in here we will find it
     *
     * @param string $class,...
     * @return Message
     * @throws \Exception
     */
    public function emitItem($class)
    {
        if(!is_string($class)){
            throw new \Exception('Class must be a string. '.(string)$class.' given');
        }

        $fullClass = self::findFullClass($class);
        $r = new ReflectionClass($fullClass);

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
    public static function findFullClass($class) {
        $classes = get_declared_classes();
        foreach($classes as $declared){
            if(($temp = strlen($declared) - strlen($class)) >= 0 && strpos($declared, $class, $temp) !== FALSE){
                return $declared;
            }
        }
        return $class;
    }
}
