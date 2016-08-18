<?php
/**
 * Created by PhpStorm.
 * User: phiamo
 * Date: 18.08.16
 * Time: 19:02
 */

namespace Mopa\Bundle\FeedBundle\Model;


use FOS\UserBundle\Model\User;
use P2\Bundle\RatchetBundle\WebSocket\Client\ClientInterface;

/**
 * Class AnonymousUser
 * @package Mopa\Bundle\FeedBundle\Model
 */
class AnonymousUser extends User implements ClientInterface
{
    /**
     * @var string
     */
    protected $accessToken;

    /**
     * AnonymousUser constructor.
     * @param $accessToken string
     */
    public function __construct($accessToken)
    {
        parent::__construct();
        $this->accessToken = $accessToken;
    }

    /**
     * @return null|string
     */
    public function getId()
    {
        return $this->getAccessToken();
    }

    /**
     * Sets the websocket access token for this client
     *
     * @param string $accessToken
     * @return ClientInterface
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * Returns the websocket access token for this client if any, or null.
     * @return null|string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Returns the array of public client data which will be transferred to the websocket client on successful
     * authentication. The websocket access token for this client should always be returned.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'accessToken' => $this->getAccessToken()
        ];
    }
}