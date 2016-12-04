<?php
namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\RuleEntity;

class RuleRepository extends EntityRepository
{
    public function getNextSort()
    {
        $item = $this->findOneBy(array(), array('sort' => 'DESC'));

        if($item == null) {

            return 1;
        }

        return ($item->getSort() + 1);
    }
}