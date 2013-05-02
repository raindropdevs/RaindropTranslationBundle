<?php

namespace Raindrop\TranslationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Raindrop\TranslationBundle\Entity\LanguageTokenRepository")
 * @ORM\Table(name="i18n_language_token")
 */
class LanguageToken {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\column(type="string", length=200)
     */
    private $token;

    /**
     * @ORM\OneToMany(targetEntity="Raindrop\TranslationBundle\Entity\LanguageTranslation", mappedBy="languageToken", fetch="EAGER", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
     */
    private $translations;


    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getToken() {
        return $this->token;
    }

    public function setToken($token) {
        $this->token = $token;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add translations
     *
     * @param \Raindrop\TranslationBundle\Entity\LanguageTranslation $translations
     * @return LanguageToken
     */
    public function addTranslation(\Raindrop\TranslationBundle\Entity\LanguageTranslation $translations)
    {
        $this->translations[] = $translations;

        return $this;
    }

    /**
     * Remove translations
     *
     * @param \Raindrop\TranslationBundle\Entity\LanguageTranslation $translations
     */
    public function removeTranslation(\Raindrop\TranslationBundle\Entity\LanguageTranslation $translations)
    {
        $this->translations->removeElement($translations);
    }

    /**
     * Get translations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    public function __toString() {
        return (string) $this->getToken();
    }
}