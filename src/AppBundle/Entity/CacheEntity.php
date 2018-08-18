<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CacheRepository")
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
    /**
     * @ORM\Column(type="integer")
     */
    protected $typeID;
    /**
     * @ORM\Column(type="decimal", precision=19, scale=2)
     */
    protected $market;
    /**
     * @ORM\Column(type="decimal", precision=19, scale=2, nullable=true)
     */
    protected $adjusted;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $lastPull;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $data;

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $id
	 * @return CacheEntity
	 */
	public function setId($id)
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTypeID()
	{
		return $this->typeID;
	}

	/**
	 * @param mixed $typeID
	 * @return CacheEntity
	 */
	public function setTypeID($typeID)
	{
		$this->typeID = $typeID;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMarket()
	{
		return $this->market;
	}

	/**
	 * @param mixed $market
	 * @return CacheEntity
	 */
	public function setMarket($market)
	{
		$this->market = $market;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAdjusted()
	{
		return $this->adjusted;
	}

	/**
	 * @param mixed $adjusted
	 * @return CacheEntity
	 */
	public function setAdjusted($adjusted)
	{
		$this->adjusted = $adjusted;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLastPull()
	{
		return $this->lastPull;
	}

	/**
	 * @param mixed $lastPull
	 * @return CacheEntity
	 */
	public function setLastPull($lastPull)
	{
		$this->lastPull = $lastPull;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getData()
	{
		return unserialize($this->data);
	}

	/**
	 * @param mixed $data
	 * @return CacheEntity
	 */
	public function setData($data)
	{
		$this->data = serialize($data);

		return $this;
	}
}
