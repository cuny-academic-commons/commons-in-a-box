
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Commons In A Box OpenLab %s', 'commons-in-a-box' ), cbox_get_version() ); ?></h1>

			<div class="about-text" style="min-height:90px;"><?php printf( __( 'Thank you for updating to the latest version!', 'commons-in-a-box' ), cbox_get_version() ); ?></div>

			<div class="cbox-badge">
				<img src="<?php echo esc_url( cbox_get_package_prop( 'badge_url' ) ); ?>" srcset="<?php echo esc_url( cbox_get_package_prop( 'badge_url_2x' ) ); ?> 2x" alt="<?php esc_attr_e( 'Badge', 'commons-in-a-box' ); ?>" />
			</div>

			<h2 class="nav-tab-wrapper">
				<a href="<?php echo self_admin_url( 'admin.php?page=cbox&whatsnew=1' ); ?>" class="nav-tab nav-tab-active">
					<?php _e( 'What&#8217;s New', 'commons-in-a-box' ); ?>
				</a>
				<a href="http://commonsinabox.org/project-team" class="nav-tab" target="_blank">
					<?php _e( 'Credits', 'commons-in-a-box' ); ?>
				</a>
			</h2>

			<div class="changelog">
				<h3><?php _e( 'Under the Hood', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section col two-col">
					<ul>
						<li><?php esc_html_e( 'Fixed redirect behavior after leaving a comment on a Doc.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improved compatibility between site cloning and the wp-piwik plugin.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Better scoping for the display of the Dashboard Panel.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fix bug that caused post-sharing-options error when using the Block widgets panel.', 'commons-in-a-box' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Plugin Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li>bbPress 2.6.14</li>
						<li>BuddyPress 14.4.0</li>
						<li>BuddyPress Docs 2.2.6</li>
						<li>BuddyPress Docs In Group 1.0.5</li>
						<li>Event Organiser 3.12.8</li>
						<li>PressForward 5.9.3</li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Theme Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li><?php esc_html_e( 'Fixed behavior of Previous Step button during group creation.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed the bulk-user-import acknowledgement checkbox.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed behavior of the portfolio site admin link when viewing as the non-owner.', 'commons-in-a-box' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'commons-in-a-box' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
