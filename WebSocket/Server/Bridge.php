<?php
namespace Mopa\Bundle\FeedBundle\WebSocket\Server;

use Mopa\Bundle\FeedBundle\WebSocket\ConnectionEvents;
use P2\Bundle\RatchetBundle\WebSocket\Connection\ConnectionInterface;
use P2\Bundle\RatchetBundle\WebSocket\Connection\ConnectionManagerInterface;
use P2\Bundle\RatchetBundle\WebSocket\ConnectionEvent;
use P2\Bundle\RatchetBundle\WebSocket\Payload;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface as SocketConnection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Bridge
 * @package Mopa\Bundle\FeedBundle\WebSocket\Server
 */
class Bridge extends \P2\Bundle\RatchetBundle\WebSocket\Server\Bridge
{
    /**
     * @param ConnectionManagerInterface $connectionManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConnectionManagerInterface $connectionManager,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger
    ) {
        parent::__construct($connectionManager, $eventDispatcher, $logger);

        $this->allowedEvents = array(
            ConnectionEvent::SOCKET_AUTH_REQUEST,
            ConnectionEvent::SOCKET_CLOSE,
        );
    }

    /**
     * Handles the connection authentication.
     *
     * @param ConnectionInterface $connection
     * @param Payload $payload
     */
    protected function handleAuthentication(ConnectionInterface $connection, Payload $payload)
    {
        parent::handleAuthentication($connection, $payload);
        $this->eventDispatcher->dispatch(ConnectionEvent::SOCKET_AUTH_REQUEST, new ConnectionEvent($connection));
    }

    /**
     * @param SocketConnection $conn
     * @throws \RuntimeException
     */
    public function onClose(SocketConnection $conn)
    {
        if($this->connectionManager->hasConnection($conn)) {
            $connection = $this->connectionManager->closeConnection($conn);

            $this->eventDispatcher->dispatch(ConnectionEvents::WEBSOCKET_CLOSE, new ConnectionEvent($connection));
            $this->logger->notice(
                sprintf(
                    'Closed connection <info>#%s</info> (<comment>%s</comment>)',
                    $connection->getId(),
                    $connection->getRemoteAddress()
                )
            );
        }

    }
}
