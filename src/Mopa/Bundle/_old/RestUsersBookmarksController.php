<?php
namespace Mopa\Bundle\BooksyncBundle\Controller;

use Mopa\Bundle\BooksyncBundle\Form\BookmarkType;
use Mopa\Bundle\BooksyncBundle\Entity\Bookmark;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 *
 * @author phiamo
 */
class RestUsersBookmarksController extends BookmarkController
{
    /**
     * List a Users Bookmarks
     *
     * @param string $username Username
     * @QueryParam(name="limit", requirements="\d+", default="10", description="List limit")
     * @QueryParam(name="offset", requirements="\d+", default="0", description="List offset")
     * @View
     * @Secure(roles="IS_AUTHENTICATED_REMEMBERED")
     * @ApiDoc(resource=true)
     */
    public function getBookmarksAction($username)
    {
        return $this->getEntities('Bookmark', $username);
    }
    /**
     * Get a Users Bookmark
     *
     * @param string $username Username
     * @param string $slug The Bookmark slug
     * @View
     * @Secure(roles="IS_AUTHENTICATED_REMEMBERED")
     * @ApiDoc(resource=true)
     */
    public function getBookmarkAction($username, $slug)
    {
        return $this->getEntity('Bookmark', $username, $slug);
    }
    /**
     * Create a new Bookmark
     *
     * @param string $username Username
     * @View()
     * @PreAuthorize("user.getUsername() == #username")
     * @ApiDoc(
     *     formType="Mopa\Bundle\BooksyncBundle\Form\BookmarkType"
     * )
     */
    public function postBookmarksAction($username)
    {
        return $this->postBookmark("mopa_booksync_api_get_user_bookmark", array('username'=>$username));
    }
    /**
     * Change an Users Bookmark
     *
     * @param string $username Username
     * @param string $slug The Bookmark slug
     * @PreAuthorize("user.getUsername() == #username")
     * @ApiDoc(
     *     formType="Mopa\Bundle\BooksyncBundle\Form\BookmarkType"
     * )
     */
    public function putBookmarkAction($username, $slug)
    {
        return $this->putBookmark($slug, "mopa_booksync_api_get_user_bookmark", array('username'=>$username));
    }
    /**
     * Delete an Users Bookmark
     *
     * @param string $username Username
     * @param string $slug The Bookmark slug
     * @PreAuthorize("user.getUsername() == #username")
     * @ApiDoc()
     */
    public function deleteBookmarkAction($username, $slug)
    {
        $this->deleteEntity('Bookmark', $slug);
    }
    /**
     * Move an Users Bookmark
     *
     * @param string $username Username
     * @param string $slug The Bookmark slug
     * @PreAuthorize("user.getUsername() == #username")
     * @ApiDoc()
     */
    public function moveBookmarkAction($username, $slug)
    {
        return $this->forward('MopaBooksyncBundle:RestUsersFolders:moveFolder', array('username'=>$username, 'slug' => $slug));
        //$this->moveBookmark($username, $slug);
    }
}
