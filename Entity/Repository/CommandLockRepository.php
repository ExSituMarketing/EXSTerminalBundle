<?php

namespace EXS\TerminalBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use EXS\TerminalBundle\Entity\CommandLock;

/**
 * Description of CommandLockRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 *
 * Created    03/26/2015
 * @author    Charles Weiss & Mathieu Delisle
 * @copyright Copyright 2015 ExSitu Marketing.
 */
class CommandLockRepository extends EntityRepository
{
    /**
     * Gets setting from DB or default setting entity.
     * Returns entity (Setting)
     *
     * @param string $lockName
     *
     * @return CommandLock
     */
    public function getCommandLockByName($lockName = '')
    {
        try {
            return $this
                ->createQueryBuilder('l')
                ->where('l.lockName = :l_lockName')
                ->setMaxResults(1)
                ->setParameter(':l_lockName', $lockName)
                ->getQuery()
                ->getSingleResult()
            ;
        } catch (NoResultException $e) {
            $newCommandLock = new CommandLock();
            $newCommandLock->setLockName($lockName);

            return $newCommandLock;
        }
    }
}
