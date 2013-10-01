<?php

namespace Raindrop\TranslationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Raindrop\TranslationBundle\Entity\LanguageTranslationRepository")
 * @ORM\Table(name="i18n_language_translation")
 */
class LanguageTranslation
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\column(type="string", length=200)
     */
    private $catalogue = 'messages';

    /**
     * @ORM\column(type="text", nullable=true)
     */
    private $translation;

    /**
     * @ORM\ManyToOne(targetEntity="Raindrop\TranslationBundle\Entity\Language")
     */
    private $language;

    /**
     * @ORM\ManyToOne(targetEntity="Raindrop\TranslationBundle\Entity\LanguageToken", inversedBy="translations")
     */
    private $languageToken;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getCatalogue()
    {
        return $this->catalogue;
    }

    public function setCatalogue($catalogue)
    {
        $this->catalogue = $catalogue;
    }

    public function getTranslation()
    {
        return $this->translation;
    }

    public function setTranslation($translation)
    {
        $this->translation = $translation;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getLanguageToken()
    {
        return $this->languageToken;
    }

    public function setLanguageToken($languageToken)
    {
        $this->languageToken = $languageToken;
    }

    public function __toString()
    {
        return (string) $this->languageToken;
    }
}
