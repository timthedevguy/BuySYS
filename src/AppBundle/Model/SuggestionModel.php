<?php
namespace AppBundle\Model;

class SuggestionModel
{
    protected $message;

    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
