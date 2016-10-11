
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Commons In A Box %s', 'cbox' ), cbox_get_version() ); ?></h1>

			<div class="about-text" style="min-height:90px;"><?php printf( __( 'Thank you for updating to the latest version!', 'cbox' ), cbox_get_version() ); ?></div>

			<div class="wp-badge"><?php printf( __( 'Version %s' ), cbox_get_version() ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a href="<?php echo self_admin_url( 'admin.php?page=cbox&whatsnew=1' ); ?>" class="nav-tab nav-tab-active">
					<?php _e( 'What&#8217;s New', 'cbox' ); ?>
				</a>
				<a href="<?php echo self_admin_url( 'admin.php?page=cbox&credits=1' ); ?>" class="nav-tab">
					<?php _e( 'Credits', 'cbox' ); ?>
				</a>
			</h2>

			<div class="changelog">
				<h3><?php _e( '1.0.13 Maintenance Release', 'cbox' ); ?></h3>
				<p><?php _e( 'The 1.0.13 release brings a number of plugins up to date, and improves compatibility with WordPress 4.6 and BuddyPress 2.6.', 'cbox' ) ?></p>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Plugin Updates', 'cbox' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Since the last version release, the following plugins were updated in CBOX:', 'cbox' ); ?></h4>
					<ul>
						<li>BuddyPress 2.6.2</li>
						<li>bbPress 2.5.10</li>
						<li>BP Groupblog 1.8.13</li>
						<li>BuddyPress Docs 1.9.1</li>
						<li>BuddyPress Docs Wiki 1.9.1</li>
						<li>BuddyPress Group Email Subscription 3.7.0</li>
						<li>BuddyPress Reply By Email 1.0-RC4</li>
						<li>CAC Featured Content 1.0.7</li>
						<li>External Group Blogs 1.6.1</li>
						<li>Invite Anyone 1.3.11</li>
						<li>More Privacy Options 4.6</li>
					</ul>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Theme Improvements', 'cbox' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Since the last version release, the following bugfixes and enhancements were added to the Commons In A Box theme:', 'cbox' ); ?></h4>
					<ul>
						<li><?php _e( 'Improved escaping for some strings.', 'cbox' ) ?></li>
						<li><?php _e( 'Don\'t show settings links when Settings component is inactive.', 'cbox' ) ?></li>
						<li><?php _e( 'Fix some PHP notices.', 'cbox' ) ?></li>
						<li><?php _e( 'Fix bug that prevented admins from visiting a private group\'s Manage tab.', 'cbox' ) ?></li>
						<li><?php _e( 'Fix bug with Manage Folders interface in BuddyPress Docs 1.9+', 'cbox' ) ?></li>
					</ul>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Under the Hood', 'cbox' ); ?></h3>

				<div class="feature-section col two-col">

					<div class="last-feature">

						<p>Fixed a bug that could cause errors when installing Commons In A Box on top of an existing BuddyPress installation.</p>

					</div>
				</div>
			</div>

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'cbox' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
