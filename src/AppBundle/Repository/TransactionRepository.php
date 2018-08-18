<?php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TransactionRepository extends EntityRepository {
	
	private $types = ["P", "S", "SRP"];

	/* All */
    public function findAll() {
		
        return $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM AppBundle:TransactionEntity t WHERE t.type IN (:types)'
            )->getResult();
    }
	
    public function findCount() {
		
        return $this->getEntityManager()
            ->createQuery(
                'SELECT COUNT(t.id) FROM AppBundle:TransactionEntity t'
            )->getSingleScalarResult();
    }
	/* END All */
	
	/* ID */
    public function findByOrderID($orderId) {
		
        return $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM AppBundle:TransactionEntity t WHERE t.orderId = :orderId'
            )->setParameter('orderId', $orderId)->getOneOrNullResult();
    }
	/* END ID */
	
	/* Types */
    public function findAllByTypes($types = null) {

        if(empty($types)) {$types = $this->types;}
		
        return $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM AppBundle:TransactionEntity t WHERE t.type IN (:types)'
            )->setParameter('types', $types)->getResult();
    }
	
    public function findCountByTypes($types = null) {

        if(empty($types)) {$types = $this->types;}
		
        return $this->getEntityManager()
            ->createQuery(
                'SELECT COUNT(t.id) FROM AppBundle:TransactionEntity t WHERE t.type IN (:types)'
            )->setParameter('types', $types)->getSingleScalarResult();
    }
	/* END Types */

	/* Types And Statuses */
	public function findAllByTypesAndStatus($types = null, $status = "Pending") {

		if(empty($types)) {$types = $this->types;}
		
        return $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.status = :includeStatus'
            )->setParameter('types', $types)->setParameter('includeStatus', $status)->getResult();
    }
	
	public function findCountByTypesAndStatus($types = null, $status = "Pending") {

        if(empty($types)) {$types = $this->types;}
		
        return $this->getEntityManager()
            ->createQuery(
                'SELECT count(t) as openTransactions
                      FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.status = :status'
            )->setParameter('types', $types)->setParameter('status', $status)->getSingleScalarResult();
    }
	/* END Types And Statuses */
	
    /* Statistics */
    public function findTransactionTotalsByTypesAndStatus($types = null, $status = "Pending") {

        if(empty($types)) {$types = $this->types;}

        return $this->getEntityManager()
            ->createQuery(
                'SELECT count(t) as totalTransactionsAccepted, sum(t.gross) as totalGrossAccepted, sum(t.net) as totalNetAccepted
                      FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.status = :status'
            )->setParameter('types', $types)->setParameter('status', $status)->getOneOrNullResult();
    }
	public function findCountByUser($user, $types = null) {

        if(empty($types)) {$types = $this->types;}
		
        return $this->findCountByUserAndTypes($user);
    }
	public function findTotalsByTypesAndStatus($types = null, $status = "Pending", $limit = 5000) {

        if(empty($types)) {$types = $this->types;}

        return $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.status <> :status ORDER BY t.created DESC'
            )->setParameter('types', $types)->setParameter('status', $status)->setMaxResults($limit)->getResult();
    }
	public function findTotalsByTypesAndExcludeStatus($types = null, $excludeStatus = "Pending", $limit = 5000) {

        if(empty($types)) {$types = $this->types;}

        return $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.status <> :excludeStatus ORDER BY t.created DESC'
            )->setParameter('types', $types)->setParameter('excludeStatus', $excludeStatus)->setMaxResults($limit)->getResult();
    }
	/* END Statistics */    

	/* User and Types */
    public function findAllByUserAndTypes($user, $types = null) {

        if(empty($types)) {$types = $this->types;}
		
        return $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.user = :user'
            )->setParameter('user', $user)->setParameter('types', $types)->getResult();
    }

    public function findCountByUserAndTypes($user, $types = null) {

        if(empty($types)) {$types = $this->types;}
		
        return $this->getEntityManager()
            ->createQuery(
                'SELECT COUNT(t.id) FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.user = :user'
            )->setParameter('user', $user)->setParameter('types', $types)->getSingleScalarResult();
    }
	/* END User and Types */

	/* User, Types And Status */
    public function findAllByUserTypesAndStatus($user, $types = null, $status = "Pending") {

        if(empty($types)) {$types = $this->types;}
		
        return $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.user = :user AND t.status = :includeStatus'
            )->setParameter('user', $user)->setParameter('types', $types)->setParameter('includeStatus', $status)->getResult();
    }

    public function findCountByUserTypesAndStatus($user, $types = null, $status = "Pending") {

        if(empty($types)) {$types = $this->types;}
		
        return $this->getEntityManager()
            ->createQuery(
                'SELECT COUNT(t.id) FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.user = :user AND t.status = :includeStatus'
            )->setParameter('user', $user)->setParameter('types', $types)->setParameter('includeStatus', $status)->getSingleScalarResult();
    }
	/* END User, Types And Status */

	/* User, Types And Exclude Status */
    public function findAllByUserTypesAndExcludeStatus($user, $types = null, $status = "Estimate") {

        if(empty($types)) {$types = $this->types;}
		
        return $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.user = :user AND t.status <> :excludeStatus'
            )->setParameter('user', $user)->setParameter('types', $types)->setParameter('excludeStatus', $status)->getResult();
    }

    public function findCountByUserTypesAndExcludeStatus($user, $types = null, $status = "Estimate") {

        if(empty($types)) {$types = $this->types;}
		
        return $this->getEntityManager()
            ->createQuery(
                'SELECT COUNT(t.id) FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.user = :user AND t.status <> :excludeStatus'
            )->setParameter('user', $user)->setParameter('types', $types)->setParameter('excludeStatus', $status)->getSingleScalarResult();
    }
	/* END User, Types And Exclude Status */
}
