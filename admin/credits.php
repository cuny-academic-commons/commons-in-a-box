
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Commons In A Box %s', 'cbox' ), cbox_get_version() ); ?></h1>

			<div class="about-text" style="min-height:90px;"><?php printf( __( 'Commons In A Box is a project of the <a href="%s">City University of New York</a> and the <a href="%s">Graduate Center, CUNY</a> and is made possible by a generous grant from the <a href="%s">Alfred P. Sloan Foundation</a>.', 'cbox' ), 'http://www.cuny.edu/', 'http://www.gc.cuny.edu/', 'http://www.sloan.org/' ); ?></div>

			<div class="wp-badge"><?php printf( __( 'Version %s' ), cbox_get_version() ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a href="<?php echo self_admin_url( 'admin.php?page=cbox&whatsnew=1' ); ?>" class="nav-tab">
					<?php _e( 'What&#8217;s New', 'cbox' ); ?>
				</a>
				<a href="<?php echo self_admin_url( 'admin.php?page=cbox&credits=1' ); ?>" class="nav-tab nav-tab-active">
					<?php _e( 'Credits', 'cbox' ); ?>
				</a>
			</h2>
			
			<p class="about-description"><?php _e( 'Commons In A Box is created by these individuals.', 'cbox' ); ?></p>

			<h4 class="wp-people-group"><?php _e( 'Project Leaders', 'cbox' ); ?></h4>

			<ul class="wp-people-group " id="wp-people-group-project-leaders">
				<li class="wp-person" id="wp-person-matt">
					<a href="http://commons.gc.cuny.edu/members/admin/"><img src="http://0.gravatar.com/avatar/a9747746855c05b5ffeca1f9bd2cc938?s=60" class="gravatar" alt="Matthew K. Gold" /></a>
					<a class="web" href="http://commons.gc.cuny.edu/members/admin/">Matthew K. Gold</a>
					<span class="title"><?php _e( 'Project Lead', 'cbox' ); ?></span>
				</li>
				<li class="wp-person" id="wp-person-boone">
					<a href="http://commons.gc.cuny.edu/members/boonebgorges/"><img src="http://0.gravatar.com/avatar/9cf7c4541a582729a5fc7ae484786c0c?s=60" class="gravatar" alt="Boone B. Gorges" /></a>
					<a class="web" href="http://commons.gc.cuny.edu/members/boonebgorges/">Boone B. Gorges</a>
					<span class="title"><?php _e( 'Lead Developer', 'cbox' ); ?></span>
				</li>
			</ul>

			<h4 class="wp-people-group"><?php _e( 'Core Developers', 'cbox' ); ?></h4>

			<ul class="wp-people-group " id="wp-people-group-core-developers">
				<li class="wp-person" id="wp-person-dom">
					<a href="http://commons.gc.cuny.edu/members/humanshell/"><img src="https://0.gravatar.com/avatar/639e517ff8357171d75a82427c1ac01a?s=60" class="gravatar" alt="Dominic Giglio" /></a>
					<a class="web" href="http://commons.gc.cuny.edu/members/humanshell/">Dominic Giglio</a>
					<span class="title"><?php _e( 'Core Developer', 'cbox' ); ?></span>
				</li>
				<li class="wp-person" id="wp-person-ray">
					<a href="http://commons.gc.cuny.edu/members/rhoh/"><img src="https://0.gravatar.com/avatar/6bd31ab878ed6e7cf746231c57fc237d?s=60" class="gravatar" alt="Raymond Hoh" /></a>
					<a class="web" href="http://commons.gc.cuny.edu/members/rhoh/">Raymond Hoh</a>
					<span class="title"><?php _e( 'Core Developer', 'cbox' ); ?></span>
				</li>
			</ul>

			<h4 class="wp-people-group"><?php _e( 'Theme Developers', 'cbox' ); ?></h4>

			<ul class="wp-people-group " id="wp-people-group-theme-developers">
				<li class="wp-person" id="wp-person-bowe">
					<a href="http://commons.gc.cuny.edu/members/bowe/"><img src="https://0.gravatar.com/avatar/cee68f03a0cfb109a51f6305062bf070?s=60" class="gravatar" alt="Bowe Frankewa" /></a>
					<a class="web" href="http://commons.gc.cuny.edu/members/bowe/">Bowe Frankewa</a>
					<span class="title"><?php _e( 'Theme Developer', 'cbox' ); ?></span>
	
				<li class="wp-person" id="wp-person-marshall">
					<a href="http://commons.gc.cuny.edu/members/marshall/"><img src="https://0.gravatar.com/avatar/65d6f7a51f72a5cdc7fed21500567a20?s=60" class="gravatar" alt="Marshall Sorenson" /></a>
					<a class="web" href="http://commons.gc.cuny.edu/members/marshall/">Marshall Sorenson</a>
					<span class="title"><?php _e( 'Theme Developer', 'cbox' ); ?></span>
				</li>
			</ul>

			<h4 class="wp-people-group"><?php _e( 'Support Team', 'cbox' ); ?></h4>

			<ul class="wp-people-group " id="wp-people-group-support-team">
				<li class="wp-person" id="wp-person-brian">
					<a href="http://commons.gc.cuny.edu/members/brianfoote/"><img src="http://commons.gc.cuny.edu/files/avatars/743/ab952884de5fba79d3ce2ec0798b3049-bpfull.jpg" class="gravatar" alt="Brian Foote" /></a>
					<a class="web" href="http://commons.gc.cuny.edu/members/brianfoote/">Brian Foote</a>
	
				<li class="wp-person" id="wp-person-sarah">
					<a href="http://commons.gc.cuny.edu/members/Sarah_Morgano/"><img src="https://0.gravatar.com/avatar/c12a1f8619958bd041b2707374faed5d?s=60" class="gravatar" alt="Sarah Morgano" /></a>
					<a class="web" href="http://commons.gc.cuny.edu/members/Sarah_Morgano/">Sarah Morgano</a>
				</li>
	
				<li class="wp-person" id="wp-person-michael">
					<a href="http://commons.gc.cuny.edu/members/msmith/"><img src="https://0.gravatar.com/avatar/4d889ac090d7456457e3c54706ccfb66?s=60" class="gravatar" alt="Michael Branson Smith" /></a>
					<a class="web" href="http://commons.gc.cuny.edu/members/msmith/">Michael Branson Smith</a>
				</li>
	
				<li class="wp-person" id="wp-person-chris">
					<a href="http://commons.gc.cuny.edu/members/cstein/"><img src="http://commons.gc.cuny.edu/files/avatars/18/headshot_150_2-avatar2.jpg" class="gravatar" alt="Christopher Stein" /></a>
					<a class="web" href="http://commons.gc.cuny.edu/members/cstein/">Christopher Stein</a>
				</li>
	
				<li class="wp-person" id="wp-person-scott">
					<a href="http://commons.gc.cuny.edu/members/scottvoth/"><img src="http://commons.gc.cuny.edu/files/avatars/455/me129d7872f4142432362a27f96088b2cd4e-avatar2.jpg" class="gravatar" alt="Scott Voth" /></a>
					<a class="web" href="http://commons.gc.cuny.edu/members/scottvoth/">Scott Voth</a>
				</li>
			</ul>

			<h4 class="wp-people-group"><?php _e( 'Core Contributors to Commons In A Box 1.0.8', 'cbox' ); ?></h4>

			<p class="wp-credits-list">
				<a href="http://commonsinabox.org/members/haystack/">Christian Wach</a> and 
				<a href="http://commonsinabox.org/members/jreeve/">Jonathan Reeve</a>.
			</p>

			<h4 class="wp-people-group"><?php _e( 'External Libraries', 'cbox' ); ?></h4>

			<p class="wp-credits-list">
				<a href="http://wordpress.org/extend/plugins/plugin-dependencies/" title="<?php _e( 'For adding plugin dependencies for WordPress.  Portions rewritten and modified by CBOX.', 'cbox' ); ?>">Plugin Dependencies</a>.
			</p>
			
			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the CBOX dashboard &rarr;</a>', 'cbox' ), self_admin_url( 'admin.php?page=cbox' ) ); ?>
			</div>

		</div>
