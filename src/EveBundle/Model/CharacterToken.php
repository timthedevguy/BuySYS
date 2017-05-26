<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 5/19/17
 * Time: 1:37 PM
 */

namespace EveBundle\Model;


class CharacterToken extends SSOToken
{

    protected $characterId;
    protected $characterName;
    protected $scopes;
    protected $characterOwnerHash;



    //GETTERS AND SETTESR
    /**
     * @return mixed
     */
    public function getCharacterId()
    {
        return $this->characterId;
    }

    /**
     * @param mixed $characterId
     * @return CharacterToken
     */
    public function setCharacterId($characterId)
    {
        $this->characterId = $characterId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCharacterName()
    {
        return $this->characterName;
    }

    /**
     * @param mixed $characterName
     * @return CharacterToken
     */
    public function setCharacterName($characterName)
    {
        $this->characterName = $characterName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @param mixed $scopes
     * @return CharacterToken
     */
    public function setScopes($scopes)
    {
        $this->scopes = $scopes;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCharacterOwnerHash()
    {
        return $this->characterOwnerHash;
    }

    /**
     * @param mixed $characterOwnerHash
     * @return CharacterToken
     */
    public function setCharacterOwnerHash($characterOwnerHash)
    {
        $this->characterOwnerHash = $characterOwnerHash;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * @param mixed $tokenType
     * @return CharacterToken
     */
    public function setTokenType($tokenType)
    {
        $this->tokenType = $tokenType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * @param mixed $expiry
     * @return CharacterToken
     */
    public function setExpiry($expiry)
    {
        $this->expiry = $expiry;
        return $this;
    }

}