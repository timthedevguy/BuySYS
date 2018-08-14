<?php
namespace AppBundle\Model;

class EstimateModel {

    protected $items;

	/**
	 * @return mixed
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @param mixed $items
	 * @return EstimateModel
	 */
	public function setItems($items)
	{
		$this->items = $items;

		return $this;
	}
}