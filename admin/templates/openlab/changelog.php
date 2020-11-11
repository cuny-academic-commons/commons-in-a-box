
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
						<li><?php esc_html_e( 'New Badges tool for groups.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'New import and export tools for Portfolios.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'New search results landing page for groups, with improved sidebar filters.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Added directory filters for "Open" and "Cloneable" groups.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'New group cloning features, including "shared cloning", a Credits section for groups and sites, and cloning for all group types.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Added fine-grained control over how group roles map to site roles.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improvements to "Additional Faculty" and "Group Contact" tools.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Group-level toggles for Docs, Files, Discussions, and Files features.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improved validation of URLs during the group creation process.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Allow users without a member type to select their own member type on their Settings panel, for better compatibility with auto-provisioned user accounts.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed bug that could cause an invalid default theme to be installed on group-type template sites.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed bug in the way that network toolbar loads on subdomain installations.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Accessibility improvements for Dashboard admin panels.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Added CLI tools for performing data migrations after CBOX upgrade.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed text visibility bug when editing a group event.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Performance improvements related to user-defined strings.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improved compatibility between network toolbar and latest versions of Block Editor.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Localization improvements.', 'commons-in-a-box' ); ?></li>

					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Plugin Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li>bbPress 2.6.6</li>
						<li>BuddyPress 6.3.0</li>
						<li>BuddyPress Docs 2.1.5</li>
						<li>Event Organiser 3.10.2</li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Theme Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li><?php esc_html_e( 'Increased the number of group members shown on the group Settings panels.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Simplification of some template parts.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improved filterability of "lost password" length in homepage login box.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improved "current page" highlighting on main nav menu.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed bug that prevented group action buttons ("Join Group", etc) from being shown to network administrators.', 'commons-in-a-box' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'commons-in-a-box' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
