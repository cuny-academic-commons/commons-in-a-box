<div class="metabox-holder postbox getting-started-cbox-classic">
	<div class="stuffbox">
		<h3><?php esc_html_e( 'Getting Started with Commons in A Box Classic', 'cbox' ); ?></h3>
	</div>

	<div class="inside">
		<div class="welcome-panel-column-container">

			<!-- SETTINGS -->
			<div class="welcome-panel-column">
				<h4><span class="icon16 icon-settings"></span> <?php _e( 'Settings', 'cbox' ); ?></h4>
				<p><?php _e( "Commons In A Box works by pulling together a number of independent WordPress and BuddyPress plugins. Customize your site by exploring the settings pages for these plugins below.", 'cbox' ); ?></p>
				<ul>

				<?php
					$cbox_plugins = CBox_Plugins::get_plugins();
					foreach ( CBox_Admin_Plugins::get_settings() as $plugin => $settings_url ) {
						echo '<li><a title="' . __( "Click here to view this plugin's settings page", 'cbox' ) . '" href="' . $settings_url .'">' . $plugin . '</a> - ' . $cbox_plugins[$plugin]['cbox_description'];

						if ( ! empty( $cbox_plugins[$plugin]['documentation_url'] ) )
							echo ' [<a title="' . __( "Click here for plugin documentation at commonsinabox.org", 'cbox' ) . '" href="' . esc_url( $cbox_plugins[$plugin]['documentation_url'] ) . '" target="_blank">' . __( 'Info...', 'cbox' ) . '</a>]';

						echo '</li>';
					}
				?>
				</ul>

				<div class="login postbox">
					<div class="message" style="text-align:center;">
						<strong><?php printf( __( '<a href="%s">Manage all your CBOX plugins here</a>', 'cbox' ), esc_url( self_admin_url( 'admin.php?page=cbox-plugins' ) ) ); ?></strong>
					</div>
				</div>
			</div>

			<!-- THEME -->
			<div class="welcome-panel-column welcome-panel-last">
				<h4><span class="icon16 icon-appearance"></span> <?php _e( 'Theme', 'cbox' ); ?></h4>
				<?php
					$theme = cbox_get_theme();

					if ( $theme->errors() ) :
						echo '<p>';
						printf( __( '<a href="%1$s">Install the %2$s theme to get started</a>.', 'cbox' ), wp_nonce_url( self_admin_url( 'admin.php?page=cbox&amp;cbox-action=install-theme' ), 'cbox_install_theme' ), esc_attr( cbox_get_theme_prop( 'name' ) ) );
						echo '</p>';
					else:

						// current theme is not the CBOX default theme
						if ( $theme->get_template() != cbox_get_theme_prop( 'directory_name' ) ) {
							$is_bp_compatible = cbox_is_theme_bp_compatible();

						?>
							<p><?php printf( __( 'Your current theme is %s.', 'cbox' ), '<strong>' . $theme->display( 'Name' ) . '</strong>' ); ?></p>

							<?php
								if ( ! $is_bp_compatible ) {
									echo '<p>';
									_e( 'It looks like this theme is not compatible with BuddyPress.', 'cbox' );
									echo '</p>';
								}
							?>

							<?php if ( cbox_get_theme_prop( 'directory_name' ) && cbox_get_theme_prop( 'screenshot_url' ) ) : ?>

								<p><?php printf( __( 'Did you know that <strong>%s</strong> comes with a cool theme? Check it out below!', 'cbox' ), esc_html( cbox_get_package_prop( 'name' ) ) ); ?></p>

								<a class="thickbox" title="<?php printf( esc_attr__( 'Screenshot of the %s theme', 'cbox' ), cbox_get_theme_prop( 'name' ) ); ?>" href="<?php echo esc_url( cbox_get_theme_prop( 'screenshot_url' ) ); ?>"><img width="200" src="<?php echo esc_url( cbox_get_theme_prop( 'screenshot_url' ) ); ?>" alt="" /></a>

								<div class="login postbox">
									<div class="message" style="text-align:center;">
										<strong><?php printf( __( '<a href="%1$s">Like the %2$s theme? Install it!</a>', 'cbox' ), wp_nonce_url( self_admin_url( 'admin.php?page=cbox&amp;cbox-action=install-theme' ), 'cbox_install_theme' ), esc_attr( cbox_get_theme_prop( 'name' ) ) ); ?></strong>
									</div>
								</div>

							<?php endif; ?>

							<?php
								if ( ! $is_bp_compatible ) {
									echo '<p>';
									printf( __( "You can also make your theme compatible with the <a href='%s'>BuddyPress Template Pack</a>.", 'buddypress' ), network_admin_url( 'plugin-install.php?type=term&tab=search&s=%22bp-template-pack%22' ) );
									echo '</p>';
								}
							?>

						<?php
						// current theme is the CBOX default theme
						} else {
						?>

							<?php if ( $theme->get_stylesheet() != cbox_get_theme_prop( 'directory_name' ) ) : ?>
								<p><?php printf( __( 'You\'re using a child theme of the <strong>%1$s</strong> theme.', 'cbox' ), esc_attr( cbox_get_theme_prop( 'name' ) ) ); ?></p>
							<?php else : ?>
								<p><?php printf( __( 'You\'re using the <strong>%1$s</strong> theme.', 'cbox' ), esc_attr( cbox_get_theme_prop( 'name' ) ) ); ?></p>
							<?php endif; ?>

							<div class="login postbox">
								<div class="message">
									<strong><?php printf( __( '<a href="%1$s">Configure the %2$s theme here</a>', 'cbox' ), esc_url( get_admin_url( cbox_get_main_site_id(), cbox_get_theme_prop( 'admin_settings' ) ) ), esc_attr( cbox_get_theme_prop( 'name' ) ) ); ?></strong>
								</div>
							</div>

						<?php
						}

					endif;
				?>
			</div>

		</div><!-- .welcome-panel-column-container -->
	</div>
</div>