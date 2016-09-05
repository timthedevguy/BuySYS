<?php
namespace EveBundle\Entity;

use Doctrine\ORM\EntityRepository;

class MarketGroupsRepository extends EntityRepository {

    public function findAllLikeName($name) {

        return $this->getEntityManager()
            ->createQuery(
                'SELECT c FROM EveBundle:MarketGroupsEntity c WHERE c.hasTypes = 1 AND c.marketGroupName LIKE :name'
            )->setParameter('name', '%'.$name.'%')->getResult();
    }
}