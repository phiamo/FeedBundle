<?php

namespace Mopa\Bundle\FeedBundle\WebSocket;

use P2\Bundle\RatchetBundle\WebSocket\ConnectionEvent;
use P2\Bundle\RatchetBundle\WebSocket\Server\ApplicationInterface;
use P2\Bundle\RatchetBundle\WebSocket\Payload;

/**
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
            'mopa_feed.websockets' => 'onMessage'
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
