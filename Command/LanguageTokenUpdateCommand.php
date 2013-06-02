<?php

namespace Raindrop\TranslationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Raindrop\TranslationBundle\Entity\LanguageToken;

/**
 * LanguageTranslationUpdate Command
 */
class LanguageTokenUpdateCommand extends ContainerAwareCommand
{
    protected $orm;
    protected $twig;
    protected $messages;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('raindrop:translation:token:update')
            ->setDefinition(array(
                new InputOption(
                    'dump-messages', null, InputOption::VALUE_NONE,
                    'Should the messages be dumped in the console'
                ),
                new InputOption(
                    'force', null, InputOption::VALUE_NONE,
                    'Should the update be done'
                )
            ))
            ->setDescription('Extract strings from templates into database and saves them to database')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command extract translation strings from templates
stored in the database. It can display them or merge the new ones into the database.

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
        // check presence of force or dump-message
        if ($input->getOption('force') !== true && $input->getOption('dump-messages') !== true) {
            $output->writeln('<info>You must choose one of --force or --dump-messages</info>');

            return 1;
        }

        $this->orm = $this->getContainer()
                ->get('doctrine.orm.default_entity_manager');

        $this->twig = $this->getContainer()->get('twig');

        $this->messages = array();

        // load templates
        $files = $this->orm
            ->getRepository('RaindropTwigLoaderBundle:TwigTemplate')
            ->findAll();

        foreach ($files as $file) {
            $this->extractTokensFromTemplate($file->getTemplate());
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
     * This function duplicates Raindrop\TwigLoaderBundle\Translation\DatabaseTwigExtractor::extractTemplate
     * we must refactor! the wrath of the gods hangs over us!
     *
     * @param type $template
     */
    protected function extractTokensFromTemplate($template)
    {
        $visitor = $this->twig->getExtension('translator')->getTranslationNodeVisitor();
        $visitor->enable();

        $this->twig->parse($this->twig->tokenize($template));

        foreach ($visitor->getMessages() as $message) {
            $this->createToken($message[0], $message[1] ?: 'messages');
        }

        $visitor->disable();

        return $visitor;
    }

    /**
     * Create token (if not present)
     *
     * @param string $message
     * @param string $catalogue
     */
    protected function createToken($message, $catalogue)
    {
        $this->messages[] = $message;

        $token = $this->orm
                ->getRepository('RaindropTranslationBundle:LanguageToken')
                ->findOneBy(array('token' => $message, 'catalogue' => $catalogue));

        if (!$token) {
            $token = new LanguageToken;
            $token->setToken($message);
            $token->setCatalogue($catalogue);
            $this->orm->persist($token);
        }

        $this->orm->flush();
    }
}
