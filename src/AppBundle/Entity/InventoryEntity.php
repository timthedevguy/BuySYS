<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table("inventory")
 */
class InventoryEntity
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @ORM\Column(type="integer")
     */
    protected $typeid;

    public function setTypeId($typeid)
    {
        $this->typeid = $typeid;

        return $this;
    }

    public function getTypeId()
    {
        return $this->typeid;
    }

    /**
     * @ORM\Column(type="string", length=255)
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

    /**
     * @ORM\Column(type="decimal")
     */
    protected $cost;

    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdOn;

    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $user;
    
    public function setUser($user)
    {
        $this->user = $user;
    
        return $this;
    }
    
    public function getUser()
    {
        return $this->user;
    }
}