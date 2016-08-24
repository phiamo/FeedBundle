<?php
/**
 * Created by PhpStorm.
 * User: phiamo
 * Date: 24.08.16
 * Time: 13:21
 */

namespace Mopa\Bundle\FeedBundle\Model;

/**
 * Interface MessageDecoratorInterface
 * @package Mopa\Bundle\FeedBundle\Model
 */
interface MessageDecoratorInterface
{
    /**
     * @param Message $message
     * @param array $formats
     * @return Message
     */
    public function decorate(Message $message, array $formats = []);
}