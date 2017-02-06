<?php
namespace Mopa\Bundle\FeedBundle\WebSocket\Server;

use P2\Bundle\RatchetBundle\WebSocket\Connection\ConnectionManagerInterface;
use P2\Bundle\RatchetBundle\WebSocket\Connection\ConnectionInterface;
use P2\Bundle\RatchetBundle\WebSocket\ConnectionEvent;
use P2\Bundle\RatchetBundle\WebSocket\Exception\InvalidPayloadException;
use P2\Bundle\RatchetBundle\WebSocket\Exception\NotManagedConnectionException;
use P2\Bundle\RatchetBundle\WebSocket\Exception\InvalidEventCallException;
use P2\Bundle\RatchetBundle\WebSocket\Payload;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface as SocketConnection;
use Ratchet\MessageComponentInterface;
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
     * @param SocketConnection $conn
     * @throws \RuntimeException
     */
    public function onClose(SocketConnection $conn)
    {
        $connection = $this->connectionManager->closeConnection($conn);

        $this->eventDispatcher->dispatch(ConnectionEvent::SOCKET_CLOSE, new ConnectionEvent($connection));

        $this->logger->notice(
            sprintf(
                'Closed connection <info>#%s</info> (<comment>%s</comment>)',
                $connection->getId(),
                $connection->getRemoteAddress()
            )
        );
    }
}
