<?php
namespace Mopa\Bundle\BooksyncBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\View;
use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class RestTagsFollowController extends BaseController
{
    /**
     * @param string $username Username
     * @View(templateVar="form")
     * @Secure(roles="IS_AUTHENTICATED_FULLY")
     * @ApiDoc()
     */
    public function patchFollowAction($slug)
    {
        $toFollow = $this->getRepository('Tag')
            ->createQueryBuilder('t')
            ->andwhere('t.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getSingleResult()
        ;
        if($toFollow->getFollowers()->contains($this->getUser())){
            $toFollow->removeFollower($this->getUser());
        }
        else{
            $toFollow->addFollower($this->getUser());
        }
        /*
        //Using event system to tell doctrine deleting cache entries based on events like this
        $qb = $this->getRepository('Bookmark')
            ->getFollowingQueryBuilder($this->getUser())
        ;
        $config = $this->getManager()->getConfiguration(); //Get an instance of the configuration
        $queryCacheDriver = $config->getQueryCacheImpl(); //Gets Query Cache Driver
        $followingQuery->getQueryCacheDriver()->delete('followedBookmarks.'.$this->getUser()->getId());
        */
        $this->getUserManager()->updateUser($this->getUser());
        return array('following' => $toFollow->getFollowers()->contains($this->getUser()));
    }
}
