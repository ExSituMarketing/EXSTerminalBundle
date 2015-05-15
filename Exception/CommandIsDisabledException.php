<?php

namespace EXS\TerminalBundle\Exception;

use EXS\TerminalBundle\Entity\CommandLock;

/**
 * Class CommandIsDisabledException
 *
 * Created      03/26/2015
 * @author      Charles Weiss & Mathieu Delisle
 * @copyright   Copyright 2015 ExSitu Marketing.
 */
class CommandIsDisabledException extends \Exception
{
    /**
     * The message template, used to generate the main message string
     * @var string
     */
    protected $messageTemplate = "Command \"%s\" is disabled in the admin since %s";

    /**
     * Store the running command object
     * @var CommandLock
     */
    protected $commandLock;

    /**
     * Set the command lock
     * @param CommandLock $commandLock
     * @return self
     */
    public function setCommandLock(CommandLock $commandLock)
    {
        $this->commandLock = $commandLock;

        $this->updateMessage();

        return $this;
    }

    /**
     * Update the message
     * @return void
     */
    protected function updateMessage()
    {
        $this->message = sprintf(
            $this->messageTemplate,
            $this->commandLock->getLockName(),
            $this->commandLock->getModified()->format("Y-m-d H:i:s")
        );
    }
}
