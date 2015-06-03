<?php
namespace Mopa\Bundle\ScoringBundle\Entity;
use Doctrine\ORM\EntityManager;

/**
 * Class ScoreManager
 * @package Mopa\Bundle\ScoringBundle\Entity
 */
class ScoreManager
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager){
        $this->entityManager = $entityManager;
    }

    /**
     * @param Action $action
     */
    public function score(Action $action) {
        $object = $action->getObject();
        $object->addScore($action->getValue());

        $this->entityManager->persist($object);
        $this->entityManager->flush($object);
    }
}