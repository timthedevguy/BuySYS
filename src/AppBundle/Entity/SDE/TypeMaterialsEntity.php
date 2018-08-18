<?php
namespace AppBundle\Entity\SDE;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="invTypeMaterials")
 */
class TypeMaterialsEntity
{
    /**
    * @ORM\Column(name="typeID", type="integer", nullable=false)
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="NONE")
    */
    protected $typeID;

    public function setTypeId($typeID)
    {
        $this->typeID = $typeID;

        return $this;
    }

    public function getTypeId()
    {
        return $this->typeID;
    }

    /**
    * @ORM\Column(name="materialTypeID", type="integer", nullable=false)
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="NONE")
    */
    protected $materialTypeID;

    public function setMaterialTypeId($materialTypeID)
    {
        $this->materialTypeID = $materialTypeID;

        return $this;
    }

    public function getMaterialTypeId()
    {
        return $this->materialTypeID;
    }

    /**
     * @ORM\Column(type="integer")
     */
    protected $quantity;

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }
}
