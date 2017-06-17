<?php

/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 5/19/17
 * Time: 12:51 PM
 */
namespace EveBundle\Model;

class Character
{


    protected $name;
    protected $id;
    protected $corpId;
    protected $allianceId;
    protected $dob;
    protected $raceId;
    protected $gender;
    protected $secStatus;
    protected $description;


    //GETTERS AND SETTERS
    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Character
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Character
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCorpId()
    {
        return $this->corpId;
    }

    /**
     * @param mixed $corpId
     * @return Character
     */
    public function setCorpId($corpId)
    {
        $this->corpId = $corpId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAllianceId()
    {
        return $this->allianceId;
    }

    /**
     * @param mixed $allianceId
     * @return Character
     */
    public function setAllianceId($allianceId)
    {
        $this->allianceId = $allianceId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDOB()
    {
        return $this->dob;
    }

    /**
     * @param mixed $dob
     * @return Character
     */
    public function setDOB($dob)
    {
        $this->dob = $dob;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRaceId()
    {
        return $this->raceId;
    }

    /**
     * @param mixed $raceId
     * @return Character
     */
    public function setRaceId($raceId)
    {
        $this->raceId = $raceId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param mixed $gender
     * @return Character
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSecStatus()
    {
        return $this->secStatus;
    }

    /**
     * @param mixed $secStatus
     * @return Character
     */
    public function setSecStatus($secStatus)
    {
        $this->secStatus = $secStatus;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return Character
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

}