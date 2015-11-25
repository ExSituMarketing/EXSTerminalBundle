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
 * @created   03/26/2015
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
     * @var array
     */
    private $emailParameters;

    /**
     * @param array $emailParameters
     */
    public function __construct(array $emailParameters)
    {
        $this->emailParameters = $emailParameters;
    }

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
            $terminalLog->setHasError(true);
        }

        $entityManager = $this->managerRegistry->getManagerForClass('EXS\TerminalBundle\Entity\TerminalLog');
        $entityManager->persist($terminalLog);
        $entityManager->flush($terminalLog);

        if ($terminalLog->isHasError()) {
            $this->sendMail($terminalLog);
        }
    }

    /**
     * Sends an email.
     *
     * @param TerminalLog $terminalLog
     */
    protected function sendMail(TerminalLog $terminalLog)
    {
        try {
            //get associated log.
            /** @var CommandLockRepository $commandLockRepo */
            $commandLockRepo = $this->managerRegistry->getRepository('EXSTerminalBundle:CommandLock');

            if (strlen($terminalLog->getLockName()) > 0) {
                $commandLock = $commandLockRepo->getCommandLockByName($terminalLog->getLockName());
            } else {
                $commandLock = new CommandLock();
                $commandLock->setLockName('NoLockNameError');
            }

            if (false === $commandLock->getNotifyOnError()) {
                return;
            }

            //build the message.
            $message = \Swift_Message::newInstance()
                ->setSubject(sprintf(
                    '%s %s',
                    $this->emailParameters['subject'],
                    $commandLock->getLockName()
                ))
                ->setFrom($this->emailParameters['from'])
                ->setTo($this->emailParameters['to'])
                ->setBody($this->container->get('templating')->render(
                    'EXSTerminalBundle:Email:onConsoleException.txt.twig',
                    array(
                        'command' => $commandLock,
                        'log' => $terminalLog,
                    )
                ));

            /** @var \Swift_Mailer $mailer */
            $mailer = $this->container->get('mailer');

            //send the message.
            $mailer->send($message);

            // Flush the spool explicitely
            $spool = $mailer->getTransport()->getSpool();
            $transport = $this->container->get('swiftmailer.transport.real');
            $spool->flushQueue($transport);
        } catch (\Exception $e) {
            //prevent looping.
            $this->container->get('exs.exception_listener')->onAnyException($e);
        }
    }
}
