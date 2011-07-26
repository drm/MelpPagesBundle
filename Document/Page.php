<?php
/**
 * @author Gerard van Helden <drm@melp.nl>
 */

namespace Melp\PagesBundle\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(alias="page")
 */
class Page
{
    /** @ODM\Id() */
    public $path;

    /** @ODM\String() */
    public $title;

    /** @ODM\String */
    public $content;


    function __construct($path = null)
    {
        if (!is_null($path)) {
            $this->path = $path;
        }
    }
}


