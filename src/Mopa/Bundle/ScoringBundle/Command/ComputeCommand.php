<?php
namespace Mopa\Bundle\ScoringBundle\Command;

use Mopa\Bundle\BooksyncUserBundle\Entity\User;
use Mopa\Bundle\BooksyncUserBundle\Entity\UserSettings;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComputeCommand extends ContainerAwareCommand
{
    /**
    * {@inheritDoc}
    */
    protected function configure()
    {
        $this
            ->setDescription('Compute the actual scorings')
            ->setName('mopa:scoring:compute')
        ;
    }

    /**
    * {@inheritDoc}
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //find al ScorableInterface objects and clear score
    }
}
