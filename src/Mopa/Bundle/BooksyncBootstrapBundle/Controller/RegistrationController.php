<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mopa\Bundle\BooksyncBootstrapBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Router;

use Symfony\Component\HttpFoundation\Session\Session;

use Mopa\Bundle\BooksyncBundle\Entity\Invitation;

use Mopa\Bundle\BooksyncBootstrapBundle\Form\Type\InvitationRegisterFormType;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Controller\RegistrationController as BaseController;

/**
 * Controller managing the registration
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class RegistrationController extends BaseController
{
    public function registerAction()
    {
        $form = $this->container->get('fos_user.registration.form');
        $formHandler = $this->container->get('fos_user.registration.form.handler');
        $confirmationEnabled = $this->container->getParameter('fos_user.registration.confirmation.enabled');

        $process = $formHandler->process($confirmationEnabled);
        if ($process) {
            $user = $form->getData();

            if ($confirmationEnabled) {
                $this->container->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());
                $route = 'fos_user_registration_check_email';
            } else {
                $this->authenticateUser($user);
                $route = 'fos_user_registration_confirmed';
            }

            $this->setFlash('fos_user_success', 'registration.flash.user_created');
            $url = $this->container->get('router')->generate($route);

            return new RedirectResponse($url);
        }

        $invitation = new Invitation();
        $form_invitation = $this->container->get('form.factory')->create(new InvitationRegisterFormType(), $invitation);

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:register.html.'.$this->getEngine(), array(
            'form' => $form->createView(),
            'form_invitation' => $form_invitation->createView(),
        ));
    }
    public function invitationAction(Request $request)
    {
        $invitation = new Invitation();
        $form = $this->container->get('form.factory')->create(new InvitationRegisterFormType(), $invitation);
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->container->get('doctrine.orm.entity_manager');
                $em->persist($invitation);
                try{
                    $em->flush();
                    $this->container->get('session')->setFlash('success', 'invitation.successful');
                }
                catch(\PDOException $e){
                    $this->container->get('session')->setFlash('error', 'invitation.unsuccessful');
                }
                return new RedirectResponse($this->container->get('router')->generate('fos_user_registration_register'));
            }
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:register.html.'.$this->getEngine(), array(
            'form_invitation' => $form->createView(),
        ));
    }

}
