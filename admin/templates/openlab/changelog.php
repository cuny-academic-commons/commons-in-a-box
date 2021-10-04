
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
						<li><?php esc_html_e( 'Compatibility with WordPress 5.8+ and BuddyPress 9.0+.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Introduce the "Creators" feature, allowing greater customization over the way a group\'s Acknowledgements are displayed and inherited by clones.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Introduce the "OpenLab Private Comments" site plugin, which allows users to post comments that are visible only to the site owner.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Introduce the "OpenLab Attributions" site plugins, a powerful tool for generating and displaying inline attributions into post content.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Introduce the Creative Commons widget.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Introduce a toggle that allows site admins to disable the OpenLab toolbar for logged-out users visiting their sites.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed bug that could cause duplicate bbPress forums to be created for a group.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed bug that prevented Pending forum topics from being viewed on the front end.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Add an admin email notification when a forum post is marked as Pending.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improved behavior when restoring a forum topic from trash/spam.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improve bbPress redirect behavior after CBOX installation.', 'commons-in-a-box' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Plugin Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li>BuddyPress 9.1.1</li>
						<li>BuddyPress Group Email Subscription 4.0.1</li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Theme Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li><?php esc_html_e( 'Introduce a clone "counter", which shows a tally of a group\'s descendants.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improvements for group search, including respect for quoted phrases.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Introduce Delete buttons to Message panels.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improvements to the way fields are pre-filled and validated when cloning an existing group.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Accessibility and contrast improvements to the Messages panel.', 'commons-in-a-box' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'commons-in-a-box' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
