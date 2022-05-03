
		<div id="welcome-panel" class="<?php cbox_welcome_panel_classes(); ?>">
			<h2><?php echo esc_html( cbox_get_string( 'dashboard_header' ) ); ?></h2>

			<div class="cbox-badge">
				<img src="<?php echo esc_url( cbox_get_package_prop( 'badge_url' ) ); ?>" srcset="<?php echo esc_url( cbox_get_package_prop( 'badge_url_2x' ) ); ?> 2x" alt="<?php esc_attr_e( 'Badge', 'commons-in-a-box' ); ?>" />
				<div class="cbox-version">
					<?php printf( __( 'Version %s', 'commons-in-a-box' ), cbox_get_version() ); ?>
				</div>
			</div>

			<div class="cbox-welcome-panel-content">
				<?php cbox_get_template_part( 'welcome-description' ); ?>
			</div><!-- .welcome-panel-content -->

		</div><!-- #welcome-panel -->
