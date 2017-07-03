<?php
namespace AppBundle\Model;

class SRPModel
{
    protected $items;

    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    public function getItems()
    {
        return $this->items;
    }
}
