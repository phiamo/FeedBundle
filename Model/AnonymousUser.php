<?php
/**
 * Created by PhpStorm.
 * User: phiamo
 * Date: 18.08.16
 * Time: 19:02
 */

namespace Mopa\Bundle\FeedBundle\Model;


use P2\Bundle\RatchetBundle\WebSocket\Client\ClientInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AnonymousUser
 * @package Mopa\Bundle\FeedBundle\Model
 */
class AnonymousUser implements ClientInterface, UserInterface
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

    /**
     * Returns the roles granted to the user.
     *
     *     public function getRoles()
     *     {
     *         return ['ROLE_USER'];
     *     }
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        // TODO: Implement getRoles() method.
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        // TODO: Implement getPassword() method.
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        // TODO: Implement getUsername() method.
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}