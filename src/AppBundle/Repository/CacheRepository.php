<?php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CacheRepository extends EntityRepository {

    public function findAllByTypeIds($typeIds)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT c FROM AppBundle:CacheEntity c WHERE c.typeID IN (:types)'
            )->setParameter('types', $typeIds)->getResult();
    }

    public function deleteAllOlderThanMinutes(int $minutes)
    {
        return $this->getEntityManager()
            ->createQuery(
                'DELETE FROM AppBundle:CacheEntity c WHERE c.lastPull < :expireTime'
            )->setParameter('expireTime', new \DateTime('-'. $minutes .' minutes'))->execute();
    }
}
