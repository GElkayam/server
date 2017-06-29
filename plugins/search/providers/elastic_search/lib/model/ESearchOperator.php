<?php

class ESearchOperator extends ESearchItem
{

	/**
	 * @var ESearchOperatorType
	 */
	protected $operator;

	/**
	 * @var array
	 */
	protected $searchItems;

	/**
	 * @return ESearchOperatorType
	 */
	public function getOperator()
	{
		return $this->operator;
	}

	/**
	 * @param ESearchOperatorType $operator
	 */
	public function setOperator($operator)
	{
		$this->operator = $operator;
	}

	/**
	 * @return array
	 */
	public function getSearchItems()
	{
		return $this->searchItems;
	}

	/**
	 * @param array $searchItems
	 */
	public function setSearchItems($searchItems)
	{
		$this->searchItems = $searchItems;
	}

	public static function createSearchQuery(array $eSearchCaptionItemsArr, $boolOperator, $additionalParams = null)
	{

	}


	public function getType()
	{
		return 'operator';
	}


}