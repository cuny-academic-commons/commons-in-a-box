
		<div id="cbox-about" class="secondary-panel">
			<h2><?php _e( 'About', 'cbox' ); ?></h2>

			<p><?php printf( __( "You're currently using <strong>Commons In A Box %s</strong>", 'cbox' ), cbox_get_version() ); ?>.</p>

			<p><?php printf( __( '<strong>Commons In A Box</strong> is a software project aimed at turning the infrastructure that successfully powers the <a href="%s">CUNY Academic Commons</a> into a free, distributable, easy-to-install package.', 'cbox' ), esc_url( 'http://commons.gc.cuny.edu' ) ); ?></p>

			<p><?php  _e( 'Commons In A Box is made possible by a generous grant from the Alfred P. Sloan Foundation.', 'cbox' ); ?></p>

			<ul>
				<li><a href="<?php echo network_admin_url( 'admin.php?page=cbox&amp;whatsnew=1' ); ?>"><?php _e( "What's New", 'cbox' ); ?></a></li>
				<li><a href="<?php echo network_admin_url( 'admin.php?page=cbox&amp;credits=1' ); ?>"><?php _e( 'Credits', 'cbox' ); ?></a></li>
				<li><a href="http://commonsinabox.org/documentation/"><?php _e( 'Documentation', 'cbox' ); ?></a></li>
				<li><a href="https://github.com/cuny-academic-commons/commons-in-a-box/commits/1.0.x"><?php _e( 'Dev tracker', 'cbox' ); ?></a></li>
			</ul>
		</div>