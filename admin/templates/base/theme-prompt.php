
	<?php if ( cbox_get_theme_prop( 'screenshot_url' ) ) : ?>
		<a rel="leanModal" title="<?php esc_html_e( 'View a larger screenshot', 'cbox' ); ?>" href="#cbox-theme-screenshot" style="float:right; margin-left:2em;"><img width="200" src="<?php echo esc_url( cbox_get_theme_prop( 'screenshot_url' ) ); ?>" alt="" /></a>
	<?php endif; ?>

	<p><?php esc_html_e( 'One last step!', 'cbox' ); ?></p>
	
	<p><?php printf( __( 'The %1$s Theme is the final piece of the Commons In A Box %2$s experience.  It ties together all functionality offered by the %2$s box in a beautiful package.', 'cbox' ), cbox_get_theme_prop( 'name' ), cbox_get_package_prop( 'name' ) ); ?></p>
	
	<p><?php esc_html_e( __( 'Please note: clicking on "Install Theme" will change your current theme.  If you would rather keep your existing theme, click on "Skip".', 'cbox' ) ); ?></p>

	<?php if ( cbox_get_theme_prop( 'screenshot_url' ) ) : ?>
		<div id="cbox-theme-screenshot" style="display:none;">
			<img src="<?php echo esc_url( cbox_get_theme_prop( 'screenshot_url' ) ); ?>" alt="" />
		</div>

		<script type="text/javascript">jQuery("a[rel*=leanModal]").leanModal();</script>
	<?php endif; ?>
