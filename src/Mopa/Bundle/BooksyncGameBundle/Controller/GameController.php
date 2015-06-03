<?php
namespace Mopa\Bundle\BooksyncGameBundle\Controller;

use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations\Get;
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
     * @return array
     */
    public function statusAction() {
        /** @var EntityManager $em */
        $em = $this->getDoctrine();
        $invitations = $em->getRepository('MopaBooksyncUserBundle:Invitation')
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

    /**
     * @Get(path="/overview")
     * @Template()
     *
     *
     * @param Request $request
     * @return array
     */
    public function overviewAction(Request $request) {
        $paginator = $this->get('knp_paginator');
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $users = $em->getRepository('MopaBooksyncUserBundle:User')
            ->createQueryBuilder('u')
            ->orderBy('u.score', 'DESC')
        ;

        $pagination = $paginator->paginate($users);

        return array(
            'request' => $request,
            'pagination'=>  $pagination
        );
    }
}