<?php
namespace AppBundle\Entity\SDE;

use Doctrine\ORM\EntityRepository;

class GroupsRepository extends EntityRepository {

    public function findAllLikeName($name) {

        return $this->getEntityManager()
            ->createQuery(
                'SELECT c FROM AppBundle:GroupsEntity c WHERE c.groupName LIKE :name'
            )->setParameter('name', '%'.$name.'%')->getResult();
    }
}