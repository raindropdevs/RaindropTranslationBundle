<?php

namespace Raindrop\TranslationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * ExportLanguageTranslation Command
 */
class ExportLanguageTranslationCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('raindrop:translation:export')
            ->setDefinition(array(
                new InputArgument('bundle', InputArgument::REQUIRED, 'The bundle where to load the messages'),
                new InputOption(
                    'output-format', null, InputOption::VALUE_OPTIONAL,
                    'Override the default output format', 'yml'
                ),
            ))
            ->setDescription('Extract translation from database')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command extract translation strings stored in the database.

<info>php %command.full_name% AcmeDemoBundle</info>
EOF
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get bundle directory
        $foundBundle = $this->getApplication()->getKernel()->getBundle($input->getArgument('bundle'));
        $bundleTransPath = $foundBundle->getPath().'/Resources/translations';

        $writer = $this->getContainer()->get('translation.writer');
        $supportedFormats = $writer->getFormats();
        if (!in_array($input->getOption('output-format'), $supportedFormats)) {
            $output->writeln('<error>Wrong output format</error>');
            $output->writeln('Supported formats are '.implode(', ', $supportedFormats).'.');

            return 1;
        }

        $this->orm = $this->getContainer()
                ->get('doctrine.orm.default_entity_manager');

        $languages = $this->orm
            ->getRepository('RaindropTranslationBundle:Language')
            ->findAll();

        $tokens = $this->orm
            ->getRepository('RaindropTranslationBundle:LanguageToken')
            ->findAll();

        foreach ($languages as $language) {

            $output->writeln(sprintf('Generating "<info>%s</info>" translation files for "<info>%s</info>"', $language, $foundBundle->getName()));

            // create catalogue
            $catalogue = new MessageCatalogue($language);

            foreach ($tokens as $token) {

                $translation = $this->orm
                        ->getRepository('RaindropTranslationBundle:LanguageTranslation')
                        ->findOneBy(array('language' => $language, 'languageToken' => $token, 'catalogue' => $token->getCatalogue()));

                $catalogue->set($token->getToken(), $translation->getTranslation());
            }

            $writer->writeTranslations($catalogue, $input->getOption('output-format'), array('path' => $bundleTransPath));
        }
    }
}
