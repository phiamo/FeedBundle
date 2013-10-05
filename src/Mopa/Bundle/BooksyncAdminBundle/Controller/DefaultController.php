<?php

namespace Mopa\Bundle\BooksyncAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('MopaBooksyncAdminBundle:Default:index.html.twig', array('name' => $name));
    }
}
