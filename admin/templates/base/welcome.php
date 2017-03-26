
		<div id="welcome-panel" class="<?php cbox_welcome_panel_classes(); ?>">
			<?php wp_nonce_field( 'welcome-panel-nonce', 'welcomepanelnonce', false ); ?>

			<?php if ( cbox_is_setup() ) : ?>
				<a class="welcome-panel-close" href="<?php echo esc_url( network_admin_url( 'admin.php?page=cbox&welcome=0' ) ); ?>"><?php _e( 'Dismiss', 'cbox' ); ?></a>
			<?php endif; ?>

			<div class="wp-badge"><?php printf( __( 'Version %s', 'cbox' ), cbox_get_version() ); ?></div>

			<div class="welcome-panel-content">
				<h3><?php _e( 'Welcome to Commons In A Box! ', 'cbox' ); ?></h3>

				<p class="about-description"><?php _e( 'Need help getting started? Looking for support or ideas? Check out our documentation and join the community of CBOX users at <a href="http://commonsinabox.org">commonsinabox.org</a>.', 'cbox' ) ?></p>

				<?php if ( cbox_is_setup() ) : ?>
					<p class="about-description"><?php _e( 'If you&#8217;d rather dive right in, here are a few things most people do first when they set up a new CBOX site.', 'cbox' ); ?></p>
					<p class="welcome-panel-dismiss"><?php printf( __( 'Already know what you&#8217;re doing? <a href="%s">Dismiss this message</a>.', 'cbox' ), esc_url( network_admin_url( 'admin.php?page=cbox&welcome=0' ) ) ); ?></p>
				<?php endif; ?>
			</div><!-- .welcome-panel-content -->

		</div><!-- #welcome-panel -->