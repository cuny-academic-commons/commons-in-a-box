<p><?php printf( __( '<strong>Commons In A Box</strong> is a software project aimed at turning the infrastructure that successfully powers the <a href="%s">CUNY Academic Commons</a> into a free, distributable, easy-to-install package.', 'cbox' ), esc_url( 'http://commons.gc.cuny.edu' ) ); ?></p>

<p><?php esc_html_e( 'Commons In A Box is made possible by a generous grant from the Alfred P. Sloan Foundation.', 'cbox' ); ?></p>

<ul class="subsubsub">
<li><a href="<?php echo cbox_admin_prop( 'url', 'admin.php?page=cbox&amp;whatsnew=1' ); ?>"><?php esc_html_e( "What's New", 'cbox' ); ?></a> |</li>
<li><a href="<?php echo cbox_admin_prop( 'url', 'admin.php?page=cbox&amp;credits=1' ); ?>"><?php esc_html_e( 'Credits', 'cbox' ); ?></a> |</li>
<li><a href="http://commonsinabox.org/documentation/"><?php _e( 'Documentation', 'cbox' ); ?></a></li>
</ul>