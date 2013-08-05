<?php
namespace Mopa\Bundle\BooksyncBundle\Controller;


use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

use JMS\SerializerBundle\Serializer\LazyLoadingSerializer;

use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\Serializer\Annotation as Serializer;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class RestBookmarksController extends BookmarkController
{
    /**
     * List Bookmarks
     *
     * @param string $username Username
     * @QueryParam(name="limit", requirements="\d+", default="10", description="List limit")
     * @QueryParam(name="offset", requirements="\d+", default="0", description="List offset")
     * @View
     * @Secure(roles="IS_AUTHENTICATED_REMEMBERED")
     * @ApiDoc(resource=true)
     */
    public function getBookmarksAction()
    {
        $filters = array();
        if ($tags = $this->getRequest()->query->get('tags', false)) {
            $tags = explode(',', $tags);
        }

        if(is_array($tags)){
            $filters = array('t.title' => $tags);
        }
        return $this->getEntities('Bookmark', null, null, null, array(), $filters);
    }
    /**
     * List Bookmarks
     *
     * @param string $username Username
     * @QueryParam(name="limit", requirements="\d+", default="10", description="List limit")
     * @QueryParam(name="offset", requirements="\d+", default="0", description="List offset")
     * @View
     * @Secure(roles="IS_AUTHENTICATED_REMEMBERED")
     * @Serializer\Groups({"bookmarklist"})
     * @ApiDoc(resource=true)
     */
    public function getBookmarksFollowingAction()
    {
        $qb = $this->getRepository('Bookmark')
            ->getFollowingQueryBuilder($this->getUser())
        ;

        $followingQuery = $this->getDecoratedQuery($qb);

        //We can do this to check what driver is currently used:
        //print_r($followingQuery->getResultCacheDriver());exit;
        //Or just enable our result caching

        // TODO: add events to invalidate cached things then we can make use of this ...

        //$followingQuery->useResultCache(true, 360, 'followedBookmarks.'.$this->getUser()->getId());
        $result = $followingQuery->getResult();

        return $result;
    }
}
