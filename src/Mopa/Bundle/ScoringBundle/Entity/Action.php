<?php
namespace Mopa\Bundle\ScoringBundle\Entity;

/**
 * Class Action
 * @package Mopa\Bundle\ScoringBundle\Entity
 */
abstract class Action
{
    /**
     * @var integer
     *
     * Values of this Score
     */
    protected $value;

    /**
     * @var object
     */
    protected $object;

    /**
     * @param $object
     * @throws \Exception
     */
    public function __construct(ScorableInterface $object){
        if(null === $this->value){
            throw new \Exception('You must set $value of '.get_class($this));
        }
        $this->object = $object;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Return the object associated with this action
     *
     * @return object
     */
    public function getObject(){
        return $this->object;
    }
}