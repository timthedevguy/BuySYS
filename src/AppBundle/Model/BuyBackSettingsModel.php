<?php
namespace AppBundle\Model;

class BuyBackSettingsModel
{
    protected $defaultTax;

    public function setDefaultTax($defaultTax)
    {
        $this->defaultTax = $defaultTax;

        return $this;
    }

    public function getDefaultTax()
    {
        return $this->defaultTax;
    }

    protected $source_id;

    public function setSourceId($source_id)
    {
        $this->source_id = $source_id;

        return $this;
    }

    public function getSourceId()
    {
        return $this->source_id;
    }

    protected $source_type;

    public function setSourceType($source_type)
    {
        $this->source_type = $source_type;

        return $this;
    }

    public function getSourceType()
    {
        return $this->source_type;
    }

    protected $source_stat;

    public function setSourceStat($source_stat)
    {
        $this->source_stat = $source_stat;

        return $this;
    }

    public function getSourceStat()
    {
        return $this->source_stat;
    }

    protected $valueMinerals;

    public function setValueMinerals($valueMinerals)
    {
        $this->valueMinerals = $valueMinerals;

        return $this;
    }

    public function getValueMinerals()
    {
        return $this->valueMinerals;
    }
    
    protected $valueSalvage;
    
    public function setValueSalvage($valueSalvage)
    {
        $this->valueSalvage = $valueSalvage;
    
        return $this;
    }
    
    public function getValueSalvage()
    {
        return $this->valueSalvage;
    }

    protected $oreRefineRate;

    public function setOreRefineRate($oreRefineRate)
    {
        $this->oreRefineRate = $oreRefineRate;

        return $this;
    }

    public function getOreRefineRate()
    {
        return $this->oreRefineRate;
    }

    protected $defaultPublicTax;

    public function setDefaultPublicTax($defaultPublicTax)
    {
        $this->defaultPublicTax = $defaultPublicTax;

        return $this;
    }

    public function getDefaultPublicTax()
    {
        return $this->defaultPublicTax;
    }

    protected $iceRefineRate;

    public function setIceRefineRate($iceRefineRate)
    {
        $this->iceRefineRate = $iceRefineRate;

        return $this;
    }

    public function getIceRefineRate()
    {
        return $this->iceRefineRate;
    }

    protected $salvageRefineRate;

    public function setSalvageRefineRate($salvageRefineRate)
    {
        $this->salvageRefineRate = $salvageRefineRate;

        return $this;
    }

    public function getSalvageRefineRate()
    {
        return $this->salvageRefineRate;
    }

    protected $defaultBuybackActionDeny;

    public function setDefaultBuybackActionDeny($defaultBuybackActionDeny)
    {
        $this->defaultBuybackActionDeny = $defaultBuybackActionDeny;

        return $this;
    }

    public function getDefaultBuybackActionDeny()
    {
        return $this->defaultBuybackActionDeny;
    }
}
