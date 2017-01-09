<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Form;

use Symfony\Component\Form\AbstractType;

/**
 * Class FilterRuleType
 * @package Mopa\Bundle\FeedBundle\Form
 */
class FilterRuleType extends AbstractType
{
    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'filter_rule';
    }
}
