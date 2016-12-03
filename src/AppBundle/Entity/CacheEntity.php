<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CacheRepository")
 * @ORM\Table(name="cache")
 */
class CacheEntity
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
     * @ORM\Column(type="decimal", precision=19, scale=2)
     */
    protected $market;

    public function setMarket($market)
    {
        $this->market = $market;

        return $this;
    }

    public function getMarket()
    {
        return $this->market;
    }

    /**
     * @ORM\Column(type="datetime")
     */
    protected $lastPull;

    public function setLastPull($lastPull)
    {
        $this->lastPull = $lastPull;

        return $this;
    }

    public function getLastPull()
    {
        return $this->lastPull;
    }
}
