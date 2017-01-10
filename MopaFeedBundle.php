<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle;

use Mopa\Bundle\FeedBundle\DependencyInjection\CompilerPass\P2CompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class MopaFeedBundle
 * @package Mopa\Bundle\FeedBundle
 */
class MopaFeedBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new P2CompilerPass());
    }
}
