<?php

namespace Mopa\Bundle\FeedBundle\Command;

use FOS\UserBundle\Model\UserInterface;
use Mopa\Bundle\FeedBundle\Entity\Message;
use Mopa\Bundle\FeedBundle\WebSocket\Server\Connection;
use P2\Bundle\RatchetBundle\WebSocket\Connection\ConnectionInterface;
use React\Stomp\AckResolver;
use React\Stomp\Client;
use React\Stomp\Protocol\Frame;
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
 *
 * If any timeouts in doctrine occur try catch an refetch
 * $em = $this->getContainer()->get('doctrine')->getManager();
 * if ($em->getConnection()->ping() === false) {
 *     $em->getConnection()->close();
 *     $em->getConnection()->connect();
 *     $ackResolver->nack();
 *     return
 * }
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
            $stompClient = $stompFactory->createClient(array(
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
            $stompClient
                ->connect()
                ->then(function (Client $stompClient) use ($output, $connectionManager, $messageHelper, $eventDispatcher, $serializer)
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
                        $stompClient->subscribeWithAck('/queue/websockets', 'client-individual', function (Frame $frame, AckResolver $ackResolver) use ($connectionManager, $messageHelper, $eventDispatcher, $serializer, $output)
                        {
                            $tmp = json_decode($frame->body, true);

                            $output->writeln('Got frame: '.$frame->body);

                            //this comes internally via jms serializer
                            /** @var Message $message */
                            $message = $serializer->deserialize(
                                    $frame->body,
                                    $tmp['class'],
                                    'json'
                            );

                            /** @var UserInterface $user */
                            $user = $message->getUser();

                            if(!$user && $message->isBroadcast()) {
                                $output->writeln("<info>Broadcasting from: ".$message->getEmittingUser()->getUsername()."</info>");
                                /** @var Connection $connection */
                                foreach($connectionManager->getConnections() as $connection) {
                                    if($message->isBroadcast() && count($message->getBroadcastTopics()) > 0) {
                                        $send = false;
                                        foreach ($message->getBroadcastTopics() as $topic) {
                                            if (in_array($topic, $connection->getBroadcastTopics())){
                                                $send = true;
                                            }
                                        }
                                        if(!$send) {
                                            continue;
                                        }
                                    }
                                    $this->send($message, $connection, $output);
                                }
                            }elseif ($user && $connectionManager->hasClientId($user->getId())) {
                                if ($output->getVerbosity() > 2) {
                                    $output->writeln("Having Client Id for User: <info>".$user->getUsername()."</info>");
                                }

                                $connections = $connectionManager->getConnectionsByClientId($message->getUser()->getId());

                                /** @var Connection $connection */
                                foreach ($connections as $connection) {
                                    if ($connection) {
                                        $this->send($message, $connection, $output);
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

                            $ackResolver->ack();
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
            throw $e;
        }
    }

    /**
     * @param Message $message
     * @param Connection $connection
     * @param OutputInterface $output
     */
    protected function send(Message $message, Connection $connection, OutputInterface $output)
    {
        $message = $this->getContainer()->get('mopa_feed.message_helper')->decorate($message, array($connection->getDataType()));
        // this is an "external endpoint so we need to use a real serializer here
        // json_decode to reform for Payload->encode()
        $msg = json_decode(

            $this->getContainer()->get('jms_serializer')->serialize($message, // using the full serializer feature set
                'json', SerializationContext::create()->setGroups("mopa_feed_websockets")
            ), true
        );

        $payload = new Payload($message->getEvent(), $msg);

        if (OutputInterface::VERBOSITY_NORMAL <= $output->getVerbosity()) {
            $output->writeln(
                '<info><comment>Stomp</comment> dispatching '.$message->getEvent().'</info> for conn #'.$connection->getId().' Type: ' .$connection->getDataType(). ''
            );
        }

        if($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $output->writeln(
                '<info>With data: </info>'.var_export($msg, true)
            );
        }
        $this->getContainer()->get('event_dispatcher')->dispatch('mopa_feed.websocket.message',
            new ConnectionEvent($connection, $payload)
        );
    }
}
