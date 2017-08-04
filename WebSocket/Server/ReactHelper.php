<?php
/**
 * Created by PhpStorm.
 * User: phiamo
 * Date: 04.08.17
 * Time: 21:52
 */

namespace Mopa\Bundle\FeedBundle\WebSocket\Server;


use React\EventLoop\LoopInterface;

/**
 * Class ReactHelper
 * @package Mopa\Bundle\FeedBundle\WebSocket\Server
 */
class ReactHelper
{
    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @return LoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * @param LoopInterface $loop
     */
    public function setLoop($loop)
    {
        $this->loop = $loop;
    }
}