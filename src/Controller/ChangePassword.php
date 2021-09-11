<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

class ChangePassword
{
    /**
     * @SecurityAssert\UserPassword(
     * message = "Wrong value for your current password"
     * )
     */
    protected $oldPassword;

    protected $password;


    function getOldPassword()
    {
        return $this->oldPassword;
    }

    function getPassword()
    {
        return $this->password;
    }

    function setOldPassword($oldPassword)
    {
        $this->oldPassword = $oldPassword;
        return $this;
    }

    function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }
}
