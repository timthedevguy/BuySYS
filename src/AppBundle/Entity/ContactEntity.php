<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 5/17/17
 * Time: 1:01 PM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ContactRepository")
 * @ORM\Table(name="contacts")
 */
class ContactEntity
{
    /**
     * @ORM\Column(name="contactEntityID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected $contactEntityID;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $contactName;

    /**
     * @ORM\Column(type="integer")
     */
    protected $contactId;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     *
     * Pilot = P
     * Corp = C
     * Alliance = A
     */
    protected $contactType;

    /**
     * @ORM\Column(type="string", length=5)
     */
    protected $contactLevel;

    /**
     * @ORM\ManyToOne(targetEntity="AuthorizationEntity")
     * @ORM\JoinColumn(name="authorizationId", referencedColumnName="id")
     */
    protected $authorization;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $lastUpdatedDate;



    //GETTERS AND SETTERS
    /**
     * @return mixed
     */
    public function getContactEntityID()
    {
        return $this->contactEntityID;
    }

    /**
     * @param mixed $contactEntityID
     * @return ContactEntity
     */
    public function setContactEntityID($contactEntityID)
    {
        $this->contactEntityID = $contactEntityID;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContactName()
    {
        return $this->contactName;
    }

    /**
     * @param mixed $contactName
     * @return ContactEntity
     */
    public function setContactName($contactName)
    {
        $this->contactName = $contactName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContactId()
    {
        return $this->contactId;
    }

    /**
     * @param mixed $contactId
     * @return ContactEntity
     */
    public function setContactId($contactId)
    {
        $this->contactId = $contactId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContactType()
    {
        return $this->contactType;
    }

    /**
     * @param mixed $contactType
     * @return ContactEntity
     */
    public function setContactType($contactType)
    {
        $this->contactType = $contactType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContactLevel()
    {
        return $this->contactLevel;
    }

    /**
     * @param mixed $contactLevel
     * @return ContactEntity
     */
    public function setContactLevel($contactLevel)
    {
        $this->contactLevel = $contactLevel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAuthorization()
    {
        return $this->authorization;
    }

    /**
     * @param mixed $authorization
     * @return ContactEntity
     */
    public function setAuthorization($authorization)
    {
        $this->authorization = $authorization;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastUpdatedDate()
    {
        return $this->lastUpdatedDate;
    }

    /**
     * @param mixed $lastUpdatedDate
     * @return ContactEntity
     */
    public function setLastUpdatedDate($lastUpdatedDate)
    {
        $this->lastUpdatedDate = $lastUpdatedDate;
        return $this;
    }

}