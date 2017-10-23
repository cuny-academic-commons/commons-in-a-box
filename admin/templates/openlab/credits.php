
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to CBOX-OL %s', 'cbox' ), cbox_get_version() ); ?></h1>

			<div class="about-text" style="min-height:90px;"><?php esc_html_e( 'Commons in a Box: OpenLab (CBOX-OL) is a free software project that aims to bring humanities instruction into the public square by making the work of faculty and students more connected to the outside world.', 'cbox' ); ?></div>

			<div class="wp-badge"><?php printf( __( 'Version %s' ), cbox_get_version() ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a href="<?php echo self_admin_url( 'admin.php?page=cbox&whatsnew=1' ); ?>" class="nav-tab">
					<?php _e( 'What&#8217;s New', 'cbox' ); ?>
				</a>
				<a href="<?php echo self_admin_url( 'admin.php?page=cbox&credits=1' ); ?>" class="nav-tab nav-tab-active">
					<?php _e( 'Credits', 'cbox' ); ?>
				</a>
			</h2>
			
			<p class="about-description"><?php _e( 'CBOX-OL is created by these individuals.', 'cbox' ); ?></p>

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'cbox' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
