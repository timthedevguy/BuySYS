<?php
Namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\OperationRepository")
 * @ORM\Table(name="operations")
 */
class OperationEntity
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
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $organizer;

    public function setOrganizer($organizer)
    {
        $this->organizer = $organizer;

        return $this;
    }

    public function getOrganizer()
    {
        return $this->organizer;
    }

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $fleetCommander;

    public function setFleetCommander($fleetCommander)
    {
        $this->fleetCommander = $fleetCommander;

        return $this;
    }

    public function getFleetCommander()
    {
        return $this->fleetCommander;
    }

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $location;

    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @ORM\Column(type="datetime")
     */
    protected $opDate;

    public function setOpDate($opDate)
    {
        $this->opDate = $opDate;

        return $this;
    }

    public function getOpDate()
    {
        return $this->opDate;
    }
}
