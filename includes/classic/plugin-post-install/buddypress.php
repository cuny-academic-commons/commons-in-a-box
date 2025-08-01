<?php
/**
 * Classic: Post-install routine for the BuddyPress plugin.
 *
 * @since 1.7.0
 */

// Get current theme.
$theme = cbox_get_theme();

/*
 * Fix a problem with CBOX Theme when updating BuddyPress to v11.4.4+.
 *
 * Drastic times calls for drastic measures!
 *
 * @see https://github.com/cuny-academic-commons/commons-in-a-box/issues/517#issuecomment-3141955221
 */
if ( ( version_compare( bp_get_version(), '11.4.4' ) < 0 ) && $theme->get_template() === cbox_get_theme_prop( 'directory_name' ) ) {
	$ajax = BP_PLUGIN_DIR . 'bp-themes/bp-default/_inc/ajax.php';

	/*
	 * cbox-theme 1.6.0 has a hardcoded require line for bp-default's ajax.php.
	 *
	 * When we update BuddyPress to v11.4.4+, this file no longer exists and will
	 * cause a fatal error. To address this, we're going to add back this ajax.php
	 * as a dummy file.
	 */
	if ( ! file_exists( $ajax ) ) {
		wp_mkdir_p( BP_PLUGIN_DIR . 'bp-themes/bp-default/_inc/' );
		file_put_contents( $ajax, '<?php' );
	}
}