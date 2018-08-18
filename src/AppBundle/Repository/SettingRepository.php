<?php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SettingRepository extends EntityRepository {

    public function findSettingsByPrefix($prefix)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT s FROM AppBundle:SettingEntity s WHERE s.name LIKE :name'
            )->setParameter('name', $prefix.'_%')->getResult();
    }

    public function findSettingsByTypes(array $types)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT s FROM AppBundle:SettingEntity s WHERE s.type in (:types)'
            )->setParameter('types', $types)->getResult();
    }
}
