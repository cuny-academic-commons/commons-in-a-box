
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
				<h3><?php _e( 'New Dashboard', 'cbox' ); ?></h3>
			
				<div class="feature-section images-stagger-right">
					<img class="image-50" alt="" src="data:image/png;base64,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" />
					<h4><?php _e( 'Simple Plugin Management', 'cbox' ); ?></h4>
					<p><?php _e( "Easily manage CBOX's recommended plugins from the CBOX dashboard!", 'cbox' ); ?></p>

					<h4><?php _e( 'CBOX Gets a Face Lift', 'cbox' ); ?></h4>
					<p><?php _e( "A tip of the hat to WordPress' own dashboard!", 'cbox' ); ?></p>
				</div>
			</div>
			
			<div class="changelog">
				<h3><?php _e( 'Under the Hood' ); ?></h3>
			
				<div class="feature-section three-col">
					<div>
						<h4><?php _e( 'Plugin Install API', 'cbox' ); ?></h4>
						<p><?php _e( 'CBOX is now able to recommend plugins to you so you can install, upgrade or activate these plugins from the CBOX dashboard with ease.', 'cbox' ); ?></p>
					</div>
					<div>
						<h4><?php _e( 'External Libraries', 'cbox' ); ?></h4>
						<p><?php _e( 'Plugin Dependencies was tweaked and added.' ); ?></p>
					</div>
					<div class="last-feature">			
					</div>
				</div>
			</div>
			
			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'cbox' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
