<?php
namespace Mopa\Bundle\FeedBundle\WebSocket\Server;

use  P2\Bundle\RatchetBundle\WebSocket\Connection\Connection as BaseConnection;
use P2\Bundle\RatchetBundle\WebSocket\Payload;

/**
 * Class Connection
 * @package Mopa\Bundle\FeedBundle\WebSocket\Server
 */
class Connection extends BaseConnection
{
    /**
     * @var array
     */
    const dataTypes = [
        'txt',
        'html'
    ];

    /**
     * @var string
     */
    protected $dataType = 'txt'; // default

    /**
     * @var array
     */
    protected $broadcastTopics = [];

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param $type
     */
    public function setDataType($type)
    {
        if (in_array($type, self::dataTypes)) { // getting null or smth else
            $this->dataType = $type;
        }
    }

    /**
     * @return array
     */
    public function getBroadcastTopics()
    {
        return $this->broadcastTopics;
    }

    /**
     * @param array $broadcastTopics
     */
    public function setBroadcastTopics(array $broadcastTopics)
    {
        $this->broadcastTopics = $broadcastTopics;
    }

    /**
     * Broadcasts an event to all
     *
     * @param $topic
     * @param Payload $payload
     */
    public function broadcastPayloadToTopic($topic, Payload $payload)
    {
        /** @var Connection $connection */
        foreach ($this->connectionManager->getConnections() as $connection) {
            if (in_array($topic, $connection->getBroadCastTopics())) {
                $connection->emit($payload);
            }
        }
    }

    /**
     * Broadcasts an event to all
     *
     * @param $topic
     * @param Payload $payload
     * @param callable $callback
     */
    public function broadcastToTopic($topic, Payload $payload, $callback)
    {
        /** @var Connection $connection */
        foreach ($this->connectionManager->getConnections() as $connection) {
            if (in_array($topic, $connection->getBroadCastTopics())) {
                $callback($connection, $topic, $payload);
            }
        }
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @param array $meta
     */
    public function setMeta(array $meta): void
    {
        $this->meta = $meta;
    }

    public function getMetaData(string $key, bool $default = null)
    {
        if(array_key_exists($key, $this->meta)) {
            return $this->meta[$key];
        }

        return $default;
    }
    public function setMetaData($key, $value)
    {
        $this->meta[$key] = $value;
    }
}
