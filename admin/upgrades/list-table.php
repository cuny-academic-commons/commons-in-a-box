<?php
namespace CBOX\Admin\Upgrades;

use CBOX\Upgrades\Upgrade_Registry;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class List_Table extends \WP_List_Table {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct( [
			'plural'   => 'upgrades',
			'singular' => 'upgrade',
			'ajax'     => false
		] );
	}

	/**
	 * Message to be displayed when there are no items.
	 *
	 * @return void
	 */
	public function no_items() {
		_e( 'No upgrades found.', 'commons-in-a-box' );
	}

	/**
	 * Get the list of columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = [
			'name'            => __( 'Name', 'commons-in-a-box' ),
			'total_items'     => __( 'Total Items', 'commons-in-a-box' ),
			'total_processed' => __( 'Total Processed', 'commons-in-a-box' ),
		];

		return $columns;
	}

	/**
	 * Default column values if no callback found
	 *
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'name':
				return $item->name;

			case 'total_processed':
				return $item->get_processed_count();

			case 'total_items':
				return $item->get_items_count();

			default:
				return isset( $item->$column_name ) ? $item->$column_name : '';
		}
	}

	/**
	 * Render the checkbox column
	 *
	 * @param object $item
	 * @return string
	 */
	protected function column_cb( $item ) {
		return '';
	}

	/**
	 * Render the upgrade name column.
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	public function column_name( $item ) {
		$url = add_query_arg( [
			'page'   => 'cbox-upgrades',
			'action' => 'view',
			'id'     => $item->id,
		], self_admin_url( 'admin.php' ) );

		$actions         = [];
		$actions['edit'] = sprintf(
			'<a href="%1$s" data-id="%2$d" title="%3$s">%4$s</a>',
			esc_url( $url ),
			esc_attr( $item->id ),
			__( 'View Upgrade', 'commons-in-a-box' ),
			__( 'View', 'commons-in-a-box' )
		);

		return sprintf(
			'<a href="%1$s"><strong>%2$s</strong></a> %3$s',
			esc_url( $url ),
			esc_html( $item->name ),
			$this->row_actions( $actions )
		);
	}

	/**
	 * Prepare the class items.
	 *
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$this->_column_headers = [ $columns ];

		$this->items = Upgrade_Registry::get_instance()->get_all_registered();
	}
}
