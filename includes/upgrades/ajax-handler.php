<?php
namespace CBOX\Upgrades;

use CBOX\Upgrades\Upgrade_Registry;

/**
 * AJAX callback for upgrade process.
 *
 * @return void
 */
function handle_upgrade() {
	if ( ! check_ajax_referer( 'cbox-upgrades', '_ajax_nonce', false ) ) {
		wp_send_json_error( [
			'message' => esc_html__( 'Permission denied.', 'commons-in-a-box' ),
		] );
	}

	// Check the upgrade id.
	$id = isset( $_POST['upgrade'] ) ? sanitize_key( $_POST['upgrade'] ) : false;
	if ( ! $id ) {
		wp_send_json_error( [
			'message' => esc_html__( 'Invalid upgrade ID.', 'commons-in-a-box' ),
		] );
	}

	$registry = Upgrade_Registry::get_instance();

	if ( $id === 'all' ) {
		$upgrades = $registry->get_all_registered();

		/** @var \CBOX\Upgrades\Upgrade */
		$upgrade = ! empty( $upgrades ) ? reset( $upgrades ) : null;
		$total   = count( $upgrades );
	} else {
		/** @var \CBOX\Upgrades\Upgrade */
		$upgrade = $registry->get_registered( $id );
		$total   = 1;
	}

	// Process the next item.
	$next_item = $upgrade->get_next_item();

	// No next item for processing. The upgrade processing is finished, probably.
	if ( ! $next_item ) {
		$upgrade->finish();

		wp_send_json_success( [
			'message'         => esc_html__( 'Processing finished.', 'commons-in-a-box' ),
			'is_finished'     => ( $id === 'all' && $total > 1 ) ? 0 : 1,
			'total_processed' => $upgrade->get_processed_count(),
			'total_items'     => $upgrade->get_items_count(),
			'percentage'      => $upgrade->get_percentage(),
			'name'            => $upgrade->name,
		] );
	}

	@set_time_limit( 0 );

	$response = $upgrade->process( $next_item );
	$upgrade->mark_as_processed( $next_item->id );
	$total_processed = $upgrade->get_processed_count();
	$total_items     = $upgrade->get_items_count();
	$percentage      = $upgrade->get_percentage();
	$name            = $upgrade->name;

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( [
			'message'         => $response->get_error_message(),
			'is_finished'     => 0,
			'total_processed' => $total_processed,
			'total_items'     => $total_items,
			'percentage'      => $percentage,
			'name'            => $name,
		] );
	}

	wp_send_json_success( [
		'name'            => $name,
		'is_finished'     => 0,
		'total_processed' => $total_processed,
		'total_items'     => $total_items,
		'percentage'      => $percentage,
		'message'         => sprintf(
			esc_html__( 'Processed item with ID: %d.', 'commons-in-a-box' ),
			$next_item->id
		),
	] );
}
add_action( 'wp_ajax_cbox_handle_upgrade', __NAMESPACE__ . '\\handle_upgrade' );

/**
 * AJAX callback for upgrade reset.
 *
 * @return void
 */
function restart_upgrade() {
	if ( ! check_ajax_referer( 'cbox-upgrades', '_ajax_nonce', false ) ) {
		wp_send_json_error( [
			'message' => esc_html__( 'Permission denied.', 'commons-in-a-box' ),
		] );
	}

	// Check the upgrade id.
	$upgrade_id = isset( $_POST['upgrade'] ) ? sanitize_key( $_POST['upgrade'] ) : false;
	if ( ! $upgrade_id ) {
		wp_send_json_error( [
			'message' => esc_html__( 'Invalid upgrade ID.', 'commons-in-a-box' ),
		] );
	}

	/** @var \CBOX\Upgrades\Upgrade */
	$upgrade = Upgrade_Registry::get_instance()->get_registered( $upgrade_id );
	$upgrade->restart();

	wp_send_json_success();
}
add_action( 'wp_ajax_cbox_restart_upgrade', __NAMESPACE__ . '\\restart_upgrade' );
