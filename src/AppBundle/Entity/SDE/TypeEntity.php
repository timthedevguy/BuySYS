<?php
namespace AppBundle\Entity\SDE;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\SDE\TypeRepository")
 * @ORM\Table(name="invTypes")
 */
class TypeEntity
{
    /**
    * @ORM\Column(name="typeID", type="integer", nullable=false)
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
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
     * @ORM\Column(type="integer", name="groupID")
     */
    protected $groupID;

    public function setGroupId($groupID)
    {
        $this->groupID = $groupID;

        return $this;
    }

    public function getGroupId()
    {
        return $this->groupID;
    }

    /**
     * @ORM\Column(type="string", length=100, name="typeName")
     */
    protected $typeName;

    public function setTypeName($typeName)
    {
        $this->typeName = $typeName;

        return $this;
    }

    public function getTypeName()
    {
        return $this->typeName;
    }

    /**
     * @ORM\Column(type="text")
     */
    protected $description;

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @ORM\Column(type="float")
     */
    protected $mass;

    public function setMass($mass)
    {
        $this->mass = $mass;

        return $this;
    }

    public function getMass()
    {
        return $this->mass;
    }

    /**
     * @ORM\Column(type="float")
     */
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

    /**
     * @ORM\Column(type="float")
     */
    protected $capacity;

    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * @ORM\Column(type="integer", name="portionSize")
     */
    protected $portionSize;

    public function setPortionSize($portionSize)
    {
        $this->portionSize = $portionSize;

        return $this;
    }

    public function getPortionSize()
    {
        return $this->portionSize;
    }

    /**
     * @ORM\Column(type="smallint", name="raceID")
     */
    protected $raceID;

    public function setRaceId($raceID)
    {
        $this->raceID = $raceID;

        return $this;
    }

    public function getRaceId()
    {
        return $this->raceID;
    }

    /**
     * @ORM\Column(type="decimal", name="basePrice")
     */
    protected $basePrice;

    public function setBasePrice($basePrice)
    {
        $this->basePrice = $basePrice;

        return $this;
    }

    public function getBasePrice()
    {
        return $this->basePrice;
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

    /**
     * @ORM\Column(type="bigint", name="marketGroupID")
     */
    protected $marketGroupID;

    public function setMarketGroupId($marketGroupID)
    {
        $this->marketGroupID = $marketGroupID;

        return $this;
    }

    public function getMarketGroupId()
    {
        return $this->marketGroupID;
    }

    /**
     * @ORM\Column(type="bigint", name="iconID")
     */
    protected $iconID;

    public function setIconId($iconID)
    {
        $this->iconID = $iconID;

        return $this;
    }

    public function getIconId()
    {
        return $this->iconID;
    }

    /**
     * @ORM\Column(type="bigint", name="soundID")
     */
    protected $soundID;

    public function setSoundId($soundID)
    {
        $this->soundID = $soundID;

        return $this;
    }

    public function getSoundId()
    {
        return $this->soundID;
    }
}
