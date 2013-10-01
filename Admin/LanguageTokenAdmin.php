<?php

namespace Raindrop\TranslationBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\DependencyInjection\Container;

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
            ));
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

    public function prePersist($languageToken)
    {
        foreach ($languageToken->getTranslations() as $translation) {
            $translation->setLanguageToken($languageToken);
        }
    }

    public function preUpdate($languageToken)
    {
        foreach ($languageToken->getTranslations() as $translation) {
            $translation->setLanguageToken($languageToken);
        }

        $languageToken->setTranslations($languageToken->getTranslations());
    }
}
