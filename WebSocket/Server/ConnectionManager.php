<?php
namespace Mopa\Bundle\FeedBundle\WebSocket\Server;


use Doctrine\Common\Persistence\ManagerRegistry;
use Mopa\Bundle\FeedBundle\Model\AnonymousUser;
use P2\Bundle\RatchetBundle\WebSocket\Client\ClientInterface;
use P2\Bundle\RatchetBundle\WebSocket\Connection\ConnectionManager as BaseConnectionManager;
use P2\Bundle\RatchetBundle\WebSocket\Connection\ConnectionInterface;
use P2\Bundle\RatchetBundle\WebSocket\Exception\NotManagedConnectionException;
use Ratchet\ConnectionInterface as SocketConnection;

/**
 * Class ConnectionManager
 * @package P2\Bundle\RatchetBundle\WebSocket\Connection
 */
class ConnectionManager extends BaseConnectionManager
{
    /**
     * @var EncryptionHelper
     */
    protected $encryptionHelper;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var Connection[][]
     */
    protected $clientIds = [];

    /**
     * Leave that method so we instanciate our own connection
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

    private function registerClientConnection(ConnectionInterface $connection, ClientInterface $client)
    {
        $connection->setClient($client);

        $clientId = $client->getId();
        if(!array_key_exists($clientId, $this->clientIds)) {
            $this->clientIds[$clientId] = [];
        }

        $this->clientIds[$clientId][] = $connection->getId();
    }

    private function unregisterClientConnection(ConnectionInterface $connection)
    {
        $client = $connection->getClient();
        if($client) {
            $connId = $connection->getId();
            $clientId = $client->getId();
            if(array_key_exists($clientId, $this->clientIds)) {
                if (($key = array_search($connId, $this->clientIds[$clientId])) !== false) {
                    unset($this->clientIds[$clientId][$key]);
                }
                if (count($this->clientIds[$clientId]) === 0) {
                    unset($this->clientIds[$clientId]);
                }
            }
        }
    }

    public function closeConnectionById(string $connectionId) {
        if(array_key_exists($connectionId, $this->connections)) {
            $connection = $this->connections[$connectionId];
            $connection->close();

            unset($this->connections[$connection->getId()]);

            $this->unregisterClientConnection($connection);
        }
    }
    /**
     * {@inheritDoc}
     */
    public function closeConnection(SocketConnection $socketConnection)
    {
        if (! $this->hasConnection($socketConnection)) {

            return false;
        }

        $connection = $this->getConnection($socketConnection);
        $connection->close();

        unset($this->connections[$connection->getId()]);

        $this->unregisterClientConnection($connection);

        return $connection;
    }

    /**
     * @param $resourceId
     * @return Connection|ConnectionInterface|false
     */
    public function getConnectionByResourceId($resourceId)
    {
        if(array_key_exists($resourceId, $this->connections)) {
            return $this->connections[$resourceId];
        }

        return false;
    }

    /**
     * @param $clientId
     * @return Connection[]
     */
    public function getConnectionsByClientId($clientId)
    {
        $conns = [];
        if(array_key_exists($clientId, $this->clientIds)) {
            foreach ($this->clientIds[$clientId] as $connId) {
                $conns[] = $this->connections[$connId];
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
        /**
         * If we have a registry and any timeouts in doctrine occur
         * @var \Doctrine\DBAL\Connection $dbalConnection
         */
        foreach ($this->registry->getConnections() as $dbalConnection) {
            if (method_exists($dbalConnection, 'ping') && $dbalConnection->ping() === false) {
                $dbalConnection->close();
                $dbalConnection->connect();
            }
        }

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

            $type = @$data["data_type"];
            $connection->setDataType($type);

            if(array_key_exists('broadcastTopics', $data)) {
                $topics = (array)@$data["broadcastTopics"];
                $connection->setBroadcastTopics($topics);
            }

            $this->registerClientConnection($connection, $client);

            return true;
        }

        if($accessToken != '') {

            $decryptedAccessToken = $this->encryptionHelper->decrypt($accessToken);

            if($decryptedAccessToken) {

                $client = new AnonymousUser($decryptedAccessToken);
                $this->registerClientConnection($connection, $client);

                return true;
            }
        }

        return false;
    }

    /**
     * @param EncryptionHelper $encryptionHelper
     */
    public function setEncryptionHelper($encryptionHelper)
    {
        $this->encryptionHelper = $encryptionHelper;
    }

    /**
     * @param null|ManagerRegistry $registry
     */
    public function setRegistry(ManagerRegistry $registry = null)
    {
        $this->registry = $registry;
    }
}
