<?php
namespace Mopa\Bundle\BooksyncBundle\Controller;


use Mopa\Bundle\BooksyncBundle\Form\FolderType;
use Mopa\Bundle\BooksyncBundle\Entity\Bookmark;

abstract class FolderController extends BaseEntityController
{

    protected function getFormType(){
        return new FolderType();
    }
    protected function getFolders() {
        return $this->getEntities('Bookmark', $this->getUser()->getUsername());
    }

    protected function postFolder($successRoute, $successRouteParameters = array())
    {
        return $this->postEntity($successRoute, $successRouteParameters);
    }
    protected function putFolder($slug, $successRoute, $successRouteParameters = array())
    {
        $foldertype = new FolderType();
        $foldertype->setUser($this->getUser());
        return $this->putEntity('Bookmark', $slug, $foldertype, $successRoute, $successRouteParameters);
    }

}