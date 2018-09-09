<?php
namespace AppBundle\Model;

class ContractValidationModel {

    protected $details;
    protected $items;

	/**
	 * @return mixed
	 */
	public function getDetails()
	{
		return $this->details;
	}

	/**
	 * @param mixed $details
	 * @return ContractValidationModel
	 */
	public function setDetails($details)
	{
		$this->details = $details;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @param mixed $items
	 * @return ContractValidationModel
	 */
	public function setItems($items)
	{
		$this->items = $items;

		return $this;
	}

}