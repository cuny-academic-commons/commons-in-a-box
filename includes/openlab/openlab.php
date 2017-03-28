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
			'icon_url'          => 'https://openlab.citytech.cuny.edu/wp-content/themes/openlab/images/default-avatar.jpg',
			'documentation_url' => 'https://openlab.citytech.cuny.edu/about/'
		);
	}
}