<?php
/**
 * Set up the admin area
 *
 * @since 0.2
 *
 * @package Commons_In_A_Box
 * @subpackage Adminstration
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Setup the CBOX admin area.
 *
 * @since 0.2
 */
class CBox_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// includes
		$this->includes();

		// setup our hooks
		$this->setup_hooks();
	}

	/**
	 * Includes.
	 */
	private function includes() {
		require( CBOX_PLUGIN_DIR . 'admin/functions.php' );
	}

	/**
	 * Setup hooks.
	 */
	private function setup_hooks() {
		// setup admin menu
		add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu',           array( $this, 'admin_menu' ) );

		// dashboard forums notice
		add_action( "admin_init",                                                   array( $this, 'dashboard_forums_notice' ) );

		// see if an admin notice should be shown
		add_action( 'admin_init',                                                   array( $this, 'setup_notice' ) );

		// persistent CSS
		add_action( 'admin_head',                                                   array( $this, 'inline_css' ) );

		// notice inline CSS
		add_action( 'admin_head',                                                   array( $this, 'notice_css' ) );

		// add an admin notice if CBOX isn't setup
		add_action( is_network_admin() ? 'network_admin_notices' : 'admin_notices', array( $this, 'display_notice' ) );

		// add a special header on the admin plugins page
		add_action( 'pre_current_active_plugins', 	                            array( $this, 'plugins_page_header' ) );

		// add a hook to manipulate BP's wizard steps
		add_action( 'admin_init',                                                   array( $this, 'bp_wizard_listener' ) );

		// after the BP wizard completes, redirect to the CBOX dashboard
		add_action( 'admin_init',                                                   array( $this, 'bp_wizard_redirect' ) );

		// after installing the cbox-theme, run the activation hook
		add_action( 'admin_init',                                                   array( $this, 'theme_activation_hook' ) );
	}

	/** ACTIONS / SCREENS *********************************************/

	/**
	 * Catches form submissions from the CBOX dashboard and sets
	 * some reference pointers depending on the type of submission.
	 *
	 * @since 0.3
	 */
	public function catch_form_submission() {
		// virgin setup - no CBOX or BP installed
		if ( ! empty( $_REQUEST['cbox-virgin-setup'] ) ) {
			// verify nonce
			check_admin_referer( 'cbox_virgin_setup', 'cbox-virgin-nonce' );

			// set reference pointer for later use
			cbox()->setup = 'virgin-setup';

			// bump the revision date in the DB after updating
			add_action( 'cbox_after_updater', create_function( '', 'cbox_bump_revision_date();' ) );

		// BP installed, but no CBOX
		} elseif ( ! empty( $_REQUEST['cbox-recommended-nonce'] ) ) {
			// verify nonce
			check_admin_referer( 'cbox_bp_installed', 'cbox-recommended-nonce' );

			// set reference pointer for later use
			cbox()->setup = 'install';

			// bump the revision date in the DB after updating
			add_action( 'cbox_after_updater', create_function( '', 'cbox_bump_revision_date();' ) );

			// if no plugins to install, redirect back to CBOX dashboard
			if ( empty( $_REQUEST['cbox_plugins'] ) ) {
				do_action( 'cbox_after_updater' );
				wp_redirect( self_admin_url( 'admin.php?page=cbox' ) );
				exit;
			}

		// plugin upgrades available
		} elseif ( ! empty( $_REQUEST['cbox-action'] ) && $_REQUEST['cbox-action'] == 'upgrade' ) {
			// verify nonce
			check_admin_referer( 'cbox_upgrade' );

			// set reference pointer for later use
			cbox()->setup = 'upgrade';

			if ( ! empty( $_REQUEST['cbox-themes'] ) )
				cbox()->theme_upgrades = $_REQUEST['cbox-themes'];

			// bump the revision date in the DB after updating
			add_action( 'cbox_after_updater', create_function( '', 'cbox_bump_revision_date();' ) );

		// install CBOX theme
		} elseif ( ! empty( $_REQUEST['cbox-action'] ) && $_REQUEST['cbox-action'] == 'install-theme' ) {
			// verify nonce
			check_admin_referer( 'cbox_install_theme' );

			// get cbox theme
			$cbox_theme = wp_get_theme( 'cbox-theme' );
			$errors = $cbox_theme->errors()->errors;

			// CBOX theme exists! so let's activate it and redirect to the
			// CBOX Theme options page!
			if ( empty( $errors['theme_not_found'] ) ) {
				switch_theme( 'cbox-theme', 'cbox-theme' );

                               // Mark the theme as having just been activated
                               // so that we can run the setup on next pageload
				bp_update_option( '_cbox_theme_activated', '1' );

				wp_redirect( admin_url( 'themes.php?page=infinity-theme' ) );
				return;
			}

			// CBOX theme doesn't exist, so set reference pointer for later use
			cbox()->setup = 'install-theme';

		// theme upgrades available
		} elseif ( ! empty( $_REQUEST['cbox-action'] ) && $_REQUEST['cbox-action'] == 'upgrade-theme' ) {
			// verify nonce
			check_admin_referer( 'cbox_upgrade_theme' );

			// set reference pointers for later use
			cbox()->setup = 'upgrade-theme';
			cbox()->theme_upgrades = $_REQUEST['cbox-themes'];
		}
	}

	/**
	 * Setup screen.
	 *
	 * @since 0.3
	 */
	private function setup_screen() {
		// do something different for each CBOX setup condition
		switch( cbox()->setup ) {
			// virgin setup - no CBOX or BP installed
			case 'virgin-setup' :
				// get CBOX plugins except optional ones
				$plugins = CBox_Plugins::get_plugins( 'all', 'optional' );

				// sort plugins by plugin state
				$plugins = CBox_Plugins::organize_plugins_by_state( $plugins );

				// include the CBOX Plugin Upgrade and Install API
				if ( ! class_exists( 'CBox_Plugin_Upgrader' ) )
					require( CBOX_PLUGIN_DIR . 'admin/plugin-install.php' );

				// some HTML markup!
				echo '<div class="wrap">';
				screen_icon( 'cbox' );
				echo '<h2>' . esc_html__('Set Up CBOX Plugins', 'cbox' ) . '</h2>';

				// start the upgrade!
				$installer = new CBox_Updater( $plugins, array(
					'redirect_link' => self_admin_url( 'admin.php?page=cbox' ),
					'redirect_text' => __( 'Return to the CBOX Dashboard', 'cbox' )
				) );

				echo '</div>';

				break;

			// BP installed, but no CBOX
			case 'install' :
				$plugins = $_REQUEST['cbox_plugins'];

				// include the CBOX Plugin Upgrade and Install API
				if ( ! class_exists( 'CBox_Plugin_Upgrader' ) )
					require( CBOX_PLUGIN_DIR . 'admin/plugin-install.php' );

				// some HTML markup!
				echo '<div class="wrap">';
				screen_icon( 'cbox' );
				echo '<h2>' . esc_html__('Install CBOX Plugins', 'cbox' ) . '</h2>';

				// start the install!
				$installer = new CBox_Updater( $plugins, array(
					'redirect_link' => self_admin_url( 'admin.php?page=cbox' ),
					'redirect_text' => __( 'Return to the CBOX Dashboard', 'cbox' )
				) );

				echo '</div>';

				break;

			// upgrading installed plugins
			case 'upgrade' :
				// setup our upgrade plugins array
				$plugins['upgrade'] = CBox_Plugins::get_upgrades( 'active' );

				// if theme upgrades are available, let's add an extra button to the end of
				// the plugin upgrader, so we can proceed with upgrading the theme
				if ( ! empty( cbox()->theme_upgrades ) ) {
					$title = esc_html__( 'Upgrading CBOX Plugins and Themes', 'cbox' );

					$redirect_link = wp_nonce_url( network_admin_url( 'admin.php?page=cbox&amp;cbox-action=upgrade-theme&amp;cbox-themes=' . cbox()->theme_upgrades ), 'cbox_upgrade_theme' );
					$redirect_text = __( "Now, let's upgrade the CBOX Default theme &rarr;", 'cbox' );


				} else {
					$title = esc_html__( 'Upgrading CBOX Plugins', 'cbox' );

					$redirect_link = self_admin_url( 'admin.php?page=cbox' );
					$redirect_text = __( 'Return to the CBOX Dashboard', 'cbox' );
				}

				// include the CBOX Plugin Upgrade and Install API
				if ( ! class_exists( 'CBox_Plugin_Upgrader' ) )
					require( CBOX_PLUGIN_DIR . 'admin/plugin-install.php' );

				// some HTML markup!
				echo '<div class="wrap">';
				screen_icon( 'cbox' );
				echo '<h2>' . $title . '</h2>';

				// start the upgrade!
				$installer = new CBox_Updater( $plugins, array(
					'redirect_link' => $redirect_link,
					'redirect_text' => $redirect_text
				) );

				echo '</div>';

				break;

			// install the cbox theme
			case 'install-theme' :
				// include the CBOX Theme Installer
				if ( ! class_exists( 'CBox_Theme_Installer' ) )
					require( CBOX_PLUGIN_DIR . 'admin/theme-install.php' );

				// get CBOX theme specs
				$theme = CBox_Theme_Specs::init()->get( 'cbox_theme' );

				$title = sprintf( _x( 'Installing %s', 'references the theme that is currently being installed', 'cbox' ), $theme['name'] . ' ' . $theme['version'] );

				$cbox_theme = new CBox_Theme_Installer( new CBox_Theme_Installer_Skin( compact( 'title' ) ) );
				$cbox_theme->install();

				break;

			// upgrade CBOX themes
			case 'upgrade-theme' :
				// include the CBOX Theme Installer
				if ( ! class_exists( 'CBox_Theme_Installer' ) )
					require( CBOX_PLUGIN_DIR . 'admin/theme-install.php' );

				// Modifies the theme action links that get displayed after theme installation
				// is complete.
				add_filter( 'update_bulk_theme_complete_actions', array( 'CBox_Theme_Installer', 'remove_theme_actions' ) );

				// some HTML markup!
				echo '<div class="wrap">';
				screen_icon( 'cbox' );
				echo '<h2>' . esc_html__('Upgrading CBOX Theme', 'cbox' ) . '</h2>';

				// get cbox theme specs
				$upgrader = new CBox_Theme_Installer( new Bulk_Theme_Upgrader_Skin() );
				$upgrader->bulk_upgrade( cbox()->theme_upgrades );

				echo '</div>';

				break;
		}
	}

	/**
	 * If we're on the BuddyPress Wizard, we do a couple of things to
	 * manipulate BP for CBOX UX reasons.
	 *
	 * 1) Alter BuddyPress' wizard to remove the "Theme" step.
	 * 2) Set a cookie if we're on the last step of the BP wizard. This
	 *    is done so we can redirect back to the CBOX dashboard after BP
	 *    wizard has done its thang.
	 *
	 * Warning: hackety-hack-hack!
	 *
	 * @since 0.3
	 */
	public function bp_wizard_listener() {
		// if we're not on the BP wizard page, stop now!
		if ( ! empty( $_REQUEST['page'] ) && $_REQUEST['page'] != 'bp-wizard' )
			return;

		global $bp;

		// check to see if the 3rd key exists in the wizard
		// this almost always exists, but just testing as a precaution!
		if ( empty( $bp->admin->wizard->steps[3] ) ) {
			return;
		}

		// alright, the 3rd key exists!
		// now let's check to see if the key equals our 'Theme' step
		// this usually occurs for BP 1.5-1.6 only as BP 1.7 removes this
		if ( $bp->admin->wizard->steps[3] = __( 'Theme', 'buddypress' ) ) {
			// 'Theme' step exists! now get rid of it!
			unset( $bp->admin->wizard->steps[3] );

			// rejig the keys
			$bp->admin->wizard->steps = array_values( $bp->admin->wizard->steps );
		}

		/* lastly, set a cookie on the last step of the BP wizard */

		// get the last step of the bp wizard
		$last_step = array_pop( array_keys( $bp->admin->wizard->steps ) );

		// get current step
		$current_step = $bp->admin->wizard->current_step();

		// set the cookie
		if ( $last_step == $current_step ) {
			@setcookie( 'cbox-bp-finish-wizard', 1, time() + 60 * 60 * 24, COOKIEPATH );
		}
	}

	/**
	 * Catch BP wizard's redirect after completion and redirect to the
	 * CBOX dashboard.
	 *
	 * @since 0.3
	 *
	 * @see CBox_Admin::bp_wizard_listener()
	 */
	public function bp_wizard_redirect() {
		// after the BP wizard completes, it gets redirected back to the BP
		// components page.
		//
		// so check to see if we're on the BP components page, if not, stop now!
		if ( ! empty( $_REQUEST['page'] ) && $_REQUEST['page'] != 'bp-components' )
			return;

		if ( ! empty( $_COOKIE['cbox-bp-finish-wizard'] ) ) {
			// remove the cookie
			@setcookie( 'cbox-bp-finish-wizard', '', time() - 3600, COOKIEPATH );

			// do some stuff after CBOX is installed
			cbox_bp_after_version_bump();

			// redirect to the CBOX dashboard
			wp_redirect( self_admin_url( 'admin.php?page=cbox' ) );
		}
	}

	/**
         * Trigger Infinity's activation hook, if necessary
         *
	 * Infinity, and therefore cbox-theme, run certain setup routines at
         * 'infinity_dashboard_activated'. However, this hook doesn't fire
         * properly when CBOX uses switch_theme() to set the current theme.
         * Instread, we set a flag at activation, and then check on each admin
         * pageload to see if the theme was just activated; if so, we run the
         * activation hook.
         */
	public function theme_activation_hook() {
		if ( function_exists( 'bp_get_option' ) && bp_get_option( '_cbox_theme_activated' ) ) {
			bp_delete_option( '_cbox_theme_activated' );
			do_action( 'infinity_dashboard_activated' );
		}
	}

	/** ADMIN PAGE-SPECIFIC *******************************************/

	/**
	 * Setup admin menu and any dependent page hooks.
	 */
	public function admin_menu() {
		$page = add_menu_page(
			__( 'Commons In A Box', 'cbox' ),
			__( 'Commons In A Box', 'cbox' ),
			'install_plugins', // todo - map cap?
			'cbox',
			array( $this, 'admin_page' )
		);

		$subpage = add_submenu_page(
			'cbox',
			__( 'Commons In A Box Dashboard', 'cbox' ),
			__( 'Dashboard', 'cbox' ),
			'install_plugins', // todo - map cap?
			'cbox',
			array( $this, 'admin_page' )
		);

		do_action( 'cbox_admin_menu' );

		// dashboard CSS
		add_action( "admin_head-{$subpage}",          array( $this, 'dashboard_css' ) );

		// enqueue JS
		add_action( "admin_print_scripts-{$subpage}", array( $this, 'enqueue_js' ) );

		// load PD
		add_action( "load-{$subpage}",                array( 'Plugin_Dependencies', 'init' ) );

		// catch form submission
		add_action( "load-{$subpage}",                array( $this, 'catch_form_submission' ) );

		// contextual help
		add_action( "load-{$subpage}",                array( $this, 'contextual_help' ) );
	}

	/**
	 * The main dashboard page.
	 */
	public function admin_page() {
		// what's new page
		if ( $this->is_changelog() ) {
			require( CBOX_PLUGIN_DIR . 'admin/changelog.php' );

		// credits page
		} elseif ( $this->is_credits() ) {
			require( CBOX_PLUGIN_DIR . 'admin/credits.php' );

		// setup screen
		} elseif( ! empty( cbox()->setup ) ) {
			$this->setup_screen();

		// regular screen should go here
		} else {
		?>
			<div class="wrap">
				<?php screen_icon( 'cbox' ); ?>
				<h2><?php _e( 'Commons In A Box Dashboard', 'cbox' ); ?></h2>

				<?php $this->welcome_panel(); ?>
				<?php $this->steps(); ?>
				<?php $this->upgrades(); ?>
				<?php $this->metaboxes(); ?>
				<?php $this->about(); ?>
			</div>
		<?php
		}
	}

	/**
	 * Registers contextual help for the CBOX dashboard page.
	 *
	 * @uses get_current_screen() Gets info about the current screen.
	 */
	public function contextual_help() {
		// about
		get_current_screen()->add_help_tab( array(
			'id'      => 'cbox-about',
			'title'   => __( 'About', 'cbox' ),
			'content' =>
				'<p>' . sprintf( __( '<strong>Commons In A Box</strong> is a software project aimed at turning the infrastructure that successfully powers the <a href="%s">CUNY Academic Commons</a> into a free, distributable, easy-to-install package.', 'cbox' ), esc_url( 'http://commons.gc.cuny.edu' ) ) . '</p>' .
				'<p>' . __( 'Commons In A Box is made possible by a generous grant from the Alfred P. Sloan Foundation.', 'cbox' ) . '</p>'
		) );

		// sidebar links
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'Useful Links:', 'cbox' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s">Changelog</a>', 'cbox' ), esc_url( self_admin_url( 'admin.php?page=cbox&whatsnew=1' ) ) ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s">Show Welcome Message</a>', 'cbox' ), esc_url( self_admin_url( 'admin.php?page=cbox&welcome=1' ) ) ) . '</p>'
		);
	}

	/**
	 * Should we show the changelog screen?
	 *
	 * @return bool
	 */
	private function is_changelog() {
		if ( ! empty( $_GET['whatsnew'] ) )
			return true;

		return false;
	}

	/**
	 * Should we show the credits screen?
	 *
	 * @return bool
	 */
	private function is_credits() {
		if ( ! empty( $_GET['credits'] ) )
			return true;

		return false;
	}

	/**
	 * The CBOX welcome panel.
	 *
	 * This is pretty much ripped off from {@link wp_welcome_panel()} :)
	 */
	private function welcome_panel() {
		if ( isset( $_GET['welcome'] ) ) {
			$welcome_checked = empty( $_GET['welcome'] ) ? 0 : 1;
			update_user_meta( get_current_user_id(), 'show_cbox_welcome_panel', $welcome_checked );
		}

		global $wp_version;

		// default class for our welcome panel container
		$classes = 'welcome-panel';

		// get our user's welcome panel setting
		$option = get_user_meta( get_current_user_id(), 'show_cbox_welcome_panel', true );

		// if welcome panel option isn't set, set it to "1" to show the panel by default
		if ( $option === '' )
			$option = 1;

		// this sets the CSS class needed to hide the welcome panel if needed
		if ( ! (int) $option )
			$classes .= ' hidden';

	?>
		<div id="welcome-panel" class="<?php echo esc_attr( $classes ); ?>">
			<?php wp_nonce_field( 'welcome-panel-nonce', 'welcomepanelnonce', false ); ?>

			<?php if ( cbox_is_setup() ) : ?>
				<a class="welcome-panel-close" href="<?php echo esc_url( network_admin_url( 'admin.php?page=cbox&welcome=0' ) ); ?>"><?php _e( 'Dismiss', 'cbox' ); ?></a>
			<?php endif; ?>

			<div class="wp-badge"><?php printf( __( 'Version %s', 'cbox' ), cbox_get_version() ); ?></div>

			<div class="welcome-panel-content">
				<h3><?php _e( 'Welcome to Commons In A Box! ', 'cbox' ); ?></h3>

				<p class="about-description"><?php _e( 'Need help getting started? Looking for support or ideas? Check out our documentation and join the community of CBOX users at <a href="http://commonsinabox.org">commonsinabox.org</a>.', 'cbox' ) ?></p>

				<?php if ( cbox_is_setup() ) : ?>
					<p class="about-description"><?php _e( 'If you&#8217;d rather dive right in, here are a few things most people do first when they set up a new CBOX site.', 'cbox' ); ?></p>
					<p class="welcome-panel-dismiss"><?php printf( __( 'Already know what you&#8217;re doing? <a href="%s">Dismiss this message</a>.', 'cbox' ), esc_url( network_admin_url( 'admin.php?page=cbox&welcome=0' ) ) ); ?></p>
				<?php endif; ?>
			</div><!-- .welcome-panel-content -->

		</div><!-- #welcome-panel -->

	<?php
	}

	/**
	 * CBOX setup steps.
	 *
	 * This shows up when CBOX hasn't completed setup yet.
	 *
	 * @since 0.3
	 *
	 * @uses cbox_is_setup() To tell if CBOX is fully setup.
	 * @uses cbox_is_upgraded() To check if CBOX just upgraded.
	 * @uses cbox_get_setup_step() Which setup step is CBOX at?
	 */
	private function steps() {
		// if CBOX is already setup, stop now!
		if ( cbox_is_setup() )
			return;

		// stop if CBOX just upgraded
		if ( cbox_is_upgraded() )
			return;

		// do something different depending on the setup step
		switch ( cbox_get_setup_step() ) {

			// (1) buddypress isn't activated or isn't installed
			case 'no-buddypress' :

			?>

				<h2><?php _e( 'Install BuddyPress', 'cbox' ); ?></h2>

				<form method="post" action="<?php echo self_admin_url( 'admin.php?page=cbox' ); ?>">
					<p class="submitted-on"><?php _e( "Before you can use Commons In A Box, we'll need to install BuddyPress and some recommended plugins. Click 'Continue' to get set up.", 'cbox' ); ?></p>

					<?php wp_nonce_field( 'cbox_virgin_setup', 'cbox-virgin-nonce' ); ?>

					<p><input type="submit" value="<?php _e( 'Continue &rarr;', 'cbox' ); ?>" class="button-primary" name="cbox-virgin-setup" /></p>
				</form>

			<?php
				break;

			// (2) buddypress is activated, but we just upgraded buddypress
			case 'buddypress-wizard' :

			?>

				<h2><?php _e( 'Set Up BuddyPress', 'cbox' ); ?></h2>

				<p class="submitted-on"><?php _e( "We're almost there! Now we need to finish setting up BuddyPress.", 'cbox' ); ?></p>

				<p class="submitted-on"><?php printf( __( "Don't worry! BuddyPress has a simple wizard to guide you through the process.  However, if you want more help, check out this <a href='%s'>codex article</a>.", 'cbox' ), 'http://codex.buddypress.org/getting-started/setting-up-a-new-installation/installation-wizard/#step-1' ); ?></p>

				<p><a class="button-primary" href="<?php cbox_the_bp_admin_wizard_url(); ?>"><?php _e( 'Continue to BuddyPress setup &rarr;', 'cbox' ); ?></a></p>

			<?php
				break;

			// (3) we're on the last step!
			case 'last-step' :

				// get recommended plugins that are available to install / upgrade
				$recommended_plugins = CBox_Plugins::organize_plugins_by_state( CBox_Plugins::get_plugins( 'recommended' ) );

				// we don't want already-installed plugins
				if ( ! empty( $recommended_plugins['deactivate'] ) )
					unset( $recommended_plugins['deactivate'] );

				// we have some recommended plugins to bug the user about!
				if ( ! empty( $recommended_plugins ) ) {
			?>

				<h2><?php _e( 'Install some other cool stuff!', 'cbox' ); ?></h2>

				<form method="post" action="<?php echo self_admin_url( 'admin.php?page=cbox' ); ?>">
					<p class="submitted-on"><?php _e( "It looks like you're already running BuddyPress. Cool! You're almost finished the setup process!", 'cbox' ); ?></p>

					<p class="submitted-on"><?php _e( "Did you know Commons In A Box comes prebundled with a few, recommended plugins?  These plugins help to add functionality to your existing WordPress and BuddyPress site. ", 'cbox' ); ?></p>

					<p class="submitted-on"><?php _e( "We have automatically selected the following plugins to install for you. However, feel free to uncheck some of these plugins based on your site's needs.", 'cbox' ); ?></p>

					<?php wp_nonce_field( 'cbox_bp_installed', 'cbox-recommended-nonce' ); ?>

					<?php
						cbox()->plugins->render_plugin_table( array(
							'type'           => 'recommended',
							'omit_activated' => true,
							'check_all'      => true
						) );
					?>
				</form>

			<?php
				// all recommended plugins are already installed
				// so bump the CBOX revision date and reload the page using javascript
				// @todo make this <noscript> friendly
				} else {
					cbox_bump_revision_date();

					echo '<script type="text/javascript">window.location = document.URL;</script>';
				}

				break;

		} // end switch()
	}

	/**
	 * Upgrade notice.
	 *
	 * This shows up when CBOX is upgraded through the WP updates panel and
	 * when installed CBOX plugins have updates.
	 *
	 * @since 0.3
	 *
	 * @uses cbox_is_upgraded() To tell if CBOX has just upgraded.
	 * @uses cbox_bump_revision_date() To bump the CBOX revision date in the DB.
	 */
	private function upgrades() {
		/** check if WordPress needs upgrading **********************************/

		// get plugin dependency requirements
		$requirements = Plugin_Dependencies::get_requirements();

		// check CBOX plugin header's 'Core' header for version requirements
		// if exists, WordPress needs to be upgraded
		if ( ! empty( $requirements['Commons In A Box']['core'] ) ) {
			$version = $requirements['Commons In A Box']['core'];
		?>

			<div id="cbox-upgrades" class="secondary-panel">
				<h2><?php _e( 'Upgrade Available', 'cbox' ); ?></h2>

				<div class="login postbox">
					<div class="message">
						<p><?php printf( __( 'Commons In A Box %s requires WordPress %s', 'cbox' ), cbox_get_version(), $version ); ?>
						<br />
						<a class="button-secondary" href="<?php echo network_admin_url( 'update-core.php' ); ?>"><?php _e( 'Upgrade now!', 'cbox' ); ?></a></p>
					</div>
				</div>
			</div>

		<?php
			return;
		}

		/** check if CBOX modules have updates **********************************/

		// include the CBOX Theme Installer
		if ( ! class_exists( 'CBox_Theme_Installer' ) )
			require( CBOX_PLUGIN_DIR . 'admin/theme-install.php' );

		// get activated CBOX plugins that need updating
		$active_cbox_plugins_need_update = CBox_Plugins::get_upgrades( 'active' );

		// check for theme upgrades
		$is_theme_upgrade = cbox_get_theme_to_update();

		// no available upgrades, so stop!
		if ( ! $active_cbox_plugins_need_update && ! $is_theme_upgrade ) {

			// if CBOX just upgraded and has no plugin updates, bump CBOX revision date and reload using JS
			// yeah, the JS redirect is a little ugly... should probably do this higher up the stack...
			if ( cbox_is_upgraded() ) {
				cbox_bump_revision_date();
				echo '<script type="text/javascript">window.location = document.URL;</script>';
			}

			return;
		}

		/* we have upgrades available! */

		// plugin count
		$plugin_count = $total_count = count( $active_cbox_plugins_need_update );

		// setup default upgrade URL
		$url = wp_nonce_url( network_admin_url( 'admin.php?page=cbox&amp;cbox-action=upgrade' ), 'cbox_upgrade' );

		// theme is available for upgrade
		if ( ! empty( $active_cbox_plugins_need_update ) && ! empty( $is_theme_upgrade ) ) {
			++$total_count;

			// theme has update, so add an extra parameter to the querystring
			$url = wp_nonce_url( network_admin_url( 'admin.php?page=cbox&amp;cbox-action=upgrade&amp;cbox-themes=' . $is_theme_upgrade ), 'cbox_upgrade' );

			$message = sprintf( _n( '%d installed plugin and the CBOX Default theme have an update available. Click on the button below to upgrade.', '%d installed plugins and the CBOX Default theme have updates available. Click on the button below to upgrade.', $plugin_count, 'cbox' ), $plugin_count );

		// just plugins
		} elseif ( ! empty( $active_cbox_plugins_need_update ) ) {
			$message = sprintf( _n( '%d installed plugin has an update available. Click on the button below to upgrade.', '%d installed plugins have updates available. Click on the button below to upgrade.', $plugin_count, 'cbox' ), $plugin_count );

		// just themes
		} else {
			// theme has update, so switch up the upgrade URL
			$url = wp_nonce_url( network_admin_url( 'admin.php?page=cbox&amp;cbox-action=upgrade-theme&amp;cbox-themes=' . $is_theme_upgrade ), 'cbox_upgrade_theme' );

			$message = __( 'The CBOX Default theme has an update available. Click on the button below to upgrade.', 'cbox' );
		}

	?>
		<div id="cbox-upgrades" class="secondary-panel">
			<h2><?php printf( _n( 'Upgrade Available', 'Upgrades Available', $total_count, 'cbox' ), $total_count ); ?></h2>

			<div class="login postbox">
				<div class="message">
					<p><?php echo $message; ?>
					<br />
					<a class="button-secondary" href="<?php echo $url; ?>"><?php _e( 'Upgrade', 'cbox' ); ?></a></p>
				</div>
			</div>
		</div>
	<?php
	}

	/**
	 * Metaboxes.
	 *
	 * These are quick action links for the admin to do stuff.
	 * Note: These metaboxes only show up when CBOX has finished setting up.
	 *
	 * @since 0.3
	 *
	 * @uses cbox_is_setup() To tell if CBOX is fully setup.
	 */
	private function metaboxes() {
		if ( ! cbox_is_setup() )
			return;

		$cbox_plugins = CBox_Plugins::get_plugins();
	?>

		<div id="cbox-links" class="secondary-panel">
			<h2><?php _e( 'Quick Links', 'cbox' ); ?></h2>

			<div class="welcome-panel-column-container">

				<!-- SETTINGS -->
				<div class="welcome-panel-column">
					<h4><span class="icon16 icon-settings"></span> <?php _e( 'Settings', 'cbox' ); ?></h4>
					<p><?php _e( "Commons In A Box works by pulling together a number of independent WordPress and BuddyPress plugins. Customize your site by exploring the settings pages for these plugins below.", 'cbox' ); ?></p>
					<ul>

					<?php
						foreach ( CBox_Plugins::get_settings() as $plugin => $settings_url ) {
							echo '<li><a title="' . __( "Click here to view this plugin's settings page", 'cbox' ) . '" href="' . $settings_url .'">' . $plugin . '</a> - ' . $cbox_plugins[$plugin]['cbox_description'];

							if ( ! empty( $cbox_plugins[$plugin]['documentation_url'] ) )
								echo ' [<a title="' . __( "Click here for plugin documentation at commonsinabox.org", 'cbox' ) . '" href="' . esc_url( $cbox_plugins[$plugin]['documentation_url'] ) . '" target="_blank">' . __( 'Info...', 'cbox' ) . '</a>]';

							echo '</li>';
						}
					?>
					</ul>

					<div class="login postbox">
						<div class="message" style="text-align:center;">
							<strong><?php printf( __( '<a href="%s">Manage all your CBOX plugins here!</a>', 'cbox' ), esc_url( network_admin_url( 'admin.php?page=cbox-plugins' ) ) ); ?></strong>
						</div>
					</div>
				</div>

				<!-- THEME -->
				<div class="welcome-panel-column welcome-panel-last">
					<h4><span class="icon16 icon-appearance"></span> <?php _e( 'Theme', 'cbox' ); ?></h4>
					<?php
						// if BP_ROOT_BLOG is defined and we're not on the root blog, switch to it
						if ( ! bp_is_root_blog() ) {
							switch_to_blog( bp_get_root_blog_id() );
						}

						$theme = wp_get_theme();

						// restore blog after switching
						if ( is_multisite() ) {
							restore_current_blog();
						}

						if ( $theme->errors() ) :
							echo '<p>';
							printf( __( '<a href="%s">Install the CBOX Default theme to get started</a>.', 'cbox' ), wp_nonce_url( network_admin_url( 'admin.php?page=cbox&amp;cbox-action=install-theme' ), 'cbox_install_theme' ) );
							echo '</p>';
						else:

							// current theme is not the CBOX default theme
							if ( $theme->get_template() != 'cbox-theme' ) {
								$is_bp_compatible = cbox_is_theme_bp_compatible();

							?>
								<p><?php printf( __( 'Your current theme is %s.', 'cbox' ), '<strong>' . $theme->display( 'Name' ) . '</strong>' ); ?></p>

								<?php
									if ( ! $is_bp_compatible ) {
										echo '<p>';
										_e( 'It looks like this theme is not compatible with BuddyPress.', 'cbox' );
										echo '</p>';
									}
								?>

								<p><?php _e( 'Did you know that <strong>CBOX</strong> comes with a cool theme? Check it out below!', 'cbox' ); ?></p>

								<a rel="leanModal" title="<?php _e( 'View a larger screenshot of the CBOX theme', 'cbox' ); ?>" href="#cbox-theme-screenshot"><img width="200" src="<?php echo cbox()->plugin_url( 'admin/images/screenshot_cbox_theme.png' ); ?>" alt="" /></a>

								<div class="login postbox">
									<div class="message" style="text-align:center;">
										<strong><?php printf( __( '<a href="%s">Like the CBOX Theme? Install it!</a>', 'cbox' ), wp_nonce_url( network_admin_url( 'admin.php?page=cbox&amp;cbox-action=install-theme' ), 'cbox_install_theme' ) ); ?></strong>
									</div>
								</div>

								<!-- hidden modal window -->
								<div id="cbox-theme-screenshot" style="display:none;">
									<img src="<?php echo cbox()->plugin_url( 'admin/images/screenshot_cbox_theme.png' ); ?>" alt="" />
								</div>
								<!-- #cbox-theme-screenshot -->

								<script type="text/javascript">jQuery("a[rel*=leanModal]").leanModal();</script>

								<?php
									if ( ! $is_bp_compatible ) {
										echo '<p>';
										printf( __( "You can also make your theme compatible with the <a href='%s'>BuddyPress Template Pack</a>.", 'buddypress' ), network_admin_url( 'plugin-install.php?type=term&tab=search&s=%22bp-template-pack%22' ) );
										echo '</p>';
									}
								?>

							<?php
							// current theme is the CBOX default theme
							} else {
								// check for upgrades
								//$is_upgrade = CBox_Theme_Specs::get_upgrades( $theme );
							?>

								<?php if ( $theme->get_stylesheet() != 'cbox-theme' ) : ?>
									<p><?php _e( "You're using a child theme of the <strong>CBOX Default</strong> theme! Good on ya!", 'cbox' ); ?></p>
								<?php else : ?>
									<p><?php _e( "You're using the <strong>CBOX Default</strong> theme! Good on ya!", 'cbox' ); ?></p>
								<?php endif; ?>

								<?php /* HIDE THIS FOR NOW ?>
								<?php if ( $is_upgrade ) : ?>
									<div class="login postbox">
										<div id="login_error" class="message">
											<?php _e( 'Update available.', 'cbox' ); ?> <strong><a href="<?php echo wp_nonce_url( network_admin_url( 'admin.php?page=cbox&amp;cbox-action=upgrade-theme&amp;cbox-themes=' . $is_upgrade ), 'cbox_upgrade_theme' ); ?>"><?php _e( 'Update now!', 'cbox' ); ?></a></strong>
										</div>
									</div>
								<?php endif; ?>
								<?php */ ?>

								<div class="login postbox">
									<div class="message">
										<strong><?php printf( __( '<a href="%s">Configure the CBOX Theme here!</a>', 'cbox' ), esc_url( get_admin_url( bp_get_root_blog_id(), 'themes.php?page=infinity-theme' ) ) ); ?></strong>
									</div>
								</div>

							<?php
							}

						endif;
					?>
				</div>

			</div><!-- .welcome-panel-column-container -->

		</div><!-- .welcome-panel -->

	<?php
	}

	/**
	 * About section.
	 *
	 * This only shows up when CBOX is fully setup.
	 *
	 * @since 0.3
	 *
	 * @uses cbox_is_setup() To tell if CBOX is fully setup.
	 */
	private function about() {
		if ( ! cbox_is_setup() )
			return;
	?>
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
	<?php
	}

	/** HEADER INJECTIONS *********************************************/

	/**
	 * Show a notice about BuddyPress' forums.
	 *
	 * The bundled forums component in BuddyPress combined with bbPress 2 leads to
	 * conflicts between the two plugins.
	 *
	 * We show a notice if both BuddyPress' bundled forums and bbPress are enabled
	 * so site admins are aware of the potential conflict with instructions of
	 * what they can do to address the issue.
	 *
	 * This only shows up when CBOX is fully setup.
	 *
	 * @since 1.0-beta4
	 *
	 * @uses cbox_is_setup() To tell if CBOX is fully setup.
	 * @uses is_network_admin() Check to see if we're in the network admin area.
	 */
	public function dashboard_forums_notice() {
		// if CBOX isn't setup yet, stop now!
		if ( ! cbox_is_setup() ) {
			return;
		}

		// make sure BuddyPress is active
		if ( ! defined( 'BP_VERSION' ) ) {
			return;
		}

		// if bundled forums are not active stop now!
		if ( ! class_exists( 'BP_Forums_Component' ) ) {
			return;
		}

		// if bbPress isn't active, stop now!
		if ( ! function_exists( 'bbpress' ) ) {
			return;
		}

		// only super admins can view this notice
		if ( ! is_super_admin( bp_loggedin_user_id() ) ) {
			return;
		}

		// add an admin notice
		$prefix = is_network_admin() ? 'network_' : '';
		add_action( $prefix . 'admin_notices', create_function( '', "
			echo '<div class=\'error\'>';
			echo '<p>' . __( 'We see you\'re running BuddyPress\' bundled forums. Commons In A Box comes with bbPress 2.2, an upgraded and improved forum tool.', 'cbox' ) . '</p>';
			echo '<p>' . sprintf( __( 'However, we don\'t recommend running BP\'s bundled forums alongside bbPress 2.2. <a href=\'%s\'>Click here</a> to learn more about your options.', 'cbox' ), 'http://commonsinabox.org/documentation/buddypress-vs-bbpress-forums' ) . '</p>';
			echo '</div>';
		" ) );
	}

	/**
	 * Setup internal variable if the admin notice should be shown.
	 *
	 * @since 0.3
	 *
	 * @uses cbox_is_setup() To tell if CBOX is fully setup.
	 * @uses current_user_can() Check if the current user has the permission to do something.
	 * @uses is_multisite() Check to see if WP is in network mode.
	 */
	public function setup_notice() {
		// if CBOX is setup, stop now!
		if ( cbox_is_setup() )
			return;

		// only show notice if we're either a super admin on a network or an admin on a single site
		$show_notice = current_user_can( 'manage_network_plugins' ) || ( ! is_multisite() && current_user_can( 'install_plugins' ) );

		if ( ! $show_notice )
			return;

		cbox()->show_notice = true;
	}

	/**
	 * Inline CSS for the admin notice.
	 *
	 * @since 0.3
	 */
	public function notice_css() {
		// if our notice marker isn't set, stop now!
		if ( empty( cbox()->show_notice ) )
			return;

		$icon_url    = cbox()->plugin_url( 'admin/images/logo-cbox_icon.png?ver='    . cbox()->version );
		$icon_url_2x = cbox()->plugin_url( 'admin/images/logo-cbox_icon-2x.png?ver=' . cbox()->version );
	?>

		<style type="text/css">
		#cbox-nag {
			position:relative;
			min-height:69px;

			background: #fff; /* Old browsers */

			color: #666;

			border-radius:3px;
			border:1px solid #BCE8F1;
			font-size: 1.45em;
			padding: 1em 1em .3em 80px;
			text-shadow:0 1px 0 rgba(255, 255, 255, 0.5);
		}

		#cbox-nag strong {color:#3A87AD;}

		#cbox-nag a {color:#005580; text-decoration:underline;}

		#cbox-nag a.callout {
			background-color: #FAA732;
			background-image: -moz-linear-gradient(center top , #FBB450, #F89406);
			background-repeat: repeat-x;
			border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
			color: #FFFFFF;
			text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
			text-decoration:none;
			border-radius: 4px 4px 4px 4px;
			border-style: solid;
			border-width: 1px;
			box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05);
			cursor: pointer;
			display: inline-block;
			font-size: 14px;
			line-height: 20px;
			margin-bottom: 0;
			padding: 4px 14px;
			text-align: center;
			vertical-align: middle;
		}

		#cbox-nag a.callout:hover {background-color:#f89406; background-position:0 -15px;}

		#cbox-nag .cbox-icon {
			position:absolute; left:15px; top:20px;
			display:block; width:48px; height:47px;
			background:url(<?php echo $icon_url; ?>) no-repeat;
		}

		/* Retina */
		@media
			only screen and (-webkit-min-device-pixel-ratio: 1.5),
			only screen and (-moz-min-device-pixel-ratio: 1.5),
			only screen and (-o-min-device-pixel-ratio: 3/2),
			only screen and (min-device-pixel-ratio: 1.5) {
				#cbox-nag .cbox-icon {
					background-image: url(<?php echo $icon_url_2x; ?>);
					background-size:  48px 47px;
				}
		}
		</style>

	<?php
	}

	/**
	 * Show an admin notice if CBOX hasn't finished setting up.
	 *
	 * @since 0.3
	 *
	 * @uses cbox_get_setup_step() Which setup step is CBOX at?
	 */
	public function display_notice() {
		// if our notice marker isn't set, stop now!
		if ( empty( cbox()->show_notice ) )
			return;

		// setup some variables depending on the setup step
		switch ( cbox_get_setup_step() ) {
			case 'no-buddypress' :
				$notice_text = __( "Let's get started!", 'cbox' );
				$button_link = network_admin_url( 'admin.php?page=cbox' );
				$button_text = __( 'Click here to get set up', 'cbox' );
				$disable_btn = 'cbox';
				break;

			case 'buddypress-wizard' :
				$notice_text = __( 'BuddyPress is installed, but needs some additional setup.', 'cbox' );
				$button_link = cbox_get_the_bp_admin_wizard_url();
				$button_text = __( 'Finish BuddyPress setup &rarr;', 'cbox' );
				$disable_btn = 'bp-wizard';
				break;

			case 'theme-update' :
				$notice_text = __( 'The CBOX Default theme needs an update.', 'cbox' );
				$button_link = wp_nonce_url( network_admin_url( 'admin.php?page=cbox&amp;cbox-action=upgrade-theme&amp;cbox-themes=' . cbox_get_theme_to_update() ), 'cbox_upgrade_theme' );
				$button_text = __( 'Update the theme &rarr;', 'cbox' );
				$disable_btn = 'cbox';
				break;

			case 'last-step' :
				$notice_text = __( 'You only have one last thing to do. We promise!', 'cbox' );
				$button_link = network_admin_url( 'admin.php?page=cbox' );
				$button_text = __( 'Click here to finish up!', 'cbox' );
				$disable_btn = 'cbox';
				break;
		}

		// change variables if we're still in setup phase
		if ( ! empty( cbox()->setup ) ) {
			if ( 'upgrade-theme' == cbox()->setup ) {
				$notice_text = __( 'Upgrading theme...', 'cbox' );
			} else {
				$notice_text = __( 'Installing plugins...', 'cbox' );
			}

			$disable_btn = 'cbox';
		}
	?>

		<div id="cbox-nag" class="updated">
			<strong><?php _e( "Commons In A Box is almost ready!", 'cbox' ); ?></strong> <?php echo $notice_text; ?>

			<?php if ( empty( $_REQUEST['page'] ) || ( ! empty( $_REQUEST['page'] ) && $_REQUEST['page'] != $disable_btn ) ) : ?>
				<p><a class="callout" href="<?php echo $button_link; ?>"><?php echo $button_text; ?></a></p>
			<?php endif; ?>

			<div class="cbox-icon"></div>
		</div>
	<?php
	}

	/**
	 * Add a special header before the admin plugins table is rendered
	 * to remind admins that CBOX plugins are on their own, special page.
	 *
	 * This only shows up when CBOX is fully setup.
	 *
	 * @since 0.3
	 *
	 * @uses cbox_is_setup() To tell if CBOX is fully setup.
	 * @uses current_user_can() Check if the current user has the permission to do something.
	 * @uses is_network_admin() Check to see if we're in the network admin area.
	 * @uses is_multisite() Check to see if WP is in network mode.
	 */
	public function plugins_page_header() {
		if ( cbox_is_setup() ) :
			$single_site = ( current_user_can( 'manage_network_plugins' ) && ! is_network_admin() ) || ( ! is_multisite() && current_user_can( 'install_plugins' ) );

			if ( $single_site )
				echo '<h3>' . __( 'CBOX Plugins', 'cbox' ) . '</h3>';
			else
				echo '<h3>' . __( 'CBOX Network Plugins', 'cbox' ) . '</h3>';

			if ( $single_site )
				echo '<p>' . __( "Don't forget that CBOX plugins can be managed from the CBOX plugins page!", 'cbox' ) .'</p>';

			echo '<p style="margin-bottom:2.1em;">' . sprintf( __( 'You can <a href="%s">manage your CBOX plugins here</a>.', 'cbox' ), network_admin_url( 'admin.php?page=cbox-plugins' ) ) . '</p>';

			if ( $single_site )
				echo '<h3>' . sprintf( __( 'Plugins on %s', 'cbox' ), get_bloginfo( 'name' ) ) . '</h3>';
			else
				echo '<h3>' . __( 'Other Network Plugins', 'cbox' ) . '</h3>';

		endif;
	}

	/** CSS / JS / ASSETS *********************************************/

	/**
	 * Enqueues JS for the main dashboard page.
	 *
	 * @since 0.3
	 *
	 * @uses wp_enqueue_script() Enqueues a given JS file in WordPress
	 */
	public function enqueue_js() {
		// enqueue leanModal - lightweight modals
		// http://leanmodal.finelysliced.com.au/
		wp_enqueue_script(
			'cbox-lean-modal',
			'https://raw.github.com/FinelySliced/leanModal.js/master/jquery.leanModal.js',
			array( 'jquery' )
		);
	}

	/**
	 * Inline CSS across the entire admin area.
	 *
	 * Primarly done to show the CBOX menu icons.
	 *
	 * @since 0.3
	 *
	 * @todo Retina these icons
	 */
	public function inline_css() {
		$menu_icon_url    = cbox()->plugin_url( 'admin/images/menu.png?ver='    . cbox()->version );
		$menu_icon_url_2x = cbox()->plugin_url( 'admin/images/menu-2x.png?ver=' . cbox()->version );
	?>

		<style type="text/css">
		#adminmenu #toplevel_page_cbox .wp-menu-image {
			background: url('<?php echo $menu_icon_url; ?>');
			background-repeat: no-repeat;
		}

		#adminmenu #toplevel_page_cbox .wp-menu-image {background-position:0 -33px;}

		#adminmenu #toplevel_page_cbox:hover .wp-menu-image,
		#adminmenu #toplevel_page_cbox.wp-has-current-submenu .wp-menu-image {background-position:0 -2px;}

		#toplevel_page_cbox .wp-menu-image img {display:none;}

		/* Retina */
		@media
			only screen and (-webkit-min-device-pixel-ratio: 1.5),
			only screen and (-moz-min-device-pixel-ratio: 1.5),
			only screen and (-o-min-device-pixel-ratio: 3/2),
			only screen and (min-device-pixel-ratio: 1.5) {
				#toplevel_page_cbox .wp-menu-image,
				#toplevel_page_cbox:hover .wp-menu-image,
				#toplevel_page_cbox.wp-has-current-submenu .wp-menu-image {
					background-image: url('<?php echo $menu_icon_url_2x; ?>');
					background-size:  39px 64px;
				}
		}
		</style>

	<?php
	}

	/**
	 * Inline CSS for the main dashboard page.
	 */
	public static function dashboard_css() {
		$badge_url        = cbox()->plugin_url( 'admin/images/logo-cbox_vert.png?ver='    . cbox()->version );
		$badge_url_2x     = cbox()->plugin_url( 'admin/images/logo-cbox_vert-2x.png?ver=' . cbox()->version );
		$icon32_url       = cbox()->plugin_url( 'admin/images/icon32.png?ver='            . cbox()->version );
	?>

		<style type="text/css">
		#icon-cbox {
			background: url( '<?php echo $icon32_url; ?>' ) no-repeat;
		}

		#welcome-panel {overflow:visible;min-height: 280px;}

		.about-text {margin-right:220px;}
		.welcome-panel-content .about-description, .welcome-panel h3 {margin-left:0; margin-right:210px; margin-bottom:.5em;}
		.welcome-panel-dismiss {margin-bottom:0;}

		#wpbody .login .message {margin:15px 0; text-align:center;}
		.login .message a.button-secondary {display:inline-block; margin:10px 0 0;}

		.secondary-panel {
			padding:20px 10px 0px;
			line-height:1.6em;
			margin:0 8px 20px 8px;
		}

		#cbox-upgrades, #cbox-links {padding-bottom:2.4em;}

		#cbox-upgrades, #cbox-links {border-bottom: 1px solid #dfdfdf;}

		#cbox-about p, .cbox-plugins-section p {color:#777;}

		.secondary-panel h2 {line-height:1;}

		.secondary-panel h4 {font-size:14px;}
			.secondary-panel h4 .icon16 {margin-top:-10px; margin-left: -32px;}

		.secondary-panel .welcome-panel-column-container {
		    clear: both;
		    overflow: hidden;
		    padding-left: 26px;
		    position: relative;
		}

		.secondary-panel .welcome-panel-column {
		    float: left;
		    margin: 0 5% 0 -25px;
		    min-width: 200px;
		    padding-left: 25px;
		    width: 47%;
		}
			.secondary-panel .welcome-panel-last {margin-right:0;}


		.secondary-panel .welcome-panel-column ul {
			margin: 1.6em 1em 1em 1.3em;
		}

		.secondary-panel .welcome-panel-column li {
			list-style-type: disc;
			padding-left: 2px;
		}

		#wpbody .update-message {margin:5px 0;}

		.submitted-on {font-size:1.3em; line-height:1.4;}

		.svg .wp-badge {
		        position:absolute;
		        width:190px; height:30px;
		        background-color:#fff;
		        background-image: url( <?php echo $badge_url; ?> );
			background-position:22px 10px;
			background-size:auto;
			background-repeat: no-repeat;
			padding-top:200px;
		        color:#999; text-shadow:none;
		}

		#welcome-panel .wp-badge {
			border: 1px solid #DFDFDF;
			border-radius: 4px;
			top:50px; right: 20px;
		}

		/* Retina */
		@media
			only screen and (-webkit-min-device-pixel-ratio: 1.5),
			only screen and (-moz-min-device-pixel-ratio: 1.5),
			only screen and (-o-min-device-pixel-ratio: 3/2),
			only screen and (min-device-pixel-ratio: 1.5) {
				.wp-badge {
					background-image: url( <?php echo $badge_url_2x; ?> );
					background-size: 77%;
					background-repeat: no-repeat;
					width: 152px;
					padding-top: 157px;
					height: 34px;
					top: 50px;
				}
		}

		/* modal */
		#lean_overlay {
			position: fixed;
			z-index:100;
			top: 0px;
			left: 0px;
			height:100%;
			width:100%;
			background: #000;
			display: none;
		}

		/* plugins table */
		.cbox-plugins-section {margin-top:0; padding:20px 20px; line-height:1.6em; border-bottom:1px solid #dfdfdf;}

		tr.cbox-plugin-row-active th, tr.cbox-plugin-row-active td {background-color:#fff;}
		tr.cbox-plugin-row-action-required th, tr.cbox-plugin-row-action-required td {background-color:#F4F4F4;}

		.column-cbox-plugin-name {width:220px;}

		span.enabled       {color:#008800;}
		span.disabled      {color:#880000;}
		span.not-installed {color:#9f9f9f;}

		/* Responsive */
		@media screen and (max-width: 600px) {
			#welcome-panel {min-height:0;}
			#welcome-panel .wp-badge, .welcome-panel-close {display:none;}
			.welcome-panel-content .about-description, .welcome-panel h3 {margin-right:10px;}
			.welcome-panel-dismiss {margin-bottom:2em;}
			.secondary-panel .welcome-panel-column {width:auto;}
		}
		</style>

	<?php
	}

}
