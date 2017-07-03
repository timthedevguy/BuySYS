<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="insurancePrices")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\InsurancesRepository")
 */
class InsurancesEntity
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }
	
    /**
     * @ORM\Column(type="integer")
     */
    protected $typeID;

    public function setTypeID($typeID)
    {
        $this->typeID = $typeID;
        return $this;
    }

    public function getTypeID()
    {
        return $this->typeID;
    }

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $insuranceLevel;

    public function setInsuranceLevel($insuranceLevel)
    {
        $this->insuranceLevel = $insuranceLevel;
        return $this;
    }

    public function getInsuranceLevel()
    {
        return $this->insuranceLevel;
    }

    /**
     * @ORM\Column(type="decimal", precision=19, scale=2)
     */
    protected $insurancePayout;

    public function setInsurancePayout($insurancePayout)
    {
        $this->insurancePayout = $insurancePayout;
        return $this;
    }

    public function getInsurancePayout()
    {
        return $this->insurancePayout;
    }

    /**
     * @ORM\Column(type="decimal", precision=19, scale=2)
     */
    protected $insuranceCost;

    public function setInsuranceCost($insuranceCost)
    {
        $this->insuranceCost = $insuranceCost;
        return $this;
    }

    public function getInsuranceCost()
    {
        return $this->insuranceCost;
    }
}
