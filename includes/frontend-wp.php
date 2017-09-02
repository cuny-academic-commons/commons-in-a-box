<?php
/**
 * WordPress Mods
 *
 * The following are modifications that CBOX does to WordPress.
 *
 * @since 1.0.2
 *
 * @package Commons_In_A_Box
 * @subpackage Frontend
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// setup globals for WordPress
// not technically a plugin, but we sometimes need to modify WP core behavior
cbox()->plugins->wp = new stdClass;
cbox()->plugins->wp->is_setup = true; // WordPress is always available :)

/**
 * Modifies the 'Updates' menu item in the WP Toolbar to omit numbers.
 *
 * This is done because CBOX doesn't load its plugin code on the frontend.
 * And we want to avoid confusion for site admins when they see an update
 * count that is different than the update count in the admin area.
 *
 * @since 1.0.2
 */
class CBox_WP_Toolbar_Updates {
	public static function init() {
		new self();
	}

	public function __construct() {
		add_action( 'add_admin_bar_menus', array( $this, 'setup_hooks' ) );
	}

	public function setup_hooks() {
		// Create menu items on sub-sites if necessary.
		add_action( 'admin_bar_menu', function() {
			if ( ! isset( cbox()->package_plugins ) || ! function_exists( 'buddypress' ) ) {
				return;
			}

			// Get package plugin manifest.
			$plugins = CBox_Plugins::get_plugins();

			// bbPress
			if ( isset( $plugins['bbPress'] ) && false === $plugins['bbPress']['network'] && false === cbox()->plugins->bbpress->is_setup ) {
				require __DIR__ . '/frontend-adminbar-bbpress.php';
			}

			// BuddyPress Docs
			if ( isset( $plugins['BuddyPress Docs'] ) && false === $plugins['BuddyPress Docs']['network'] && false === function_exists( 'bp_docs_init' ) ) {
				require __DIR__ . '/frontend-adminbar-bpdocs.php';
			}

			// BuddyPress Event Organiser
			if ( isset( $plugins['BuddyPress Event Organiser'] ) && false === $plugins['BuddyPress Event Organiser']['network'] && false === function_exists( 'bpeo_include' ) ) {
				require __DIR__ . '/frontend-adminbar-bpeo.php';
			}
		}, 0 );

		// remove the current WP updates menu
		remove_action( 'admin_bar_menu', 'wp_admin_bar_updates_menu', 40 );

		// add our custom one... sigh!
		add_action( 'admin_bar_menu',    array( $this, 'modified_updates_menu' ), 40 );
	}

	/**
	 * A copy of {@link wp_admin_bar_updates_menu()} but removing numerical values
	 * attached to the 'title' attribute.
	 */
	function modified_updates_menu( $wp_admin_bar ) {

		// check to see if any updates are available
		$update_data = wp_get_update_data();

		// no updates? stop rendering this menu
		if ( !$update_data['counts']['total'] )
			return;

		// @todo Load textdomain on frontend
		$reader_text = __( 'Updates available.', 'cbox' );

		$title = '<span class="ab-icon"></span>';
		$title .= '<span class="screen-reader-text">' . $reader_text . '</span>';

		$wp_admin_bar->add_menu( array(
			'id'    => 'updates',
			'title' => $title,
			'href'  => network_admin_url( 'update-core.php' ),
			'meta'  => array(
				'title' => $reader_text,
			),
		) );
	}
}

/**
 * Create .htaccess in WordPress uploads directory for Apache environments.
 *
 * @since 1.0.16
 */
add_action( 'wp', function() {
	// If not an Apache install, bail.
	if ( ! $GLOBALS['is_apache'] ) {
		return;
	}

	$dir = wp_upload_dir();

	$htaccess = trailingslashit( $dir['basedir'] ) . '.htaccess';
	if ( true !== apply_filters( 'cbox_create_uploads_htaccess', true ) || file_exists( $htaccess ) ) {
		return;
	}

	if ( ! function_exists( 'insert_with_markers' ) ) {
		require ABSPATH . '/wp-admin/includes/misc.php';
	}

	insert_with_markers( $htaccess, 'CBOX PHP BLOCK', array(
		'<FilesMatch "\.(php|php\.)$">',
		'Order Allow,Deny',
		'Deny from all',
		'</FilesMatch>'
	) );
} );