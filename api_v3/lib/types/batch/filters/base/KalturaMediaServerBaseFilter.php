<?php
/**
 * @package api
 * @subpackage filters.base
 * @abstract
 */
abstract class KalturaMediaServerBaseFilter extends KalturaFilter
{
	static private $map_between_objects = array
	(
		"createdAtGreaterThanOrEqual" => "_gte_created_at",
		"createdAtLessThanOrEqual" => "_lte_created_at",
		"updatedAtGreaterThanOrEqual" => "_gte_updated_at",
		"updatedAtLessThanOrEqual" => "_lte_updated_at",
		"reportedAtGreaterThanOrEqual" => "_gte_reported_at",
		"reportedAtLessThanOrEqual" => "_lte_reported_at",
	);

	static private $order_by_map = array
	(
		"+createdAt" => "+created_at",
		"-createdAt" => "-created_at",
		"+updatedAt" => "+updated_at",
		"-updatedAt" => "-updated_at",
		"+reportedAt" => "+reported_at",
		"-reportedAt" => "-reported_at",
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), self::$order_by_map);
	}

	/**
	 * @var int
	 */
	public $createdAtGreaterThanOrEqual;

	/**
	 * @var int
	 */
	public $createdAtLessThanOrEqual;

	/**
	 * @var int
	 */
	public $updatedAtGreaterThanOrEqual;

	/**
	 * @var int
	 */
	public $updatedAtLessThanOrEqual;

	/**
	 * @var int
	 */
	public $reportedAtGreaterThanOrEqual;

	/**
	 * @var int
	 */
	public $reportedAtLessThanOrEqual;
}
