<?php
namespace Mopa\Bundle\BooksyncGameBundle\Command;

use Doctrine\ORM\EntityManager;
use Mopa\Bundle\BooksyncBundle\Entity\BookmarkRecommendation;
use Mopa\Bundle\BooksyncGameBundle\Entity\InvitationAction;
use Mopa\Bundle\BooksyncGameBundle\Entity\RecommendationAction;
use Mopa\Bundle\BooksyncUserBundle\Entity\Invitation;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
            ->setName('mopa:booksync:scoring:compute')
        ;
    }

    /**
    * {@inheritDoc}
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        /*

        $scoreInvitations = $em->getRepository('MopaBooksyncGameBundle:ScoreInvitation')->findAll();
        foreach($scoreInvitations as $scoreInvitation) {
            $em->remove($scoreInvitation);
        }

        $em->flush();
        */

        $sm = $this->getContainer()->get('mopa_scoring.score_manager');
        $invitations = $em->getRepository('MopaBooksyncUserBundle:Invitation')->findAll();

        /** @var Invitation $invitation */
        foreach($invitations as $invitation) {
            if($invitation->getRecommendor()) {
                $ia = new InvitationAction($invitation->getRecommendor());
                $sm->score($ia);
            }
        }

        $em->flush();
        $sm = $this->getContainer()->get('mopa_scoring.score_manager');
        $invitations = $em->getRepository('MopaBooksyncBundle:BookmarkRecommendation')->findAll();

        /** @var BookmarkRecommendation $recommendation */
        foreach($invitations as $recommendation) {
            $ia = new RecommendationAction($recommendation->getEmitter());
            $sm->score($ia);
        }

        $em->flush();
    }
}
