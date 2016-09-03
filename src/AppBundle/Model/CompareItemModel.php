<?php
namespace AppBundle\Model;

class CompareItemModel {

    protected $typeId;

    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    public function getTypeId()
    {
        return $this->typeId;
    }

    protected $Name;

    public function setName($Name)
    {
        $this->Name = $Name;

        return $this;
    }

    public function getName()
    {
        return $this->Name;
    }

    protected $unitPrice;

    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    protected $refinePrice;

    public function setRefinePrice($refinePrice)
    {
        $this->refinePrice = $refinePrice;

        return $this;
    }

    public function getRefinePrice()
    {
        return $this->refinePrice;
    }

    protected $decision;

    public function setDecision($decision)
    {
        $this->decision = $decision;

        return $this;
    }

    public function getDecision()
    {
        return $this->decision;
    }
}