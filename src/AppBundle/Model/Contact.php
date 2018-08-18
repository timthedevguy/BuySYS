<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 5/19/17
 * Time: 2:25 PM
 */

namespace AppBundle\Model;


class Contact
{

    /**
     * Contact constructor.
     * @param $contactId
     * @param $contactName
     * @param $contactType
     * @param $standing
     * @param $isInWatchlist
     */
    public function __construct($contactId, $contactName, $contactType, $standing, $isInWatchlist)
    {
        $this->contactId = $contactId;
        $this->contactName = $contactName;
        $this->contactType = $contactType;
        $this->standing = $standing;
        $this->isInWatchlist = $isInWatchlist;
    }


    protected $contactId;
    protected $contactName;
    protected $contactType;
    protected $standing;
    protected $isInWatchlist;



    //GETTERS AND SETTERS
    /**
     * @return mixed
     */
    public function getContactId()
    {
        return $this->contactId;
    }

    /**
     * @param mixed $contactId
     * @return Contact
     */
    public function setContactId($contactId)
    {
        $this->contactId = $contactId;
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
     * @return Contact
     */
    public function setContactName($contactName)
    {
        $this->contactName = $contactName;
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
     * @return Contact
     */
    public function setContactType($contactType)
    {
        $this->contactType = $contactType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStanding()
    {
        return $this->standing;
    }

    /**
     * @param mixed $standing
     * @return Contact
     */
    public function setStanding($standing)
    {
        $this->standing = $standing;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getisInWatchlist()
    {
        return $this->isInWatchlist;
    }

    /**
     * @param mixed $isInWatchlist
     * @return Contact
     */
    public function setIsInWatchlist($isInWatchlist)
    {
        $this->isInWatchlist = $isInWatchlist;
        return $this;
    }

}