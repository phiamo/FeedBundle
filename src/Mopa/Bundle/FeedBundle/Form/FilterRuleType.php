<?php

namespace Mopa\Bundle\FeedBundle\Form;


use Symfony\Component\Form\AbstractType;

class FilterRuleType extends AbstractType {

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'filter_role';
    }
}