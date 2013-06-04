<?php

namespace Raindrop\TranslationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Raindrop\TranslationBundle\Entity\Language;
use Raindrop\TranslationBundle\Entity\LanguageToken;
use Raindrop\TranslationBundle\Entity\LanguageTranslation;

/**
 * ImportLanguageTranslation Command
 */
class ImportLanguageTranslationCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('raindrop:translation:import')
            ->setDefinition(array(
                new InputArgument('resource', InputArgument::REQUIRED, 'The csv file'),
                new InputArgument('catalogue', InputArgument::REQUIRED, 'The catalogue'),
                new InputOption(
                    'dump-messages', null, InputOption::VALUE_NONE,
                    'Should the translations be dumped in the console'
                ),
            ))
            ->setDescription('Extract translation from database')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command import translation strings stored in a csv file.

<info>php %command.full_name% --dump-messages</info>
EOF
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** the csv file */
        $resource = $input->getArgument('resource');

        /** the catalogue */
        $catalogue = $input->getArgument('catalogue');

        $this->orm = $this->getContainer()
                ->get('doctrine.orm.default_entity_manager');

        $this->reader = $this->getContainer()
                ->get('raindrop_import.reader');

        $this->reader->open($resource);

        while ($row = $this->reader->getRow()) {

            $getHeaders = $this->reader->getHeaders();
            $headers = array_splice($getHeaders, 2);

            $i = 2;
            foreach ($headers as $locale) {

                $token = $this->getToken($row[0], $catalogue);
                $languageEntity = $this->getLanguage($locale);

                $translation = $this->getTranslation($token, $languageEntity, $catalogue);

                // if a translation exists
                if ($row[$i]) {
                    $translation->setTranslation($row[$i]);
                }

                $i++;
            }

            $this->orm->flush();
        }
    }

    /**
     *
     * @param  type     $locale
     * @return Language
     */
    protected function getLanguage($locale)
    {
        $language = $this->orm
                ->getRepository('RaindropTranslationBundle:Language')
                ->findOneByLocale($locale);

        if (!$language) {
            $language = new Language;
            $language->setLocale($locale);
            $language->setName($locale);
            $this->orm->persist($language);
            $this->orm->flush();
        }

        return $language;
    }

    /**
     *
     * @param  type          $message
     * @param  type          $catalogue
     * @return LanguageToken
     */
    protected function getToken($message, $catalogue)
    {
        $token = $this->orm
                ->getRepository('RaindropTranslationBundle:LanguageToken')
                ->findOneBy(array('token' => $message, 'catalogue' => $catalogue));

        if (!$token) {
            $token = new LanguageToken;
            $token->setToken($message);
            $token->setCatalogue($catalogue);
            $this->orm->persist($token);
            $this->orm->flush();
        }

        return $token;
    }

    /**
     *
     * @param  type                $token
     * @param  type                $languageEntity
     * @param  type                $catalogue
     * @return LanguageTranslation
     */
    protected function getTranslation($token, $languageEntity, $catalogue)
    {
        $translation = $this->orm
                ->getRepository('RaindropTranslationBundle:LanguageTranslation')
                ->findByLanguageAndTokenAndCatalogue($languageEntity, $token, $catalogue);

        if (!$translation) {
            $translation = new LanguageTranslation;
            $translation->setLanguage($languageEntity);
            $translation->setLanguageToken($token);
            $translation->setCatalogue($catalogue);
            $this->orm->persist($translation);
        }

        return $translation;
    }
}
