<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Contact
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    private $name;

    #[Assert\NotBlank]
    #[Assert\Email]
    private $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    private $subject;

    #[Assert\NotBlank]
    #[Assert\Length(min: 10)]
    private $message;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }
}
