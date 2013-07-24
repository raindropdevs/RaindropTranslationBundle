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

        $formMapper
            ->add('language', 'text', array(
                'read_only' => true,
                'data_class' => 'Raindrop\TranslationBundle\Entity\Language'
            ))
            ->add('translation')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('language')
            ->add('languageToken', 'doctrine_orm_callback', array(
                'callback' => array($this, 'callbackToken')
            ), 'text', array())
            ->add('translation')
        ;
    }

    public function callbackToken($queryBuilder, $alias, $field, $value)
    {
        if (!is_array($value) or empty($value['value'])) {
            return;
        }

        $queryBuilder
                ->leftJoin( $alias . '.languageToken', 't')
                ->andWhere('t.token LIKE :token')
                ->setParameter('token', '%' . $value['value'] . '%')
                ;

        return true;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('language')
            ->add('languageToken')
            ->add('translation')
        ;
    }

    public function preUpdate($translation)
    {
        var_dump($translation);
        die();
    }

    public function postUpdate($translation)
    {
    }
}
