<?php

namespace Mopa\Bundle\FeedBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('MopaFeedBundle:Default:index.html.twig', array('name' => $name));
    }
}
