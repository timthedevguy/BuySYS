<?php
namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class NewsRepository extends EntityRepository {

    public function findAllOrderedByDate() {

        return $this->getEntityManager()
            ->createQuery(
                'SELECT p FROM AppBundle:NewsEntity p ORDER BY p.createdOn DESC'
            )
            ->getResult();
    }

    public function findAllAfterDate($date) {

        return $this->getEntityManager()
            ->createQuery(
                'SELECT p FROM AppBundle:NewsEntity p WHERE p.createdOn > :target'
            )->setParameter('target', $date)->getResult();
    }
}
