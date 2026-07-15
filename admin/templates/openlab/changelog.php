
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
						<li><?php esc_html_e( 'Overhauled the customizations that CBOX applies to the WP toolbar on secondary sites, for better theme compatibility and ease-of-use.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'New Communications widgets on site Dashboards.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improved user-facing error reporting during the site creation flow.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Changed the behavior of the site template picker so that no template is selected by default.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Ensure that super admins can always use the bulk group-member import feature.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'During portfolio creation, "show portfolio link" is now checked by default.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Added a feature that allows network admins to disable the core WordPress Welcome banner by default.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed bug that prevented the "WordPress Biography" BuddyPress field type from being properly usable on CBOX profiles.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed bug that prevented Gravity Forms forms from being copied during site clone.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed bug that prevented the site template chooser from appearing when creating a site via the Settings section of an existing group.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed bug that caused group site link not to appear in sidebar in certain cases.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed bug arising from legacy group data after BP 14.0.0 update.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed bug that caused "Hi, {user}" link to go to the wrong place in certain instances.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed certain notices appearing in the Site Editor.', 'commons-in-a-box' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Plugin Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li>BuddyPress 14.5.0</li>
						<li>BuddyPress Docs 2.2.7</li>
						<li>BuddyPress Docs in Group 1.0.5</li>
						<li>PressForward 5.9.5</li>
						<li>OpenLab Attributions 2.1.5</li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Theme Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li><?php esc_html_e( 'Added "attention required" bubble for pending membership requests to group nav menus.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Ensure that "Sign Up" sectiond doesn\'t appear in the homepage login box when registration is disabled.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improve "required" behavior for academic units during group creation/edit.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improve handling of browser focus during registration AJAX events.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Contrast improvements for accessibility.', 'commons-in-a-box' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'commons-in-a-box' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
