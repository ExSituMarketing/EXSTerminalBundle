<?php
namespace  EXS\TerminalBundle\Services\Listeners;

use Doctrine\DBAL\DBALException;
use EXS\TerminalBundle\Services\Output\TerminalOutput;
use EXS\TerminalBundle\Services\Managers\OutputManager;
use EXS\ErrorBundle\Services\Listeners\ExceptionListener;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
/*
 * Description of ConsoleLogOutputEventListener
 * 
 * Listens for output parameters in console commands
 * Logs all command output to the terminal logs table.
 * 
 * Created      03/26/2015
 * @author      Charles Weiss & Mathieu Delisle
 * @copyright   Copyright 2015 ExSitu Marketing.
 */
class ConsoleLogOutputEventListener implements EventSubscriberInterface
{

    /**
     * The exception listener
     * @var ExceptionListener
     */
    protected $exceptionListener;

    /**
     * The output manager
     * @var OutputManager
     */
    protected $outputManager;

    /**
     * Constructor
     *
     * @param OutputManager $outputManager
     */
    public function __construct(OutputManager $outputManager, ExceptionListener $exceptionListener)
    {
        $this->outputManager = $outputManager;
        $this->exceptionListener = $exceptionListener;
    }

    /**
     * Register event subscriber method.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            ConsoleEvents::EXCEPTION => array('onConsoleCommandException', 9999),
            ConsoleEvents::TERMINATE => array('onConsoleCommandTerminate', 9999),
        );
    }

    /**
     * Command exception events
     * @param ConsoleExceptionEvent $event
     */
    public function onConsoleCommandException(ConsoleExceptionEvent $event)
    {
        $this->logConsoleOuput($event);
    }

    /**
     * Handle graceful exists
     * @param ConsoleTerminateEvent $event
     */
    public function onConsoleCommandTerminate(ConsoleTerminateEvent $event)
    {
        $this->logConsoleOuput($event);
    }

    /**
     * Return input object.
     *
     * @param ConsoleTerminateEvent|ConsoleExceptionEvent $event
     * @return ArgvInput
     */
    protected function getInput($event)
    {
        // merge the application's input definition
        $event->getCommand()->mergeApplicationDefinition();

        $input = new ArgvInput();

        // we use the input definition of the command
        $input->bind($event->getCommand()->getDefinition());

        return $input;
    }

    /**
     * Log the console output
     * @param ConsoleTerminateEvent|ConsoleExceptionEvent $event
     * @throws \Exception
     */
    protected function logConsoleOuput($event)
    {
        try {
            //get console ouput.
            /** @var DoctrineOutput $output */
            $output = $event->getOutput();
            $lockName = '';
            if ($output instanceof TerminalOutput) {
                //find the process name.
                $command = $this->getInput($event);
                if ($command->hasOption('lockname')) {
                    $lockName = $this->getInput($event)->getOption('lockname');
                }
                if (strlen($lockName) == 0) {
                    $lockName = $event->getCommand()->getName();
                }

                //check if command finish with error.
                $hasError = ($event->getExitCode() > 0) ? true : false;

                $terminalLog = $output->getTerminalLog();
                $terminalLog->setHasError($hasError);
                $terminalLog->setLockName($lockName);
            }
        } catch (DBALException $e) {
            //TerminalBundle is not fully setup. Is it normal to get this error on installation.
            $event->getOutput()->writeln("<error>TerminalBundle is not setup properly.</error>");
            $event->getOutput()->writeln(
                "To avoid this error message please update your Entities with Doctrine:Schema:Update to continue."
            );
            $this->exceptionListener->onAnyException($e);
        } catch (\Exception $e) {
            //log any other exception.
            $this->exceptionListener->onAnyException($e);

            //lets the system handle the exception.
            throw $e;
        }
    }
}
