<?php
namespace AppBundle\Repository\SDE;

use Doctrine\ORM\EntityRepository;

class MarketGroupsRepository extends EntityRepository {

    public function findAllLikeName($name) {

        return $this->getEntityManager()
            ->createQuery(
                'SELECT c FROM AppBundle:MarketGroupsEntity c WHERE c.hasTypes = 1 AND c.marketGroupName LIKE :name'
            )->setParameter('name', '%'.$name.'%')->getResult();
    }
}