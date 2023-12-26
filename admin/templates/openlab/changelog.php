
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
						<li><?php esc_html_e( 'New "External Files" feature in group File Library.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Support for search within group Discussions.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improvements to the formatting of email notifications.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'New "Add to Portfolio" toggle during portfolio creation.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improvements to Site Template interface.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improve compatibility with Broken Link Checker plugin during site clone.', 'commons-in-a-box' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Plugin Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li>BP Group Documents 1.24</li>
						<li>BuddyPress 11.4.0</li>
						<li>BuddyPress Docs 2.2.1</li>
						<li>BuddyPress Group Email Subscription 4.2.1</li>
						<li>Event Organiser 3.12.4</li>
						<li>Invite Anyone 1.4.7</li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Theme Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li><?php esc_html_e( 'New "Default Profile Photo" feature in Customizer allows admin to configure the default avatar for users.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improvements to the appearance and behavior of Recent Comments section on group profiles.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'When changing a group type name, the corresponding nav item text is also changed.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improvements to the behavior of the About Sidebar.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Homepage accessibility improvements.', 'commons-in-a-box' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'commons-in-a-box' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
