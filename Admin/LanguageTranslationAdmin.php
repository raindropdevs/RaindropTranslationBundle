<?php

namespace Raindrop\TranslationBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\DependencyInjection\Container;
use Raindrop\TranslationBundle\Entity\Language;

class LanguageTranslationAdmin extends Admin
{
    protected $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $data = '';
        if ($this->getSubject()->getLanguage() instanceof Language) {
            $data = $this->getSubject()->getLanguage()->getLocale();
        }

        $formMapper
            ->add('language', 'text', array(
                'read_only' => true,
                'property_path' => false,
                'data' => $data,
                'attr' => array(
                    'class' => 'span2'
                )
            ))
            ->add('translation', 'textarea', array('required' => true))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('language')
            ->add('translation')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('language')
            ->addIdentifier('languageToken')
            ->addIdentifier('translation')
        ;
    }

    public function preUpdate($variable)
    {
    }

    public function postUpdate($variable)
    {
    }
}
