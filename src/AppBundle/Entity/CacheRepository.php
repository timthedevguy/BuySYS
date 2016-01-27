<?php
namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CacheRepository extends EntityRepository {

    public function findAllByTypeIds($typeIds) {

        return $this->getEntityManager()
            ->createQuery(
                'SELECT c FROM AppBundle:CacheEntity c WHERE c.typeID IN (:types)'
            )->setParameter('types', $typeIds)->getResult();
    }
}
