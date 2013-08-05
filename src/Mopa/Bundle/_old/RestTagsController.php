<?php
namespace Mopa\Bundle\BooksyncBundle\Controller;


use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class RestTagsController extends BaseController
{
    /**
     * List a Users Tags
     *
     * @param string $username Username
     * @param string $_format Format to return
     * @QueryParam(name="limit", requirements="\d+", default="10", description="List limit")
     * @QueryParam(name="offset", requirements="\d+", default="0", description="List offset")
     * @View
     * @Secure(roles="IS_AUTHENTICATED_REMEMBERED")
     * @ApiDoc(
     *     resource=true
     * )
     */
    public function getTagsAction(){
        return $this->getEntities('Tag', false);
    }
    /**
     * @View
     */
    public function getTagAction($slug)
    {
        return null;
    }

}
