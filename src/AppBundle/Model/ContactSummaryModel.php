<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 5/18/17
 * Time: 12:39 PM
 */

namespace AppBundle\Model;


class ContactSummaryModel
{

    /**
     * ContactSummaryModel constructor.
     * @param $contactLevel
     * @param $contactCount
     * @param $updatedDate
     * @param $selectedRole
     * @param $selectedEntitlements
     */
    public function __construct($contactLevel, $contactCount = 0, $updatedDate = null, $selectedRole = null, $selectedEntitlements = "")
    {
        $this->contactLevel = $contactLevel;
        $this->contactCount = $contactCount;
        $this->updatedDate = $updatedDate;
        $this->selectedRole = $selectedRole;
        $this->selectedEntitlements = $selectedEntitlements;
    }



    protected $contactLevel;
    protected $contactCount;
    protected $updatedDate;
    protected $selectedRole;
    protected $selectedEntitlements;



    //GETTERS AND SETTERS
    /**
     * @return mixed
     */
    public function getContactLevel()
    {
        return $this->contactLevel;
    }

    /**
     * @param mixed $contactLevel
     */
    public function setContactLevel($contactLevel)
    {
        $this->contactLevel = $contactLevel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContactCount()
    {
        return $this->contactCount;
    }

    /**
     * @param mixed $contactCount
     */
    public function setContactCount($contactCount)
    {
        $this->contactCount = $contactCount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * @param mixed $updatedDate
     */
    public function setUpdatedDate($updatedDate)
    {
        $this->updatedDate = $updatedDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSelectedRole()
    {
        return $this->selectedRole;
    }

    /**
     * @param mixed $selectedRole
     */
    public function setSelectedRole($selectedRole)
    {
        $this->selectedRole = $selectedRole;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSelectedEntitlements()
    {
        return $this->selectedRole;
    }

    /**
     * @param mixed $selectedEntitlements
     */
    public function setSelectedEntitlements($selectedEntitlements)
    {
        $this->selectedEntitlements = $selectedEntitlements;
        return $this;
    }


}