<?php

namespace EXS\TerminalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TerminalLog
 *
 * Created      03/26/2015
 * @author      Charles Weiss & Mathieu Delisle
 * @copyright   Copyright 2015 ExSitu Marketing.
 *
 * @ORM\Table(name="terminallogs")
 * @ORM\Entity()
 */
class TerminalLog
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="pid", type="integer")
     */
    private $pid = 0;

    /**
     * Process name
     *
     * @var string
     *
     * @ORM\Column(name="lockName", type="string", length=60, options={"comment"="Process name"})
     */
    private $lockName = "";

    /**
     * @var boolean
     *
     * @ORM\Column(name="hasError", type="boolean")
     */
    private $hasError = false;

    /**
     * @var string
     *
     * @ORM\Column(name="log", type="text")
     */
    private $log = "";

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    public function appendToLog($log = '', $newLine = false)
    {
        $this->log .= $log . ($newLine ? PHP_EOL : '');
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     *
     * @return TerminalLog
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return TerminalLog
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param string $log
     *
     * @return TerminalLog
     */
    public function setLog($log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * @return int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @param int $pid
     *
     * @return TerminalLog
     */
    public function setPid($pid)
    {
        $this->pid = $pid;

        return $this;
    }

    /**
     * @return string
     */
    public function getLockName()
    {
        return $this->lockName;
    }

    /**
     * @param string $lockName
     *
     * @return TerminalLog
     */
    public function setLockName($lockName)
    {
        $this->lockName = $lockName;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isHasError()
    {
        return $this->hasError;
    }

    /**
     * @param boolean $hasError
     *
     * @return TerminalLog
     */
    public function setHasError($hasError)
    {
        $this->hasError = $hasError;

        return $this;
    }
}
