<?php

namespace Mopa\Bundle\FeedBundle\Command;

use Mopa\Bundle\FeedBundle\WebSocket\Server\ServerStartEvent;
use P2\Bundle\RatchetBundle\WebSocket\ConnectionEvent;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class WebsocketServerCommand
 * @package Mopa\Bundle\FeedBundle\Command
 */
class WebsocketServerCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    const ARG_ADDRESS = 'address';

    /**
     * @var string
     */
    const ARG_PORT = 'port';
    const SERVER_START = 'mopa_feed.websocket_server.start';

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->addArgument(static::ARG_PORT, InputArgument::OPTIONAL, 'The port to listen on incoming connections')
            ->addArgument(static::ARG_ADDRESS, InputArgument::OPTIONAL, 'The address to listen on')
            ->setDescription('Starts a enhanced web socket server')
            ->setHelp('mopa:feed:websocketserver:start [port] [address]')
            ->setName('mopa:feed:websocketserver:start');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->getVerbosity() > 0) {
            $output->writeln('Verbosity: '.$output->getVerbosity());
            $output->writeln('Env: '.$this->getContainer()->get('kernel')->getEnvironment());
        }
        try {
            /** @var \P2\Bundle\RatchetBundle\WebSocket\Server\Factory $factory */
            $factory = $this->getContainer()->get('p2_ratchet.websocket.server_factory');

            if (null !== $address = $input->getArgument(static::ARG_ADDRESS)) {
                $factory->setAddress($address);
            }

            if (null !== $port = $input->getArgument(static::ARG_PORT)) {
                $factory->setPort($port);
            }

            $server = $factory->create();

            $command = $this->getApplication()->find('rabbitmq:setup-fabric');

            $arguments = array();

            $greetInput = new ArrayInput($arguments);
            $returnCode = $command->run($greetInput, $output);

            if($returnCode !== 0) {
                $output->writeln('<error>Could not setup rabbitmq</error>');
            }

            $output->writeln(
                sprintf(
                    '<info><comment>Ratchet</comment> - listening on %s:%s</info>',
                    $factory->getAddress(),
                    $factory->getPort()
                )
            );

            $reactLoop = $server->loop;

            $output->writeln('Using '.get_class($reactLoop).' reaktLoop implementation');

            $this->getContainer()->get('event_dispatcher')->dispatch(self::SERVER_START, new ServerStartEvent($reactLoop));

            $server->run();

            return 0;
        } catch (\Exception $e) {
            $output->writeln(sprintf("<error>Finally: %s</error>", $e->getMessage()));
            throw $e;
        }
    }

}
