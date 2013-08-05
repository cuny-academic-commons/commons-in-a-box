<?php
/**
 * Set up the settings page.
 *
 * @since 1.0-beta2
 *
 * @package Commons_In_A_Box
 * @subpackage Adminstration
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Setup the CBOX settings area.
 *
 * @since 1.0-beta2
 */
class CBox_Settings {

	/**
	 * Static variable to hold our various settings
	 *
	 * @var array
	 */
	private static $settings = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		// setup our hooks
		$this->setup_hooks();
	}

	/**
	 * Setup our hooks.
	 */
	private function setup_hooks() {
		add_action( 'admin_init',      array( $this, 'register_settings_hook' ) );

		// setup the CBOX plugin menu
		add_action( 'cbox_admin_menu', array( $this, 'setup_settings_page' ), 20 );
	}

	/** SETTINGS-SPECIFIC *********************************************/

	/**
	 * Public function to call our private register_settings() method.
	 *
	 * @since 1.0.5
	 */
	public function register_settings_hook() {
		$this->register_settings();
	}

	/**
	 * Register settings.
	 *
	 * Used to render the checkboxes as well as the format to load these settings
	 * on the frontend.
	 *
	 * @see CBox_Settings::register_setting()
	 */
	private function register_settings() {
		// setup BP settings array
		$bp_settings = array();

		$bp_settings[] = array(
			'label'       => __( 'Member Profile Default Tab', 'cbox' ),
			'description' => __( 'On a member page, set the default tab to "Profile" instead of "Activity".', 'cbox' ),
			'class_name'  => 'CBox_BP_Profile_Tab', // this will load up the corresponding class; class must be created
		);

		if ( function_exists( 'bp_is_active' ) && bp_is_active( 'groups' ) &&
			( function_exists( 'bbp_is_group_forums_active' ) && bbp_is_group_forums_active() ) ||
			( function_exists( 'bp_forums_is_installed_correctly' ) && bp_forums_is_installed_correctly() ) ) {
			$bp_settings[] = array(
				'label'       => __( 'Group Forum Default Tab', 'cbox' ),
				'description' => __( 'On a group page, set the default tab to "Forum" instead of "Activity".', 'cbox' ),
				'class_name'  => 'CBox_BP_Group_Forum_Tab'
			);
		}

		// BuddyPress
		self::register_setting( array(
			'plugin_name' => 'BuddyPress',
			'key'         => 'bp',
			'settings'    => $bp_settings
		) );

		// BuddyPress Group Email Subscription
		self::register_setting( array(
			'plugin_name' => 'BuddyPress Group Email Subscription',
			'key'         => 'ges',
			'settings'    => array(
				array(
					'label'       => __( 'Forum Full Text', 'cbox' ),
					'description' => __( 'Check this box if you would like the full text of bbPress forum posts to appear in email notifications.', 'cbox' ),
					'class_name'  => 'CBox_GES_bbPress2_Full_Text'
				)
			),
		) );
	}

	/**
	 * Register a plugin's settings in CBOX.
	 *
	 * Updates our private, static $settings variable in the process.
	 *
	 * @see CBox_Admin_Settings::register_settings()
	 */
	private function register_setting( $args = '' ) {
		$defaults = array(
			'plugin_name' => false,   // (required) the name of the plugin as in the plugin header
			'key'         => false,   // (required) this is used to identify the plugin;
			                          //            also used for the filename suffix, see /includes/frontend.php
			'settings'    => array(), // (required) multidimensional array
		);

		$r = wp_parse_args( $args, $defaults );

		if ( empty( $r['plugin_name'] ) || empty( $r['key'] ) || empty( $r['settings'] ) )
			return false;

		self::$settings[ $r['plugin_name'] ]['key']      = $r['key'];
		self::$settings[ $r['plugin_name'] ]['settings'] = $r['settings'];

	}

	/** ADMIN PAGE-SPECIFIC *******************************************/

	/**
	 * Setup CBOX's settings menu item.
	 */
	public function setup_settings_page() {
		// see if CBOX is fully setup
		if ( ! cbox_is_setup() )
			return;

		// add our settings page
		$page = add_submenu_page(
			'cbox',
			__( 'Commons In A Box Settings', 'cbox' ),
			__( 'Settings', 'cbox' ),
			'install_plugins', // todo - map cap?
			'cbox-settings',
			array( $this, 'admin_page' )
		);

		// load Plugin Dependencies plugin on the CBOX plugins page
		add_action( "load-{$page}", array( 'Plugin_Dependencies', 'init' ) );

		// validate any settings changes submitted from the CBOX settings page
		add_action( "load-{$page}", array( $this, 'validate_settings' ) );

		// inline CSS
		add_action( "admin_head-{$page}", array( 'CBox_Admin', 'dashboard_css' ) );
		//add_action( "admin_head-{$page}", array( $this, 'inline_css' ) );
	}

	/**
	 * Validates settings submitted from the settings admin page.
	 */
	public function validate_settings() {
		if ( empty( $_REQUEST['cbox-settings-save'] ) )
			return;

		check_admin_referer( 'cbox_settings_options' );

		// get submitted values
		$submitted = (array) $_REQUEST['cbox_settings'];

		// update settings
		bp_update_option( cbox()->settings_key, $submitted );

		// add an admin notice
		$prefix = is_network_admin() ? 'network_' : '';
		add_action( $prefix . 'admin_notices', create_function( '', "
			echo '<div class=\'updated\'><p><strong>' . __( 'Settings saved.', 'cbox' ) . '</strong></p></div>';
		" ) );
	}

	/**
	 * Renders the settings admin page.
	 */
	public function admin_page() {
	?>
		<div class="wrap">
			<?php screen_icon( 'cbox' ); ?>
			<h2><?php _e( 'Commons In A Box Settings', 'cbox' ); ?></h2>

			<p><?php _e( 'CBOX can configure some important options for certain plugins.', 'cbox' ); ?>

			<form method="post" action="">
				<?php $this->render_options(); ?>

				<?php wp_nonce_field( 'cbox_settings_options' ); ?>

				<p><input type="submit" value="<?php _e( 'Save Changes', 'cbox' ); ?>" class="button-primary" name="cbox-settings-save" /></p>
			</form>
		</div>

	<?php
	}

	/**
	 * Renders all our checkboxes on the settings admin page.
	 */
	private function render_options() {
		// get all installed CBOX plugins
		$cbox_plugins = cbox()->plugins->get_plugins();

		// get all CBOX plugins by name
		$active = cbox()->plugins->organize_plugins_by_state( $cbox_plugins );

		// sanity check.  will probably never encounter this use-case.
		if ( empty( $active ) )
			return false;

		// get only active plugins and flip them for faster processing
		$active = array_flip( $active['deactivate'] );

		// get saved settings
		$cbox_settings = bp_get_option( cbox()->settings_key );

		// parse and output settings
		foreach( self::$settings as $plugin => $settings ) {
			// if plugin doesn't exist, don't show the settings for that plugin
			if( ! isset( $active[$plugin] ) )
				continue;

			// grab the key so we can reference it later
			$key = $settings['key'];

			// drop the key for the $settings loop
			unset( $settings['key'] );
		?>
			<h3><?php echo $plugin; ?></h3>

			<table class="form-table">
			<?php foreach ( $settings['settings'] as $setting ) : ?>

				<tr valign="top">
					<th scope="row"><?php echo $setting['label']; ?></th>
					<td>
						<input id="<?php echo sanitize_title( $setting['label'] ); ?>" name="cbox_settings[<?php echo $key;?>][]" type="checkbox" value="<?php echo $setting['class_name']; ?>" <?php $this->is_checked( $setting['class_name'], $cbox_settings, $key ); ?>  />
						<label for="<?php echo sanitize_title( $setting['label'] ); ?>"><?php echo $setting['description']; ?></label>
					</td>
				</tr>

			<?php endforeach; ?>
			</table>
		<?php
		}

	}

	/**
	 * Helper function to see if an option is checked.
	 */
	private function is_checked( $class_name, $settings, $key ) {
		if ( isset( $settings[$key] ) && in_array( $class_name, (array) $settings[$key] ) ) {
			echo 'checked="checked"';
		}
	}
}
