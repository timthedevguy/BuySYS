<?php
namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class OperationRepository extends EntityRepository {

    public function findAllUpcomingOrderedByDate() {

        return $this->getEntityManager()
            ->createQuery(
                'SELECT p FROM AppBundle:OperationEntity p WHERE p.opDate > :today ORDER BY p.opDate DESC'
            )->setParameter('today', new \DateTime("now"))->getResult();
    }
}
