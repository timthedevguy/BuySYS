<?php
namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class SettingRepository extends EntityRepository {

    public function findSettingsByPrefix($prefix) {

        return $this->getEntityManager()
            ->createQuery(
                'SELECT s FROM AppBundle:SettingEntity s WHERE s.name LIKE :name'
            )->setParameter('name', $prefix.'_%')->getResult();
    }
}
