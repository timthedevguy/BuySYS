<?php
namespace AppBundle\Entity\SDE;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SDE\TypeRepository")
 * @ORM\Table(name="invTypes")
 */
class TypeEntity
{
    /**
    * @ORM\Column(name="typeID", type="integer", nullable=false)
    * @ORM\Id
	* @ORM\GeneratedValue(strategy="NONE")
    */
    protected $typeID;

    /**
     * @ORM\Column(type="integer", name="groupID", nullable=true)
     */
    protected $groupID;

    /**
     * @ORM\Column(type="string", length=100, name="typeName", nullable=true)
     */
    protected $typeName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $mass;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $volume;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $capacity;

    /**
     * @ORM\Column(type="integer", name="portionSize", nullable=true)
     */
    protected $portionSize;

    /**
     * @ORM\Column(type="integer", name="raceID", nullable=true)
     */
    protected $raceID;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=4, name="basePrice", nullable=true)
     */
    protected $basePrice;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $published;

    /**
     * @ORM\Column(type="integer", name="marketGroupID", nullable=true)
     */
    protected $marketGroupID;

    /**
     * @ORM\Column(type="integer", name="iconID", nullable=true)
     */
    protected $iconID;

    /**
     * @ORM\Column(type="integer", name="soundID", nullable=true)
     */
    protected $soundID;

    /**
     * @ORM\Column(type="integer", name="graphicID", nullable=true)
     */
    protected $graphicID;

	/**
	 * @return mixed
	 */
	public function getTypeID()
	{
		return $this->typeID;
	}

	/**
	 * @param mixed $typeID
	 * @return TypeEntity
	 */
	public function setTypeID($typeID)
	{
		$this->typeID = $typeID;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getGroupID()
	{
		return $this->groupID;
	}

	/**
	 * @param mixed $groupID
	 * @return TypeEntity
	 */
	public function setGroupID($groupID)
	{
		$this->groupID = $groupID;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTypeName()
	{
		return $this->typeName;
	}

	/**
	 * @param mixed $typeName
	 * @return TypeEntity
	 */
	public function setTypeName($typeName)
	{
		$this->typeName = $typeName;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param mixed $description
	 * @return TypeEntity
	 */
	public function setDescription($description)
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMass()
	{
		return $this->mass;
	}

	/**
	 * @param mixed $mass
	 * @return TypeEntity
	 */
	public function setMass($mass)
	{
		$this->mass = $mass;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getVolume()
	{
		return $this->volume;
	}

	/**
	 * @param mixed $volume
	 * @return TypeEntity
	 */
	public function setVolume($volume)
	{
		$this->volume = $volume;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCapacity()
	{
		return $this->capacity;
	}

	/**
	 * @param mixed $capacity
	 * @return TypeEntity
	 */
	public function setCapacity($capacity)
	{
		$this->capacity = $capacity;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPortionSize()
	{
		return $this->portionSize;
	}

	/**
	 * @param mixed $portionSize
	 * @return TypeEntity
	 */
	public function setPortionSize($portionSize)
	{
		$this->portionSize = $portionSize;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRaceID()
	{
		return $this->raceID;
	}

	/**
	 * @param mixed $raceID
	 * @return TypeEntity
	 */
	public function setRaceID($raceID)
	{
		$this->raceID = $raceID;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBasePrice()
	{
		return $this->basePrice;
	}

	/**
	 * @param mixed $basePrice
	 * @return TypeEntity
	 */
	public function setBasePrice($basePrice)
	{
		$this->basePrice = $basePrice;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPublished()
	{
		return $this->published;
	}

	/**
	 * @param mixed $published
	 * @return TypeEntity
	 */
	public function setPublished($published)
	{
		$this->published = $published;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMarketGroupID()
	{
		return $this->marketGroupID;
	}

	/**
	 * @param mixed $marketGroupID
	 * @return TypeEntity
	 */
	public function setMarketGroupID($marketGroupID)
	{
		$this->marketGroupID = $marketGroupID;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getIconID()
	{
		return $this->iconID;
	}

	/**
	 * @param mixed $iconID
	 * @return TypeEntity
	 */
	public function setIconID($iconID)
	{
		$this->iconID = $iconID;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSoundID()
	{
		return $this->soundID;
	}

	/**
	 * @param mixed $soundID
	 * @return TypeEntity
	 */
	public function setSoundID($soundID)
	{
		$this->soundID = $soundID;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getGraphicID()
	{
		return $this->graphicID;
	}

	/**
	 * @param mixed $graphicID
	 * @return TypeEntity
	 */
	public function setGraphicID($graphicID)
	{
		$this->graphicID = $graphicID;

		return $this;
	}
}
