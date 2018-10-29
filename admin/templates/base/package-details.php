
<?php if ( cbox_get_theme_prop( 'name', $_GET[ 'cbox-package-details' ] ) ) : ?>

	<?php if ( cbox_get_theme_prop( 'screenshot_url', $_GET[ 'cbox-package-details' ] ) ) : ?>
		<a class="thickbox" title="<?php esc_html_e( 'Screenshot of theme', 'cbox' ); ?>" href="<?php echo esc_url( cbox_get_theme_prop( 'screenshot_url', $_GET[ 'cbox-package-details' ] ) ); ?>" style="float:right; margin-left:2em;"><img width="200" src="<?php echo esc_url( cbox_get_theme_prop( 'screenshot_url', $_GET[ 'cbox-package-details' ] ) ); ?>" alt="" /></a>
	<?php endif; ?>

	<h3><?php esc_html_e( 'Theme', 'cbox' ); ?></h3>
	<?php cbox_get_template_part( 'package-details-theme', $_GET[ 'cbox-package-details' ] ); ?>

<?php endif; ?>

<h3><?php esc_html_e( 'Plugins', 'cbox' ); ?></h3>
<?php cbox_get_template_part( 'package-details-plugins', $_GET[ 'cbox-package-details' ] ); ?>
