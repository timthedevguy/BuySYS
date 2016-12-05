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

    public function findAllByTypeAndGroup($type, $group, $marketgroup) {

        /*$query = $this->createQueryBuilder('r')
            ->where('r.targetId = :typeid AND r.target = :typegroup')
            ->where('r.targetId = :marketgroupid AND r.target = :groupgroup')
            ->setParameter('typeid', $type)
            ->setParameter('typegroup', 'item')
            ->setParameter('marketgroupid', $group)
            ->setParameter('groupgroup', 'group')
            ->orderBy('r.sort', 'ASC')
            ->getQuery();*/

        $query = $this->createQueryBuilder('r');
        $query = $this->createQueryBuilder('r')
            ->where($query->expr()->orX(
                $query->expr()->andX(
                    $query->expr()->eq('r.target', ':typegroup'),
                    $query->expr()->eq('r.targetId', ':typeid')
                ),
                $query->expr()->andX(
                    $query->expr()->eq('r.target', ':marketgroup'),
                    $query->expr()->eq('r.targetId', ':marketgroupid')
                ),
                $query->expr()->andX(
                    $query->expr()->eq('r.target', ':groupgroup'),
                    $query->expr()->eq('r.targetId', ':groupid')
                )
            ))
            ->setParameter('typeid', $type)
            ->setParameter('typegroup', 'type')
            ->setParameter('marketgroupid', $marketgroup)
            ->setParameter('marketgroup', 'marketgroup')
            ->setParameter('groupid', $group)
            ->setParameter('groupgroup', 'group')
            ->orderBy('r.sort', 'ASC')
            ->getQuery();

        return $query->getResult();
    }
}