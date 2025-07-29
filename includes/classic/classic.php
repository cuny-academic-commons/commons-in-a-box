<?php
/**
 * Package: Classic Core class
 *
 * @package    Commons_In_A_Box
 * @subpackage Package
 * @since      1.1.0
 */

/**
 * The "classic" CBOX package.
 *
 * For plugin manifest, see {@link CBox_Plugins_Classic}.
 * For admin settings page, see {@link CBox_Settings_Classic}.
 *
 * @todo Name subject to change.
 *
 * @since 1.1.0
 */
class CBox_Package_Classic extends CBox_Package {
	/**
	 * @var string Display name for our package.
	 */
	public static $name = 'Classic';

	/**
	 * @var array Configuration holder.
	 */
	protected static $config = array();

	/**
	 * Package configuration.
	 *
	 * @since 1.1.0
	 */
	protected static function config() {
		return array(
			'icon_url'          => cbox()->plugin_url( 'admin/images/logo-cbox_icon-2x.png' ),
			'settings_key'      => '_cbox_admin_settings',
			'documentation_url' => 'http://commonsinabox.org/cbox-classic-overview/?modal=1'
		);
	}

	/**
	 * Register theme.
	 *
	 * @since 1.1.0
	 */
	protected static function theme() {
		return array(
			'name'           => 'Commons In A Box',
			'version'        => '1.7.0-beta1',
			'directory_name' => 'cbox-theme',
			'download_url'   => CBOX_PLUGIN_DIR . 'includes/zip/cbox-theme-1.7.0-beta1.zip',
			'admin_settings' => 'themes.php?page=infinity-theme',
			'screenshot_url' => cbox()->plugin_url( 'admin/images/screenshot_cbox_theme.png' ),
		);
	}

	/**
	 * Custom hooks used during package initialization.
	 *
	 * @since 1.1.0
	 */
	protected function custom_init() {
		/**
	         * Trigger Infinity's activation hook
	         *
		 * Infinity, and therefore cbox-theme, runs certain setup routines at
	         * 'infinity_dashboard_activated'. We need to run this hook just after CBOX
	         * activates a theme, so we do that here.
	         */
		add_action( 'cbox_classic_theme_activated', function() {
			if ( ! cbox_get_installed_revision_date() ) {
				remove_action( 'infinity_dashboard_activated', 'infinity_dashboard_activated_redirect', 99 );
			}

			do_action( 'infinity_dashboard_activated' );
		} );
	}
}
