<?php
namespace Mopa\Bundle\BooksyncBootstrapBundle\Invitation;
use Mopa\Bundle\BooksyncBundle\Entity\Invitation;
use Symfony\Component\HttpFoundation\Request;
use Mopa\Bundle\BooksyncBootstrapBundle\Form\Type\InvitationRegisterFormType;
use Doctrine\DBAL\DBALException;


class InvitationService
{
    protected $form_factory;
    /**
     * TODO: TYPE THEM
     * @param unknown $form_factory
     * @param unknown $em
     */
    public function __construct($form_factory, $em) {
        $this->form_factory = $form_factory;
        $this->em = $em;
    }
    public function getForm(){
        $invitation = new Invitation();
        return $this->form_factory->create(new InvitationRegisterFormType(), $invitation);
    }
    public function processForm(Request $request){
        $invitation = new Invitation();
        $form = $this->getForm();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $this->em->persist($invitation);
                try{
                    $this->em->flush();
                    return true;
                }
                catch(\PDOException $e){
                    return false;
                }
                catch(DBALException $e){
                    return false;
                }
            }
        }
    }
}
