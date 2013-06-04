<?php

namespace Raindrop\TranslationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Raindrop\TranslationBundle\Entity\Language;
use Raindrop\TranslationBundle\Entity\LanguageToken;
use Raindrop\TranslationBundle\Entity\LanguageTranslation;

/**
 * LanguageTranslationUpdateCommand
 *
 * This class extracts tokens from database and saves translation to database where
 * you can edit using sonata admin.
 * It will also touch the file required by symfony to be aware of the
 * translation catalogue.
 * In the example we found this code from, the author refers to the message
 * domain as 'catalogue'. Not sure this is correct, i left it as is. teo
 *
 * @author teito
 */
class LanguageTranslationUpdateCommand extends ContainerAwareCommand
{
    protected $orm, $twig, $messages;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('raindrop:translation:update')
            ->setDefinition(array(
                new InputArgument('locale', InputArgument::REQUIRED, 'The locale'),
                new InputOption(
                    'dump-messages', null, InputOption::VALUE_NONE,
                    'Should the messages be dumped in the console'
                ),
                new InputOption(
                    'force', null, InputOption::VALUE_NONE,
                    'Should the update be done'
                )
            ))
            ->setDescription('Extract tokens from database and saves translation to database')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command extract translation strings stored in the database
and saves translations to database.

<info>php %command.full_name% --dump-messages en</info>
EOF
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // check presence of force or dump-message
        if ($input->getOption('force') !== true && $input->getOption('dump-messages') !== true) {
            $output->writeln('<info>You must choose one of --force or --dump-messages</info>');

            return 1;
        }

        $this->orm = $this->getContainer()
                ->get('doctrine.orm.default_entity_manager');

        $this->twig = $this->getContainer()->get('twig');

        $this->messages = array();

        // create language: the language is an ORM entity that holds locale
        // and references translation tokens.
        $language = $this->getLanguage($input->getArgument('locale'));

        // load templates
        $tokens = $this->orm
            ->getRepository('RaindropTranslationBundle:LanguageToken')
            ->findAll();

        foreach ($tokens as $token) {
            $this->createMessageForLocale($token, $language, $token->getCatalogue());
        }

        // show compiled list of messages
        if ($input->getOption('dump-messages') === true) {
            foreach ($this->messages as $message) {
                $output->writeln(sprintf("Found message <info>%s</info>", $message));
            }
        }

        // save the files
        if ($input->getOption('force') === true) {
            $output->writeln("\nWriting to database");
            $this->orm->flush();
        }
    }

    /**
     * Setup language files
     *
     * @param string   $catalogue
     * @param Language $languageEntity
     */
    protected function setupLanguageFile($catalogue, $languageEntity)
    {
        $languageFile = __DIR__ . sprintf("/../Resources/translations/%s.%s.db", $catalogue, $languageEntity);
        if (!file_exists($languageFile)) {
            touch($languageFile);
        }
    }

    /**
     * Create translation (if not present)
     *
     * @param LanguageToken $token
     * @param Language      $languageEntity
     * @param string        $catalogue
     */
    protected function createMessageForLocale($token, $languageEntity, $catalogue)
    {
        // this gets invoked to make sure the catalogue file is present
        $this->setupLanguageFile($catalogue, $languageEntity);

        $this->messages []= $token;

        $translation = $this->orm
                ->getRepository('RaindropTranslationBundle:LanguageTranslation')
                ->findByLanguageAndTokenAndCatalogue($languageEntity, $token, $catalogue);

        if (!$translation) {
            $translation = new LanguageTranslation;
            $translation->setLanguage($languageEntity);
            $translation->setLanguageToken($token);
            $translation->setCatalogue($catalogue);
        }

        $this->orm->persist($translation);
        $this->orm->clear();
        $this->orm->flush();
    }

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
        }

        return $language;
    }
}
