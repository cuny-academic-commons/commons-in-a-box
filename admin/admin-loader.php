<?php
/**
 * Set up the admin area
 *
 * @package Commons_In_A_Box
 * @subpackage Adminstration
 * @since 0.1.1
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Setup the CBox admin area.
 *
 * @since 0.1.1
 */
class CIAB_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', array( &$this, 'admin_menu' ) );
	}

	/**
	 * Setup admin menu and any dependent page hooks.
	 */
	public function admin_menu() {
		$page = add_menu_page(
			__( 'Commons in a Box', 'cbox' ),
			__( 'CBox', 'cbox' ),
			'activate_plugins', // todo - map cap?
			'cbox',
			array( &$this, 'admin_page' ),
			$this->icon_16_black() // temp icon; i think it looks neat...
		);

		$subpage = add_submenu_page(
			'cbox',
			__( 'Commons in a Box Dashboard', 'cbox' ),
			__( 'Dashboard', 'cbox' ),
			'activate_plugins', // todo - map cap?
			'cbox',
			array( &$this, 'admin_page' )
		);

		// inline CSS
		add_action( "admin_head-{$subpage}", array( &$this, 'inline_css' ) );

		// contextual help
		add_action( "load-{$subpage}",       array( &$this, 'contextual_help' ) );

		// load Plugin Dependencies plugin on the Cbox dashboard page
		add_action( "load-{$subpage}",       array( 'Plugin_Dependencies', 'init' ) );
	}

	/**
	 * The main dashboard page.
	 */
	public function admin_page() {
		// show this page during update
		if ( $this->is_update() ) {
			CIAB_Plugins::update_screen();
		}

		// if upgrade process is finished, show upgrade screen
		elseif ( $this->is_upgraded() ) {
			require( CIAB_PLUGIN_DIR . 'admin/changelog.php' );
		}

		// regular screen should go here
		else {
		?>
			<div class="wrap">
				<?php screen_icon( 'index' ); ?>
				<h2><?php _e( 'Commons in a Box Dashboard', 'cbox' ); ?></h2>

				<?php $this->welcome_panel(); ?>

				<form method="post" action="<?php echo self_admin_url( 'admin.php?page=cbox' ); ?>">
					<?php do_action( 'cbox_dashboard_form' ); ?>

					<?php wp_nonce_field( 'cbox_update' ); ?>

					<p><input type="submit" value="<?php _e( 'Update', 'cbox' ); ?>" class="button-primary" id="cbox-update" name="cbox-update" /></p>
				</form>
			</div>
		<?php
		}
	}

	/**
	 * Registers contextual help for the Cbox dashboard page
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
	 * Are we updating?
	 *
	 * @see CIAB_Plugins::validate_cbox_dashboard()
	 * @return bool
	 */
	private function is_update() {
		if ( ! empty( cbox()->update ) )
			return true;

		return false;
	}

	/**
	 * Did we just finish the upgrade process?
	 *
	 * @todo This should be fletched out as soon as we finish the upgrade wizard!
	 *
	 * @return bool
	 */
	private function is_upgraded() {
		if ( ! empty( $_GET['whatsnew'] ) )
			return true;

		// toggle this to true if you want to see what the upgrade screen looks like!
		return false;
	}

	/**
	 * The CBox welcome panel.
	 */
	private function welcome_panel() {
		if ( isset( $_GET['welcome'] ) ) {
			$welcome_checked = empty( $_GET['welcome'] ) ? 0 : 1;
			update_user_meta( get_current_user_id(), 'show_cbox_welcome_panel', $welcome_checked );
		}

		$classes = 'welcome-panel';

		$option = get_user_meta( get_current_user_id(), 'show_cbox_welcome_panel', true );

		if ( $option === false )
			$option = 1;

		/*
			upgrade routine - todo
			if ( ! is_multisite() )
				update_user_meta( $user_id, 'show_cbox_welcome_panel', 1 );
			elseif ( ! is_super_admin( $user_id ) && ! metadata_exists( 'user', $user_id, 'show_cbox_welcome_panel' ) )
				update_user_meta( $user_id, 'show_cbox_welcome_panel', 2 );
		*/

		// 0 = hide, 1 = toggled to show or single site creator, 2 = multisite site owner
		$hide = 0 == $option || ( 2 == $option && wp_get_current_user()->user_email != get_option( 'admin_email' ) );
		if ( $hide )
			$classes .= ' hidden';

		$display_version = constant( 'CBOX_VERSION' );
	?>
		<div id="welcome-panel" class="<?php echo esc_attr( $classes ); ?>">
		<?php wp_nonce_field( 'welcome-panel-nonce', 'welcomepanelnonce', false ); ?>
		<a class="welcome-panel-close" href="<?php echo esc_url( network_admin_url( 'admin.php?page=cbox&welcome=0' ) ); ?>"><?php _e('Dismiss'); ?></a>
		<div class="wp-badge"><?php printf( __( 'Version %s' ), $display_version ); ?></div>

		<div class="welcome-panel-content">
		<h3><?php _e( 'Welcome to Commons in a Box! ', 'cbox' ); ?></h3>
		<p class="about-description"><?php _e( 'If you need help getting started, check out our documentation on <a href="https://github.com/cuny-academic-commons/commons-in-a-box/wiki">our wiki</a>. If you&#8217;d rather dive right in, here are a few things most people do first when they set up a new CBox site.' ); ?></p>
		<div class="welcome-panel-column-container">
		<div class="welcome-panel-column">
			<h4><span class="icon16 icon-settings"></span> <?php _e( 'Setup Plugins', 'cbox' ); ?></h4>
			<p><?php _e( 'Here are a few things you can do to get your feet wet.', 'cbox' ); ?></p>
			<ul>
			<li><?php echo sprintf(	__( '<a href="%s">Link to screencast?</a>', 'cbox' ), esc_url( admin_url('#') ) ); ?></li>
			</ul>
		</div>
		<div class="welcome-panel-column">
			<h4><span class="icon16 icon-page"></span> <?php _e( 'More content here!', 'cbox' ); ?></h4>
			<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque nulla dui, luctus ornare condimentum quis, eleifend non leo.', 'cbox' ); ?></p>
			<ul>
			<li><?php echo sprintf( __( '<a href="%s">Narf!</a>' ), esc_url( admin_url('#') ) ); ?></li>
			</ul>
		</div>
		<div class="welcome-panel-column welcome-panel-last">
			<h4><span class="icon16 icon-appearance"></span> <?php _e( 'Customize Your Site' ); ?></h4>
			<?php
				$theme = wp_get_theme();
				if ( $theme->errors() ) :
					echo '<p>';
					printf( __( '<a href="%s">Install a theme</a> to get started customizing your site.' ), esc_url( admin_url( 'themes.php' ) ) );
					echo '</p>';
				else:
					$customize_links = array();
					if ( 'cbox' == $theme->get_stylesheet() )
						$customize_links[] = sprintf( __( '<a href="%s">Choose light or dark</a>' ), esc_url( admin_url( 'themes.php?page=theme_options' ) ) );

				if ( current_theme_supports( 'custom-background' ) )
					$customize_links[] = sprintf( __( '<a href="%s">Set a background color</a>' ), esc_url( admin_url( 'themes.php?page=custom-background' ) ) );

				if ( current_theme_supports( 'custom-header' ) )
					$customize_links[] = sprintf( __( '<a href="%s">Select a new header image</a>' ), esc_url( admin_url( 'themes.php?page=custom-header' ) ) );

				if ( current_theme_supports( 'widgets' ) )
					$customize_links[] = sprintf( __( '<a href="%s">Add some widgets</a>' ), esc_url( admin_url( 'widgets.php' ) ) );

				if ( ! empty( $customize_links ) ) {
					echo '<p>';

					if ( $theme->get_stylesheet() != 'cbox' )
						printf( __( 'Use the current theme &mdash; %1$s &mdash; or <a href="%2$s">use the CBox Default theme</a>. If you stick with %1$s, here are a few ways to make your site look unique.' ), $theme->display('Name'), esc_url( admin_url( 'themes.php' ) ) );
					else
						printf( __( "You're using the Cbox Default theme! Good on ya! Here are a few ways to make your site look unique.", 'cbox' ) );

					echo '</p>';
				?>
				<ul>
					<?php foreach ( $customize_links as $customize_link ) : ?>
					<li><?php echo $customize_link ?></li>
					<?php endforeach; ?>
				</ul>
				<?php
				} else {
					echo '<p>';
					printf( __( 'Use the current theme &mdash; %1$s &mdash; or <a href="%2$s">choose a new one</a>.' ), $ct->title, esc_url( admin_url( 'themes.php' ) ) );
					echo '</p>';
				}
			endif; ?>
		</div>
		</div>
		<p class="welcome-panel-dismiss"><?php printf( __( 'Already know what you&#8217;re doing? <a href="%s">Dismiss this message</a>.' ), esc_url( network_admin_url( 'admin.php?page=cbox&welcome=0' ) ) ); ?></p>
		</div>
		</div>
	<?php
	}

	/**
	 * Inline CSS for the main dashboard page.
	 */
	public function inline_css() {
	?>
		<style type="text/css">
			/* temp icon! */
			.toplevel_page_cbox .wp-menu-image {background:url(<?php echo $this->icon_16_gray(); ?>) 7px 7px no-repeat;}
			.toplevel_page_cbox .wp-menu-image img {display:none;}

			.about-text {margin-right:220px;}
			.welcome-panel-content .about-description, .welcome-panel h3 {margin-left:210px;}

			span.enabled       {color:#008800;}
			span.disabled      {color:#880000;}
			span.not-installed {color:#9f9f9f;}
			
			.dep-list li {list-style:disc; margin-left:1.5em;}

			.wp-badge {
			        -webkit-box-shadow:  0px 0px 6px 2px rgba(51, 51, 51, .3);
			        box-shadow:  0px 0px 6px 2px rgba(51, 51, 51, .3);
				-webkit-border-radius: 8px;
				border-radius: 8px;
			        width:190px;
				padding-top:120px;
			        background-color:#333; background-position:8px 30px;
			        background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAALYAAABGCAYAAABhYm4dAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2hpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDowNjgwMTE3NDA3MjA2ODExODA4M0YxOUQ2MDNGMjcyOCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDoxRkIzRUM5REE0QTgxMUUxQTBCMEYwOEYxNTE3NTAxNyIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDoxRkIzRUM5Q0E0QTgxMUUxQTBCMEYwOEYxNTE3NTAxNyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M2IChNYWNpbnRvc2gpIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6MDY4MDExNzQwNzIwNjgxMTgwODNGMTlENjAzRjI3MjgiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6MDY4MDExNzQwNzIwNjgxMTgwODNGMTlENjAzRjI3MjgiLz4gPC9yZGY6RGVzY3Jp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);
		</style>
	<?php
	}

	/**
	 * Base64 data-encoded version of Gentleface Toolbar's "Cube" 16x16 gray icon.
	 *
	 * @link http://www.gentleface.com/free_icon_set.html
	 * @license http://creativecommons.org/licenses/by-nc/3.0/
	 */
	public function icon_16_gray() {
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAJ42lUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4KPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNC4yLjItYzA2MyA1My4zNTI2MjQsIDIwMDgvMDcvMzAtMTg6MTI6MTggICAgICAgICI+CiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPgogIDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiCiAgICB4bWxuczpwaG90b3Nob3A9Imh0dHA6Ly9ucy5hZG9iZS5jb20vcGhvdG9zaG9wLzEuMC8iCiAgICB4bWxuczpJcHRjNHhtcENvcmU9Imh0dHA6Ly9pcHRjLm9yZy9zdGQvSXB0YzR4bXBDb3JlLzEuMC94bWxucy8iCiAgICB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iCiAgICB4bWxuczp4bXBSaWdodHM9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9yaWdodHMvIgogICAgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIgogICAgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iCiAgICB4bWxuczpzdEV2dD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlRXZlbnQjIgogICBwaG90b3Nob3A6RGF0ZUNyZWF0ZWQ9IjIwMTAtMDEtMDEiCiAgIHBob3Rvc2hvcDpDcmVkaXQ9Ind3dy5nZW50bGVmYWNlLmNvbSIKICAgcGhvdG9zaG9wOkF1dGhvcnNQb3NpdGlvbj0iQXJ0IERpcmVjdG9yIgogICBJcHRjNHhtcENvcmU6SW50ZWxsZWN0dWFsR2VucmU9InBpY3RvZ3JhbSIKICAgeG1wOk1ldGFkYXRhRGF0ZT0iMjAxMC0wMS0wM1QyMTozOTo0MSswMTowMCIKICAgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo5RDY1NTAxRUE4RjhERTExODIxQ0U0QjJDN0UzNkQ3MCIKICAgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo5RDY1NTAxRUE4RjhERTExODIxQ0U0QjJDN0UzNkQ3MCIKICAgeG1wTU06T3JpZ2luYWxEb2N1bWVudElEPSJ4bXAuZGlkOjlENjU1MDFFQThGOERFMTE4MjFDRTRCMkM3RTM2RDcwIj4KICAgPElwdGM0eG1wQ29yZTpDcmVhdG9yQ29udGFjdEluZm8KICAgIElwdGM0eG1wQ29yZTpDaUFkckNpdHk9IlByYWd1ZSIKICAgIElwdGM0eG1wQ29yZTpDaUFkclBjb2RlPSIxNjAwMCIKICAgIElwdGM0eG1wQ29yZTpDaUFkckN0cnk9IkN6ZWNoIFJlcHVibGljIgogICAgSXB0YzR4bXBDb3JlOkNpRW1haWxXb3JrPSJrYUBnZW50bGVmYWNlLmNvbSIKICAgIElwdGM0eG1wQ29yZTpDaVVybFdvcms9Ind3dy5nZW50bGVmYWNlLmNvbSIvPgogICA8ZGM6cmlnaHRzPgogICAgPHJkZjpBbHQ+CiAgICAgPHJkZjpsaSB4bWw6bGFuZz0ieC1kZWZhdWx0Ij5DcmVhdGl2ZSBDb21tb25zIEF0dHJpYnV0aW9uIE5vbi1Db21tZXJjaWFsIE5vIERlcml2YXRpdmVzPC9yZGY6bGk+CiAgICA8L3JkZjpBbHQ+CiAgIDwvZGM6cmlnaHRzPgogICA8ZGM6Y3JlYXRvcj4KICAgIDxyZGY6U2VxPgogICAgIDxyZGY6bGk+QWxleGFuZGVyIEtpc2VsZXY8L3JkZjpsaT4KICAgIDwvcmRmOlNlcT4KICAgPC9kYzpjcmVhdG9yPgogICA8ZGM6ZGVzY3JpcHRpb24+CiAgICA8cmRmOkFsdD4KICAgICA8cmRmOmxpIHhtbDpsYW5nPSJ4LWRlZmF1bHQiPlRoaXMgaXMgdGhlIGljb24gZnJvbSBHZW50bGVmYWNlLmNvbSBmcmVlIGljb25zIHNldC4gPC9yZGY6bGk+CiAgICA8L3JkZjpBbHQ+CiAgIDwvZGM6ZGVzY3JpcHRpb24+CiAgIDxkYzpzdWJqZWN0PgogICAgPHJkZjpCYWc+CiAgICAgPHJkZjpsaT5pY29uPC9yZGY6bGk+CiAgICAgPHJkZjpsaT5waWN0b2dyYW08L3JkZjpsaT4KICAgIDwvcmRmOkJhZz4KICAgPC9kYzpzdWJqZWN0PgogICA8ZGM6dGl0bGU+CiAgICA8cmRmOkFsdD4KICAgICA8cmRmOmxpIHhtbDpsYW5nPSJ4LWRlZmF1bHQiPmdlbnRsZWZhY2UuY29tIGZyZWUgaWNvbiBzZXQ8L3JkZjpsaT4KICAgIDwvcmRmOkFsdD4KICAgPC9kYzp0aXRsZT4KICAgPHhtcFJpZ2h0czpVc2FnZVRlcm1zPgogICAgPHJkZjpBbHQ+CiAgICAgPHJkZjpsaSB4bWw6bGFuZz0ieC1kZWZhdWx0Ij5DcmVhdGl2ZSBDb21tb25zIEF0dHJpYnV0aW9uIE5vbi1Db21tZXJjaWFsIE5vIERlcml2YXRpdmVzPC9yZGY6bGk+CiAgICA8L3JkZjpBbHQ+CiAgIDwveG1wUmlnaHRzOlVzYWdlVGVybXM+CiAgIDx4bXBNTTpIaXN0b3J5PgogICAgPHJkZjpTZXE+CiAgICAgPHJkZjpsaQogICAgICBzdEV2dDphY3Rpb249InNhdmVkIgogICAgICBzdEV2dDppbnN0YW5jZUlEPSJ4bXAuaWlkOjlENjU1MDFFQThGOERFMTE4MjFDRTRCMkM3RTM2RDcwIgogICAgICBzdEV2dDp3aGVuPSIyMDEwLTAxLTAzVDIxOjM5OjQxKzAxOjAwIgogICAgICBzdEV2dDpjaGFuZ2VkPSIvbWV0YWRhdGEiLz4KICAgIDwvcmRmOlNlcT4KICAgPC94bXBNTTpIaXN0b3J5PgogIDwvcmRmOkRlc2NyaXB0aW9uPgogPC9yZGY6UkRGPgo8L3g6eG1wbWV0YT4KPD94cGFja2V0IGVuZD0iciI/PhB5uC8AAAAZdEVYdFNvZnR3YXJlAEFkb2JlIEltYWdlUmVhZHlxyWU8AAAAPHRFWHRBTFRUYWcAVGhpcyBpcyB0aGUgaWNvbiBmcm9tIEdlbnRsZWZhY2UuY29tIGZyZWUgaWNvbnMgc2V0LiDYa+jEAAAAH3RFWHRDb3B5cmlnaHQAUk9ZQUxUWSBGUkVFIExJQ0VOU0Ug3tmLaQAAAEVpVFh0RGVzY3JpcHRpb24AAAAAAFRoaXMgaXMgdGhlIGljb24gZnJvbSBHZW50bGVmYWNlLmNvbSBmcmVlIGljb25zIHNldC4gvBH4GgAAACNpVFh0Q29weXJpZ2h0AAAAAABST1lBTFRZIEZSRUUgTElDRU5TRSAnXQpKAAABLUlEQVR42rRSzW2DMBTGVW5cIiS4coABskE7QjeAEbpBRwgjpBt0g4QNOgBIyRUQNkhISAic7xGDSEpJeoilz5af3/d+Pj9Ne9Zq29YD9l3XSTrpfpfUNI0NbAEOyBlw9W4PHEZbXdcU3WeMvT1aoZTygGO3Upce/11IeKlgWFVV2Qj0QdUA6xmOoKwgBrqun0ZrURRcCPEJjL2VZenBvgcknXQfo8CP/InXV8A5l5PedpTFMIzwNn2e569KK/9KxCzL5gT4QbDANM2vNE09kKi1zS8daEuS5E8FLctiS+8rNTT3hkp7bgCM62KApfcX5eAjy5Ey3WKoYAZH4l0NUhzH9E2k9vtgcxyHwT4V8RsIYA/HFibOZAyjKOonEvBVBQLf2E+g67qnKecswADj+/NYHAH4qAAAAABJRU5ErkJggg==';
	}

	/**
	 * Base64 data-encoded version of the Gentleface Toolbar's "Cube" 16x16 mono icon.
	 *
	 * @link http://www.gentleface.com/free_icon_set.html
	 * @license http://creativecommons.org/licenses/by-nc/3.0/
	 */
	public function icon_16_black() {
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAALVWlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4KPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNC4yLjItYzA2MyA1My4zNTI2MjQsIDIwMDgvMDcvMzAtMTg6MTI6MTggICAgICAgICI+CiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPgogIDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiCiAgICB4bWxuczpwaG90b3Nob3A9Imh0dHA6Ly9ucy5hZG9iZS5jb20vcGhvdG9zaG9wLzEuMC8iCiAgICB4bWxuczpJcHRjNHhtcENvcmU9Imh0dHA6Ly9pcHRjLm9yZy9zdGQvSXB0YzR4bXBDb3JlLzEuMC94bWxucy8iCiAgICB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iCiAgICB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIKICAgIHhtbG5zOnN0RXZ0PSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VFdmVudCMiCiAgICB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iCiAgICB4bWxuczp4bXBSaWdodHM9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9yaWdodHMvIgogICBwaG90b3Nob3A6QXV0aG9yc1Bvc2l0aW9uPSJBcnQgRGlyZWN0b3IiCiAgIHBob3Rvc2hvcDpDcmVkaXQ9Ind3dy5nZW50bGVmYWNlLmNvbSIKICAgcGhvdG9zaG9wOkRhdGVDcmVhdGVkPSIyMDEwLTAxLTAxIgogICBJcHRjNHhtcENvcmU6SW50ZWxsZWN0dWFsR2VucmU9InBpY3RvZ3JhbSIKICAgeG1wOk1ldGFkYXRhRGF0ZT0iMjAxMC0wMS0wM1QyMTozMzoxNCswMTowMCIKICAgeG1wTU06T3JpZ2luYWxEb2N1bWVudElEPSJ4bXAuZGlkOjJDNUZGRjNDODFGN0RFMTE5RUFCOTBENzA3OEFGOTRBIgogICB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjJDNUZGRjNDODFGN0RFMTE5RUFCOTBENzA3OEFGOTRBIgogICB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjNBRTA2QTM3QTdGOERFMTE4MjFDRTRCMkM3RTM2RDcwIj4KICAgPElwdGM0eG1wQ29yZTpDcmVhdG9yQ29udGFjdEluZm8KICAgIElwdGM0eG1wQ29yZTpDaUFkckNpdHk9IlByYWd1ZSIKICAgIElwdGM0eG1wQ29yZTpDaUFkclBjb2RlPSIxNjAwMCIKICAgIElwdGM0eG1wQ29yZTpDaUFkckN0cnk9IkN6ZWNoIFJlcHVibGljIgogICAgSXB0YzR4bXBDb3JlOkNpRW1haWxXb3JrPSJrYUBnZW50bGVmYWNlLmNvbSIKICAgIElwdGM0eG1wQ29yZTpDaVVybFdvcms9Ind3dy5nZW50bGVmYWNlLmNvbSIvPgogICA8eG1wTU06SGlzdG9yeT4KICAgIDxyZGY6U2VxPgogICAgIDxyZGY6bGkKICAgICAgc3RFdnQ6YWN0aW9uPSJzYXZlZCIKICAgICAgc3RFdnQ6aW5zdGFuY2VJRD0ieG1wLmlpZDoyQzVGRkYzQzgxRjdERTExOUVBQjkwRDcwNzhBRjk0QSIKICAgICAgc3RFdnQ6d2hlbj0iMjAxMC0wMS0wMlQxMDoyODo1MSswMTowMCIKICAgICAgc3RFdnQ6Y2hhbmdlZD0iL21ldGFkYXRhIi8+CiAgICAgPHJkZjpsaQogICAgICBzdEV2dDphY3Rpb249InNhdmVkIgogICAgICBzdEV2dDppbnN0YW5jZUlEPSJ4bXAuaWlkOjUxOTczMDAzREJGN0RFMTFBOTAwODNFMEExMjUzQkZEIgogICAgICBzdEV2dDp3aGVuPSIyMDEwLTAxLTAyVDIxOjExOjI5KzAxOjAwIgogICAgICBzdEV2dDpjaGFuZ2VkPSIvbWV0YWRhdGEiLz4KICAgICA8cmRmOmxpCiAgICAgIHN0RXZ0OmFjdGlvbj0ic2F2ZWQiCiAgICAgIHN0RXZ0Omluc3RhbmNlSUQ9InhtcC5paWQ6M0FFMDZBMzdBN0Y4REUxMTgyMUNFNEIyQzdFMzZENzAiCiAgICAgIHN0RXZ0OndoZW49IjIwMTAtMDEtMDNUMjE6MzM6MTQrMDE6MDAiCiAgICAgIHN0RXZ0OmNoYW5nZWQ9Ii9tZXRhZGF0YSIvPgogICAgPC9yZGY6U2VxPgogICA8L3htcE1NOkhpc3Rvcnk+CiAgIDxkYzp0aXRsZT4KICAgIDxyZGY6QWx0PgogICAgIDxyZGY6bGkgeG1sOmxhbmc9IngtZGVmYXVsdCI+Z2VudGxlZmFjZS5jb20gZnJlZSBpY29uIHNldDwvcmRmOmxpPgogICAgPC9yZGY6QWx0PgogICA8L2RjOnRpdGxlPgogICA8ZGM6c3ViamVjdD4KICAgIDxyZGY6QmFnPgogICAgIDxyZGY6bGk+aWNvbjwvcmRmOmxpPgogICAgIDxyZGY6bGk+cGljdG9ncmFtPC9yZGY6bGk+CiAgICA8L3JkZjpCYWc+CiAgIDwvZGM6c3ViamVjdD4KICAgPGRjOmRlc2NyaXB0aW9uPgogICAgPHJkZjpBbHQ+CiAgICAgPHJkZjpsaSB4bWw6bGFuZz0ieC1kZWZhdWx0Ij5UaGlzIGlzIHRoZSBpY29uIGZyb20gR2VudGxlZmFjZS5jb20gZnJlZSBpY29ucyBzZXQuIDwvcmRmOmxpPgogICAgPC9yZGY6QWx0PgogICA8L2RjOmRlc2NyaXB0aW9uPgogICA8ZGM6Y3JlYXRvcj4KICAgIDxyZGY6U2VxPgogICAgIDxyZGY6bGk+QWxleGFuZGVyIEtpc2VsZXY8L3JkZjpsaT4KICAgIDwvcmRmOlNlcT4KICAgPC9kYzpjcmVhdG9yPgogICA8ZGM6cmlnaHRzPgogICAgPHJkZjpBbHQ+CiAgICAgPHJkZjpsaSB4bWw6bGFuZz0ieC1kZWZhdWx0Ij5DcmVhdGl2ZSBDb21tb25zIEF0dHJpYnV0aW9uIE5vbi1Db21tZXJjaWFsIE5vIERlcml2YXRpdmVzPC9yZGY6bGk+CiAgICA8L3JkZjpBbHQ+CiAgIDwvZGM6cmlnaHRzPgogICA8eG1wUmlnaHRzOlVzYWdlVGVybXM+CiAgICA8cmRmOkFsdD4KICAgICA8cmRmOmxpIHhtbDpsYW5nPSJ4LWRlZmF1bHQiPkNyZWF0aXZlIENvbW1vbnMgQXR0cmlidXRpb24gTm9uLUNvbW1lcmNpYWwgTm8gRGVyaXZhdGl2ZXM8L3JkZjpsaT4KICAgIDwvcmRmOkFsdD4KICAgPC94bXBSaWdodHM6VXNhZ2VUZXJtcz4KICA8L3JkZjpEZXNjcmlwdGlvbj4KIDwvcmRmOlJERj4KPC94OnhtcG1ldGE+Cjw/eHBhY2tldCBlbmQ9InIiPz5lAIcAAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAIRJREFUeNpiYKAhiAfi/UD8H0rHE6NJHoj7gfg9VCM6fg+Vl8dnG7EY7ComavubbC+AJOrRJPAFojxU/XuYALIt84HYHocL7aHyyOoxDIDh80i2xkP5GOoYkQzABRjxyVMcC4PHgAQgfkCi3gdQfRjRtB5LNCHz1+OJZqwpkgFfCgQIMACds0rOmEjKqQAAAABJRU5ErkJggg==';
	}
}

// initialize admin area
new CIAB_Admin;

?>