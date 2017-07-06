<?php
namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CacheRepository extends EntityRepository {

    public function findAllByTypeIdsAndSettingType($typeIds, $settingType)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT c FROM AppBundle:CacheEntity c WHERE c.typeID IN (:types) AND c.settingType = :settingType'
            )->setParameter('types', $typeIds)->setParameter('settingType', $settingType)->getResult();
    }

    public function deleteAllOlderThanMinutes(int $minutes)
    {
        return $this->getEntityManager()
            ->createQuery(
                'DELETE FROM AppBundle:CacheEntity c WHERE c.lastPull < :expireTime'
            )->setParameter('expireTime', new \DateTime('-'. $minutes .' minutes'))->execute();
    }
}
