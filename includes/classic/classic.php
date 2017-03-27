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
			'icon_url' => cbox()->plugin_url( 'admin/images/logo-cbox_icon-2x.png' ),
		);
	}

	/**
	 * Register theme.
	 *
	 * @since 1.1.0
	 */
	protected function register_theme() {
		return array(
			'name'           => 'Commons In A Box',
			'version'        => '1.0.14',
			'directory_name' => 'cbox-theme',
			'download_url'   => 'http://github.com/cuny-academic-commons/cbox-theme/archive/1.0.14.zip',
			'admin_settings' => 'themes.php?page=infinity-theme',
			'screenshot_url' => cbox()->plugin_url( 'admin/images/screenshot_cbox_theme.png' ),
		);
	}
}