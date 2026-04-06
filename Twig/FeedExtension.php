<?php
/**
 * Created by PhpStorm.
 * User: phiamo
 * Date: 10.01.17
 * Time: 15:02
 */

namespace Mopa\Bundle\FeedBundle\Twig;

use Mopa\Bundle\FeedBundle\WebSocket\Server\EncryptionHelper;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Twig\TwigFunction;

/**
 * Class FeedExtension
 * @package Mopa\Bundle\FeedBundle\Twig
 */
class FeedExtension extends TwigExtension
{
    /**
     * @var EncryptionHelper
     */
    private $encryptionHelper;

    /**
     * FeedExtension constructor.
     * @param EncryptionHelper $encryptionHelper
     */
    public function __construct(EncryptionHelper $encryptionHelper)
    {
        $this->encryptionHelper = $encryptionHelper;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new TwigFunction('mopa_feed_encrypt', array($this, 'encrypt')),
        );
    }

    /**
     * @param $string
     * @return string
     * @throws \Exception
     */
    public function encrypt($string)
    {
        return $this->encryptionHelper->encrypt($string);
    }
}