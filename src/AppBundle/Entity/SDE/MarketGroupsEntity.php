<?php
namespace AppBundle\Entity\SDE;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SDE\MarketGroupsRepository")
 * @ORM\Table(name="invMarketGroups")
 */
class MarketGroupsEntity
{
    /**
    * @ORM\Column(name="marketGroupID", type="integer", nullable=false)
    * @ORM\Id
    */
    protected $marketGroupID;

    /**
     * @ORM\Column(type="integer", name="parentGroupID", nullable=true)
     */
    protected $parentGroupID;

    /**
     * @ORM\Column(type="string", length=100, name="marketGroupName", nullable=true)
     */
    protected $marketGroupName;

    /**
     * @ORM\Column(type="string", length=3000, nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="integer", name="iconID", nullable=true)
     */
    protected $iconID;

    /**
     * @ORM\Column(type="boolean", name="hasTypes", nullable=true)
     */
    protected $hasTypes;

	/**
	 * @return mixed
	 */
	public function getMarketGroupID()
	{
		return $this->marketGroupID;
	}

	/**
	 * @param mixed $marketGroupID
	 * @return MarketGroupsEntity
	 */
	public function setMarketGroupID($marketGroupID)
	{
		$this->marketGroupID = $marketGroupID;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getParentGroupID()
	{
		return $this->parentGroupID;
	}

	/**
	 * @param mixed $parentGroupID
	 * @return MarketGroupsEntity
	 */
	public function setParentGroupID($parentGroupID)
	{
		$this->parentGroupID = $parentGroupID;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMarketGroupName()
	{
		return $this->marketGroupName;
	}

	/**
	 * @param mixed $marketGroupName
	 * @return MarketGroupsEntity
	 */
	public function setMarketGroupName($marketGroupName)
	{
		$this->marketGroupName = $marketGroupName;

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
	 * @return MarketGroupsEntity
	 */
	public function setDescription($description)
	{
		$this->description = $description;

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
	 * @return MarketGroupsEntity
	 */
	public function setIconID($iconID)
	{
		$this->iconID = $iconID;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getHasTypes()
	{
		return $this->hasTypes;
	}

	/**
	 * @param mixed $hasTypes
	 * @return MarketGroupsEntity
	 */
	public function setHasTypes($hasTypes)
	{
		$this->hasTypes = $hasTypes;

		return $this;
	}
}
