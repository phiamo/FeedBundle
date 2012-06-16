<?php
namespace Mopa\Bundle\RestTestBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Mopa\Bundle\BootstrapBundle\Navbar\NavbarFormInterface;

class RestTestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('url', 'text', array(
            'attr' => array(
                'placeholder' => 'The Rest API Url to test'
            )
        ))
        ->add('queryString', 'text', array(
            'attr' => array(
                'placeholder' => 'The Querystring to append',
            ),
            'required' => false
        ))
        ->add('method', 'choice', array(
            'choices' => array(
                'post'   => 'POST',
                'get'    => 'GET',
                'put'    => 'PUT',
                'delete' => 'DELETE',
                'patch'  => 'PATCH',
                'head'   => 'HEAD'
            )
        ))
        ->add('content', 'textarea', array(
            'required' => false
        ))
        ->add('deSerialize', 'text', array(
            'attr' => array(
                'placeholder' => 'JMS de-serilizer string',
            ),
            'required' => false
        ))
        ->add('authType', 'choice', array(
            'choices' => array(
                'none'  => 'No Auth',
                'wsse'  => 'WSSE',
                'BasicAuth' => 'BasicAuth'
            )
        ))
        ->add('username', 'text')
        ->add('password', 'text')
        ;
    }
    public function getName()
    {
        return 'mopa_rest_test';
    }
}

