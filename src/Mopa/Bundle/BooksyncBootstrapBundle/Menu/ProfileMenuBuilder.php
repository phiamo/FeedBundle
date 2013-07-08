<?php
namespace Mopa\Bundle\BooksyncBootstrapBundle\Menu;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Mopa\Bundle\BootstrapBundle\Navbar\AbstractNavbarMenuBuilder;

class ProfileMenuBuilder extends AbstractNavbarMenuBuilder{

    protected $security_context;
    /**
     *
     * @param FactoryInterface $factory
     * @param SecurityContextInterface $security_context
     */
    public function __construct(FactoryInterface $factory, SecurityContextInterface $security_context)
    {
        parent::__construct($factory);
        $this->security_context = $security_context;
    }


    public function getUser()
    {
        if (null === $token = $this->security_context->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }
    public function createMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav nav-list ');
        $menu->setChildrenAttribute('id', 'leftnav');
        if($this->getUser()){
            $menu->addChild('show_bookmark', array('route' => 'mopa_booksync_list_bookmarks'))
                ->setLabel('Bookmarks');
            $menu->addChild('oauth_list_clients', array('route' => 'mopa_booksync_oauth_list_clients',
                    "routeParameters" => array('user'=>$this->getUser()->getUsername())))
                ->setLabel('Oauth');
        }
        return $menu;
    }
}