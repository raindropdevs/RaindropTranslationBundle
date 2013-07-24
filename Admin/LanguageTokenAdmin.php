<?php

namespace Raindrop\TranslationBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\DependencyInjection\Container;
use Raindrop\TranslationBundle\Transformer\LanguageToLocaleTransformer;

class LanguageTokenAdmin extends Admin
{
    protected $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {

        $formMapper
            ->add('token', null, array('required' => true))
            ->with('Translations')
            ->add('translations', 'sonata_type_collection', array(
                'required' => false,
                'by_reference' => false,
                'label' => 'Translations'
            ), array(
                'edit' => 'inline',
                'inline' => 'table'
            ))
        ;

        /**
         * There is need to show the locale for each translation but
         * not to edit, so we add a transformer that simply puts language object
         * as token property on form submission not to break doctrine cascade
         * saving operations.
         */
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $transformer = new LanguageToLocaleTransformer($em);
        $formMapper->get('translations')->addModelTransformer($transformer);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('token')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('token')
        ;
    }
}
