<?php
/**
 * Set up the admin area
 *
 * @package Commons_In_A_Box
 * @subpackage Adminstration
 * @since 0.1
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class CIAB_Admin {

	public function __construct() {
		$this->setup_hooks();
	}

	private function setup_hooks() {
		add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', array( &$this, 'admin_menu' ) );
	}

	public function admin_menu() {
		$page = add_menu_page(
			__( 'Commons in a Box', 'cbox' ),
			__( 'CIAB', 'cbox' ),
			'activate_plugins', // todo - map cap?
			'ciab',
			array( &$this, 'admin_page' )
		);

		$subpage = add_submenu_page(
			'ciab',
			__( 'Dashboard', 'cbox' ),
			__( 'Dashboard', 'cbox' ),
			'activate_plugins', // todo - map cap?
			'ciab',
			array( &$this, 'admin_page' )
		);
	}

	public function admin_page() {
	?>
		<div class="wrap">
			<?php screen_icon( 'plugins' ); ?>
			<h2><?php _e( 'Commons in a Box', 'cbox' ); ?></h2>

			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut varius eros porttitor ipsum eleifend id facilisis velit molestie. Proin mollis enim et elit tincidunt eget rutrum nulla auctor. Phasellus placerat semper erat ac eleifend. Ut malesuada odio eu velit varius at porttitor lorem accumsan. Pellentesque sed lectus nec mauris vulputate suscipit at id purus. Phasellus sollicitudin, nulla at commodo facilisis, eros ante venenatis lorem, eu egestas dolor odio sed ipsum.</p>

			<p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Quisque cursus diam quis diam sagittis vitae adipiscing sem tempus. Donec neque quam, lacinia sed vulputate quis, pretium cursus dui. Morbi eget ullamcorper justo. Donec ultricies nisl vel orci condimentum vel venenatis mi tincidunt. Fusce sagittis egestas turpis, ut interdum nisl venenatis at. Pellentesque euismod, turpis id mattis convallis, massa eros molestie lectus, ut volutpat mi leo vel nibh. Fusce placerat, nisi in vestibulum mollis, justo metus iaculis quam, a eleifend ipsum diam eget nunc.</p>

			<p>Phasellus pellentesque nibh viverra felis sollicitudin id faucibus velit tristique. Sed ut risus non magna volutpat vehicula. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eros libero, tincidunt sit amet consectetur quis, eleifend quis metus. Nullam mattis, leo eget lobortis vestibulum, nunc mauris convallis dolor, non egestas libero lacus et quam.</p>

			<p>Quisque pulvinar purus in est egestas sed imperdiet massa viverra. Vivamus volutpat odio ac ante gravida consectetur. Vestibulum id arcu ante, vel viverra felis. Phasellus urna libero, mollis sit amet suscipit eget, fermentum id ante. Curabitur nibh enim, eleifend eget dignissim a, lobortis non sem. Vivamus venenatis mollis nisl ut ullamcorper. Vivamus elementum ornare nisi sit amet consectetur. Suspendisse in turpis nisl, sit amet euismod purus. Suspendisse sodales condimentum elit, non rhoncus erat dignissim sed. Phasellus sit amet nunc dolor, ac placerat justo. Proin a ante ipsum.</p>
		</div>

	<?php
	}

}

// initialize admin area
new CIAB_Admin;

?>