<?php
namespace AppBundle\Model;

class OreReviewModel {

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
    
    protected $name;

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    protected $color;

    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    public function getColor()
    {
        return $this->color;
    }

    protected $iskPer;

    public function setIskPer($iskPer)
    {
        $this->iskPer = $iskPer;

        return $this;
    }

    public function getIskPer()
    {
        return $this->iskPer;
    }

    protected $iskPerM;

    public function setIskPerM($iskPerM)
    {


        $this->iskPerM = $iskPerM;

        return $this;
    }

    public function getIskPerM()
    {
        return $this->iskPerM;
    }

    protected $volume;

    public function setVolume($volume)
    {
        $this->volume = $volume;

        return $this;
    }

    public function getVolume()
    {
        return $this->volume;
    }

    protected $canUnits;

    public function setCanUnits($canUnits)
    {
        $this->canUnits = $canUnits;

        return $this;
    }

    public function getCanUnits()
    {
        return $this->canUnits;
    }

    protected $canPrice;

    public function setCanPrice($canPrice)
    {
        $this->canPrice = $canPrice;

        return $this;
    }

    public function getCanPrice()
    {
        return $this->canPrice;
    }
}
