<?php
/**
 * @author Gerard van Helden <drm@melp.nl>
 */

namespace Melp\PagesBundle\Form\Type;

use \Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilder;

class PageType extends AbstractType
{
    function buildForm(FormBuilder $builder, array $options)
    {
        $builder
                ->add('title', 'text', array('required' => true))
                ->add('content', 'textarea')
        ;
    }
}