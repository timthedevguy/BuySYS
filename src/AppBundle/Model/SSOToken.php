<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 5/19/17
 * Time: 1:28 PM
 */

namespace AppBundle\Model;


class SSOToken
{

    protected $accessTokenValue;
    protected $tokenType;
    protected $expiry;
    protected $refreshToken;


    //GETTERS AND SETTERS
    /**
     * @return mixed
     */
    public function getAccessTokenValue()
    {
        return $this->accessTokenValue;
    }

    /**
     * @param mixed $accessTokenValue
     * @return SSOToken
     */
    public function setAccessTokenValue($accessTokenValue)
    {
        $this->accessTokenValue = $accessTokenValue;
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
     * @return SSOToken
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
     * @return SSOToken
     */
    public function setExpiry($expiry)
    {
        $this->expiry = $expiry;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param mixed $refreshToken
     * @return SSOToken
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

}