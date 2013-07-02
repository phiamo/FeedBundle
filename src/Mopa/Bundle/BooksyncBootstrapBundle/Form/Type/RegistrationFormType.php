<?php
namespace Mopa\Bundle\BooksyncBootstrapBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseRegistrationFormType;
use Doctrine\ORM\EntityRepository;

class RegistrationFormType extends BaseRegistrationFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('invitation', 'mopa_booksync_invitation_type');
    }
    public function getName()
    {
        return 'mopa_booksync_user_registration';
    }
}