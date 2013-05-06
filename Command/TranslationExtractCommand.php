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
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Description of TranslationExtractCommand
 *
 * This class extracts tokens from twig database templates (as provided by
 * Raindrop\TwigLoaderBundle) and saves to database where u can edit using
 * sonata admin.
 * It will also touch the file required by symfony to be aware of the
 * translation catalogue.
 * In the example we found this code from, the author refers to the message
 * domain as 'catalogue'. Not sure this is correct, i left it as is. teo
 *
 * @author teito
 */
class TranslationExtractCommand extends ContainerAwareCommand {

    protected $orm, $twig, $messages;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('raindrop:translation:database:update')
            ->setDefinition(array(
                new InputArgument('locale', InputArgument::REQUIRED, 'The locale'),
                new InputOption(
                    'prefix', null, InputOption::VALUE_OPTIONAL,
                    'Override the default prefix', '__'
                ),
                new InputOption(
                    'dump-messages', null, InputOption::VALUE_NONE,
                    'Should the messages be dumped in the console'
                ),
                new InputOption(
                    'force', null, InputOption::VALUE_NONE,
                    'Should the update be done'
                )
            ))
            ->setDescription('Updates the translation file fetching data from database')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command extract translation strings from templates
stored in the database. It can display them or merge the new ones into the translation files.
When new translation strings are found it can automatically add a prefix to the translation
message.

<info>php %command.full_name% --dump-messages en</info>
<info>php %command.full_name% --force --prefix="new_" fr</info>
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
        $files = $this->orm
            ->getRepository('RaindropTwigLoaderBundle:TwigTemplate')
            ->findAll();


        foreach ($files as $file) {
            $this->extractTokensFromTemplate($language, $file->getTemplate());
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

    protected function setupLanguageFile($catalogue, $languageEntity) {
        $languageFile = __DIR__ . sprintf("/../Resources/translations/%s.%s.db", $catalogue, $languageEntity);
        if (!file_exists($languageFile)) {
            touch($languageFile);
        }
    }

    /**
     * This function duplicates Raindrop\TwigLoaderBundle\Translation\DatabaseTwigExtractor::extractTemplate
     * we must refactor! the wrath of the gods hangs over us!
     *
     * @param type $language
     * @param type $template
     */
    protected function extractTokensFromTemplate($languageEntity, $template) {
        $visitor = $this->twig->getExtension('translator')->getTranslationNodeVisitor();
        $visitor->enable();

        $this->twig->parse($this->twig->tokenize($template));

        foreach ($visitor->getMessages() as $message) {
            $this->createTokenAndMessageForLocale($message[0], $languageEntity, $message[1] ?: 'messages');
        }

        $visitor->disable();

        return $visitor;
    }

    /**
     * Create token and translation (if not present)
     *
     * @param type $message
     * @param type $catalogue
     */
    protected function createTokenAndMessageForLocale($message, $languageEntity, $catalogue) {

        // this gets invoked to make sure the catalogue file is present
        $this->setupLanguageFile($catalogue, $languageEntity);

        $this->messages []= $message;

        $token = $this->orm
                ->getRepository('RaindropTranslationBundle:LanguageToken')
                ->findOneByToken($message);

        if (!$token) {
            $token = new LanguageToken;
            $token->setToken($message);
            $this->orm->persist($token);
        }

        $translation = $this->orm
                ->getRepository('RaindropTranslationBundle:LanguageTranslation')
                ->findByLanguageAndTokenAndCatalogue($languageEntity->getLocale(), $message, $catalogue);

        if (!$translation) {
            $translation = new LanguageTranslation;
            $translation->setLanguage($languageEntity);
            $translation->setLanguageToken($token);
            $translation->setCatalogue($catalogue);
        }

        $this->orm->persist($translation);
    }

    protected function getLanguage($locale) {
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

?>
