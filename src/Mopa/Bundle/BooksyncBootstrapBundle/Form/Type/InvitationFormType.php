<?php

namespace Mopa\Bundle\BooksyncBootstrapBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;
use Mopa\Bundle\BooksyncBootstrapBundle\Form\DataTransformer\InvitationToCodeTransformer;

class InvitationFormType extends AbstractType
{
    protected $invitationTransformer;

    public function __construct(InvitationToCodeTransformer $invitationTransformer)
    {
        $this->invitationTransformer = $invitationTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->prependClientTransformer($this->invitationTransformer);
    }

    public function getDefaultOptions()
    {
        return array(
                'class' => 'Mopa\Bundle\BooksyncBundle\Entity\Invitation',
                'required' => true,
        );
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'invitation';
    }
}