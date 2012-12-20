
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
				<h3><?php _e( 'Plugin Updates', 'cbox' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Since the last major version release, the following plugins were updated in CBOX:', 'cbox' ); ?></h4>
					<ul>
						<li>BuddyPress Group Email Subscription 3.3.1</li>
						<li>BuddyPress Docs 1.2.8</li>
					</ul>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Theme Improvements', 'cbox' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Since the last major version release, the following bugfixes and enhancements were added to the Commons In A Box theme:', 'cbox' ); ?></h4>
					<ul>
						<li>Improved appearance for the Messages panel</li>
						<li>Improved support for BuddyPress plugins that introduce sub-navigation, such as Bebop</li>
					</ul>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Under the Hood', 'cbox' ); ?></h3>

				<div class="feature-section col two-col">
					<div>
						<h4><?php _e( 'Behind The Scenes Forum Integration', 'cbox' ); ?></h4>
						<p><?php _e( "If you're installing CBOX and BuddyPress for the first time, CBOX will automatically configure the best forums setup for you.", 'cbox' ); ?></p>

						<p><?php printf( __( "If you have an existing BuddyPress site and are using BP's Discussion Forums component, <a href='%s'>please read this article</a>.", 'cbox' ), 'http://commonsinabox.org/documentation/buddypress-vs-bbpress-forums' ); ?></p>
					</div>

					<div class="last-feature">
						<h4><?php _e( 'Improved Support for Child Themes', 'cbox' ); ?></h4>
						<p><?php _e( "Running a child theme of cbox-theme? Cool! Commons In A Box now does a better job recognizing your child theme, and upgrading the parent theme seamlessly in the background.", 'cbox' ); ?></p>
					</div>
				</div>

				<div class="feature-section col two-col">
					<div>
						<h4><?php _e( 'Localization Support', 'cbox' ); ?></h4>
						<p><?php _e( 'CBOX is now fully localized.', 'cbox' ); ?></p>

						<p><?php printf( __( "If you want to translate CBOX to another language, please <a href='%s'>contact us</a> or <a href='%s'>send us a pull request on Github</a> with your translation files and we will bundle it in the next version of the plugin.", 'cbox' ), 'mailto:commons@gc.cuny.edu', 'https://github.com/cuny-academic-commons/commons-in-a-box' ); ?></p>
					</div>
				</div>

			</div>

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'cbox' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
