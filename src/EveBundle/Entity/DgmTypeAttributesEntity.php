<?php
namespace EveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="dgmTypeAttributes")
 */
class DgmTypeAttributesEntity
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
    * @ORM\Column(name="attributeID", type="integer", nullable=false)
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="NONE")
    */
    protected $attributeID;

    public function setAttributeId($attributeID)
    {
        $this->attributeID = $attributeID;

        return $this;
    }

    public function getAttributeId()
    {
        return $this->attributeID;
    }

    /**
     * @ORM\Column(type="integer")
     */
    protected $valueInt;

    public function setValueInt($valueInt)
    {
        $this->valueInt = $valueInt;

        return $this;
    }

    public function getValueInt()
    {
        return $this->valueInt;
    }

    /**
     * @ORM\Column(type="float")
     */
    protected $valueFloat;

    public function setValueFloat($valueFloat)
    {
        $this->valueFloat = $valueFloat;

        return $this;
    }

    public function getValueFloat()
    {
        return $this->valueFloat;
    }
}
