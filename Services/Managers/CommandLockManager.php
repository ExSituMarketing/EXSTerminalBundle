<?php

namespace EXS\TerminalBundle\Services\Managers;

use Doctrine\ORM\EntityManager;
use EXS\TerminalBundle\Entity\CommandLock;
use EXS\TerminalBundle\Entity\Repository\CommandLockRepository;
use EXS\TerminalBundle\Exception\CommandIsInterruptedException;
/*
 * Description of CommandLockManager
 * 
 * Handles the actual writing to logs
 * 
 * Created      03/26/2015
 * @author      Charles Weiss & Mathieu Delisle
 * @copyright   Copyright 2015 ExSitu Marketing.
 */
class CommandLockManager
{

    /**
     * The repo
     * @var CommandLockRepository
     */
    protected $commandLockRepository;

    /**
     * The entity manager
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Constructor
     * 
     * @param EntityManager         $entityManager
     * @param CommandLockRepository $commandLockRepository
     */
    public function __construct(EntityManager $entityManager, CommandLockRepository $commandLockRepository)
    {
        $this->entityManager = $entityManager;
        $this->commandLockRepository = $commandLockRepository;
    }

    /**
     * Get CommandLock by name.
     *
     * @param $name
     * @return CommandLock
     */
    public function get($name)
    {
        return $this->commandLockRepository->getCommandLockByName($name);
    }

    /**
     * Save a command lock.
     *
     * @param CommandLock $commandLock
     */
    public function save(CommandLock $commandLock, $validatePid = true)
    {
        //just to be sure we have the pid.
        if ($validatePid === true) {
            $commandLock->setCurrentPid(getmypid());
        }

        $this->entityManager->persist($commandLock);
        $this->entityManager->flush($commandLock);
    }

    /**
     * Action to do on application shutdown.
     *
     * @param string $lockName
     * @param bool   $throwException
     * @param int    $signo
     * @throws CommandIsInterruptedException
     */
    public function shutDown($lockName, $throwException = false, $signo = 0)
    {

        $this->entityManager->getConnection()->close();
        $this->entityManager->getConnection()->connect();

        $commandLock = $this->get($lockName);

        $now = new \DateTime();
        $diff = $now->diff($commandLock->getLockedSince());

        $commandLock->setLastRunTime($diff->s);

        if ($commandLock->isHasError() == false && $throwException == true) {
            $commandLock->setHasError($throwException);
        }
        $this->save($commandLock);

        if ($throwException) {
            $exception = new CommandIsInterruptedException("Command is interrupted", 1);
            throw $exception->setCommandLock($commandLock)->setSigNo($signo);
        }

    }

    /**
     * Start the command lock
     *
     * @param CommandLock $commandLock
     */
    public function start(CommandLock $commandLock)
    {
        $commandLock->setCurrentPid(getmypid());
        $commandLock->setLockedSince(new \DateTime());
        $commandLock->setHasError(false);
        $this->save($commandLock);

        $this->registerShutDownFunction($commandLock->getLockName());
    }

    /**
     * Register shutdown function to use after command terminate.
     *
     * @param string $lockName
     */
    protected function registerShutDownFunction($lockName)
    {
        //on shutdown function.
        register_shutdown_function(
            array($this, "shutDown"),
            $lockName
        );

    }
}
