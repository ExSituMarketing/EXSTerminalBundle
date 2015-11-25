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
class SleepCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('terminal:test:sleep')
            ->setDescription('Just container for test.')
            ->setHelp('Just container for test.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Falling asleep...');

        sleep(60);

        $output->writeln('Wake up!');
    }
}
