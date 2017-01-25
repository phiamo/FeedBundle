<?php

namespace Mopa\Bundle\FeedBundle\WebSocket;

use P2\Bundle\RatchetBundle\WebSocket\ConnectionEvent;
use P2\Bundle\RatchetBundle\WebSocket\ApplicationInterface;
use P2\Bundle\RatchetBundle\WebSocket\Payload;

/**
 * Symfony2 Event Subscriber to hook into P2Ratchet
 *
 * Class Application
 * @package Mopa\Bundle\FeedBundle\WebSocket
 */
class Application implements ApplicationInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            'mopa_feed.websocket.message' => 'onMessage'
        );
    }

    /**
     * Just
     * @param ConnectionEvent $event
     */
    public function onMessage(ConnectionEvent $event)
    {
        $payload = $event->getPayload();
        $data = $payload->getData();

        $event->getConnection()->emit(
            new Payload(
                'mopa_feed.'.$payload->getEvent(),
                $data
            )
        );

    }
}
