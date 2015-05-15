<?php

namespace EXS\TerminalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CommandLock
 * Created      03/26/2015
 * @author      Charles Weiss & Mathieu Delisle
 * @copyright   Copyright 2015 ExSitu Marketing.
 *
 *
 * @ORM\Table(name="commandlocks")
 * @ORM\Entity(repositoryClass="EXS\TerminalBundle\Entity\Repository\CommandLockRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CommandLock
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
     * @ORM\Column(name="currentPid", type="integer", options={"default"="0"})
     */
    private $currentPid = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="hasError", type="boolean")
     */
    private $hasError = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isActive", type="boolean")
     */
    private $isActive = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastError", type="datetime", options={"default"="0000-00-00 00:00:00"})
     */
    private $lastError;

    /**
     * @var float
     *
     * @ORM\Column(name="lastRunTime", type="float", options={"default"="0"})
     */
    private $lastRunTime = 0;

    /**
     * Process name
     *
     * @var string
     *
     * @ORM\Column(name="lockName", type="string", length=60, options={"comment"="Process name"})
     */
    private $lockName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lockedSince", type="datetime", options={"default"="0000-00-00 00:00:00"})
     */
    private $lockedSince;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modified", type="datetime")
     */
    private $modified;

    /**
     * The constructor
     *
     * Set the created time
     */
    public function __construct()
    {
        $this->setLockedSince(new \DateTime("0000-00-00 00:00:00"));
        $this->setCreated(new \DateTime());
        $this->setModified(new \DateTime());
        $this->setLastError(new \DateTime("0000-00-00 00:00:00"));
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return CommandLock
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPid()
    {
        return $this->currentPid;
    }

    /**
     * @param int $currentPid
     *
     * @return CommandLock
     */
    public function setCurrentPid($currentPid)
    {
        $this->currentPid = $currentPid;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * @param \DateTime $lastError
     *
     * @return CommandLock
     */
    public function setLastError(\DateTime $lastError)
    {
        $this->lastError = $lastError;

        return $this;
    }

    /**
     * Get lastRunTime
     *
     * @return float
     */
    public function getLastRunTime()
    {
        return $this->lastRunTime;
    }

    /**
     * Set lastRunTime
     *
     * @param float $lastRunTime
     *
     * @return CommandLock
     */
    public function setLastRunTime($lastRunTime)
    {
        $this->lastRunTime = $lastRunTime;

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
     * @return CommandLock
     */
    public function setLockName($lockName)
    {
        $this->lockName = $lockName;

        return $this;
    }

    /**
     * Get lockedSince
     *
     * @return \DateTime
     */
    public function getLockedSince()
    {
        return $this->lockedSince;
    }

    /**
     * Set lockedSince
     *
     * @param \DateTime $lockedSince
     *
     * @return CommandLock
     */
    public function setLockedSince($lockedSince)
    {
        $this->lockedSince = $lockedSince;

        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     *
     * @return CommandLock
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
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
     * @return CommandLock
     */
    public function setHasError($hasError)
    {
        $this->hasError = $hasError;

        return $this;
    }

    /**
     * @param boolean $isActive
     *
     * @return CommandLock
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Force the modified time pre update
     *
     * @ORM\PreUpdate
     */
    public function setModifiedValue()
    {
        $this->setModified(new \DateTime());
    }
}
