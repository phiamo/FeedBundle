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

    static $lastPing = 0;
    static $pinging = false;
    /**
     * Establish connection
     */
    private function establishConnection()
    {
        if (self::$pinging || self::$lastPing + 10 > time()) {
            return;
        }

        self::$pinging = true;
        /**
         * If any timeouts in doctrine occur
         * @var \Doctrine\DBAL\Connection $connection
         */
        foreach ($this->doctrine->getConnections() as $connection) {
            if ($connection->ping() === false) {
                $connection->close();
                $connection->connect();
            }
        }


        foreach ($this->doctrine->getManagers() as $name => $manager) {
            if (method_exists($manager, 'isOpen') && !$manager->isOpen()) {
                $this->doctrine->resetManager($name);
            }
        }
        self::$lastPing = time();
        self::$pinging = false;
    }

    public function onMessage(SocketConnection $from, $msg)
    {
        try {
            parent::onMessage($from, $msg);
        } catch (\Throwable) {
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            exit;
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
                $this->logger->debug(sprintf('Dispatched event: %s', $payload->getEvent()) . (array_key_exists('topic', $payload->getData()) ? ' for topic: ' . $payload->getData()['topic'] : ''));
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
        if (!$this->connectionManager->authenticate($connection, $payload->getData())) {
            $connection->emit(new Payload(ConnectionEvent::SOCKET_AUTH_FAILURE, 'Invalid access token.'));

            $this->eventDispatcher->dispatch(ConnectionEvent::SOCKET_AUTH_FAILURE, new ConnectionEvent($connection));

            $this->logger->notice(
                sprintf(
                    'Authentication error <info>#%s</info> (<comment>%s</comment>)',
                    $connection->getId(),
                    $connection->getRemoteAddress()
                )
            );

            $this->connectionManager->closeConnectionById($connection->getId());
            return;
        }

        $response = new Payload(ConnectionEvent::SOCKET_AUTH_SUCCESS, $connection->getClient()->jsonSerialize());
        $connection->emit($response);

        $this->eventDispatcher->dispatch(ConnectionEvent::SOCKET_AUTH_SUCCESS, new ConnectionEvent($connection));

        $this->logger->notice(
            sprintf(
                'Authenticated <info>#%s</info> (<comment>%s</comment>)',
                $connection->getId(),
                $connection->getRemoteAddress()
            )
        );
    }

    /**
     * @param SocketConnection $conn
     * @param \Exception $e
     */
    public function onError(SocketConnection $conn, \Exception $e)
    {
        // give us a chance to cleanup and send this to our app
        $this->eventDispatcher->dispatch(ConnectionEvents::WEBSOCKET_ERROR, new ErrorEvent($conn->resourceId));

        $this->connectionManager->closeConnection($conn);
        $this->logger->error($e->getMessage(), $e->getTrace());
    }

    /**
     * @param SocketConnection $conn
     * @throws \RuntimeException
     */
    public function onClose(SocketConnection $conn)
    {
        $connection = $this->connectionManager->getConnection($conn);

        if ($connection) {
            $this->eventDispatcher->dispatch(ConnectionEvents::WEBSOCKET_CLOSE, new ConnectionEvent($connection));

            $this->logger->notice(
                sprintf(
                    'Closed connection <info>#%s</info> (<comment>%s</comment>)',
                    $connection->getId(),
                    $connection->getRemoteAddress()
                )
            );
        } else {
            $this->logger->log('warning', 'Could not get connection for closing', json_decode(json_encode($conn), true));
        }

        $this->connectionManager->closeConnection($conn);
    }
}
