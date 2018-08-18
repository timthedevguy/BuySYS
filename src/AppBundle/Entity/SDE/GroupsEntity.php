<?php
namespace AppBundle\Entity\SDE;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SDE\GroupsRepository")
 * @ORM\Table(name="invGroups")
 */
class GroupsEntity
{
    /**
     * @ORM\Column(name="groupID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected $groupID;

    /**
     * @ORM\Column(type="integer", name="categoryID", nullable=true)
     */
    protected $categoryID;

    /**
     * @ORM\Column(type="string", length=100, name="groupName", nullable=true)
     */
    protected $groupName;

    /**
     * @ORM\Column(type="integer", name="iconID", nullable=true)
     */
    protected $iconID;

    /**
     * @ORM\Column(type="boolean", name="useBasePrice", nullable=true)
     */
    protected $useBasePrice;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $anchored;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $anchorable;

    /**
     * @ORM\Column(type="boolean", name="fittableNonSingleton", nullable=true)
     */
    protected $fittableNonSingleton;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $published;

	/**
	 * @return mixed
	 */
	public function getGroupID()
	{
		return $this->groupID;
	}

	/**
	 * @param mixed $groupID
	 * @return GroupsEntity
	 */
	public function setGroupID($groupID)
	{
		$this->groupID = $groupID;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCategoryID()
	{
		return $this->categoryID;
	}

	/**
	 * @param mixed $categoryID
	 * @return GroupsEntity
	 */
	public function setCategoryID($categoryID)
	{
		$this->categoryID = $categoryID;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getGroupName()
	{
		return $this->groupName;
	}

	/**
	 * @param mixed $groupName
	 * @return GroupsEntity
	 */
	public function setGroupName($groupName)
	{
		$this->groupName = $groupName;

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
	 * @return GroupsEntity
	 */
	public function setIconID($iconID)
	{
		$this->iconID = $iconID;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getUseBasePrice()
	{
		return $this->useBasePrice;
	}

	/**
	 * @param mixed $useBasePrice
	 * @return GroupsEntity
	 */
	public function setUseBasePrice($useBasePrice)
	{
		$this->useBasePrice = $useBasePrice;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAnchored()
	{
		return $this->anchored;
	}

	/**
	 * @param mixed $anchored
	 * @return GroupsEntity
	 */
	public function setAnchored($anchored)
	{
		$this->anchored = $anchored;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAnchorable()
	{
		return $this->anchorable;
	}

	/**
	 * @param mixed $anchorable
	 * @return GroupsEntity
	 */
	public function setAnchorable($anchorable)
	{
		$this->anchorable = $anchorable;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getFittableNonSingleton()
	{
		return $this->fittableNonSingleton;
	}

	/**
	 * @param mixed $fittableNonSingleton
	 * @return GroupsEntity
	 */
	public function setFittableNonSingleton($fittableNonSingleton)
	{
		$this->fittableNonSingleton = $fittableNonSingleton;

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
	 * @return GroupsEntity
	 */
	public function setPublished($published)
	{
		$this->published = $published;

		return $this;
	}
}
