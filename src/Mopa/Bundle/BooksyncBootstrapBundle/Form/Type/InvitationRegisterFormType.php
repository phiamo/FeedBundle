<?php

namespace Mopa\Bundle\BooksyncBootstrapBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;
use Mopa\Bundle\BooksyncBootstrapBundle\Form\DataTransformer\InvitationToCodeTransformer;

class InvitationRegisterFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->setAttribute('render_fieldset', false)
            ->add('email', null, array(
                'widget_control_group' => false,
                'widget_controls' => false
            ))
        ;
    }

    public function getDefaultOptions()
    {
        return array(
                'class' => 'Mopa\Bundle\BooksyncBundle\Entity\Invitation',
                'required' => true,
        );
    }

    public function getName()
    {
        return 'invitation_registration';
    }
}