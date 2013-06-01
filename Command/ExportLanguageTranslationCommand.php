<?php

namespace Raindrop\TranslationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;

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
                new InputOption(
                    'dump-messages', null, InputOption::VALUE_NONE,
                    'Should the messages be dumped in the console'
                ),
            ))
            ->setDescription('Extract translation from database')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command extract translation strings stored in the database.

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
        $this->orm = $this->getContainer()
                ->get('doctrine.orm.default_entity_manager');

        $languages = $this->orm
            ->getRepository('RaindropTranslationBundle:Language')
            ->findAll();

        $tokens = $this->orm
            ->getRepository('RaindropTranslationBundle:LanguageToken')
            ->findAll();

        foreach ($tokens as $token) {

            foreach ($languages as $language) {
                $translation = $this->orm
                        ->getRepository('RaindropTranslationBundle:LanguageTranslation')
                        ->findOneBy(array('language' => $language, 'languageToken' => $token, 'catalogue' => $token->getCatalogue()));

                $output->writeln($token->getToken() . ' --- ' . $translation->getTranslation());
            }
        }
    }
}
