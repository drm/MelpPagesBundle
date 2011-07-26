<?php
/**
 * @author Gerard van Helden <drm@melp.nl>
 */

namespace Melp\PagesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use \Melp\PagesBundle\Document\Page;
use \Melp\PagesBundle\Form\Type\PageType;

class PageController extends Controller
{
    /**
     * @Route("/")
     */
    function indexAction()
    {
        return $this->redirect($this->generateUrl('view', array('path' => $this->get('pages')->default)));
    }


    /**
     * @Route("/{path}", requirements={"path" = "(?!(edit|delete|create)).+"}, name="view")
     * @Template
     */
    function viewAction($path)
    {
        $node = $this->get('pages')->findPath($path);

        if (!$node) {
            if ($path != $this->get('pages')->default) {
                throw $this->createNotFoundException("Page not found at {$path}");
            } else {
                $node = $this->get('pages')->createDefault();
                $this->_flash("Homepage was created");
            }
        }
        return array(
            'node' => (array)$node,
            'edit' => $this->generateUrl('edit', array('path' => $path)),
            'delete' => $this->generateUrl('delete', array('path' => $path)),
            'create' => $this->generateUrl('create', array('parentPath' => $path))
        );
    }


    /**
     * @Route("/edit/{path}", name="edit", requirements={"path"=".+"})
     * @Template
     */
    function editAction($path)
    {
        $node = $this->get('pages')->findPath($path);

        if (!$node) {
            throw $this->createNotFoundException("Page at path {$path} does not exist.");
        }

        $form = $this->createForm(new PageType(), $node);

        if ($this->getRequest()->getMethod() == 'POST') {
            $form->bindRequest($this->getRequest());

            if ($form->isValid()) {
                $this->get('pages')->persist($node);
            }

            $this->_flash('Changes saved');
            return $this->redirect($this->generateUrl('view', array('path' => trim($path, '/'))));
        }

        return array(
            'form' => $form->createView(),
            'action' => $this->generateUrl('edit', array('path' => trim($path, '/')))
        );
    }

    /**
     * @Route("/create/{parentPath}", requirements={"parentPath" = ".*"}, name="create")
     * @Template
     */
    function createAction($parentPath)
    {
        $pages = $this->get('pages');

        $node = new Page;

        $form = $this->createForm(new PageType(), $node);
        if ($this->getRequest()->getMethod() == 'POST') {
            $form->bindRequest($this->getRequest());

            // conjure a path based on the document title.
            $path = $parentPath . '/' . preg_replace('/[^0-9a-z-]+/', '-', strtolower($node->title));
            $node->path = $path;

            if ($form->isValid()) {
                try {
                    $pages->persist($node);
                } catch (\Exception $e) {
                    $this->_flash("Error while saving page: " . $e->getMessage());
                }

                if (!isset($e)) {
                    $this->_flash('Page created');
                    return $this->redirect($this->generateUrl('view', array('path' => trim($path, '/'))));
                }
            }
        }

        return array(
            'form' => $form->createView(),
            'action' => $this->generateUrl('create', array('parentPath' => $parentPath))
        );
    }


    /**
     * @Route("/delete/{path}", requirements={"path" = ".+"}, name="delete")
     */
    function deleteAction($path)
    {
        if ($this->get('pages')->removePath($path)) {
            $this->_flash('Page at ' . $path . ' was deleted');
            return $this->redirect($this->generateUrl('view', array('path' => dirname($path))));
        } else {
            $this->_flash('Can not delete root node of site');
            return $this->redirect($this->generateUrl('view', array('path' => $path)));
        }
    }


    /**
     * Helper method to store a flash message in the session
     *
     * @param $message
     * @return void
     */
    private function _flash($message)
    {
        $this->getRequest()->getSession()->setFlash('pages', $message);
    }
}