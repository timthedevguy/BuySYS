<?php
namespace AppBundle\Entity\SDE;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="dgmTypeAttributes")
 */
class DgmTypeAttributesEntity
{
    /**
    * @ORM\Column(name="typeID", type="integer", name="typeID", nullable=false)
    * @ORM\Id
    */
    protected $typeID;

    /**
	 * @ORM\Column(type="integer", name="attributeID")
	 * @ORM\Id
     */
    protected $attributeID;

    /**
     * @ORM\Column(type="integer", name="valueInt", nullable=true)
     */
    protected $valueInt;

    /**
     * @ORM\Column(type="float", name="valueFloat", nullable=true)
     */
    protected $valueFloat;

	/**
	 * @return mixed
	 */
	public function getTypeID()
	{
		return $this->typeID;
	}

	/**
	 * @param mixed $typeID
	 * @return DgmTypeAttributesEntity
	 */
	public function setTypeID($typeID)
	{
		$this->typeID = $typeID;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAttributeID()
	{
		return $this->attributeID;
	}

	/**
	 * @param mixed $attributeID
	 * @return DgmTypeAttributesEntity
	 */
	public function setAttributeID($attributeID)
	{
		$this->attributeID = $attributeID;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getValueInt()
	{
		return $this->valueInt;
	}

	/**
	 * @param mixed $valueInt
	 * @return DgmTypeAttributesEntity
	 */
	public function setValueInt($valueInt)
	{
		$this->valueInt = $valueInt;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getValueFloat()
	{
		return $this->valueFloat;
	}

	/**
	 * @param mixed $valueFloat
	 * @return DgmTypeAttributesEntity
	 */
	public function setValueFloat($valueFloat)
	{
		$this->valueFloat = $valueFloat;

		return $this;
	}
}
