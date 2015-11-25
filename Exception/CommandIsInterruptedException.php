<?php

namespace EXS\TerminalBundle\Exception;

use EXS\TerminalBundle\Entity\CommandLock;

/**
 * Class CommandIsInterruptedException
 *
 * Created    03/26/2015
 * @author    Charles Weiss & Mathieu Delisle
 * @copyright Copyright 2015 ExSitu Marketing.
 */
class CommandIsInterruptedException extends \Exception
{
    /**
     * The message template, used to generate the main message string.
     *
     * @var string
     */
    protected $messageTemplate = "Command \"%s\" has been interrupted by %s signal.";

    /**
     * Currently running CommandLock.
     *
     * @var CommandLock
     */
    protected $commandLock;

    /**
     * The exit signal.
     *
     * @var int
     */
    protected $sigNo;

    /**
     * Sets the command lock.
     *
     * @param CommandLock $commandLock
     *
     * @return CommandIsInterruptedException
     */
    public function setCommandLock(CommandLock $commandLock)
    {
        $this->commandLock = $commandLock;

        $this->updateMessage();

        return $this;
    }

    /**
     * Sets the signal.
     *
     * @param int $sigNo
     *
     * @return CommandIsInterruptedException
     */
    public function setSigNo($sigNo)
    {
        $this->sigNo = $sigNo;

        return $this;
    }

    /**
     * Updates the message.
     */
    protected function updateMessage()
    {
        $this->message = sprintf(
            $this->messageTemplate,
            $this->commandLock->getLockName(),
            $this->sigNo
        );
    }
}
