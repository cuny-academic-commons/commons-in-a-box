
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
				<h3><?php _e( '1.0.16 maintenance Release', 'cbox' ); ?></h3>
				<p><?php _e( 'The 1.0.16 release brings a number of plugins up to date, and improves compatibility with BuddyPress 2.9.', 'cbox' ) ?></p>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Plugin Updates', 'cbox' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Since the last version release, the following plugins were updated in CBOX:', 'cbox' ); ?></h4>
					<ul>
						<li>BuddyPress 2.9.1</li>
						<li>bbPress 2.5.14</li>
						<li>BuddyPress Docs 1.9.4</li>
						<li>BuddyPress Group Email Subscription 3.7.2</li>
						<li>BuddyPress Reply By Email 1.0-RC5</li>
						<li>Invite Anyone 1.3.19</li>
					</ul>

				</div>
			</div>

			<?php /*
			<div class="changelog">
				<h3><?php _e( 'Theme Improvements', 'cbox' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Since the last version release, the following bugfixes and enhancements were added to the Commons In A Box theme:', 'cbox' ); ?></h4>
					<ul>
						<li><?php _e( 'Fix positioning of search interface on forum tag page.', 'cbox' ) ?></li>
						<li><?php _e( 'Fix positioning bug with certain widget titles.', 'cbox' ) ?></li>
						<li><?php _e( 'Improve compatibility with BuddyPress 2.8+.', 'cbox' ) ?></li>
						<li><?php _e( 'Improve compatibility with PHP 7.1+.', 'cbox' ) ?></li>
						<li><?php _e( 'Fix some PHP notices.', 'cbox' ) ?></li>
					</ul>

				</div>
			</div>
			*/ ?>

			<div class="changelog">
				<h3><?php _e( 'Under the Hood', 'cbox' ); ?></h3>

				<div class="feature-section col two-col">

					<div class="last-feature">

						<p>Security hardening for protection against certain upload vulnerabilities.</p>

					</div>
				</div>
			</div>

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'cbox' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
