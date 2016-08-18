<?php
namespace Mopa\Bundle\FeedBundle\WebSocket\Server;


use Mopa\Bundle\FeedBundle\Model\AnonymousUser;
use P2\Bundle\RatchetBundle\WebSocket\Connection\ConnectionManager as BaseConnectionManager;
use P2\Bundle\RatchetBundle\WebSocket\Connection\ConnectionInterface;
use P2\Bundle\RatchetBundle\WebSocket\Exception\NotManagedConnectionException;
use Ratchet\ConnectionInterface as SocketConnection;
use FOS\UserBundle\Model\UserInterface;

/**
 * Class ConnectionManager
 * @package P2\Bundle\RatchetBundle\WebSocket\Connection
 */
class ConnectionManager extends BaseConnectionManager
{
    /**
     * @param  SocketConnection                                                                             $socketConnection
     * @return bool|Connection|\P2\Bundle\RatchetBundle\WebSocket\Connection\Connection|ConnectionInterface
     */
    public function addConnection(SocketConnection $socketConnection)
    {
        if (! $this->hasConnection($socketConnection)) {
            $connection = new Connection($this, $socketConnection);
            $this->connections[$connection->getId()] = $connection;

            return $connection;
        }

        return false;
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function hasClientId($user_id)
    {
        foreach ($this->connections as $id => $connection) {
            if (null !== $user_id && $connection->getClient() && $connection->getClient()->getId() == $user_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  UserInterface $user
     * @return bool
     */
    public function hasClient(UserInterface $user)
    {
        foreach ($this->connections as $id => $connection) {
            if ($connection->getClient() == $user) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $user_id
     * @return array<Connection>
     */
    public function getConnectionsByClientId($user_id)
    {
        $conns = array();
        foreach ($this->connections as $id => $connection) {
            /** @var ConnectionInterface $connection */
            if ($connection->getClient() && $connection->getClient()->getId() == $user_id) {
                $conns[] = $connection;
            }
        }

        return $conns;
    }

    /**
     * @param  ConnectionInterface|Connection $connection
     * @param  array $data
     * @return bool
     * @throws \P2\Bundle\RatchetBundle\WebSocket\Exception\NotManagedConnectionException
     */
    public function authenticate(ConnectionInterface $connection, $data)
    {
        if (!isset($data["token"])) {
            $accessToken = '';
        }
        else{
            $accessToken = $data["token"];
        }

        if (! isset($this->connections[$connection->getId()])) {
            throw new NotManagedConnectionException();
        }

        if (null !== $client = $this->clientProvider->findByAccessToken($accessToken)) {
            $connection->setClient($client);
            $type = @$data["data_type"];
            $connection->setDataType($type);

            return true;
        }

        if ($accessToken != '') {
            $client = new AnonymousUser($accessToken);
            $connection->setClient($client);

            return true;
        }

        return false;
    }
}
