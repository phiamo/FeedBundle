<?php
/**
 * Created by PhpStorm.
 * User: phiamo
 * Date: 20.05.17
 * Time: 20:50
 */

namespace Mopa\Bundle\FeedBundle\WebSocket\Server;


use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class ErrorEvent
 * @package Mopa\Bundle\FeedBundle\WebSocket\Server
 */
class ErrorEvent extends Event
{
    /**
     * @var
     */
    private $resourceId;

    /**
     * ErrorEvent constructor.
     * @param $resourceId
     */
    public function __construct($resourceId)
    {
        $this->resourceId = $resourceId;
    }

    /**
     * @return mixed
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }
}
