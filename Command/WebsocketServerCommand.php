<?php

namespace Mopa\Bundle\FeedBundle\Command;

use Mopa\Bundle\FeedBundle\WebSocket\Server\ServerStartEvent;
use P2\Bundle\RatchetBundle\WebSocket\Server\Factory;
use PhpAmqpLib\Connection\Heartbeat\PCNTLHeartbeatSender;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class WebsocketServerCommand extends  Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    protected static $defaultName = 'mopa:feed:websocketserver:start';
    private Factory $factory;

    public function __construct(
        Factory $factory
    )
    {
        parent::__construct(self::$defaultName);
        $this->factory = $factory;
    }

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
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->getVerbosity() > 0) {
            $output->writeln('Verbosity: '.$output->getVerbosity());
            $output->writeln('Env: '.$this->container->get('kernel')->getEnvironment());
        }
        try {
            if (null !== $address = $input->getArgument(static::ARG_ADDRESS)) {
                $this->factory->setAddress($address);
            }

            if (null !== $port = $input->getArgument(static::ARG_PORT)) {
                $this->factory->setPort($port);
            }

            $server = $this->factory->create();

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
                    $this->factory->getAddress(),
                    $this->factory->getPort()
                )
            );

            $reactLoop = $server->loop;

            $output->writeln('Using '.get_class($reactLoop).' reaktLoop implementation');

            $this->container->get('event_dispatcher')->dispatch(new ServerStartEvent($reactLoop), self::SERVER_START);

            $server->run();

            return 0;
        } catch (\Exception $e) {
            $output->writeln(sprintf("<error>Finally: %s</error>", $e->getMessage()));
            throw $e;
        }
    }

}
