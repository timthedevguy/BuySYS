<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 4/26/17
 * Time: 4:00 PM
 */

namespace AppBundle\Model;


class UserModel
{
    protected $preferredTheme;

    /**
     * @return mixed
     */
    public function getPreferredTheme()
    {
        return $this->preferredTheme;
    }
    /**
     * @param mixed $preferredTheme
     */
    public function setPreferredTheme($preferredTheme)
    {
        $this->preferredTheme = $preferredTheme;
    }
}