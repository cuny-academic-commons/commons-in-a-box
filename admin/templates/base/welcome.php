
		<div id="welcome-panel" class="<?php cbox_welcome_panel_classes(); ?>">
			<h2><?php echo esc_html( cbox_get_string( 'dashboard_header' ) ); ?></h2>

			<div class="wp-badge"><?php printf( __( 'Version %s', 'cbox' ), cbox_get_version() ); ?></div>

			<div class="welcome-panel-content">
				<?php cbox_get_template_part( 'welcome-description' ); ?>
			</div><!-- .welcome-panel-content -->

		</div><!-- #welcome-panel -->