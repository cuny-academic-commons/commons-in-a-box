<?php
namespace CBOX\Upgrades;

class Upgrade_Registry {

	/**
	 * Registered upgrades.
	 *
	 * @var CBOX\Upgrades\Upgrade[]
	 */
	private $upgrades = [];

	/**
	 * Container for the main instance of the class.
	 *
	 * @var CBOX\Upgrades\Upgrade_Registry|null
	 */
	private static $instance = null;

	/**
	 * Register a upgrade.
	 *
	 * @param string                $id    Unique identifier of the upgrade.
	 * @param CBOX\Upgrades\Upgrade $upgrade Upgrade object.
	 * @return bool
	 */
	public function register( $id, $upgrade ) {
		// @todo check if name is valid, etc.
		$this->upgrades[ $id ] = $upgrade;

		return true;
	}

	/**
	 * Retrieves a registered upgrade.
	 *
	 * @param string $id Upgrade id.
	 * @return CBOX\Upgrades\Upgrade|null The registered upgrade, or null if it is not registered.
	 */
	public function get_registered( $id ) {
		if ( ! $this->is_registered( $id ) ) {
			return null;
		}

		return $this->upgrades[ $id ];
	}

	/**
	 * Retrieves all registered upgrades.
	 *
	 * @return CBOX\Upgrades\Upgrade[] Associative array of `$upgrade_id => $upgrade` pairs.
	 */
	public function get_all_registered() {
		return $this->upgrades;
	}

	/**
	 * Checks if a upgrade is registered.
	 *
	 * @param string $id Upgrade name.
	 * @return bool True if the upgrade is registered, false otherwise.
	 */
	public function is_registered( $id ) {
		return isset( $this->upgrades[ $id ] );
	}

	/**
	 * Utility method to retrieve the main instance of the class.
	 *
	 * The instance will be created if it does not exist yet.
	 *
	 * @return CBOX\Upgrades\Upgrade_Registry The main instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
