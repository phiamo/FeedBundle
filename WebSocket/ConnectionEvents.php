<?php
/**
 * Created by PhpStorm.
 * User: phiamo
 * Date: 11.02.17
 * Time: 00:20
 */

namespace Mopa\Bundle\FeedBundle\WebSocket;

/**
 * Class ConnectionEvents
 * @package Mopa\Bundle\FeedBundle\WebSocket
 */
class ConnectionEvents
{
    const WEBSOCKET_MESSAGE = 'mopa_feed.websocket.message';
    const WEBSOCKET_CLOSE = 'mopa_feed.websocket.close';
    const WEBSOCKET_ERROR = 'mopa_feed.websocket.error';
}