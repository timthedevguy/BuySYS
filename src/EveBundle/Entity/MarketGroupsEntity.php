<?php
namespace EveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="EveBundle\Entity\MarketGroupsRepository")
 * @ORM\Table(name="invMarketGroups")
 */
class MarketGroupsEntity
{
    /**
    * @ORM\Column(name="marketGroupID", type="integer", nullable=false)
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $marketGroupID;

    public function setMarketGroupID($marketGroupID)
    {
        $this->marketGroupID = $marketGroupID;

        return $this;
    }

    public function getMarketGroupID()
    {
        return $this->marketGroupID;
    }

    /**
     * @ORM\Column(type="integer")
     */
    protected $parentGroupID;

    public function setParentGroupID($parentGroupID)
    {
        $this->parentGroupID = $parentGroupID;

        return $this;
    }

    public function getParentGroupID()
    {
        return $this->parentGroupID;
    }

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $marketGroupName;

    public function setMarketGroupName($marketGroupName)
    {
        $this->marketGroupName = $marketGroupName;

        return $this;
    }

    public function getMarketGroupName()
    {
        return $this->marketGroupName;
    }

    /**
     * @ORM\Column(type="string", length=3000)
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
    protected $hasTypes;

    public function setHasTypes($hasTypes)
    {
        $this->hasTypes = $hasTypes;

        return $this;
    }

    public function getHasTypes()
    {
        return $this->hasTypes;
    }
}
