<p><?php esc_html_e( 'Commons In A Box OpenLab is designed for teaching, learning, and collaboration. It allows faculty members, departments, and entire institutions to create commons spaces for open learning.', 'commons-in-a-box' ); ?></p>

<p><?php
printf(
	// translators: 1. URL of the CUNY Graduate Center; 2. URL of New York City College of Technology; 3. URL of NEH ODH, 4. URL of the CUNY OER initiative, 5. URL of the BMCC OpenLab
	__( 'It was created through a collaboration between <a href="%1$s" target="_blank">The Graduate Center, CUNY</a> and <a href="%2$s" target="_blank">New York City College of Technology, CUNY</a> with funding from the National Endowment for the Humanities’ <a href="%3$s" target="_blank">Office of Digital Humanities</a>. Funding for continued development of new features has been provided by the <a href="%4$s" target="_blank">CUNY Open Educational Resources Initiative</a> and our partners at the <a href="%5$s" target="_blank">BMCC OpenLab</a>.', 'commons-in-a-box' ),
	'https://www.gc.cuny.edu/',
	'https://www.citytech.cuny.edu/',
	'https://www.neh.gov/divisions/odh',
	'https://www.cuny.edu/libraries/open-educational-resources/',
	'https://openlab.bmcc.cuny.edu'
);
?></p>

<ul class="subsubsub">
<li><a href="<?php echo cbox_admin_prop( 'url', 'admin.php?page=cbox&amp;whatsnew=1' ); ?>"><?php esc_html_e( "What's New", 'commons-in-a-box' ); ?></a> |</li>
<li><a href="http://commonsinabox.org/project-team" target="_blank"><?php esc_html_e( 'Credits', 'commons-in-a-box' ); ?></a> |</li>
<li><a href="http://commonsinabox.org/cbox-openlab-overview" target="_blank"><?php _e( 'Documentation', 'commons-in-a-box' ); ?></a></li>
</ul>
