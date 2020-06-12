<?php
namespace CBOX\Upgrades;

/**
 * CBOX\Upgrades\Upgrade class.
 *
 * Extend this class to create custom upgrades.
 *
 * Note: Upgrades must be registered via 'init' action.
 *
 * Example:
 * Upgrade_Registry::get_instance()->register( $id, $upgrade ).
 */
abstract class Upgrade {

	/**
	 * Unique identifier for upgrade.
	 *
	 * @var string
	 */
	public $id = 'abstract_upgrade';

	/**
	 * Upgrade name/description.
	 *
	 * @var string
	 */
	public $name = 'Abstract Upgrade';

	/**
	 * Data store of upgrade items.
	 *
	 * @var CBOX\Upgrades\Upgrade_Item[]
	 */
	protected $items = [];

	/**
	 * Initialize.
	 */
	public function __construct() {
		$this->setup();
	}

	/**
	 * To setup the upgrade data use the push() method to add CBOX\Upgrades\Upgrade_Item instances to the queue.
	 *
	 * Note: If the operation of obtaining data is expensive, cache it to avoid slowdowns.
	 *
	 * @return void
	 */
	abstract public function setup();

	/**
	 * Handles processing of upgrade item. One at a time.
	 *
	 * In order to work it correctly you must return values as follows:
	 *
	 * - true - If the item was processed successfully.
	 * - WP_Error instance - If there was an error. Add message to display it in the admin area.
	 *
	 * @param CBOX\Upgrades\Upgrade_Item $item
	 *
	 * @return \WP_Error|bool
	 */
	abstract public function process( $item );

	/**
	 * Called when specific process is finished (all items were processed).
	 * This method can be overriden in the process class.
	 *
	 * @return void
	 */
	public function finish() {}

	/**
	 * Queues the item for processing.
	 *
	 * @param CBOX\Upgrades\Upgrade_Item $item
	 */
	protected function push( $item ) {
		if ( ! is_array( $this->items ) ) {
			$this->items = [];
		}

		$this->items[] = $item;
	}


	/**
	 * Get next upgrade item.
	 *
	 * @param CBOX\Upgrades\Upgrade_Item $item
	 *
	 * @return CBOX\Upgrades\Upgrade_Item|bool
	 */
	public function get_next_item() {
		$processed = $this->get_processed_items();

		foreach ( $this->items as $item ) {
			if ( ! in_array( $item->id, $processed ) ) {
				return $item;
			}
		}

		return false;
	}

	/**
	 * Check if the upgrade is finished.
	 *
	 * @return bool
	 */
	public function is_finished() {
		return ! $this->get_next_item();
	}


	/**
	 * Check if upgrade item was processed.
	 *
	 * @param CBOX\Upgrades\Upgrade_Item $item
	 *
	 * @return bool
	 */
	public function is_processed( $item ) {
		return in_array( $item->id, $this->get_processed_items() );
	}

	/**
	 * Returns processed upgrade item ids.
	 *
	 * @return array
	 */
	public function get_processed_items() {
		$processed = get_option( $this->get_db_identifier(), [] );

		return $processed;
	}

	/**
	 * Returns the count of the processed items.
	 *
	 * @return int
	 */
	public function get_processed_count() {
		return count( $this->get_processed_items() );
	}

	/**
	 * Mark specific item as processed.
	 *
	 * @param int $id Item ID.
	 */
	public function mark_as_processed( $id ) {
		$processed = $this->get_processed_items();
		array_push( $processed, $id );
		$processed = array_unique( $processed );
		update_option( $this->get_db_identifier(), $processed );
	}

	/**
	 * Returns the count of the total items.
	 *
	 * @return int
	 */
	public function get_items_count() {
		return count( $this->items );
	}

	/**
	 * Returns the percentage.
	 *
	 * @return float
	 */
	public function get_percentage() {
		$total_items     = $this->get_items_count();
		$total_processed = $this->get_processed_count();
		$percentage      = ( ! empty( $total_items ) ) ? 100 - ( ( ( $total_items - $total_processed ) / $total_items ) * 100 ) : 0;

		return number_format( (float) $percentage, 2, '.', '' );
	}

	/**
	 * Returns the upgrade identifier for WP options.
	 *
	 * @return string
	 */
	public function get_db_identifier() {
		return 'upgrade_' . $this->id;
	}

	/**
	 * Restarts the processed items store.
	 */
	public function restart() {
		delete_option( $this->get_db_identifier() );
	}
}
