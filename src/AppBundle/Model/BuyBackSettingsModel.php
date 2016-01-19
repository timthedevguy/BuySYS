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
}
