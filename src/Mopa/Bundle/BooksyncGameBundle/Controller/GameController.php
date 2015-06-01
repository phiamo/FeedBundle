<?php
namespace Mopa\Bundle\BooksyncGameBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GameController
 * @package Mopa\Bundle\BooksyncGameBundle\Controller
 */
class GameController extends Controller {

    /**
     * @Template()
     * @param Request $request
     * @return array
     */
    public function statusAction(Request $request) {
        $invitations = $this->getDoctrine()->getRepository('MopaBooksyncUserBundle:Invitation')
            ->createQueryBuilder('i')
            ->select('i.id')
            ->leftJoin('i.recommendor', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $this->getUser()->getId())
            ->getQuery()
            ->getResult()
        ;
        return array(
            'invitations' => count($invitations)
        );
    }
}