<?php
namespace Mopa\Bundle\FeedBundle\WebSocket\Server;

use  P2\Bundle\RatchetBundle\WebSocket\Connection\Connection as BaseConnection;

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
    public function setBroadcastTopics($broadcastTopics)
    {
        $this->broadcastTopics = $broadcastTopics;
    }
}
