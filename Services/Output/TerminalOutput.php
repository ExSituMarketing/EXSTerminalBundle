<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXS\TerminalBundle\Services\Output;

use EXS\TerminalBundle\Entity\TerminalLog;
use EXS\TerminalBundle\Services\Managers\OutputManager;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * ConsoleOutput is the default class for all CLI output. It uses STDOUT.
 *
 * This class is a convenient wrapper around `StreamOutput`.
 *
 *     $output = new ConsoleOutput();
 *
 * This is equivalent to:
 *
 *     $output = new StreamOutput(fopen('php://stdout', 'w'));
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class TerminalOutput extends StreamOutput implements ConsoleOutputInterface
{
    /**
     * @var OutputManager
     */
    protected $outputManager;

    /**
     * @var TerminalLog
     */
    private $terminalLog;

    /**
     * Constructor.
     *
     * @param OutputManager            $outputManager
     * @param int                      $verbosity
     * @param bool                     $decorated
     * @param OutputFormatterInterface $formatter
     */
    public function __construct(
        OutputManager $outputManager,
        $verbosity = OutputInterface::VERBOSITY_NORMAL,
        $decorated = null,
        OutputFormatterInterface $formatter = null
    )
    {
        $outputStream = tmpfile();

        $this->outputManager = $outputManager;
        $this->terminalLog = $outputManager->createLog();

        parent::__construct($outputStream, $verbosity, $decorated, $formatter);

        register_shutdown_function(array($this, 'closeStream'), $lockName);
    }

    /**
     * @return TerminalLog
     */
    public function getTerminalLog()
    {
        return $this->terminalLog;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorOutput()
    {
        return $this;
    }

    public function save($exitCode = 0)
    {
        $stream = $this->getStream();
        fseek($stream, 0);
        $output = '';
        while (!feof($stream)) {
            $output = fread($stream, 4096);
        }

        $this->terminalLog->setLog($output);
        $this->terminalLog->setHasError($exitCode);
        $this->outputManager->save($this->terminalLog);
    }

    public function closeStream()
    {
        $stream = $this->getStream();
        fclose($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function setDecorated($decorated)
    {
        parent::setDecorated($decorated);
    }

    /**
     * Sets the OutputInterface used for errors.
     *
     * @param OutputInterface $error
     */
    public function setErrorOutput(OutputInterface $error)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        parent::setFormatter($formatter);
    }

    /**
     * @param OutputManager $outputManager
     *
     * @return TerminalOutput
     */
    public function setOutputManager(OutputManager $outputManager)
    {
        $this->outputManager = $outputManager;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setVerbosity($level)
    {
        parent::setVerbosity($level);
    }

    /**
     * Returns true if the stream supports colorization.
     *
     * Colorization is disabled if not supported by the stream:
     *
     *  -  Windows without Ansicon and ConEmu
     *  -  non tty consoles
     *
     * @return bool    true if the stream supports colorization, false otherwise
     */
    protected function hasColorSupport()
    {
        return false;
    }
}
