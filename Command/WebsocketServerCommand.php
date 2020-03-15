<?php

namespace Mopa\Bundle\FeedBundle\Command;

use Doctrine\DBAL\DBALException;
use Mopa\Bundle\FeedBundle\Model\Message;
use Mopa\Bundle\FeedBundle\WebSocket\Server\Connection;
use React\EventLoop\Timer\Timer;
use React\Stomp\AckResolver;
use React\Stomp\Client;
use React\Stomp\Protocol\Frame;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use React\Stomp\Factory as ReactStompFactory;
use P2\Bundle\RatchetBundle\WebSocket\ConnectionEvent;
use P2\Bundle\RatchetBundle\WebSocket\Payload;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Security\Core\User\UserInterface;

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

    private function establishConnection()
    {
        /**
         * If any timeouts in doctrine occur
         * @var \Doctrine\DBAL\Connection $connection
         */
        foreach($this->getContainer()->get('doctrine')->getConnections() as $connection) {
            if ($connection->ping() === false) {
                $connection->close();
                $connection->connect();
            }
        }
    }

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
            $this->getContainer()->get('event_dispatcher')->dispatch(self::SERVER_START);

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
            $rhost = $this->getContainer()->getParameter('rabbitmq_host');
            $rvhost = $this->getContainer()->getParameter('rabbitmq_vhost');
            $ruser = $this->getContainer()->getParameter('rabbitmq_user');
            $rpass = $this->getContainer()->getParameter('rabbitmq_pass');

            $reactLoop = $server->loop;

            $output->writeln('Using '.get_class($reactLoop).' reaktLoop implementation');

            $this->getContainer()->get('Mopa\Bundle\FeedBundle\WebSocket\Server\ReactHelper')->setLoop($reactLoop);

            // hooking stomp to the loop
            $stompFactory = new ReactStompFactory($reactLoop);
            // adding stomp login
            /** @var Client $client */
            $stompClient = $stompFactory->createClient(array(
                'host'      => $rhost,
                'vhost' => $rvhost,
                'login' => $ruser,
                'passcode' => $rpass
            ));

            $connectionManager = $this->getContainer()->get('p2_ratchet.websocket.connection_manager');
            $serializer = $this->getContainer()->get('jms_serializer');
            $eventDispatcher = $this->getContainer()->get('event_dispatcher');

            $stompClient
                ->connect()
                ->then(function (Client $stompClient) use ($output, $serializer, $connectionManager, $eventDispatcher)
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
                        $stompClient->subscribeWithAck('/queue/websockets', 'client-individual', function (Frame $frame, AckResolver $ackResolver)
                        use ($output, $serializer, $connectionManager, $eventDispatcher)
                        {
                            try {
                                $tmp = json_decode($frame->body, true);

                                if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                                    $output->writeln('Got frame: ' . $frame->body);
                                }

                                if (!isset($tmp['class'])) {
                                    throw new \Exception('Please define $class property for mopa_feed_websockets.internal serialization');
                                }

                                try {
                                    //this comes internally via jms serializer
                                    /** @var Message $message */
                                    $message = $serializer->deserialize(
                                        $frame->body,
                                        $tmp['class'],
                                        'json'
                                    );
                                }
                                catch (\ReflectionException $e){
                                    $output->writeln('<warning>Unknown Class unserialized: '.$e->getMessage());
                                    $ackResolver->ack();
                                    return;
                                }

                                if ($message->getEvent() === 'dwbn_chat.auth_success') {
                                    $data = $message->getData();
                                    $connection = $connectionManager->getConnectionByResourceId($data['connectionId']);
                                    if($connection) {
                                        $response = new Payload(ConnectionEvent::SOCKET_AUTH_SUCCESS, []);
                                        $connection->emit($response);
                                        $connection->setMetaData('muted', false);
                                        $connection->setMetaData('token', $data['token']);
                                        $connection->setBroadcastTopics($data['topics']);
                                        $eventDispatcher->dispatch(ConnectionEvent::SOCKET_AUTH_SUCCESS, new ConnectionEvent($connection));
                                    }
                                    return;
                                }
                                else {
                                    if ($message->isBroadcast()) {

                                        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                                            if ($message->getEmittingUser()) {
                                                $output->writeln("<info>Broadcasting from: " . $message->getEmittingUser()->getUsername() . ":</info>" . $message->getEvent());
                                            } else {
                                                $output->writeln("<info>Broadcasting from message without emiting user: </info>" . $message->getEvent());
                                            }
                                        }

                                        /** @var Connection $connection */
                                        foreach ($connectionManager->getConnections() as $connection) {

                                            if (!$connection->getMetaData('token')) {
                                                if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL ) {
                                                    $output->writeln("<warning>No client for: " . $connection->getId() . "</warning>");
                                                }
                                                continue;
                                            }
                                            if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL ) {
                                                $output->writeln("<info>Broadcasting to: " . $connection->getMetaData('token') . "</info>");
                                            }

                                            //$message->setUser($connection->getClient());

                                            if (count($message->getBroadcastTopics()) > 0) {
                                                $send = false;
                                                foreach ($message->getBroadcastTopics() as $topic) {
                                                    if (in_array($topic, $connection->getBroadcastTopics())) {
                                                        $send = true;
                                                    }
                                                }
                                                if (!$send) {
                                                    continue;
                                                }
                                            }

                                            $this->send($message, $connection, $output);
                                        }
                                    } else {
                                        if(!$message->getUser()) {
                                            var_dump($message);exit;
                                        }
                                        $token = $message->getUser()->getAccessToken();
                                        $connections = $connectionManager->getConnectionsByToken($token);

                                        /** @var Connection $connection */
                                        foreach ($connections as $connection) {
                                            if ($connection) {
                                                $this->send($message, $connection, $output);
                                            } else {
                                                if ($output->getVerbosity() > 2) {
                                                    $output->writeln("Discarding Msg for User: <info>" . $connection->getMetaData('token') . "</info> .. no connection");
                                                }
                                            }
                                        }
                                    }
                                }

                                $ackResolver->ack();
                            }
                            catch(DBALException $e) {
                                $this->establishConnection();
                                // we dont wanna loose it, so we hope we wont generate a endless loop
                                $ackResolver->nack();
                            }
                        });
                    }
                    catch (\Exception $e) { // do not exit the loop due to ANY failure in here ... ;(
                        $output->writeln(sprintf('Catched <error>%s</error>', $e->getMessage()));
                        if ($output->getVerbosity() > 2) {
                            $output->writeln(sprintf("Trace: \n%s", $e->getTraceAsString()));
                        }
                    }
                }, function(\Exception $e) use ($output){
                    $output->writeln('<error>'.$e->getMessage().'</error>');
                    exit;
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
     * @return bool
     */
    protected function send(Message $message, Connection $connection, OutputInterface $output)
    {

        /*
        if($message->getFirewallName()) {
            try {
                $this->getContainer()->get($this->getContainer()->getParameter('mopa_feed.login_manager'))->loginUser($message->getFirewallName(), $connection->getClient());
            } catch (\Exception $e) {
                $output->writeln('<warning>' . $connection->getClient()->getUsername() . ' had unexpected login probs: ' . $e->getMessage() . '</warning>');
                return false;
            }
        }*/

        //$message = $this->getContainer()->get('mopa_feed.message_helper')->decorate($message, array($connection->getDataType()));
        // this is an "external endpoint" so we need to use a real serializer here
        // json_decode to reform for Payload->encode()
        $msg = json_decode(
            $this->getContainer()->get('jms_serializer')->serialize($message, // using the full serializer feature set
                'json', SerializationContext::create()->setGroups("mopa_feed_websockets")
            ), true
        );

        $payload = new Payload($message->getEvent(), $msg);

        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL ) {
            $output->writeln(
                '<info><comment>Stomp</comment> dispatching '.$message->getEvent().'</info> for conn #'.$connection->getId().' Type: ' .$connection->getDataType(). ''
            );
        }

        if($output->getVerbosity() > OutputInterface::VERBOSITY_DEBUG) {
            $output->writeln(
                '<info>With data: </info>'.var_export($msg, true)
            );
        }

        $this->getContainer()->get('event_dispatcher')->dispatch('mopa_feed.websocket.message',
            new ConnectionEvent($connection, $payload)
        );


        return true;
    }
}
