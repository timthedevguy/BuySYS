<?php
namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TransactionRepository extends EntityRepository {
	
	private $types = ["P", "PS", "S", "SRP"];

    public function findValidTransactionsByTypesOrderedByDate($types = null, $limit = 5000) {

        return $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.status <> :excludeStatus ORDER BY t.created DESC'
            )->setParameter('types', $types)->setParameter('excludeStatus', 'Estimate')->setMaxResults($limit)->getResult();
    }

    public function findAcceptedTransactionTotals() {

        return $this->getEntityManager()
            ->createQuery(
                'SELECT count(t) as totalTransactionsAccepted, sum(t.gross) as totalGrossAccepted, sum(t.net) as totalNetAccepted
                      FROM AppBundle:TransactionEntity t WHERE t.status = :status'
            )->setParameter('status', 'Complete')->getOneOrNullResult();
    }

    public function countOpenTransactionsByTypes($types = null) {

        return $this->getEntityManager()
            ->createQuery(
                'SELECT count(t) as openTransactions
                      FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.status = :status'
            )->setParameter('types', $types)->setParameter('status', 'Pending')->getSingleScalarResult();
    }

    public function findAllVisibleByUser($user) {
		
		trigger_error("TransactionRepository::findAllVisibleByUser is deprecated. Please use a more specific method here.");

        return $this->findAllByUserTypesAndExcludeStatus($user);
    }

    public function findCountByUser($user, $types = null) {

		if(empty($types))
			$types = $this->types;
		
        return $this->findCountByUserAndTypes($user);
    }

    public function findAllByUserAndTypes($user, $types = null) {

		if(empty($types))
			$types = $this->types;
		
        return $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.user = :user'
            )->setParameter('user', $user)->setParameter('types', $types)->getResult();
    }

    public function findCountByUserAndTypes($user, $types = null) {

		if(empty($types))
			$types = $this->types;
		
        return $this->getEntityManager()
            ->createQuery(
                'SELECT COUNT(t.id) FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.user = :user'
            )->setParameter('user', $user)->setParameter('types', $types)->getSingleScalarResult();
    }

    public function findAllByUserTypesAndStatus($user, $types = null, $status = "Pending") {

		if(empty($types))
			$types = $this->types;
		
        return $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.user = :user AND t.status = :includeStatus'
            )->setParameter('user', $user)->setParameter('types', $types)->setParameter('includeStatus', $status)->getResult();
    }

    public function findCountByUserTypesAndStatus($user, $types = null, $status = "Pending") {

		if(empty($types))
			$types = $this->types;
		
        return $this->getEntityManager()
            ->createQuery(
                'SELECT COUNT(t.id) FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.user = :user AND t.status = :includeStatus'
            )->setParameter('user', $user)->setParameter('types', $types)->setParameter('includeStatus', $status)->getSingleScalarResult();
    }

    public function findAllByUserTypesAndExcludeStatus($user, $types = null, $status = "Estimate") {

		if(empty($types))
			$types = $this->types;
		
        return $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.user = :user AND t.status <> :excludeStatus'
            )->setParameter('user', $user)->setParameter('types', $types)->setParameter('excludeStatus', $status)->getResult();
    }

    public function findCountByUserTypesAndExcludeStatus($user, $types = null, $status = "Estimate") {

		if(empty($types))
			$types = $this->types;
		
        return $this->getEntityManager()
            ->createQuery(
                'SELECT COUNT(t.id) FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.user = :user AND t.status <> :excludeStatus'
            )->setParameter('user', $user)->setParameter('types', $types)->setParameter('excludeStatus', $status)->getSingleScalarResult();
    }
}
