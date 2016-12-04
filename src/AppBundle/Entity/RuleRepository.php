<?php
namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\RuleEntity;

class RuleRepository extends EntityRepository
{
    /**
     * Gets the next rule sort id in the list, this helps keep rules in numerical order
     * @return int
     */
    public function getNextSort() {

        $item = $this->findOneBy(array(), array('sort' => 'DESC'));

        if($item == null) {

            return 1;
        }

        return ($item->getSort() + 1);
    }

    public function findAllSortedBySort() {

        return $this->findBy(array(), array('sort' => 'ASC'));
    }

    public function findAllAfter($sort) {

        $query = $this->createQueryBuilder('r')
            ->where('r.sort > :sort')
            ->setParameter('sort', $sort)
            ->orderBy('r.sort', 'ASC')
            ->getQuery();

        return $query->getResult();
    }

    public function findAllBefore($sort) {

        $query = $this->createQueryBuilder('r')
            ->where('r.sort < :sort')
            ->setParameter('sort', $sort)
            ->orderBy('r.sort', 'ASC')
            ->getQuery();

        return $query->getResult();
    }
}