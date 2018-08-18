<?php
namespace AppBundle\Repository;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\RuleEntity;

class RuleRepository extends EntityRepository
{
    /**
     * Gets the next rule sort id in the list, this helps keep rules in numerical order
     * @return int
     */
    public function getNextSort(string $ruleType)
    {
        $item = $this->findOneBy(array('ruleType' => $ruleType), array('sort' => 'DESC'));

        if ($item == null)
        {
            return 1;
        }

        return ($item->getSort() + 1);
    }

    public function findAllSortedBySort(string $ruleType)
    {
        return $this->findBy(array('ruleType' => $ruleType), array('sort' => 'ASC'));
    }

    public function findAllAfter($sort, $ruleType)
    {
        $query = $this->createQueryBuilder('r')
            ->where('r.sort > :sort')
            ->andWhere('r.ruleType = :ruleType')
            ->setParameter('sort', $sort)
            ->setParameter('ruleType', $ruleType)
            ->orderBy('r.sort', 'ASC')
            ->getQuery();

        return $query->getResult();
    }

    public function findAllBefore($sort, $ruleType)
    {
        $query = $this->createQueryBuilder('r')
            ->where('r.sort < :sort')
            ->andWhere('r.ruleType = :ruleType')
            ->setParameter('sort', $sort)
            ->setParameter('ruleType', $ruleType)
            ->orderBy('r.sort', 'ASC')
            ->getQuery();

        return $query->getResult();
    }

    public function findAllByTypeAndGroup($type, $group, $marketgroup)
    {

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

    public function findAllForTypeIds(array $typeids)
    {
        $types = $this->getEntityManager()
            ->createQuery(
                'SELECT 
                        invTypes.typeID, 
                        invTypes.groupID,
                        invTypes.marketGroupID,
                        (SELECT valueInt FROM dgmTypeAttributes WHERE dgmTypeAttributes.typeID = invTypes.typeID AND dgmTypeAttributes.attributeID = 790) as refineSkill
                     FROM
                        invTypes
                     WHERE
                        invTypes.typeID
                     IN
                        :types;'
            )->setParameter('types', $typeids)->getResult();

        return $types;
    }
}
