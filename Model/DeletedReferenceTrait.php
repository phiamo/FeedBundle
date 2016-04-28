<?php
/**
 * Created by PhpStorm.
 * User: phiamo
 * Date: 28.04.16
 * Time: 12:03
 */

namespace Mopa\Bundle\FeedBundle\Model;
use Doctrine\ODM\MongoDB\DocumentNotFoundException;

/**
 * Class DeletedReferenceTrait
 * @package Mopa\Bundle\FeedBundle\Model
 *
 * Trait to help detecting deleted (softdeletable probably) references
 */
trait DeletedReferenceTrait
{
    /**
     * Get the referenced object or null
     * @return object|null
     */
    abstract public function getReference();

    /**
     * @return bool
     * @throws \Exception
     */
    public function isReferenceDeleted()
    {
        if(null !== $object = $this->getReference()) {
            try{
                if(!method_exists($object, 'deletedAt')) {
                    return $object->getDeletedAt() !== null; // depending on the order of method calls returning getDeleted == null might return wrong values
                }
                throw new \Exception('Method deletedAt does not exist on referenced object, does "'.get_class($object).'" use SoftDeletableDocument Trait or similar?');
            }
            catch(DocumentNotFoundException $e){
                return true;
            }
        }

        return true;
    }
}