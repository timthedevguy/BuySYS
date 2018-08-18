<?php
namespace AppBundle\Entity\SDE;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\SDE\GroupsRepository")
 * @ORM\Table(name="invGroups")
 */
class GroupsEntity
{
    /**
     * @ORM\Column(name="groupID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $groupID;

    public function setGroupID($groupID)
    {
        $this->groupID = $groupID;

        return $this;
    }

    public function getGroupID()
    {
        return $this->groupID;
    }

    /**
     * @ORM\Column(type="integer")
     */
    protected $categoryID;

    public function setCategoryID($categoryID)
    {
        $this->categoryID = $categoryID;

        return $this;
    }

    public function getCategoryID()
    {
        return $this->categoryID;
    }

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $groupName;

    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;

        return $this;
    }

    public function getGroupName()
    {
        return $this->groupName;
    }

    /**
     * @ORM\Column(type="integer")
     */
    protected $iconID;

    public function setIconID($iconID)
    {
        $this->iconID = $iconID;

        return $this;
    }

    public function getIconID()
    {
        return $this->iconID;
    }

    /**
     * @ORM\Column(type="boolean")
     */
    protected $useBasePrice;

    public function setUseBasePrice($useBasePrice)
    {
        $this->useBasePrice = $useBasePrice;

        return $this;
    }

    public function getUseBasePrice()
    {
        return $this->useBasePrice;
    }

    /**
     * @ORM\Column(type="boolean")
     */
    protected $anchored;

    public function setAnchored($anchored)
    {
        $this->anchored = $anchored;

        return $this;
    }

    public function getAnchored()
    {
        return $this->anchored;
    }

    /**
     * @ORM\Column(type="boolean")
     */
    protected $anchorable;

    public function setAnchorable($anchorable)
    {
        $this->anchorable = $anchorable;

        return $this;
    }

    public function getAnchorable()
    {
        return $this->anchorable;
    }

    /**
     * @ORM\Column(type="boolean")
     */
    protected $fittableNonSingleton;

    public function setFittableNonSingleton($fittableNonSingleton)
    {
        $this->fittableNonSingleton = $fittableNonSingleton;

        return $this;
    }

    public function getFittableNonSingleton()
    {
        return $this->fittableNonSingleton;
    }

    /**
     * @ORM\Column(type="boolean")
     */
    protected $published;

    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    public function getPublished()
    {
        return $this->published;
    }
}
