
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
						<li><?php esc_html_e( 'Compatibility with WordPress 5.6+.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed the logic of the default "Term" value when creating a Course.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed bug that made it impossible to save an empty value for "Term" in a Course.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improved behavior when populating the "Clone Existing" dropdown during group creation.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Better flushing of permalinks on newly created sites.', 'commons-in-a-box' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Plugin Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li>BuddyPress 6.4.0</li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Theme Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li><?php esc_html_e( 'Compatibility with latest versions of jQuery included in WordPress 5.6+.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improvements to the behavior of nav menus on medium-width screens.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed "Sent between" text that appears on private message threads.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Better consistency when generating group navigation for group Moderators.', 'commons-in-a-box' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'commons-in-a-box' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
