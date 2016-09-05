<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="exclusion")
 */
class ExclusionEntity
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
    protected $marketGroupId;
    
    public function setMarketGroupId($marketGroupId)
    {
        $this->marketGroupId = $marketGroupId;
    
        return $this;
    }
    
    public function getMarketGroupId()
    {
        return $this->marketGroupId;
    }

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $whitelist;
    
    public function setWhitelist($whitelist)
    {
        $this->whitelist = $whitelist;
    
        return $this;
    }
    
    public function getWhitelist()
    {
        return $this->whitelist;
    }

    /**
     * @ORM\Column(type="string", length=255)
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
}