<?php
namespace AppBundle\Model;

class ResetPasswordModel
{
    protected $resetCode;

    public function setResetCode($resetCode)
    {
        $this->resetCode = $resetCode;

        return $this;
    }

    public function getResetCode()
    {
        return $this->resetCode;
    }

    protected $newPassword;

    public function setNewPassword($newPassword)
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getNewPassword()
    {
        return $this->newPassword;
    }
}
