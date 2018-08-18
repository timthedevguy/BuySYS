<?php
namespace AppBundle\Entity\SDE;

use Doctrine\ORM\EntityRepository;

class TypeRepository extends EntityRepository {

    public function findAllLikeName($name)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT c FROM AppBundle:TypeEntity c WHERE c.typeName LIKE :name'
            )->setParameter('name', '%'.$name.'%')->getResult();
    }

    public function findNamesForTypes($typeIds)
    {
        $results =  $this->getEntityManager()
            ->createQuery(
                'SELECT c.typeID,c.typeName FROM AppBundle:TypeEntity c WHERE c.typeID IN (:typeids)'
            )->setParameter('typeids', $typeIds)->getResult();

        $properResults = array();

        foreach($results as $result)
        {
            $properResults[$result['typeID']] = $result['typeName'];
        }

        return $properResults;
    }
}
