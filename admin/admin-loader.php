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

		/**
		 * Hook to declare when the CBOX admin area is loaded at its earliest.
		 *
		 * @since 1.1.0
		 *
		 * @param CBox_Admin $this
		 */
		do_action( 'cbox_admin_loaded', $this );
	}

	/**
	 * Setup hooks.
	 */
	private function setup_hooks() {
		// Do not register menu if on a sub-site and package wants the main site only.
		if ( ( false === cbox_get_package_prop( 'admin' ) || 'site' === cbox_get_package_prop( 'admin' ) ) &&
			cbox_get_main_site_id() !== get_current_blog_id() ) {

		// Admin menu registration.
		} else {
			add_action( cbox_admin_prop( 'menu' ), array( $this, 'admin_menu' ) );
		}

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

		// after installing a theme, do something
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
		// no package / reset package.
		if ( isset( $_REQUEST['cbox-package'] ) ) {
			// verify nonce
			check_admin_referer( 'cbox_select_package' );

			// We want to select a new package.
			if ( empty( $_REQUEST['cbox-package'] ) ) {
				delete_site_option( '_cbox_current_package' );
				delete_site_option( '_cbox_revision_date' );

			// We've selected a package.
			} else {
				update_site_option( '_cbox_current_package', $_REQUEST['cbox-package'] );
			}
			wp_redirect( cbox_admin_prop( 'url', 'admin.php?page=cbox' ) );
			die();

		// virgin setup
		} elseif ( ! empty( $_REQUEST['cbox-virgin-setup'] ) ) {
			// verify nonce
			check_admin_referer( 'cbox_virgin_setup', 'cbox-virgin-nonce' );

			// set reference pointer for later use
			cbox()->setup = 'virgin-setup';

		// BP installed, but no CBOX
		} elseif ( ! empty( $_REQUEST['cbox-recommended-nonce'] ) ) {
			// verify nonce
			check_admin_referer( 'cbox_bp_installed', 'cbox-recommended-nonce' );

			// set reference pointer for later use
			cbox()->setup = 'install';

			// If no plugins to install, redirect back to CBOX dashboard
			if ( empty( $_REQUEST['cbox_plugins'] ) ) {
				// CBOX and CBOX theme hasn't been installed ever, so prompt for install.
				if ( ! cbox_get_installed_revision_date() && ! cbox_get_theme( cbox_get_theme_prop( 'directory_name' ) )->exists() ) {
					cbox()->setup = 'theme-prompt';

				// Bump the revision date in the DB after updating
				} else {
					add_action( 'cbox_after_updater', function() { cbox_bump_revision_date(); } );
					do_action( 'cbox_after_updater' );

					wp_redirect( self_admin_url( 'admin.php?page=cbox' ) );
					exit;
				}
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
			add_action( 'cbox_after_updater', function() { cbox_bump_revision_date(); } );

		// theme prompt
		} elseif ( ! empty( $_REQUEST['cbox-action'] ) && $_REQUEST['cbox-action'] == 'theme-prompt' ) {
			check_admin_referer( 'cbox_theme_prompt' );

			// CBOX theme doesn't exist, so set reference pointer for later use
			cbox()->setup = 'theme-prompt';

			// bump the revision date in the DB after updating
			add_action( 'cbox_after_updater', function() { cbox_bump_revision_date(); } );

		// install CBOX theme
		} elseif ( ! empty( $_REQUEST['cbox-action'] ) && $_REQUEST['cbox-action'] == 'install-theme' ) {
			// verify nonce
			check_admin_referer( 'cbox_install_theme' );

			// get cbox theme
			$theme = cbox_get_theme( cbox_get_theme_prop( 'directory_name' ) );
			$errors = $theme->errors()->errors;

			// CBOX theme exists! so let's activate it and redirect to the
			// CBOX Theme options page!
			if ( empty( $errors['theme_not_found'] ) ) {
				switch_theme( cbox_get_theme_prop( 'directory_name' ), cbox_get_theme_prop( 'directory_name' ) );

				/**
				 * Mark the theme as having just been activated so that we can run the setup
				 * on next pageload
				 */
				update_blog_option( cbox_get_main_site_id(), '_cbox_theme_activated', '1' );

				wp_redirect( admin_url( cbox_get_theme_prop( 'admin_settings' ) ) );
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

		// Complete step.
		if ( ! empty( $_GET['cbox-action'] ) && 'complete' === $_GET['cbox-action'] && ! cbox_get_installed_revision_date() ) {
			cbox_bump_revision_date();

			wp_redirect( self_admin_url( 'admin.php?page=cbox' ) );
			die();
		}

		// Remove admin notice during setup mode.
		if ( ! empty( cbox()->setup ) ) {
			remove_action( is_network_admin() ? 'network_admin_notices' : 'admin_notices', array( $this, 'display_notice' ) );
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
				// get required CBOX plugins.
				$plugins = CBox_Plugins::get_plugins( 'required' );

				// sort plugins by plugin state
				$plugins = CBox_Admin_Plugins::organize_plugins_by_state( $plugins );

				// include the CBOX Plugin Upgrade and Install API
				if ( ! class_exists( 'CBox_Plugin_Upgrader' ) )
					require( CBOX_PLUGIN_DIR . 'admin/plugin-install.php' );

				// some HTML markup!
				echo '<div class="wrap">';
				echo '<h2>' . esc_html__( 'Installing Required Plugins', 'cbox' ) . '</h2>';

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
				echo '<h2>' . esc_html__( 'Installing Selected Plugins', 'cbox' ) . '</h2>';

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
				$plugins['upgrade'] = CBox_Admin_Plugins::get_upgrades( 'active' );

				// if theme upgrades are available, let's add an extra button to the end of
				// the plugin upgrader, so we can proceed with upgrading the theme
				if ( ! empty( cbox()->theme_upgrades ) ) {
					$title = esc_html__( 'Upgrading CBOX Plugins and Themes', 'cbox' );

					$redirect_link = wp_nonce_url( self_admin_url( 'admin.php?page=cbox&amp;cbox-action=upgrade-theme&amp;cbox-themes=' . cbox()->theme_upgrades ), 'cbox_upgrade_theme' );
					$redirect_text = sprintf( __( "Now, let's upgrade the %s theme &rarr;", 'cbox' ), cbox_get_theme( cbox()->theme_upgrades )->get( 'Name' ) );


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
				echo '<h2>' . $title . '</h2>';

				// start the upgrade!
				$installer = new CBox_Updater( $plugins, array(
					'redirect_link' => $redirect_link,
					'redirect_text' => $redirect_text
				) );

				echo '</div>';

				break;

			// prompt for theme install
			case 'theme-prompt' :
				// some HTML markup!
				echo '<div class="wrap">';

				echo '<h2>' . esc_html__( 'Theme Installation', 'cbox' ) . '</h2>';

				cbox_get_template_part( 'theme-prompt' );

				echo '<div style="margin-top:2em;">';
					echo '<a href="' . self_admin_url( 'admin.php?page=cbox&amp;cbox-action=complete' ) . '" style="display:inline-block; margin:5px 15px 0 0;">' . esc_html__( 'Skip', 'cbox' ) . '</a>';

					echo '<a class="button button-primary" href="' . wp_nonce_url( self_admin_url( 'admin.php?page=cbox&amp;cbox-action=install-theme' ), 'cbox_install_theme' ) . '">' . esc_html__( 'Install Theme', 'cbox' ). '</a>';
				echo '</div>';

				echo '</div>';

				break;

			// install the cbox theme
			case 'install-theme' :
				// include the CBOX Theme Installer
				if ( ! class_exists( 'CBox_Theme_Installer' ) ) {
					require( CBOX_PLUGIN_DIR . 'admin/theme-install.php' );
				}

				$title = sprintf( _x( 'Installing %s theme', 'references the theme that is currently being installed', 'cbox' ), cbox_get_theme_prop( 'name' ) . ' ' . cbox_get_theme_prop( 'version' ) );

				$cbox_theme = new CBox_Theme_Installer( new Theme_Installer_Skin( compact( 'title' ) ) );
				$cbox_theme->install();

				break;

			// upgrade CBOX themes
			case 'upgrade-theme' :
				// include the CBOX Theme Installer
				if ( ! class_exists( 'CBox_Theme_Installer' ) )
					require( CBOX_PLUGIN_DIR . 'admin/theme-install.php' );

				// some HTML markup!
				echo '<div class="wrap">';
				echo '<h2>' . esc_html__('Upgrading Theme', 'cbox' ) . '</h2>';

				// get cbox theme specs
				$upgrader = new CBox_Theme_Installer( new Bulk_Theme_Upgrader_Skin() );

				// Modifies the theme action links that get displayed after theme installation
				// is complete.
				add_filter( 'update_bulk_theme_complete_actions', array( $upgrader, 'remove_theme_actions' ) );

				$upgrader->bulk_upgrade( cbox()->theme_upgrades );

				echo '</div>';

				break;
		}
	}

	/**
         * Do something just after a theme is activated on the next page load.
         *
         * @since 1.0-beta1
         */
	public function theme_activation_hook() {
		if ( get_blog_option( cbox_get_main_site_id(), '_cbox_theme_activated' ) ) {
			delete_blog_option( cbox_get_main_site_id(), '_cbox_theme_activated' );

			/**
			 * Do something just after a theme is activated on the next page load.
			 *
			 * This is a dynamic hook, based off of the current package ID.
			 *
			 * @since 1.1.0
			 */
			do_action( 'cbox_' . cbox_get_current_package_id() . '_theme_activated' );

			// CBOX finished updating, but DB version not saved; do it now.
			if ( ! cbox_get_installed_revision_date() ) {
				cbox_bump_revision_date();
			}
		}
	}

	/** ADMIN PAGE-SPECIFIC *******************************************/

	/**
	 * Setup admin menu and any dependent page hooks.
	 */
	public function admin_menu() {
		$name = cbox_get_package_prop( 'name' ) ? sprintf( __( 'CBOX: %s', 'cbox' ), cbox_get_package_prop( 'name' ) ) : __( 'Commons In A Box', 'cbox' );
		$page = add_menu_page(
			$name,
			$name,
			'install_plugins', // todo - map cap?
			'cbox',
			array( $this, 'admin_page' ),
			'none',
			2
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
			cbox_get_template_part( 'changelog' );

		// credits page
		} elseif ( $this->is_credits() ) {
			cbox_get_template_part( 'credits' );

		// setup screen
		} elseif( ! empty( cbox()->setup ) ) {
			$this->setup_screen();

		// regular screen should go here
		} else {
		?>
			<div class="wrap">
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
			// (0) No package.
			case 'no-package' :
				wp_enqueue_script( 'thickbox' );
				wp_enqueue_style( 'thickbox' );
			?>

				<div style="text-align:center;">
					<h2><?php _e( 'Select a box', 'cbox' ); ?></h2>

					<p><?php _e( 'A box is a specially-crafted bundle of plugins and a theme for WordPress, designed for quick installation and configuration. Select the box that best suits your site.', 'cbox' ); ?></p>
				</div>

				<form method="post" action="<?php echo self_admin_url( 'admin.php?page=cbox' ); ?>">
					<div class="wp-list-table widefat">
					<div id="the-list">

			<?php foreach ( cbox_get_packages() as $package => $class ) :
				$incompatible = ! is_multisite() && true === cbox_get_package_prop( 'network', $package );
			?>

			<div class="plugin-card plugin-card-<?php echo sanitize_html_class( cbox_get_package_prop( 'name', $package ) ); ?>" style="width:100%; margin-left:0;">
			<div class="plugin-card-top">
				<div class="name column-name">
					<h3><?php esc_attr_e( cbox_get_package_prop( 'name', $package ) ); ?>

					<img src="<?php echo esc_url( cbox_get_package_prop( 'icon_url', $package ) ); ?>" class="plugin-icon" alt="">
					</h3>
				</div>

				<div class="action-links">
					<ul class="plugin-action-buttons">
						<li><a href="<?php echo $incompatible ? '#' : wp_nonce_url( self_admin_url( 'admin.php?page=cbox&amp;cbox-package=' . $package ), 'cbox_select_package' ); ?>" class="button <?php echo $incompatible ? 'disabled' : 'activate-now'; ?>" aria-label="<?php printf( esc_html__( 'Select %s', 'cbox' ), cbox_get_package_prop( 'name', $package ) ); ?>"><?php esc_html_e( 'Select', 'cbox' ); ?></a></li>
						<li><a href="<?php echo esc_url( cbox_get_package_prop( 'documentation_url', $package ) ); ?>?TB_iframe=true&amp;width=600&amp;height=550" class="thickbox open-plugin-details-modal" aria-label="<?php printf( esc_attr__( 'More information about %s', 'cbox' ), cbox_get_package_prop( 'name', $package ) ); ?>" data-title="<?php echo esc_attr( cbox_get_package_prop( 'name', $package ) ); ?>"><?php esc_html_e( 'More Details', 'cbox' ); ?></a></li>
					</ul>
				</div>

				<div class="desc column-description">
					<?php cbox_get_template_part( 'description', $package ); ?>
					<!--<p class="authors"> <cite>By <a href="">CBOX Team</a></cite></p>-->
				</div>

				<div class="prompt" style="display:none">
					<p><?php esc_html_e( 'Selecting this box can:', 'cbox' ); ?></p>

					<ul>
					<?php cbox_get_template_part( 'permissions', $package ); ?>
					</ul>

					<p><?php esc_html_e( 'Are you sure you want to continue?', 'cbox' ); ?></p>
				</div>
			</div>

			<div class="plugin-card-bottom">
				<div class="column-compatibility">
					<?php if ( $incompatible ) : ?>
						<span class="compatibility-incompatible"><?php _e( 'Requires WordPress Multisite.', 'cbox' ); ?> <?php printf( '<a href="%1$s" target="_blank">%2$s</a>', 'https://codex.wordpress.org/Create_A_Network', esc_html__(
						'Find out how to convert to a WordPress Multisite network here.', 'cbox' ) ); ?></span>
					<?php else : ?>
						<span class="compatibility-compatible"><strong>Compatible</strong> with your version of WordPress</span>
					<?php endif; ?>
				</div>
			</div>

			</div>

			<?php endforeach; ?>

					</div>
					</div>
				</form>

				<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.1.1/jquery-confirm.min.css">
				<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.1.1/jquery-confirm.min.js"></script>

				<script>
jQuery('a.activate-now').confirm({
	type: 'red',
	content: function() {
		var package = this.$target.closest( '.plugin-card' );

		// Set modal title.
		this.setTitle( package.find( 'h3' )[0].textContent );

		// Set modal content.
		return package.find( '.prompt' )[0].innerHTML;
	},
	title: function() {},
	boxWidth: '500px',
	useBootstrap: false,
	bgOpacity: 0.7,
	buttons: {
	        no: {
			text: '<?php esc_attr_e( 'No', 'cbox' ); ?>',
			action: function() {}
		},
		yes: {
			text: '<?php esc_attr_e( 'Yes', 'cbox' ); ?>',
			btnClass: 'btn-red',
			action: function () {
				location.href = this.$target.attr('href');
			}
		},
	}
});
				</script>

<style type="text/css">
.jconfirm ul {list-style-type:disc; padding-left:25px;}
</style>

			<?php
				break;

			// (1) required plugins need to be installed/upgraded first if necessary.
			case 'required-plugins' :

			?>

				<h2><?php _e( 'Required Plugins', 'cbox' ); ?></h2>

				<form method="post" action="<?php echo self_admin_url( 'admin.php?page=cbox' ); ?>">
					<p class="submitted-on"><?php printf( __( "Before you can use Commons In A Box %s, we'll need to install some required plugins. Click 'Continue' to get set up.", 'cbox' ), cbox_get_package_prop( 'name' ) ); ?></p>

					<?php wp_nonce_field( 'cbox_virgin_setup', 'cbox-virgin-nonce' ); ?>

					<p><input type="submit" value="<?php _e( 'Continue &rarr;', 'cbox' ); ?>" class="button-primary" name="cbox-virgin-setup" /></p>
				</form>

			<?php
				break;

			// (2) next, recommended plugins are offered if available.
			case 'recommended-plugins' :
			?>

				<h2><?php _e( 'Recommended Plugins', 'cbox' ); ?></h2>

				<form method="post" action="<?php echo self_admin_url( 'admin.php?page=cbox' ); ?>">
					<p class="submitted-on"><?php _e( "You're almost finished the setup process!", 'cbox' ); ?></p>

					<p class="submitted-on"><?php printf( __( "Did you know Commons In A Box %s comes prebundled with a few, recommended plugins?  These plugins help to add functionality to your existing WordPress site.", 'cbox' ), cbox_get_package_prop( 'name' ) ); ?>

					<p class="submitted-on"><?php _e( "We have automatically selected the following plugins to install for you. However, feel free to uncheck some of these plugins based on your site's needs.", 'cbox' ); ?></p>

					<?php wp_nonce_field( 'cbox_bp_installed', 'cbox-recommended-nonce' ); ?>

					<?php
						CBox_Admin_Plugins::render_plugin_table( array(
							'type'           => 'recommended',
							'omit_activated' => true,
							'check_all'      => true
						) );
					?>
				</form>

			<?php
				break;

			// (3) bump revision date if we ever reach here.
			default :
				cbox_bump_revision_date();
				echo '<script type="text/javascript">window.location = document.URL;</script>';

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
		$active_cbox_plugins_need_update = CBox_Admin_Plugins::get_upgrades( 'active' );

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
		$url = wp_nonce_url( self_admin_url( 'admin.php?page=cbox&amp;cbox-action=upgrade' ), 'cbox_upgrade' );

		// theme is available for upgrade
		if ( ! empty( $active_cbox_plugins_need_update ) && ! empty( $is_theme_upgrade ) ) {
			++$total_count;

			// theme has update, so add an extra parameter to the querystring
			$url = wp_nonce_url( self_admin_url( 'admin.php?page=cbox&amp;cbox-action=upgrade&amp;cbox-themes=' . $is_theme_upgrade ), 'cbox_upgrade' );

			$message = sprintf( _n( '%d installed plugin and the theme have an update available. Click on the button below to upgrade.', '%d installed plugins and the theme have updates available. Click on the button below to upgrade.', $plugin_count, 'cbox' ), $plugin_count );

		// just plugins
		} elseif ( ! empty( $active_cbox_plugins_need_update ) ) {
			$message = sprintf( _n( '%d installed plugin has an update available. Click on the button below to upgrade.', '%d installed plugins have updates available. Click on the button below to upgrade.', $plugin_count, 'cbox' ), $plugin_count );

		// just themes
		} else {
			// theme has update, so switch up the upgrade URL
			$url = wp_nonce_url( self_admin_url( 'admin.php?page=cbox&amp;cbox-action=upgrade-theme&amp;cbox-themes=' . $is_theme_upgrade ), 'cbox_upgrade_theme' );

			$message = sprintf( __( 'The %s theme has an update available. Click on the button below to upgrade.', 'cbox' ), cbox_get_theme_prop( 'name' ) );
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

		cbox_get_template_part( 'dashboard' );
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

		cbox_get_template_part( 'footer' );
	}

	/** HEADER INJECTIONS *********************************************/

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
			case 'no-package' :
			case 'required-plugins' :
				$notice_text = __( "Let's get started!", 'cbox' );
				$button_link = cbox_admin_prop( 'url', 'admin.php?page=cbox' );
				$button_text = __( 'Click here to get set up', 'cbox' );
				$disable_btn = 'cbox';
				break;

			case 'theme-update' :
				$notice_text = sprintf( __( 'The %1$s theme needs an update.', 'cbox' ), esc_attr( cbox_get_theme_prop( 'name' ) ) );
				$button_link = wp_nonce_url( cbox_admin_prop( 'url', 'admin.php?page=cbox&amp;cbox-action=upgrade-theme&amp;cbox-themes=' . esc_attr( cbox_get_theme_prop( 'directory_name' ) ) ), 'cbox_upgrade_theme' );
				$button_text = __( 'Update the theme &rarr;', 'cbox' );
				$disable_btn = 'cbox';
				break;

			case 'recommended-plugins' :
				$notice_text = __( 'You only have one last thing to do. We promise!', 'cbox' );
				$button_link = cbox_admin_prop( 'url', 'admin.php?page=cbox' );
				$button_text = __( 'Click here to finish up!', 'cbox' );
				$disable_btn = 'cbox';
				break;

			case '' :
				return;
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

			echo '<p style="margin-bottom:2.1em;">' . sprintf( __( 'You can <a href="%s">manage your CBOX plugins here</a>.', 'cbox' ), cbox_admin_prop( 'url', 'admin.php?page=cbox-plugins' ) ) . '</p>';

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
			'https://cdn.rawgit.com/FinelySliced/leanModal.js/master/jquery.leanModal.js',
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
		/* Plugin row */
		.row-actions-visible .plugin-card {width: 100%; padding: 0px 8px; margin: 0;}
		.row-actions-visible .plugin-card .update-now {line-height: 1.8;}

		#adminmenu #toplevel_page_cbox .wp-menu-image {
			background: url('<?php echo $menu_icon_url; ?>');
			background-repeat: no-repeat;
		}

		#adminmenu #toplevel_page_cbox .wp-menu-image {background-position:4px -32px;}

		#adminmenu #toplevel_page_cbox:hover .wp-menu-image,
		#adminmenu #toplevel_page_cbox.wp-has-current-submenu .wp-menu-image {background-position:4px -1px;}

		#toplevel_page_cbox .wp-menu-image img {display:none;}

		.cbox-admin-wrap .nav-tab:first-child {margin-left: 0;}
		.cbox-admin-wrap .nav-tab-active {background: #fff; border-bottom: 1px solid #fff;}

		.cbox-admin-content {background: #fff; border: 1px solid #ccc; border-top: none; margin-bottom: 40px; padding: 20px 20px;}
		.cbox-admin-content.has-top-border {border-top: 1px solid #ccc;}

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
