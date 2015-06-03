<?php
namespace Mopa\Bundle\ScoringBundle\Entity;

/**
 * Interface ScorableUserInterface
 * @package Mopa\Bundle\ScoringBundle\Entity
 */
interface ScorableInterface {
    /**
     * @param integer $score
     */
    public function addScore($score);

    /**
     * @return integer
     */
    public function getScore();

    /**
     * @return integer
     */
    public function resetScore();
}