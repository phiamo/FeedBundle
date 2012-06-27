<?php

namespace Mopa\Bundle\BooksyncBootstrapBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;

class ProfileFormType extends BaseType
{
    private $class;

    /**
     * @param string $class The User class name
     */
    public function __construct($class)
    {
        $this->class = $class;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('user');
        $child = $builder->create('user', 'form', array(
                'data_class' => $this->class,
                'widget_control_group' => false
            ))
        ;
        $this->buildUserForm($child, $options);
        $builder
            ->add($child)

        ;
    }

    /**
     * Builds the embedded form representing the user.
     *
     * @param FormBuilderInterface $builder
     * @param array       $options
     */
    protected function buildUserForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
            ->add('description', 'textarea', array(
                'required' => false,
            ))
        ;
    }
    public function getName()
    {
        return 'mopa_booksync_profile';
    }
}
