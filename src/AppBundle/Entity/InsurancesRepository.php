<?php
namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class InsurancesRepository extends EntityRepository {
	
    public function getInsuranceDataByTypeIDAndLevel($typeID, $insuranceLevel = "Platinum") {

        return $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM AppBundle:InsurancesEntity t WHERE t.typeID = :typeID AND t.insuranceLevel = :level'
            )->setParameter('typeID', $typeID)->setParameter('level', $insuranceLevel)->getResult()[0];
			
    }
}