<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 5/18/17
 * Time: 10:58 AM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ContactRepository extends EntityRepository
{

    public function getContactSummary() {

        return $this->getEntityManager()
            ->createQuery(
                'SELECT count(c.contactId) as contactCount, min(c.lastUpdatedDate) as lastUpdated, c.contactLevel
                      FROM AppBundle:ContactEntity c 
                      GROUP BY c.contactLevel'
            )->getResult();
    }

    public function getExistingContact($characterID, $corporationID, $allianceID) {

        $ids = Array($characterID, $corporationID, $allianceID);

        return $this->getEntityManager()
            ->createQuery(
                'SELECT c
                      FROM AppBundle:ContactEntity c 
                      WHERE c.contactId IN (:ids)'
            )->setParameter('ids', $ids)->getResult();
    }

    public function getContactCount() {

        return $this->getEntityManager()
            ->createQuery(
                'SELECT count(c) as contactCount FROM AppBundle:ContactEntity c'
            )->getSingleScalarResult();
    }

    public function deleteAll() {

        return $this->getEntityManager()
            ->createQuery(
                'DELETE FROM AppBundle:ContactEntity'
            )->execute();
    }
}