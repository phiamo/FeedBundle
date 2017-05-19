<?php
namespace Mopa\Bundle\FeedBundle\WebSocket\Server;

use Doctrine\Bundle\DoctrineBundle\Registry;
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
     * @var Registry
     */
    protected $doctrine;

    /**
     * @param ConnectionManagerInterface $connectionManager
     * @param EventDispatcherInterface $eventDispatcher
     *
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
     * @param Registry $doctrine
     */
    public function setRegistry($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Establish connection
     */
    private function establishConnection()
    {
        /**
         * If any timeouts in doctrine occur
         * @var \Doctrine\DBAL\Connection $connection
         */
        foreach($this->doctrine->getConnections() as $connection) {
            if ($connection->ping() === false) {
                $connection->close();
                $connection->connect();
            }
        }
    }

    /**
     * Handles the the given payload received by the given connection.
     *
     * @param ConnectionInterface $connection
     * @param Payload $payload
     */
    protected function handle(ConnectionInterface $connection, Payload $payload)
    {
        $this->establishConnection();
        switch ($payload->getEvent()) {
            case ConnectionEvent::SOCKET_AUTH_REQUEST:
                $this->handleAuthentication($connection, $payload);
                break;
            default:
                $this->eventDispatcher->dispatch($payload->getEvent(), new ConnectionEvent($connection, $payload));
                $this->logger->log('notice', sprintf('Dispatched event: %s', $payload->getEvent()).(array_key_exists('topic', $payload->getData()) ? ' for topic: '.$payload->getData()['topic'] : ''));
        }
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
        $this->establishConnection();

        $connection = $this->connectionManager->closeConnection($conn);

        if($connection) {
            $this->eventDispatcher->dispatch(ConnectionEvents::WEBSOCKET_CLOSE, new ConnectionEvent($connection));

            $this->logger->notice(
                sprintf(
                    'Closed connection <info>#%s</info> (<comment>%s</comment>)',
                    $connection->getId(),
                    $connection->getRemoteAddress()
                )
            );
        }
        else{
            $this->logger->log('warning', 'Could not get connection for closing', json_decode(json_encode($conn), true));
        }

    }
}
