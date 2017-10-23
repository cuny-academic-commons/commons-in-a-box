<p><?php esc_html_e( 'Commons in a Box: OpenLab (CBOX-OL) is a free software project that aims to bring humanities instruction into the public square by making the work of faculty and students more connected to the outside world.', 'cbox' ); ?></p>

<p><?php esc_html_e( 'It allows members to discuss and collaborate on coursework and research, share creations publically through digital portfolios, form clubs to share initiatives and interests, and generally create a more diverse online community for your institution.', 'cbox' ); ?></p>

<p><?php printf( __( 'CBOX-OL is a project of the %1$s, %2$s, and %3$s and is made possible by a generous grant from the %4$s.', 'cbox' ), '<a href="http://www.cuny.edu" target="_blank">City University of New York</a>', '<a href-"http://www.gc.cuny.edu" target="_blank">the Graduate Center, CUNY</a>', '<a href="http://www.citytech.cuny.edu/" target="_blank"New York City College of Technology, CUNY</a>', '<a href="https://www.neh.gov/" target="_blank">National Endowment for the Humanities</a>' ); ?> </p>

<ul class="subsubsub">
<li><a href="<?php echo cbox_admin_prop( 'url', 'admin.php?page=cbox&amp;whatsnew=1' ); ?>"><?php esc_html_e( "What's New", 'cbox' ); ?></a> |</li>
<li><a href="<?php echo cbox_admin_prop( 'url', 'admin.php?page=cbox&amp;credits=1' ); ?>"><?php esc_html_e( 'Credits', 'cbox' ); ?></a> |</li>
<li><a href="http://commonsinabox.org/documentation/"><?php _e( 'Documentation', 'cbox' ); ?></a></li>
</ul>