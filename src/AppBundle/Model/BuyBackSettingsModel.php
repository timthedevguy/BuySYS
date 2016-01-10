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
}
