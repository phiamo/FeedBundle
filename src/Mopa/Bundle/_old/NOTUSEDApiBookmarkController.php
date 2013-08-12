<?php
/****
 *
 * No need for this we are not quering bookmarks via api snywhere
 *
 *
 *

namespace Mopa\Bundle\BooksyncBundle\Controller;

use Symfony\Component\HttpFoundation\Request,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Mopa\Bundle\BooksyncBundle\Entity\Bookmark,
    Mopa\Bundle\BooksyncBundle\Form\Type\BookmarkType;

use JMS\SecurityExtraBundle\Annotation\SecureParam;
use FOS\RestBundle\Request\ParamFetcher;

/**
 * @NamePrefix("mopa_booksync_api_")
 * Following annotation is redundant, since FosRestController implements ClassResourceInterface
 * so the Controller name is used to define the resource. However with this annotation its
 * possible to set the resource to something else unrelated to the Controller name
 * @RouteResource("Bookmark")
 */
class ApiBookmarkController extends BaseEntityController
{
    /**
     * Get the bookmark
     *
     * @param string $bookmark path
     * @return View view instance
     *
     * @View()
     * @ApiDoc()
     * @ParamConverter("bookmark", options={"mapping": {"bookmark": "slug"}})
     * @SecureParam(name="bookmark", permissions="VIEW")
     */
    public function getAction(Bookmark $bookmark)
    {
        return $bookmark;
    }
    /**
     * Get the bookmarks
     *
     * @param string $bookmark path
     * @return View view instance
     *
     * @View()
     * @ApiDoc()
     * @ParamConverter("bookmark", options={"mapping": {"bookmark": "slug"}})
     * @QueryParam(name="limit", requirements="\d+", default="10", description="List limit")
     * @QueryParam(name="offset", requirements="\d+", default="0", description="List offset")
     * @QueryParam(name="order", requirements="(createdAt)|(updatedAt)|(id)", default="updatedAt", description="Order by")
     * @QueryParam(name="dir", requirements="(asc)|(desc)", default="asc", description="Order Direction")
     */
    public function cgetAction()
    {
        $qb = $this->getRepository('Bookmark')->getAllQueryBuilder();
        $qb = $this->applyLimitAndOffsetToQueryBuilder($qb);
        $qb = $this->applySortAndOrderToQueryBuilder($qb);
        $query = $qb->getQuery();
        return $query->getResult();
    }

}
