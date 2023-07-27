
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
						<li><?php esc_html_e( 'Introduce Template Chooser tool, allowing users creating a new site to select from an admin-curated set of template sites.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improvements to the Attributions tool, including a movable modal and better performance on mobile devices.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improvements to the Import/Export tool, including clearer UI for downloading archives and better tools for selecting authors during export.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Introduce a new tool allowing Academic Terms (eg "Fall 2023") to be editable and sortable by the admin.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed bug in Docs pagination inside groups.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed bug that caused some labels not to be properly pre-filled when creating new Member Type.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed bug that caused sitewide footer to appear when editing widgets.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Ensure that new blog comments trigger a "last active" change for the corresponding group.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed bug that caused Academic Term order to be forgotten on certain edit events.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improved appearance of Log In toolbar button across themes.', 'commons-in-a-box' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Plugin Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li>BuddyPress 11.2.0</li>
						<li>BuddyPress Group Email Subscription 4.1.0</li>
						<li>BP Group Documents 1.21</li>
						<li>Event Organiser 3.12.3</li>
						<li>Invite Anyone 1.4.4</li>
						<li>OpenLab Attributions 2.1.2</li>
						<li>OpenLab Portfolio 1.1.2</li>
						<li>PressForward 5.5.0</li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Theme Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li><?php esc_html_e( 'Support for per-profile-field visibility.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Added a "sort" dropdown to My Profile group lists.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Added support for @-mentions in group forum posts.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Accessibility improvements.', 'commons-in-a-box' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'commons-in-a-box' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
