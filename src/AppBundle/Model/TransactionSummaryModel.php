<?php
namespace AppBundle\Model;
/**
 * This class takes a list of Transactions and creates a summary
 *
 * Created by PhpStorm.
 * User: a23413h
 * Date: 4/26/17
 * Time: 9:23 AM
 */

class TransactionSummaryModel {

    protected $totalTransactionAccepted;
    protected $totalTransactionPending;
    protected $totalGrossAccepted;
    protected $totalGrossPending;
    protected $totalNetAccepted;
    protected $totalNetPending;
    protected $totalProfitAccepted;
    protected $totalProfitPending;

    /**
     * TransactionSummaryModel constructor.
     *
     * @param mixed $transactions
     */
    public function __construct(&$transactions)
    {
        //loop through provided transactions
        foreach ($transactions as $elementKey => $transaction) {

            if($transaction->getStatus() == "Complete") { //set accepted values
                $this->totalTransactionAccepted++;

                if ($transaction->getType() == "P" || $transaction->getType() == "PS" ) {
                    $this->totalGrossAccepted = $this->totalGrossAccepted + $transaction->getGross();
                    $this->totalNetAccepted = $this->totalNetAccepted + $transaction->getNet();
                }
            } elseif ($transaction->getStatus() == "Pending") { //set pending values
                $this->totalTransactionPending++;

                if ($transaction->getType() == "P" || $transaction->getType() == "PS" ) {
                    $this->totalGrossPending = $this->totalGrossPending + $transaction->getGross();
                    $this->totalNetPending = $this->totalNetPending + $transaction->getNet();
                }
            }
            //ignore Pending or Declined transactions
        }

        //set profits as difference between gross and net
        $this->totalProfitAccepted = $this->totalGrossAccepted - $this->totalNetAccepted;
        $this->totalProfitPending = $this->totalGrossPending - $this->totalNetPending;
    }



    /**
     * @return mixed
     */
    public function getTotalTransactionAccepted()
    {
        return $this->totalTransactionAccepted;
    }

    /**
     * @param mixed $totalTransactionAccepted
     */
    public function setTotalTransactionAccepted($totalTransactionAccepted)
    {
        $this->totalTransactionAccepted = $totalTransactionAccepted;
    }

    /**
     * @return mixed
     */
    public function getTotalTransactionPending()
    {
        return $this->totalTransactionPending;
    }

    /**
     * @param mixed $totalTransactionPending
     */
    public function setTotalTransactionPending($totalTransactionPending)
    {
        $this->totalTransactionPending = $totalTransactionPending;
    }

    /**
     * @return mixed
     */
    public function getTotalGrossAccepted()
    {
        return $this->totalGrossAccepted;
    }

    /**
     * @param mixed $totalGrossAccepted
     */
    public function setTotalGrossAccepted($totalGrossAccepted)
    {
        $this->totalGrossAccepted = $totalGrossAccepted;
    }

    /**
     * @return mixed
     */
    public function getTotalGrossPending()
    {
        return $this->totalGrossPending;
    }

    /**
     * @param mixed $totalGrossPending
     */
    public function setTotalGrossPending($totalGrossPending)
    {
        $this->totalGrossPending = $totalGrossPending;
    }

    /**
     * @return mixed
     */
    public function getTotalNetAccepted()
    {
        return $this->totalNetAccepted;
    }

    /**
     * @param mixed $totalNetAccepted
     */
    public function setTotalNetAccepted($totalNetAccepted)
    {
        $this->totalNetAccepted = $totalNetAccepted;
    }

    /**
     * @return mixed
     */
    public function getTotalNetPending()
    {
        return $this->totalNetPending;
    }

    /**
     * @param mixed $totalNetPending
     */
    public function setTotalNetPending($totalNetPending)
    {
        $this->totalNetPending = $totalNetPending;
    }

    /**
     * @return mixed
     */
    public function getTotalProfitAccepted()
    {
        return $this->totalProfitAccepted;
    }

    /**
     * @param mixed $totalProfitAccepted
     */
    public function setTotalProfitAccepted($totalProfitAccepted)
    {
        $this->totalProfitAccepted = $totalProfitAccepted;
    }

    /**
     * @return mixed
     */
    public function getTotalProfitPending()
    {
        return $this->totalProfitPending;
    }

    /**
     * @param mixed $totalProfitPending
     */
    public function setTotalProfitPending($totalProfitPending)
    {
        $this->totalProfitPending = $totalProfitPending;
    }



}

