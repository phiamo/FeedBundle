<?php

namespace Mopa\Bundle\FeedBundle\Command;

use FOS\UserBundle\Model\UserInterface;
use Mopa\Bundle\FeedBundle\Entity\Message;
use Mopa\Bundle\FeedBundle\WebSocket\Server\Connection;
use React\Stomp\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use React\Stomp\Factory as ReactStompFactory;
use P2\Bundle\RatchetBundle\WebSocket\ConnectionEvent;
use P2\Bundle\RatchetBundle\WebSocket\Payload;
use JMS\Serializer\SerializationContext;

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

            $output->writeln(
                sprintf(
                    '<info><comment>Ratchet</comment> - listening on %s:%s</info>',
                    $factory->getAddress(),
                    $factory->getPort()
                )
            );
            $rhost = $this->getContainer()->getParameter('rabbitmq_host');
            $rvhost = $this->getContainer()->getParameter('rabbitmq_vhost');
            $ruser = $this->getContainer()->getParameter('rabbitmq_user');
            $rpass = $this->getContainer()->getParameter('rabbitmq_pass');
            $loop = $server->loop;
            // hooking stomp to the loop
            $stompFactory = new ReactStompFactory($loop);
            // adding stomp login
            /** @var Client $client */
            $client = $stompFactory->createClient(array(
                'host'      => $rhost,
                'vhost' => $rvhost,
                'login' => $ruser,
                'passcode' => $rpass
            ));

            // fetching services, no need to do in the loop
            $eventDispatcher = $this->getContainer()->get('event_dispatcher');
            $connectionManager = $this->getContainer()->get('p2_ratchet.websocket.connection_manager');
            $messageHelper = $this->getContainer()->get('mopa_feed.message_helper');
            $serializer = $this->getContainer()->get('jms_serializer');
            $client
                ->connect()
                ->then(function (Client $client) use ($output, $connectionManager, $messageHelper, $eventDispatcher, $serializer)
                {
                    try { // do not exit the loop due to ANY failure in here ... ;(
                        $output->writeln(sprintf(
                            '<info><comment>Stomp</comment> - connected .. verbosity: %s</info>',
                            $output->getVerbosity()
                        ));
                        /**
                         * This closure just takes any event from the websocket queue
                         * Gets the connections its relevant to determined by user_id
                         * and emits it as ConnectionEvent to the all connections the user has via the Websocket Application
                         */
                        $client->subscribe('/queue/websockets', function ($frame) use ($connectionManager, $messageHelper, $eventDispatcher, $serializer, $output)
                        {
                            $tmp = json_decode($frame->body, true);

                            //this comes internally via jms serializer
                            /** @var Message $message */
                            $message = $serializer->deserialize(
                                    $frame->body,
                                    $tmp['class'],
                                    'json'
                            );

                            $event = $message->getEvent();

                            /** @var UserInterface $user */
                            $user = $message->getUser();

                            if ($output->getVerbosity() > 1) {
                                if ($user) {
                                    $output->writeln("Got Msg for User: <info>".$user->getUsername()."</info>");
                                } else {
                                    $output->writeln("Couldnt get User for: <info>".$frame->body."</info>");
                                }
                            }

                            if ($user && $connectionManager->hasClientId($user->getId())) {
                                if ($output->getVerbosity() > 2) {
                                    $output->writeln("Having Client Id for User: <info>".$user->getUsername()."</info>");
                                }                                $connections = $connectionManager->getConnectionsByClientId($message->getUser()->getId());


                                /** @var Connection $connection */
                                foreach ($connections as $connection) {
                                    if ($connection) {
                                        $message = $messageHelper->decorate($message, array($connection->getDataType()));
                                        // this is an "external endpoint so we need to use a real serializer here
                                        // json_decode to reform for Payload->encode()
                                        $msg = json_decode(
                                                $serializer->serialize($message, // using the full serializer feature set
                                                    'json', SerializationContext::create()->setGroups("websockets")
                                            )
                                        );

                                        $payload = new Payload($event, $msg);

                                        if (OutputInterface::VERBOSITY_NORMAL <= $output->getVerbosity()) {
                                            $output->writeln(
                                                    '<info><comment>Stomp</comment> dispatching '.$event.'</info> for conn #'.$connection->getId()." User ".$message->getUser()->getId().' Type: ' .$connection->getDataType(). ''
                                            );
                                        }

                                        if($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                                            $output->writeln(
                                                '<info>With data</info>'.var_export($msg, true)
                                            );
                                        }

                                        $eventDispatcher->dispatch('websockets',
                                            new ConnectionEvent($connection, $payload)
                                        );
                                    } else {
                                        if ($output->getVerbosity() > 2) {
                                            $output->writeln("Discarding Msg for User: <info>".$user->getUsername()."</info> .. no connection");
                                        }
                                    }
                                }
                            } else {
                                if ($output->getVerbosity() > 1) {
                                    if (!$user) {
                                        $output->writeln("Couldnt get User for: <info>".$frame->body."</info>");
                                    } else {
                                        $output->writeln("No Connection for User: <info>".$user->getId()."</info>");
                                    }
                                }
                            }
                        });
                    } catch (\Exception $e) { // do not exit the loop due to ANY failure in here ... ;(
                        $output->writeln(sprintf('Catched <error>%s</error>', $e->getMessage()));
                        if ($output->getVerbosity() > 2) {
                            $output->writeln(sprintf("Trace: \n%s", $e->getTraceAsString()));
                        }
                    }
                });
            $server->run();

            return 0;
        } catch (\Exception $e) {
            $output->writeln(sprintf("<error>Finally: %s</error>", $e->getMessage()));
            $output->writeln(sprintf("Trace: \n%s", $e->getTraceAsString()));

            return -1;
        }
    }
}
