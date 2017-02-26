<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 12/21/16
 * Time: 3:02 PM
 */

namespace AppBundle\Model;


class LineItemComparisonModel
{
    protected $isExactMatch;

    public function setIsExactMatch($isExactMatch)
    {
        $this->isExactMatch = $isExactMatch;

        return $this;
    }

    public function isExactMatch()
    {
        return $this->isExactMatch;
    }

    protected $totalExcess;

    public function setTotalExcess($totalExcess)
    {
        $this->totalExcess = $totalExcess;

        return $this;
    }

    public function getTotalExcess()
    {
        return $this->totalExcess;
    }

    protected $totalMissing;

    public function setTotalMissing($totalMissing)
    {
        $this->totalMissing = $totalMissing;

        return $this;
    }

    public function getTotalMissing()
    {
        return $this->totalMissing;
    }

    protected $excessLineItems = Array();

    public function setExcessLineItems($excessLineItems)
    {
        $this->excessLineItems = $excessLineItems;

        return $this;
    }

    public function getExcessLineItems()
    {
        return $this->excessLineItems;
    }


    protected $missingLineItems = Array();

    public function setMissingLineItems($missingLineItems)
    {
        $this->missingLineItems = $missingLineItems;

        return $this;
    }

    public function getMissingLineItems()
    {
        return $this->missingLineItems;
    }

}