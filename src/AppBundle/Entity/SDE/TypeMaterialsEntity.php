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
    */
    protected $typeID;

    /**
     * @ORM\Column(type="integer", name="materialTypeID")
	 * @ORM\Id
     */
    protected $materialTypeID;

    /**
     * @ORM\Column(type="integer")
     */
    protected $quantity;

	/**
	 * @return mixed
	 */
	public function getTypeID()
	{
		return $this->typeID;
	}

	/**
	 * @param mixed $typeID
	 * @return TypeMaterialsEntity
	 */
	public function setTypeID($typeID)
	{
		$this->typeID = $typeID;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMaterialTypeID()
	{
		return $this->materialTypeID;
	}

	/**
	 * @param mixed $materialTypeID
	 * @return TypeMaterialsEntity
	 */
	public function setMaterialTypeID($materialTypeID)
	{
		$this->materialTypeID = $materialTypeID;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}

	/**
	 * @param mixed $quantity
	 * @return TypeMaterialsEntity
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;

		return $this;
	}
}
