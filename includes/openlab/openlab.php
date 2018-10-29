<?php
/**
 * Package: OpenLab Core class
 *
 * @package    Commons_In_A_Box
 * @subpackage Package
 * @since      1.1.0
 */

/**
 * The "OpenLab" CBOX package.
 *
 * For plugin manifest, see {@link CBox_Plugins_OpenLab}.
 *
 * @todo Name subject to change.
 *
 * @since 1.1.0
 */
class CBox_Package_OpenLab extends CBox_Package {
	/**
	 * @var string Display name for our package.
	 */
	public static $name = 'OpenLab';

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
			'network'           => true,
			'icon_url'          => cbox()->plugin_url( 'admin/images/logo-cbox-ol_vert.png' ),
			'badge_url'         => cbox()->plugin_url( 'admin/images/logo-cbox-ol_vert.png' ),
			'badge_url_2x'      => cbox()->plugin_url( 'admin/images/logo-cbox-ol_vert-2x.png' ),
			'documentation_url' => 'http://commonsinabox.org/cbox-openlab-overview/?modal=1'
		);
	}

	/**
	 * String setter method.
	 *
	 * @since 1.1.0
	 *
	 * @return array
	 */
	protected static function strings() {
		return array(
			'tab_plugin_optional' => __( 'Community Features', 'cbox' )
		);
	}

	/**
	 * Register theme.
	 *
	 * @since 1.1.0
	 */
	protected static function theme() {
		return array(
			'name'           => 'CBOX OpenLab',
			'version'        => '1.1.0-beta2',
			'directory_name' => 'openlab-theme',
			'download_url'   => 'http://github.com/cuny-academic-commons/openlab-theme/archive/1.1.0-beta2.zip',
			'screenshot_url' => cbox()->plugin_url( 'admin/images/screenshot_openlab_theme.png' ),
			'force_install'  => true
		);
	}

	/**
	 * Custom hooks used during package initialization.
	 *
	 * @since 1.1.0
	 */
	protected function custom_init() {
		/**
	         * Always enable the "Plugins" page on sub-sites.
	         */
		add_filter( 'site_option_menu_items', function( $retval ) {
			if ( empty( $retval['plugins'] ) ) {
				$retval['plugins'] = 1;
			}
			return $retval;
		} );
	}

	/**
	 * Deactivation routine.
	 *
	 * @since 1.1.0
	 */
	public static function deactivate() {
		// Deactivate CBOX-OpenLab-Core plugin, as it's OL-specific only.
		deactivate_plugins( 'cbox-openlab-core/cbox-openlab-core.php', true );
	}
}
