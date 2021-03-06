<?php

namespace EXS\TerminalBundle\Services\Managers;

use EXS\TerminalBundle\Entity\CommandLock;
use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 * Class Mailer
 *
 * @package EXS\TerminalBundle\Services
 */
class EmailManager
{
    /**
     * @var TwigEngine
     */
    private $templating;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var \Swift_Transport
     */
    private $transport;

    /**
     * @var array
     */
    private $parameters;

    /**
     * Constructor.
     *
     * @param TwigEngine       $templating
     * @param \Swift_Mailer    $mailer
     * @param \Swift_Transport $transport
     * @param array            $parameters
     */
    public function __construct(TwigEngine $templating, \Swift_Mailer $mailer, \Swift_Transport $transport, array $parameters)
    {
        $this->templating = $templating;
        $this->mailer = $mailer;
        $this->transport = $transport;
        $this->parameters = $parameters;
    }

    /**
     * @param CommandLock $commandLock
     * @param \Exception  $exception
     *
     * @return bool|null
     */
    public function sendErrorEmail(CommandLock $commandLock, \Exception $exception)
    {
        try {
            $message = \Swift_Message::newInstance()
                ->setSubject(sprintf(
                    '%s - %s',
                    $this->parameters['subject'],
                    $commandLock->getLockName()
                ))
                ->setFrom($this->parameters['from'])
                ->setTo($this->parameters['to'])
                ->setBody($this->templating->render(
                    'EXSTerminalBundle:Email:onConsoleException.txt.twig',
                    array(
                        'command' => $commandLock,
                        'exception' => $exception,
                    )
                ))
            ;

            $sent = $this->mailer->send($message);

            $spool = $this->mailer->getTransport()->getSpool();
            $spool->flushQueue($this->transport);

            return (0 < $sent);
        } catch (\Exception $e) {
            return null;
        }
    }
}
