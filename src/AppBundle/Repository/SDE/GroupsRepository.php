<?php
namespace AppBundle\Repository\SDE;

use Doctrine\ORM\EntityRepository;

class GroupsRepository extends EntityRepository {

    public function findAllLikeName($name) {

        return $this->getEntityManager()
            ->createQuery(
                'SELECT c FROM AppBundle:SDE\GroupsEntity c WHERE c.groupName LIKE :name'
            )->setParameter('name', '%'.$name.'%')->getResult();
    }
}