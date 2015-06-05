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
     * @var string
     */
    protected $messageClass;

    /**
     * @param Producer $websocketProducer
     * @param ObjectManager $objectManager
     * @param Serializer $serializer
     * @param MessageHelper $messageHelper
     * @param $messageClass
     */
    public function __construct(Producer $websocketProducer, ObjectManager $objectManager, Serializer $serializer, MessageHelper $messageHelper, $messageClass)
    {
        $this->websocketProducer = $websocketProducer;
        $this->objectManager = $objectManager;
        $this->serializer = $serializer;
        $this->messageHelper = $messageHelper;
        $this->messageClass = $messageClass;
    }

    /**
     * Emit a Messageable
     *
     * @param  AbstractMessageableInterface $messageAble
     * @param  boolean $andFlush
     * @return Message
     */
    public function emit(AbstractMessageableInterface $messageAble, $andFlush = true)
    {
        $this->objectManager->persist($messageAble);
        $message = $messageAble->toMessage($this->messageClass);

        if ($messageAble instanceof SerializableMessageableInterface) {
            /** @var $messageAble SerializableMessageableInterface */
            $message->setData($messageAble);
        }

        $messageAble->setUpdated(new \DateTime());

        if ($messageAble instanceof MessageableInterface) {
            /** @var MessageableInterface $messageAble */
            $messageAble->resetReadAt();
        }

        $this->objectManager->flush($messageAble);

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
}
