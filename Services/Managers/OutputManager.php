<?php

namespace EXS\TerminalBundle\Services\Managers;

use EXS\TerminalBundle\Entity\CommandLock;
use EXS\TerminalBundle\Entity\TerminalLog;
use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\ORM\EntityManager;
/**
 * OutputManager
 *
 * Class used to process console output (including traditional)
 *
 * Created      03/26/2015
 * @author      Charles Weiss & Mathieu Delisle
 * @copyright   Copyright 2015 ExSitu Marketing.
 */
class OutputManager extends ContainerAware
{

    /**
     * The entity manager
     * @var EntityManager
     */
    protected $EntityManager;

    /**
     * Setter for the em
     * @param EntityManager $EntityManager
     */
    public function setManager(EntityManager $EntityManager)
    {
        $this->EntityManager = $EntityManager;
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

        $this->EntityManager->persist($terminalLog);
        $this->EntityManager->flush($terminalLog);

        if ($terminalLog->isHasError()) {
            $this->sendMail($terminalLog);
        }
    }

    /**
     * Send an email
     * @param TerminalLog $terminalLog
     */
    protected function sendMail(TerminalLog $terminalLog)
    {
        try {
            //get associated log.
            // CommandLockRepository $commandLockRepo
            $commandLockRepo = $this->EntityManager->getRepository("EXSTerminalBundle:CommandLock");
            if (strlen($terminalLog->getLockName()) > 0) {
                $commandLock = $commandLockRepo->getCommandLockByName($terminalLog->getLockName());
            } else {
                $commandLock = new CommandLock();
                $commandLock->setLockName("NoLockNameError");
            }

            //build the message.
            $message = \Swift_Message::newInstance()
                ->setSubject($this->container->getParameter('exs.emails.error.subject') . ' ' . $commandLock->getLockName())
                ->setFrom($this->container->getParameter('exs.emails.error.from'))
                ->setTo($this->container->getParameter('exs.emails.error.to'))
                ->setBody(
                $this->container->get('templating')->render(
                    'EXSTerminalBundle:Email:onConsoleException.txt.twig', array(
                    'command' => $commandLock,
                    "log" => $terminalLog)
                )
            );

            // \Swift_Mailer $mailer
            $mailer = $this->container->get('mailer');

            //send the message.
            $sent = $mailer->send($message);

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
