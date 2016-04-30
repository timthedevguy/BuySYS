<?php
namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Common\Collections\ArrayCollection;

class ChapterRepository extends EntityRepository {

    public function findAll()
    {
        return $this->findBy(array(), array('chapterNumber' => 'ASC'));
    }

    public function findAllArray()
    {
        return new ArrayCollection($this->getEntityManager()
            ->createQuery(
                'SELECT c FROM AppBundle:ChapterEntity c ORDER BY c.chapterNumber ASC'
            )->getResult());
    }
}
