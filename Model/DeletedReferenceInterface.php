<?php
/**
 * Created by PhpStorm.
 * User: phiamo
 * Date: 28.04.16
 * Time: 12:02
 */

namespace Mopa\Bundle\FeedBundle\Model;

/**
 * Interface DeletedReferenceInterface
 * @package Mopa\Bundle\FeedBundle\Model
 *
 * FeedItems implementing this interface may contain already deleted Items
 */
interface DeletedReferenceInterface
{
    /**
     * @return boolean
     */
    public function isReferenceDeleted();

    /**
     * @return object|mixed
     */
    public function getReference();
}