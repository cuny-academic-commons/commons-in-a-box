
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Commons In A Box %s', 'commons-in-a-box' ), cbox_get_version() ); ?></h1>

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

			<?php /*
			<div class="changelog">
				<h3><?php _e( 'Under the Hood', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section col two-col">

					<div class="last-feature">
						<p><?php esc_html_e( 'Improved compatibility with BP Customizer settings when using the Nouveau template pack.', 'commons-in-a-box' ); ?></p>
						<p><?php esc_html_e( 'Improvements to the behavior of network-activated CBOX plugins.', 'commons-in-a-box' ); ?></p>
						<p><?php esc_html_e( 'Fixed bug in WP-CLI tools that caused theme update to unzip to wrong location in some cases.', 'commons-in-a-box' ); ?></p>
						<p><?php esc_html_e( 'Improved compatibility with Multi-Network setups.', 'commons-in-a-box' ); ?></p>
					</div>
				</div>
			</div>
			*/ ?>

			<div class="changelog">
				<h3><?php esc_html_e( 'Plugin Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li>BuddyPress 6.1.0</li>
						<li>BuddyPress Docs 2.1.4</li>
					</ul>
				</div>
			</div>

			<?php /*
			<div class="changelog">
				<h3><?php esc_html_e( 'Theme Updates', 'commons-in-a-box' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li><?php esc_html_e( 'Fixed an issue with making searches on BuddyPress directory pages.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed a bug with tab navigation in CBOX Theme options.', 'commons-in-a-box' ); ?></li>
					</ul>
				</div>
			</div>
			*/ ?>

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'commons-in-a-box' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
