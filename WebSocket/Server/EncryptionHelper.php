<?php

namespace Mopa\Bundle\FeedBundle\WebSocket\Server;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;

/**
 * Class EncryptionHelper
 * @package Mopa\Bundle\FeedBundle\WebSocket\Server
 */
class EncryptionHelper
{
    /**
     * @var string
     */
    private $cacheDir;

    /**
     * EncryptionHelper constructor.
     * @param $cacheDir
     */
    public function __construct($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * @param $data
     * @return string
     */
    public function encrypt($data)
    {
        return Crypto::encrypt($data, $this->getServerKey());
    }

    /**
     * @param $data
     * @return bool|string
     */
    public function decrypt($data)
    {
        try {
            $decryptedAccessToken = Crypto::decrypt($data, $this->getServerKey());

            return $decryptedAccessToken;
        } catch (WrongKeyOrModifiedCiphertextException $ex) {
            // An attack! Either the wrong key was loaded,
            // or the ciphertext is corrupted intentionally
            // by Eve trying to carry out an attack.
            return false;
        }
    }

    /**
     * @return Key
     */
    private function getServerKey()
    {
        $file = $this->cacheDir.DIRECTORY_SEPARATOR.'mopa_feed'.DIRECTORY_SEPARATOR.'serverKey';

        if(!file_exists($file)) {
            @mkdir(dirname($file), 0777, true);

            $key = Key::createNewRandomKey();

            file_put_contents($file, $key->saveToAsciiSafeString());
        }

        $keyAscii = file_get_contents($file);

        return Key::loadFromAsciiSafeString($keyAscii);
    }
}