<?php


namespace Mopa\Bundle\FeedBundle\WebSocket\Server;


use React\EventLoop\LoopInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ServerStartEvent extends Event
{
    /**
     * @var LoopInterface
     */
    private LoopInterface $reactLoop;

    public function __construct(LoopInterface $reactLoop)
    {
        $this->reactLoop = $reactLoop;
    }

    public function getLoop() {
        return $this->reactLoop;
    }
}
