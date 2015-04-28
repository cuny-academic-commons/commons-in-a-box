
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
				<h3><?php _e( '1.0.10 Maintenance Release', 'cbox' ); ?></h3>
				<p><?php _e( 'The 1.0.10 release brings a number of plugins up to date, and improves compatibility with WordPress 4.2 and BuddyPress 2.2.', 'cbox' ) ?></p>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Plugin Updates', 'cbox' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Since the last version release, the following plugins were updated in CBOX:', 'cbox' ); ?></h4>
					<ul>
						<li>bbPress 2.5.7</li>
						<li>BP Group Announcements 1.0.5</li>
						<li>BP Groupblog 1.8.11</li>
						<li>BuddyPress 2.2.3.1</li>
						<li>BuddyPress Docs 1.8.6</li>
						<li>BuddyPress Docs Wiki add-on 1.0.9</li>
						<li>BuddyPress Group Email Subscription 3.5.1</li>
						<li>CAC Featured Content 1.0.5</li>
						<li>External Group Blogs 1.6.0</li>
						<li>Invite Anyone 1.3.7</li>
					</ul>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Theme Improvements', 'cbox' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Since the last version release, the following bugfixes and enhancements were added to the Commons In A Box theme:', 'cbox' ); ?></h4>
					<ul>
						<li><?php _e( 'Added an option to the Recent Blog Posts widget that allows the inclusion of groupblog-related posts.', 'cbox' ) ?></li>
						<li><?php _e( 'Improved styling for forum topic headers.', 'cbox' ) ?></li>
						<li><?php _e( 'Fixed bug where theme admin panel JavaScript did not load on some setups.', 'cbox' ) ?></li>
						<li><?php _e( 'Fixed bug that prevented media uploads in the theme admin on some setups.', 'cbox' ) ?></li>
						<li><?php _e( 'Updated registration templates to match recent versions of BuddyPress.', 'cbox' ) ?></li>
						<li><?php _e( 'Added support for the DiRT Directory Client plugin.', 'cbox' ) ?></li>
						<li><?php _e( "Improved appearance of CBOX Theme's mobile menu with the WP Admin Bar.", 'cbox' ) ?></li>
						<li><?php _e( 'Improved appearance of form fields on IE.', 'cbox' ) ?></li>
						<li><?php _e( 'Improved compatibility with bxSlider.', 'cbox' ) ?></li>
						<li><?php _e( 'Improved localizability of some strings.', 'cbox' ) ?></li>
						<li><?php _e( 'Improved appearance of groupblog navigation item.', 'cbox' ) ?></li>
					</ul>

				</div>
			</div>

			<?php /*
			<div class="changelog">
				<h3><?php _e( 'Under the Hood', 'cbox' ); ?></h3>

				<div class="feature-section col two-col">

					<div class="last-feature">

						<p><strong>Paste from Microsoft Word in forums</strong> We've added a button to the visual editor to make it easier to paste content that you've formatted in Microsoft Word.</p>

					</div>
				</div>
			</div>
			*/ ?>

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'cbox' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
