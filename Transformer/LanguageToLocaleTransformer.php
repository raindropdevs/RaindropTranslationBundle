<?php

namespace Raindrop\TranslationBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Description of LocaleToModelTransformer
 *
 * @author teito
 */
class LanguageToLocaleTransformer implements DataTransformerInterface
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $om
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Transforms language to locale
     * (does nothing at the moment)
     *
     * @param  Language|null $language
     * @return string
     */
    public function transform($language)
    {
        if (null === $language) {
            return "";
        }

        return $language;
    }

    /**
     * @param  $collection
     *
     * @return Language|null
     *
     * @throws TransformationFailedException if object (language) is not found.
     */
    public function reverseTransform($collection)
    {
        if (!$collection) {
            return null;
        }

        foreach ($collection->getIterator() as $translation) {
            $language = $this->em
                ->getRepository('RaindropTranslationBundle:Language')
                ->findOneBy(array('locale' => $translation->getLanguage()))
            ;

            $translation->setLanguage($language);
        }

        return $collection;
    }
}

?>
