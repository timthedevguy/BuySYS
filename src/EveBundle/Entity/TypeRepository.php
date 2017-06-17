<?php
namespace EveBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TypeRepository extends EntityRepository {

    public function findAllLikeName($name)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT c FROM EveBundle:TypeEntity c WHERE c.typeName LIKE :name'
            )->setParameter('name', '%'.$name.'%')->getResult();
    }

    public function findNamesForTypes($typeIds)
    {
        $results =  $this->getEntityManager()
            ->createQuery(
                'SELECT c.typeID,c.typeName FROM EveBundle:TypeEntity c WHERE c.typeID IN (:typeids)'
            )->setParameter('typeids', $typeIds)->getResult();

        $properResults = array();

        foreach($results as $result)
        {
            $properResults[$result['typeID']] = $result['typeName'];
        }

        return $properResults;
    }
}
