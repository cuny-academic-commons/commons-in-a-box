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
 * @todo Add theme.
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
			'icon_url'          => 'https://openlab.citytech.cuny.edu/wp-content/themes/openlab/images/default-avatar.jpg',
			'documentation_url' => 'https://openlab.citytech.cuny.edu/about/'
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
}