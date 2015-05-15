<?php
namespace EXS\TerminalBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of SleepCommand
 *
 * Sample command to manually test locks
 *
 * Created      03/26/2015
 * @author      Charles Weiss & Mathieu Delisle
 * @copyright   Copyright 2015 ExSitu Marketing.
 */
class SleepCommand extends ContainerAwareCommand
{
   /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('terminal:test:lock_sleep')
            ->setDescription('Just container for test.')
            ->setHelp("");
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->singleTest($output);
    }

    /**
     * basic command used for testing
     * @param type $output
     */
    protected function singleTest($output)
    {
        $output->writeln('commence sleeping');
        sleep(3);
        $output->writeln('end sleeping');
    }
}
