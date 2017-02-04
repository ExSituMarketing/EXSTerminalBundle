<?php

namespace EXS\TerminalBundle\Services\Subscribers;

use Doctrine\DBAL\DBALException;
use EXS\TerminalBundle\Exception\CommandAlreadyRunningException;
use EXS\TerminalBundle\Exception\CommandIsDisabledException;
use EXS\TerminalBundle\Services\Managers\CommandLockManager;
use EXS\TerminalBundle\Services\Managers\EmailManager;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listens for lock parameters in console commands.
 * Prevents the same command from running concurrently.
 *
 * Created    03/26/2015
 * @author    Charles Weiss & Mathieu Delisle
 * @copyright Copyright 2015 ExSitu Marketing.
 */
class CommandLockSubscriber implements EventSubscriberInterface
{
    /**
     * The lock manager
     * @var CommandLockManager
     */
    protected $commandLockManager;

    /**
     * @var EmailManager
     */
    private $emailManager;

    /**
     * Constructor.
     *
     * @param CommandLockManager $commandLockManager
     */
    public function __construct(CommandLockManager $commandLockManager, EmailManager $emailManager)
    {
        $this->commandLockManager = $commandLockManager;
        $this->emailManager = $emailManager;
    }

    /**
     * Register event subscriber method.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            ConsoleEvents::COMMAND => array(
                array('onConsoleCommand', 1024),
            ),
            ConsoleEvents::EXCEPTION => array(
                array('onConsoleCommandException', 1000),
                array('sendErrorEmail', 500),
            ),
            ConsoleEvents::TERMINATE => array(
                array('onConsoleCommandTerminate', 1000),
            ),
        );
    }

    /**
     * When console command start.
     *
     * @param ConsoleCommandEvent $event
     *
     * @throws CommandAlreadyRunningException
     * @throws CommandIsDisabledException
     * @throws \Exception
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $isLocked = $this->configInput($event);
        if ($isLocked == true) {
            try {
                $lockName = $this->getLockname($event);

                $commandLock = $this->commandLockManager->get($lockName);
                $isActive = $commandLock->isActive();
                //make sure command is active.
                if ($isActive == true) {
                    $isRunning = $this->pidIsRunning($commandLock->getCurrentPid());
                    //check if command is not lock, process id is not running.
                    if ($isRunning == false) {
                        $this->commandLockManager->start($commandLock);
                    } else {
                        $exception = new CommandAlreadyRunningException("Command already running", 0);
                        throw $exception->setCommandLock($commandLock);
                    }
                } else {
                    $exception = new CommandIsDisabledException("Command is disabled", 0);
                    throw $exception->setCommandLock($commandLock);
                }
            } catch (DBALException $e) {
                $this->outputSetupException($event);
            } catch (\PDOException $e) {
                $this->outputSetupException($event);
            } catch (\Exception $e) {
                //lets the system handle the exception.
                throw $e;
            }
        }
    }

    /**
     * When console terminate with exception.
     *
     * @param ConsoleExceptionEvent $event
     */
    public function onConsoleCommandException(ConsoleExceptionEvent $event)
    {
        $lockName = $this->getLockname($event);

        if (strlen($lockName) > 0) {
            $commandLock = $this->commandLockManager->get($lockName);
            $commandLock->setHasError(true);
            $commandLock->setLastError(new \DateTime());

            $this->commandLockManager->save($commandLock);
        }
    }

    /**
     * Do we need to close this lock?
     *
     * @param ConsoleTerminateEvent $event
     */
    public function onConsoleCommandTerminate(ConsoleTerminateEvent $event)
    {
        $lockName = $this->getLockname($event);

        if (strlen($lockName) > 0) {
            /*
             * In sf 2.4, 2.5 and 2.6 Terminate is before Exception.
             * Need to do this to be sure it is executed in the end.
             */
            register_shutdown_function(array($this, 'shutDownOnConsoleCommandterminate'), $lockName);
        }
    }

    /**
     * Close the lock gracefully.
     *
     * @param string $lockName
     */
    public function shutDownOnConsoleCommandterminate($lockName)
    {
        if (strlen($lockName) > 0) {
            $commandLock = $this->commandLockManager->get($lockName);
            $commandLock->setCurrentPid(0);
            $commandLock->setLockedSince(new \DateTime('0000-00-00 00:00:00'));

            $this->commandLockManager->save($commandLock, false);
        }
    }

    /**
     * Configure the lockname input.
     *
     * @param ConsoleCommandEvent|ConsoleTerminateEvent|ConsoleExceptionEvent $event
     *
     * @return bool
     */
    protected function configInput($event)
    {
        $inputDefinition = $event->getCommand()->getApplication()->getDefinition();

        // create input definition for the lockname.
        $inputDefinition->addOption(new InputOption(
            'lock', 'l', InputOption::VALUE_OPTIONAL, 'Specify whether to lock this command.', null
        ));

        // merge the application's input definition
        $event->getCommand()->mergeApplicationDefinition();

        $input = new ArgvInput();

        // we use the input definition of the command
        $input->bind($event->getCommand()->getDefinition());

	// Is the input locked
        $isLocked = true;
        if ($input->hasOption('lock')) {
            $isLocked = (bool) $input->getOption('lock');
        }
        return $isLocked;
    }

    /**
     * Handle exception
     *
     * @param ConsoleCommandEvent $event
     */
    protected function outputSetupException(ConsoleCommandEvent $event)
    {
        //TerminalBundle is not fully setup. Is it normal to get this error on installation.
        $event->getOutput()->writeln('<error>TerminalBundle is not setup properly.</error>');
        $event->getOutput()->writeln(
            'To avoid this error message please update your Entities with Doctrine:Schema:Update to continue.'
        );
    }

    /**
     * Check if the PID is running.
     *
     * @param int|null $pid
     *
     * @return bool
     */
    protected function pidIsRunning($pid)
    {
        if (intval($pid) > 0 && posix_getpgid($pid) !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the lockname
     *
     * @param ConsoleTerminateEvent|ConsoleExceptionEvent $event
     *
     * @return string
     */
    public function getLockname($event)
    {
        $lockName = $event->getCommand()->getName();

        return $lockName;
    }

    /**
     * @param ConsoleExceptionEvent $event
     */
    public function sendErrorEmail(ConsoleExceptionEvent $event)
    {
        $lockName = $this->getLockname($event);

        if (strlen($lockName) > 0) {
            $commandLock = $this->commandLockManager->get($lockName);

            if (true === $commandLock->getNotifyOnError()) {
                $this->emailManager->sendErrorEmail(
                    $commandLock,
                    $event->getException()
                );
            }
        }
    }
}
