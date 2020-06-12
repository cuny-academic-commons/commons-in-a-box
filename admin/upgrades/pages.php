<?php
namespace CBOX\Admin\Upgrades;

use CBOX\Upgrades\Upgrade_Registry;
use CBOX\Admin\Upgrades\List_Table;

/**
 * Setup sub-menu page for Upgrades.
 *
 * @return void
 */
function setup_upgrades_page() {
	$subpage = add_submenu_page(
		'cbox',
		__( 'Upgrades', 'commons-in-a-box' ),
		__( 'Upgrades', 'commons-in-a-box' ),
		'install_plugins',
		'cbox-upgrades',
		__NAMESPACE__ . '\\upgrades_page'
	);

	add_action( "admin_print_scripts-{$subpage}", __NAMESPACE__ . '\\enqueue_assets' );
}
add_action( 'cbox_admin_menu', __NAMESPACE__ . '\\setup_upgrades_page' );

/**
 * Load upgrade page assets.
 *
 * @return void
 */
function enqueue_assets() {
	wp_enqueue_style(
		'cbox-upgrade-styles',
		cbox()->plugin_url( 'assets/css/upgrades.css' ),
		[],
		cbox()->version
	);

	wp_enqueue_script(
		'cbox-upgrade-script',
		cbox()->plugin_url( 'assets/js/upgrades.js' ),
		[ 'jquery' ],
		cbox()->version,
		true
	);

	wp_localize_script( 'cbox-upgrade-script', 'CBOXUpgrades', [
		'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'cbox-upgrades' ),
		'upgrade'  => isset( $_GET['id'] ) ? sanitize_key( $_GET['id'] ) : null,
		'delay'    => 0,
		'text'     => [
			'processing' => __( 'Processing...', 'commons-in-a-box' ),
			'start'      => __( 'Start', 'commons-in-a-box' ),
		]
	] );
}

/**
 * Render "Upgrades" page.
 *
 * @return void
 */
function upgrades_page() {
	$action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';

	?>
	<div class="wrap">
		<h2><?php esc_html_e( 'Upgrades', 'commons-in-a-box' ); ?></a></h2>
		<?php if ( $action === 'view' ) : ?>
			<?php upgrades_view(); ?>
		<?php else : ?>
			<?php upgrades_list_table(); ?>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render "Upgrades" list table.
 *
 * @return void
 */
function upgrades_list_table() {
	$list_table = new List_Table();
	?>
	<form method="get">
		<?php $list_table->prepare_items(); ?>
		<?php $list_table->display(); ?>
	</form>
	<?php
}

/**
 * Render "Upgrades" singular view.
 *
 * @return void
 */
function upgrades_view() {
	$id  = isset( $_GET['id'] ) ? sanitize_key( $_GET['id'] ) : null;

	/** @var \CBOX\Upgrades\Upgrade */
	$upgrade = Upgrade_Registry::get_instance()->get_registered( $id );

	if ( ! $upgrade ) {
		_e( 'Upgrade doesn\'t exists!' );
		return;
	}

	$percentage = $upgrade->get_percentage();
	$style      = $percentage > 0 ? 'style="width: '.$percentage.'%"' : '';
	?>
	<div class="cbox-upgrade">
		<h3><?php echo esc_html( $upgrade->name ); ?></h3>
		<div class="cbox-upgrade-main">
			<ul class="cbox-upgrade-stats">
				<li>
					<strong><?php esc_html_e( 'Total', 'commons-in-a-box' ); ?></strong> <span id="cbox-upgrade-total"><?php echo $upgrade->get_items_count(); ?></span>
				</li>
				<li>
					<strong><?php esc_html_e( 'Processed', 'commons-in-a-box' ); ?></strong> <span id="cbox-upgrade-processed"><?php echo $upgrade->get_processed_count(); ?></span> <span id="cbox-upgrade-percentage">(<?php echo $percentage; ?>%)</span>
				</li>
			</ul>
			<div class="cbox-upgrade-progress-bar">
				<div class="cbox-upgrade-progress-bar-inner" <?php echo $style; ?>></div>
			</div>
			<div class="cbox-upgrade-current-item"></div>
		</div>
		<div class="cbox-upgrade-actions">
			<?php if ( ! $upgrade->is_finished() ) : ?>
				<button class="button-primary" id="cbox-upgrade-start"><?php esc_html_e( 'Start', 'commons-in-a-box' ); ?></button>
				<button class="button" id="cbox-upgrade-pause"><?php esc_html_e( 'Pause', 'commons-in-a-box' ); ?></button>
			<?php else: ?>
				<button class="button-primary" id="cbox-upgrade-restart"><?php esc_html_e( 'Restart', 'commons-in-a-box' ); ?></button>
			<?php endif; ?>
		</div>
	</div>
	<?php
}
