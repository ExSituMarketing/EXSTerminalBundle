<?php

namespace EXS\TerminalBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sample command to manually test locks.
 *
 * Created    03/26/2015
 * @author    Charles Weiss & Mathieu Delisle
 * @copyright Copyright 2015 ExSitu Marketing.
 */
class ExceptionCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('terminal:test:exception')
            ->setDescription('Dummy command that throw an exception.')
            ->setHelp('Dummy command that throw an exception.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('For now everything is alright...');

        throw new \Exception('Oh my god! Something horrible arrived!');
    }
}
