<?php
namespace AppBundle\Helper;

class Helper
{
    private $doctrine;

    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function doSomething()
    {
        // Do something here
    }
}
