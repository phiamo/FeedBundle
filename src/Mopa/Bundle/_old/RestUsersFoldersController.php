<?php
namespace Mopa\Bundle\BooksyncBundle\Controller;

use Mopa\Bundle\BooksyncBundle\Entity\Bookmark,
    Mopa\Bundle\BooksyncBundle\Form\FolderType,
    Mopa\Bundle\BooksyncBundle\Entity\Folder;
use FOS\RestBundle\Request\ParamFetcher,
    FOS\RestBundle\View\View as FOSView,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam;
use JMS\SecurityExtraBundle\Annotation\Secure,
    JMS\SecurityExtraBundle\Annotation\PreAuthorize,
    JMS\SecurityExtraBundle\Security\Authorization\Expression\Expression;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Security\Core\SecurityContext,
    Symfony\Component\HttpFoundation\Request;


/**
 *
 * @author phiamo
 */
class RestUsersFoldersController extends FolderController
{
    /**
     * List a Users Folders
     *
     * @param string $username Username
     * @QueryParam(name="limit", requirements="\d+", default="10", description="List limit")
     * @QueryParam(name="offset", requirements="\d+", default="0", description="List offset")
     * @View
     * @Secure(roles="IS_AUTHENTICATED_FULLY")token
     * @ApiDoc(resource=true)
     */
    public function getFoldersAction($username)
    {
            $qb = $this->getRepository('Bookmark')
            ->getByOwnerQueryBuilder($this->getUser())
            ->andWhere('b.isFolder = true')
        ;
        $query = $this->getDecoratedQuery($qb);
        return $query->getResult();
    }
    /**
     * Get a Users Folder
     *
     * @param string $username Username
     * @param string $slug The Folder slug
     * @View
     * @Secure(roles="IS_AUTHENTICATED_FULLY")
     * @ApiDoc(resource=true)
     */
    public function getFolderAction($username, $slug)
    {
        $qb = $this->getRepository('Bookmark')
            ->getByOwnerQueryBuilder($this->getUser(), 'b')
            ->andWhere('b.isFolder = true')
            ->andWhere('b.slug = :slug')
            ->setParameter('slug', $slug)
        ;
        $query = $this->getDecoratedQuery($qb);
        return $query->getResult();
    }
    /**
     * Create a new Folder
     *
     * @param string $username Username
     * @PreAuthorize("token.getUsername() == #username")
     * @ApiDoc(
     *     formType="Mopa\Bundle\BooksyncBundle\Form\FolderType"
     * )
     */
    public function postFoldersAction(Request $request, $username)
    {
        $handler = $this->get('fos_rest.view_handler');
        $view = FOSView::create();

        $form = $this->getForm(null, $handler->isFormatTemplating($view->getFormat()));

        $form->bind($request);

        if ($form->isValid()) {
            // Note: normally one would likely create/update something in the database
            // and/or send an email and finally redirect to the newly created or updated resource url
            $this->processNewEntity($form->getData());
            $view->setData(array('username'=>$username))
                 ->setRoute("mopa_booksync_api_get_user_folder");
        } else {
            $view->setData($form);
            $view->setTemplate('MopaBooksyncBundle:RestUsersBookmarks:postBookmarks.html.twig');
        }

        return $this->get('fos_rest.view_handler')->handle($view);
        /*
         * oldstyle
         *

        $folder->setIsFolder(true);
        $foldertype = new FolderType();
        $foldertype->setUser($this->getUser());

        $form = $this->createForm($entityForm, $entity, array('csrf_protection' => !$this->getRequest()->isXmlHttpRequest()));
        $viewData = array_merge(array('form' => $form), $viewVariables);

        $form->bind($this->getRequest());

        if ($form->isValid()) {
            try{
                if ($entity instanceof TaggableEntityInterface){
                    $em = $this->getManager();
                    $tags = $entity->getTags();
                    foreach($tags as $tag){
                        if(!$em->contains($tag)){
                            $managed = $this->getRepository('Tag')->findOneBy(array('title'=>$tag->getTitle()));
                            if($managed != null){
                                $entity->removeTag($tag);
                                $entity->addTag($managed);
                            }
                        }
                    }
                }
                $this->updateAclAware($entity);
                $successRouteParameters = array_merge($successRouteParameters, array("slug" => $entity->getSlug()));
                if (array_key_exists('success', $flashs)) {
                    $this->container->get('session')->setFlash('success', $flashs['success']);
                }

                // wofür redrect, macht nur probs bei jquery anfragen !!
                if($successRoute && !$this->getRequest()->isXmlHttpRequest()){ // wenn route vorhanden, und KEIN QUERY REQUEST
                    return RouteRedirectView::create($successRoute, $successRouteParameters, Codes::HTTP_OK);
                }
                else{ // keine ROUTE, ORDER JQUERY REquest
                    return $entity;
                }
                //hioer kommen wir eigentlich nie hin
                return FOSView::create($viewData, Codes::HTTP_OK);
            }
            catch (\PDOException $e) {
                if ($e->errorInfo[1] === 1062) {  // duplicate
                    $form->addError(new FormError($e->getMessage()));
                }
            }
            catch (\Exception $e) {
                throw $e;
            }
            return FOSView::create($viewData, Codes::HTTP_CONFLICT);
        }

        return FOSView::create($viewData, Codes::HTTP_BAD_REQUEST);
         *
         *
        $folder = new Bookmark($this->getUser());

        return $this->postFolder($folder, "mopa_booksync_api_get_user_folder", array('username'=>$username));
        */
    }
    /**
     * Change an Users Folder
     *
     * @param string $username Username
     * @param string $slug The Folder slug
     * @View(templateVar="form")
     * @PreAuthorize("user.getUsername() == #username")
     * @ApiDoc(
     *     formType="Mopa\Bundle\BooksyncBundle\Form\FolderType"
     * )
     */
    public function putFolderAction($username, $slug)
    {
        return $this->putFolder($slug, "mopa_booksync_api_get_user_folder", array('username'=>$username));
    }
    /**
     * Delete an Users Folder
     *
     * @param string $username Username
     * @param string $slug The Folder slug
     * @PreAuthorize("user.getUsername() == #username")
     * @ApiDoc()
     */
    public function deleteFolderAction($username, $slug)
    {
        $this->deleteEntity('Bookmark', $slug);
    }
    /**
     * Move an Users Folder
     *
     * @param string $username Username
     * @param string $slug The Folder slug
     * @PreAuthorize("user.getUsername() == #username")
     * @ApiDoc()
     */
    public function moveFolderAction($username, $slug)
    {
        return $this->moveFolder($username, $slug);
    }
}
