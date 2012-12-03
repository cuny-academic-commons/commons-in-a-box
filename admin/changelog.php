
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
				<h3><?php _e( 'New Plugins', 'cbox' ); ?></h3>

				<div class="feature-section images-stagger-right">
					<img class="image-50" alt="" src="http://news.commons.gc.cuny.edu/files/2012/08/rbe8.jpg" />

					<h4><?php _e( 'Reply By Email', 'cbox' ); ?></h4>
					<p><?php printf( __( 'Participate in discussions on your site without leaving your inbox. When you receive a notification of a forum post, a private message, or a public @-mention, reply to the notification email, and your reply will be posted to your CBOX-powered site. <a href="%s">Find it under the "&Agrave; la carte" section</a>.  Also, be sure to <a href="%s">read the setup guide</a>.', 'cbox' ), self_admin_url( 'admin.php?page=cbox-plugins' ), 'https://github.com/r-a-y/bp-reply-by-email/wiki' ); ?></p>

					<h4><?php _e( 'Group Announcements', 'cbox' ); ?></h4>
					<p><?php printf( __( 'This plugin removes the "What\'s New" post form from group home pages and adds a new "Announcements" tab to groups. On the "Announcements" tab, all users can see updates to the group, and admins/mods can post new updates. The plugin was created to avoid confusion, which can occur when both discussion forums and activity posting are enabled in groups. <a href="%s">Find it under the "Recommended" section</a>.', 'cbox' ), self_admin_url( 'admin.php?page=cbox-plugins' ) ); ?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'New Feature', 'cbox' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'CBOX Settings Page', 'cbox' ); ?></h4>
					<p><?php printf( __( 'You can now configure some special options for certain CBOX plugins all on one page. <a href="%s">Check it out here</a>.', 'cbox' ), self_admin_url( 'admin.php?page=cbox-settings' ) ); ?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Plugin Updates', 'cbox' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'The following plugins were updated in CBOX:', 'cbox' ); ?></h4>
					<ul>
						<li>bbPress 2.2.2</li>
						<li>BuddyPress Docs 1.2.6</li>
						<li>BuddyPress Docs Wiki 1.0.2</li>
						<li>BuddyPress Group Email Subscription 3.2.3</li>
						<li>CAC Featured Content 1.0.3</li>
						<li>External Group Blogs 1.5</li>
					</ul>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Theme Improvements', 'cbox' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'The following bugfixes and enhancements were added to the Commons In A Box theme:', 'cbox' ); ?></h4>
					<ul>
						<li><?php _e( 'Fixed problem with theme options that caused them not to work on setups that forbid certain kinds of browser cookies', 'cbox' ) ?></li>
						<li><?php _e( 'Better slider performance', 'cbox' ) ?></li>
						<li><?php _e( 'Improved responsiveness', 'cbox' ) ?></li>
						<li><?php _e( 'Better theme admin performance on Windows IIS servers', 'cbox' ) ?></li>
						<li><?php _e( 'Improved current-page highlighting on BuddyPress item navigation', 'cbox' ) ?></li>
						<li><?php _e( 'Fixed some broken image links', 'cbox' ) ?></li>
						<li><?php _e( 'Improved styling for bbPress 2.2.x forum threads', 'cbox' ) ?></li>
						<li><?php _e( 'Improved default menu configuration', 'cbox' ) ?></li>
						<li><?php _e( 'Added a login/logout link to mobile navigation', 'cbox' ) ?></li>
					</ul>

				</div>
			</div>

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'cbox' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
