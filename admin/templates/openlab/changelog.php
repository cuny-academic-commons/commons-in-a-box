
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
						<li><?php esc_html_e( 'New feature: Bulk user import for groups.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'New feature: "Dashboard Panel" allows network admins to show a customizable message on all Dashboards in the network.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'New feature: "Main Site Banner" allows network admins to show a customizable banner on the main site of the network.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'New feature: Group admins can make a specific group "non-joinable" while keeping content publicly available.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improved nav menu behavior on group sites, including Block Theme compatibility and better handling during cloning.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improved URL replacement during site cloning.', 'commons-in-a-box' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Plugin Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li>bbPress 2.6.13</li>
						<li>BP Group Documents 2.0</li>
						<li>BuddyPress 14.3.4</li>
						<li>BuddyPress Docs 2.2.5</li>
						<li>BuddyPress Group Email Subscription 4.2.4</li>
						<li>BuddyPress Reply By Email 1.0-RC11</li>
						<li>Event Organiser 3.12.8</li>
						<li>Invite Anyone 1.4.10</li>
						<li>PressForward 5.8.0</li>
						<li>OpenLab Attributions 2.1.4</li>
						<li>WP Grade Comments 1.6.0</li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Theme Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li><?php esc_html_e( 'New feature: Sortable group membership list.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Accessibility improvements.', 'commons-in-a-box' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'commons-in-a-box' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
