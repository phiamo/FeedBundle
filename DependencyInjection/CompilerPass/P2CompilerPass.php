<?php
/**
 * Created by PhpStorm.
 * User: phiamo
 * Date: 10.01.17
 * Time: 15:41
 */

namespace Mopa\Bundle\FeedBundle\DependencyInjection\CompilerPass;


use Mopa\Bundle\FeedBundle\WebSocket\Server\Bridge;
use Mopa\Bundle\FeedBundle\WebSocket\Server\Connection;
use Mopa\Bundle\FeedBundle\WebSocket\Server\ConnectionManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class P2CompilerPass
 * @package Mopa\Bundle\FeedBundle\DependencyInjection\CompilerPass
 */
class P2CompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $container->setParameter('p2_ratchet.websocket.server_bridge.class', Bridge::class);
        $container->getDefinition('p2_ratchet.websocket.server_bridge')
            ->replaceArgument('2', new Reference('logger'))
            ->setClass(Bridge::class)
            ->addMethodCall('setRegistry', [new Reference('doctrine')]);

        $container->setParameter('p2_ratchet.websocket.connection.class', Connection::class);
        $container->getDefinition('p2_ratchet.websocket.connection_manager')
            ->setClass(ConnectionManager::class)
            ->addMethodCall('setEncryptionHelper', [new Reference('mopa_feed.encryption_helper')])
            ->addMethodCall('setRegistry', [new Reference('doctrine')]);
    }
}
