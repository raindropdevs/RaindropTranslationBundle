<?php

namespace Raindrop\TranslationBundle\Loader;

use Symfony\Component\Translation\Loader\LoaderInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\MessageCatalogue;

class DbLoader implements LoaderInterface
{
    private $translationRepository;
    private $languageRepository;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->translationRepository = $entityManager->getRepository("RaindropTranslationBundle:LanguageTranslation");
        $this->languageRepository = $entityManager->getRepository("RaindropTranslationBundle:Language");
    }

    //Load from the db for the specified local
    public function load($resource, $locale, $domain = 'messages')
    {
        $language = $this->languageRepository->findByLocale($locale);
        $translations = $this->translationRepository->getTranslations($language, $domain);
        $catalogue = new MessageCatalogue($locale);

        foreach ($translations as $translation) {
            $catalogue->set($translation->getLanguageToken()->getToken(), $translation->getTranslation(), $domain);
        }

        return $catalogue;
    }
}
