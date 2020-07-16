
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

			<?php /*
			<div class="changelog">
				<h3><?php _e( 'Under the Hood', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section col two-col">

					<div class="last-feature">

						<p><?php esc_html_e( 'Hide "Group Home" link from site navs when the group is inaccessible to current user.', 'commons-in-a-box' ); ?></p>
						<p><?php esc_html_e( 'Fix bug that could prevent site admins from accessing Dashboard > Plugins in some cases.', 'commons-in-a-box' ); ?></p>
						<p><?php esc_html_e( 'Improvements to the behavior of network-activated CBOX plugins.', 'commons-in-a-box' ); ?></p>
						<p><?php esc_html_e( 'Fixed bug in WP-CLI tools that caused theme update to unzip to wrong location in some cases.', 'commons-in-a-box' ); ?></p>
						<p><?php esc_html_e( 'Improved compatibility with Multi-Network setups.', 'commons-in-a-box' ); ?></p>

					</div>
				</div>
			</div>
			*/ ?>

			<div class="changelog">
				<h3><?php esc_html_e( 'Plugin Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li>bbPress 2.6.5</li>
						<li>BuddyPress 6.1.0</li>
						<li>BuddyPress Docs 2.1.4</li>
						<li>BuddyPress Event Organiser 1.2.0</li>
						<li>BuddyPress Group Email Subscription 4.0.0</li>
						<li>Event Organiser 3.9.1</li>
						<li>Invite Anyone 1.4.1</li>
						<li>PressForward 5.2.3</li>
						<li>WP Grade Comments 1.3.2</li>
					</ul>
				</div>
			</div>

			<?php /*
			<div class="changelog">
				<h3><?php esc_html_e( 'Theme Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li><?php esc_html_e( 'Fixed bug with BuddyPress Docs edit mode.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improved appearance of single page/post content.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed incorrect "Recent Docs" and "Recent Discussions" subheaders when WordPress is installed in a subdirectory.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improved language regarding "Professor(s)" in group headers.', 'commons-in-a-box' ); ?></li>
					</ul>
				</div>
			</div>
			*/ ?>

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'commons-in-a-box' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
