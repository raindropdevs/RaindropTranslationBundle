<?php

namespace Raindrop\TranslationBundle\Entity;

use Doctrine\ORM\EntityRepository;

class LanguageTranslationRepository extends EntityRepository {

    /**
     * Return all translations for specified token
     * @param type $token
     * @param type $domain
     */
    public function getTranslations($language, $catalogue = "messages"){
        $query = $this->getEntityManager()->createQuery("SELECT t FROM RaindropTranslationBundle:LanguageTranslation t WHERE t.language = :language AND t.catalogue = :catalogue");
        $query->setParameter("language", $language);
        $query->setParameter("catalogue", $catalogue);

        return $query->getResult();
    }
}
