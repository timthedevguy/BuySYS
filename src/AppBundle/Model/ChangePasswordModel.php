<?php
namespace AppBundle\Model;

use DoctrineORMMapping as ORM;
use SymfonyComponentValidatorConstraints as Assert;

class ChangePasswordModel
{
    protected $current_password;
    protected $new_password;

    /**
     * Set CurrentPassword
     *
     * @param string $current_password
     *
     * @return current_password
     */
    public function setCurrentPassword($current_password)
    {
        $this->current_password = $current_password;

        return $this;
    }

    /**
     * Get CurrentPassword
     *
     * @return string
     */
    public function getCurrentPassword()
    {
        return $this->current_password;
    }

    /**
     * Set NewPassword
     *
     * @param string $new_password
     *
     * @return new_password
     */
    public function setNewPassword($new_password)
    {
        $this->new_password = $new_password;

        return $this;
    }

    /**
     * Get NewPassword
     *
     * @return string
     */
    public function getNewPassword()
    {
        return $this->new_password;
    }
}
