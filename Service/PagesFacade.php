<?php

namespace Melp\PagesBundle\Service;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\DocumentRepository;
use Melp\PagesBundle\Document;

class PagesFacade
{
    public $dm;
    public $repo;

    protected $documentClass;

    public function __construct(\Doctrine\ODM\PHPCR\DocumentManager $dm, $documentClass, $root, $default)
    {
        $this->dm = $dm;
        $this->root = '/' . trim($root, '/') . '/';
        $this->default = $default;

        $this->repo = $dm->getRepository($documentClass);
        $this->documentClass = $documentClass;
    }


    function createDefault($title = "Homepage")
    {
        $page = new $this->documentClass;

        $page->path = $this->normalize($this->default);
        $page->title = $title;

        $this->persist($page);

        return $page;
    }


    function removePath($path)
    {
        $normalized = $this->normalize($path);
        if ($normalized == $this->root) {
            throw new \InvalidArgumentException("can not delete root node");
        }
        $node = $this->find($normalized);
        if (!$node) {
            return false;
        }
        $this->remove($node);
        return true;
    }


    function findPath($path)
    {
        return $this->find($this->normalize($path));
    }


    function remove($node)
    {
        $this->dm->remove($node);
        $this->dm->flush();
    }


    function persist($node)
    {
        if (substr($node->path, 0, strlen($this->root)) != $this->root) {
            $node->path = $this->normalize($node->path);
        }
        $this->dm->persist($node);
        $this->dm->flush();
    }


    protected function find($path)
    {
        return $this->repo->find($path);
    }


    protected function normalize($path)
    {
        return $this->root . trim($path, '/');
    }
}