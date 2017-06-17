<?php
namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TransactionRepository extends EntityRepository {

    public function findValidTransactionsOrderedByDate($limit = 5000) {

        return $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM AppBundle:TransactionEntity t WHERE t.status <> :excludeStatus ORDER BY t.created DESC'
            )->setParameter('excludeStatus', 'Estimate')->setMaxResults($limit)->getResult();
    }

    public function findAcceptedTransactionTotals() {

        return $this->getEntityManager()
            ->createQuery(
                'SELECT count(t) as totalTransactionsAccepted, sum(t.gross) as totalGrossAccepted, sum(t.net) as totalNetAccepted
                      FROM AppBundle:TransactionEntity t WHERE t.status = :status'
            )->setParameter('status', 'Complete')->getOneOrNullResult();
    }

    public function countOpenPurchaseTransactions() {

        return $this->getEntityManager()
            ->createQuery(
                'SELECT count(t) as openPurchaseTransactions
                      FROM AppBundle:TransactionEntity t WHERE t.status = :status'
            )->setParameter('status', 'Pending')->getSingleScalarResult();
    }

    public function findAllVisibleByUser($user) {

        /*$query = $this->getEntityManager()->createQueryBuilder('t')
            ->where('t.user = :user')
            ->andWhere('t.is_complete = 0')
            ->andWhere('t.type = :type')
            ->andWhere('t.status = :status')
            ->orderBy('t.created', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('type', "P")
            ->setParameter('status', "Pending")
            ->getQuery();

        return $query->getResult();*/

        return $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM AppBundle:TransactionEntity t WHERE t.type IN (:types) AND t.user = :user AND t.status <> :excludeStatus'
            )->setParameter('user', $user)->setParameter('types', Array('P', 'PS'))->setParameter('excludeStatus', 'Estimate')->getResult();
    }
}
