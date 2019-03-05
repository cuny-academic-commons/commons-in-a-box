
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Commons In A Box OpenLab %s', 'cbox' ), cbox_get_version() ); ?></h1>

			<div class="about-text" style="min-height:90px;"><?php printf( __( 'Thank you for updating to the latest version!', 'cbox' ), cbox_get_version() ); ?></div>

			<div class="cbox-badge">
				<img src="<?php echo esc_url( cbox_get_package_prop( 'badge_url' ) ); ?>" srcset="<?php echo esc_url( cbox_get_package_prop( 'badge_url_2x' ) ); ?> 2x" alt="<?php esc_attr_e( 'Badge', 'cbox' ); ?>" />
			</div>

			<h2 class="nav-tab-wrapper">
				<a href="<?php echo self_admin_url( 'admin.php?page=cbox&whatsnew=1' ); ?>" class="nav-tab nav-tab-active">
					<?php _e( 'What&#8217;s New', 'cbox' ); ?>
				</a>
				<a href="http://commonsinabox.org/project-team" class="nav-tab" target="_blank">
					<?php _e( 'Credits', 'cbox' ); ?>
				</a>
			</h2>

			<div class="changelog">
				<h3><?php _e( 'Under the Hood', 'cbox' ); ?></h3>

				<div class="feature-section col two-col">

					<div class="last-feature">

						<p><?php esc_html_e( 'Fixed problem with image and other upload paths after site cloning.', 'cbox' ); ?></p>
						<p><?php esc_html_e( 'Fixed Google Maps API key integration in Event Organiser.', 'cbox' ); ?></p>
						<p><?php esc_html_e( 'Fixed problems with category fetching when creating new events in BP Event Organiser.', 'cbox' ); ?></p>
						<p><?php esc_html_e( 'Improved compatibility with older versions of PHP.', 'cbox' ); ?></p>
						<p><?php esc_html_e( 'Improved compatibility non-standard multisite and multinetwork configurations.', 'cbox' ); ?></p>
						<p><?php esc_html_e( 'Improved coverage of CLI update tools.', 'cbox' ); ?></p>
						<p><?php esc_html_e( 'Improved compatibility with WordPress 5.0+.', 'cbox' ); ?></p>
						<p><?php esc_html_e( 'Localization improvements.', 'cbox' ); ?></p>

					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Plugin Updates', 'cbox' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li>Anthologize 0.8.0</li>
						<li>Braille 0.0.6</li>
						<li>BuddyPress 4.2.0</li>
						<li>BuddyPress Docs 2.1.2</li>
						<li>Event Organiser 3.7.4</li>
						<li>Invite Anyone 1.4.0</li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Theme Updates', 'cbox' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li>Improved compatibility with BuddyPress 4.0 privacy tools</li>
					</ul>
				</div>
			</div>

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'cbox' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
