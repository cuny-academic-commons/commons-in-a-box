
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
						<li><?php esc_html_e( 'Improved markup on Welcome page for better cross-browser appearance.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improvements to Dashboard menu positioning when scrolling.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Provide some configurable labels missing in 1.2.2 release.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed bug that may cause certain group types not to appear in admin UI in some cases.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improved data cleanup after a site is manually deleted via the Network Admin.', 'commons-in-a-box' ); ?></li>

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
						<li><?php esc_html_e( 'Improved appearance for Help page templates.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed missing icon on "Clone" button.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Removed irrelevant filter dropdowns from certain content directories.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Fixed bug that caused "type" sorting not to work in member directories.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improvements to 404 template.', 'commons-in-a-box' ); ?></li>
						<li><?php esc_html_e( 'Improved backward compatibility for the display of group contacts.', 'commons-in-a-box' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'commons-in-a-box' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
