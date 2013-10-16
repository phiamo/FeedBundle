<?php
namespace Mopa\Bundle\BooksyncBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\View;
use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


class RestUsersFollowController extends BaseController
{
    /**
     * @View(templateVar="form")
     * @PreAuthorize("isAuthenticated() and user.getUsername() == #username")
     */
    public function getFollowingAction($username)
    {
        $user = $this->getRepository('User', 'MopaBooksyncUserBundle')
            ->createQueryBuilder('u')
            ->select('u')
            ->andWhere('u = :user')
            ->setParameter('user', $this->getUser())
            ->leftJoin('u.followingUsers', 'f')
            ->getQuery()
            ->getSingleResult()
        ;
        $this->get('serializer')->setGroups(array('follow'));
        return $user->getFollowingUsers();
    }
    /**
     * @param string $username Username
     * @View(templateVar="form")
     * @Secure(roles="IS_AUTHENTICATED_FULLY")
     */
    public function getFollowersAction($username)
    {
        $toFollow = $this->getRepository('User', 'MopaBooksyncUserBundle')
            ->createQueryBuilder('u')
            ->andwhere('u.username = :username')
            ->leftJoin('u.followers', 'f')
            ->setParameter('username', $username)
            ->getQuery()
            ->getSingleResult()
        ;
        $this->get('serializer')->setGroups(array('follow'));
        return $toFollow->getFollowers();
    }
    /**
     * Marks the user issueing the Request, as a Follower of $username
     *
     * @param string $username Username
     * @View(templateVar="form")
     * @Secure(roles="IS_AUTHENTICATED_FULLY")
     * @ApiDoc()
     */
    public function patchFollowAction($username)
    {
        $toFollow = $this->getRepository('User', 'MopaBooksyncUserBundle')
            ->createQueryBuilder('u')
            ->andwhere('u.username = :username')
            ->setParameter('username', $username)
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
        $qb = $this->getRepository('Bookmark')
            ->getFollowingQueryBuilder($this->getUser())
        ;
        //Using event system to tell doctrine deleting cache entries based on events like this

        $followingQuery = $this->getDecoratedQuery($qb);
        $followingQuery->getQueryCacheDriver()->delete('followedBookmarks.'.$this->getUser()->getId());
        */
        $this->getUserManager()->updateUser($this->getUser());
        return array('following' => $toFollow->getFollowers()->contains($this->getUser()));
    }
}
