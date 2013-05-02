<?php

namespace Raindrop\TranslationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="YourApp\YourBundle\Repository\LanguageTokenRepository")
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
}