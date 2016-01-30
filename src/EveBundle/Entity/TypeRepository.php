<?php
namespace EveBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TypeRepository extends EntityRepository {

    public function findAllLikeName($name) {
        
        return $this->getEntityManager()
            ->createQuery(
                'SELECT c FROM EveBundle:TypeEntity c WHERE c.typeName LIKE :name'
            )->setParameter('name', '%'.$name.'%')->getResult();
    }
}
