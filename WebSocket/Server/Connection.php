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
}
