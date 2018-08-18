<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class EstimateEntityRepository extends EntityRepository {

	public function deleteByUser($userid)
	{
		return $this->getEntityManager()
			->createQuery(
				'DELETE FROM AppBundle:EstimateEntity e WHERE e.userId = :user'
			)->setParameter('user', $userid)->execute();
	}
}