<?php

namespace EXS\TerminalBundle\Services\Managers;

use EXS\TerminalBundle\Entity\CommandLock;
use EXS\TerminalBundle\Entity\Repository\CommandLockRepository;
use EXS\TerminalBundle\Entity\TerminalLog;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * OutputManager processes console output (including traditional).
 *
 * Created    03/26/2015
 * @author    Charles Weiss & Mathieu Delisle
 * @copyright Copyright 2015 ExSitu Marketing.
 */
class OutputManager extends ContainerAware
{
    /**
     * The entity manager
     *
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * Sets the ManagerRegistry.
     *
     * @param ManagerRegistry $managerRegistry
     */
    public function setManagerRegistry(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * Creates a new terminal log with the pid
     * @return TerminalLog
     */
    public function createLog()
    {
        $log = new TerminalLog();
        $log->setPid(getmypid());

        return $log;
    }

    /**
     * Save the entity.
     *
     * @param TerminalLog $terminalLog
     */
    public function save(TerminalLog $terminalLog)
    {
        $terminalLog->setCreated(new \DateTime());

        if (strlen($terminalLog->getLockName()) == 0) {
            // when have some runtime exception, event subscriber are not hit and lockName is not set.
            // if lockName is not set. its an error and should flag it.
            $terminalLog->setLockName('Undefined');
            $terminalLog->setHasError(true);
        }

        $entityManager = $this->managerRegistry->getManagerForClass('EXS\TerminalBundle\Entity\TerminalLog');
        $entityManager->persist($terminalLog);
        $entityManager->flush($terminalLog);
    }
}
