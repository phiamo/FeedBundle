<?php

namespace Mopa\Bundle\RestTestBundle\Controller;

use Buzz\Message\Response;

use Mopa\Bundle\WSSEAuthenticationBundle\Buzz\Listener\WSSEAuthenticationBuzzListener;

use Mopa\Bundle\RestTestBundle\Form\Type\RestTestFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/mopa/rest/test", name="mopa_rest_test")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $response = null;
        $form = $this->createForm(new RestTestFormType());
        $parameters = array();
        if ($request->getMethod() === 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $values = $form->getData();
                $headers = array();
                if ($values['authType']) {
                    if ($values['authType'] == 'wsse') {
                        $listener = new WSSEAuthenticationBuzzListener();
                        $listener->setCredentials($values['username'], $values['password']);
                        $this->container->get('buzz')->addListener($listener);
                    } elseif ($values['authType'] == 'basic') {
                        $headers[] = 'Authorization: Basic '.base64_encode($username.':'.$password);
                    }
                }
                if ($values['queryString']) {
                    $values['url'] .= "?" . $values['queryString'];
                }
                $response = $this->communicate($values['method'], $values['url'], $headers, $values['content']);
                if ($values['deSerialize']) {
                    $response->setContent($this->get('serializer')->deserialize($response->getContent(), $values['deSerialize'], 'json'));
                }
                if ($loction = $response->getHeader('Location')) {
                    $values['url'] = $loction;
                }
            }
        }
        return array("form" => $form->createView(), 'response' => $response);
    }
    /**
     *
     * @param unknown_type $method
     * @param unknown_type $path
     * @param unknown_type $headers
     * @param unknown_type $content
     * @throws FormException
     * @return Response
     */
    protected function communicate($method, $path, $headers = array(), $content = '') {
        // Store the bound data in case of a post request
        $this->container->get('buzz')->getClient()->setMaxredirects(0);
        switch (strtoupper($method)) {
            case 'POST':
            case 'PUT':
            case 'DELETE':
            case 'PATCH':
                parse_str($content, $content);
                break;
            case 'GET':
                break;
            default:
                throw new FormException(sprintf('The request method "%s" is not supported', $request->getMethod()));
        }
        if ($method == 'get') {
            $response = $this->container->get('buzz')->$method($path, $headers);
        } else {
            $response = $this->container->get('buzz')->submit($path, $content, strtoupper($method), $headers);
        }
        return $response;
    }
}
