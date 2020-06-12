<?php
namespace CBOX\Upgrades;

class Upgrade_Item {

	/**
	 * Unique identifier of the upgrade item.
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Additional data for the item
	 *
	 * @var array
	 */
	public $data;

	/**
	 * Constructor
	 *
	 * @param int   $id   Unique identifier of the upgrade item.
	 * @param array $data Additional data for the item.
	 */
	public function __construct( $id, $data = [] ) {
		$this->id = $id;
		$this->data = $data;
	}

	/**
	 * Return data value
	 *
	 * @param string $key
	 * @param null   $default
	 *
	 * @return mixed|null
	 */
	public function get_value( $key, $default = null ) {
		return isset( $this->data[ $key ] ) ? $this->data[ $key ] : $default;
	}
}
