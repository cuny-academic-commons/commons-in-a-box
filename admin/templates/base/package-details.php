
<?php if ( cbox_get_theme_prop( 'name', $_GET[ 'cbox-package-details' ] ) ) : ?>

	<?php if ( cbox_get_theme_prop( 'screenshot_url', $_GET[ 'cbox-package-details' ] ) ) : ?>
		<a class="thickbox" title="<?php esc_html_e( 'Screenshot of theme', 'cbox' ); ?>" href="<?php echo esc_url( cbox_get_theme_prop( 'screenshot_url', $_GET[ 'cbox-package-details' ] ) ); ?>" style="float:right; margin-left:2em;"><img width="200" src="<?php echo esc_url( cbox_get_theme_prop( 'screenshot_url', $_GET[ 'cbox-package-details' ] ) ); ?>" alt="" /></a>
	<?php endif; ?>

	<h3><?php esc_html_e( 'Theme', 'cbox' ); ?></h3>
	<p><?php printf( esc_html__( "The %s theme will change the way your site looks, and much of its architecture. Any widgets or menus that you have currently set will need to be reconfigured under the Appearance menu in the Dashboard.", 'cbox' ), cbox_get_package_prop( 'name', $_GET[ 'cbox-package-details' ] ) ); ?></p>

<?php endif; ?>

<h3><?php esc_html_e( 'Plugins', 'cbox' ); ?></h3>
<p><?php printf( __( "CBOX %s will install and activate plugins that you do not already have installed.", 'cbox' ), cbox_get_package_prop( 'name', $_GET[ 'cbox-package-details' ] ) ); ?></p>
