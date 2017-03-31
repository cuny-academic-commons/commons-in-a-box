<?php
/**
 * Package: OpenLab Plugins class
 *
 * Part of the OpenLab package.
 *
 * @package    Commons_In_A_Box
 * @subpackage Package
 * @since      1.1.0
 */

/**
 * Plugin manifest for the CBOX OpenLab package.
 *
 * @since 1.1.0
 */
class CBox_Plugins_OpenLab {
	/**
	 * Initiator.
	 *
	 * @since 1.1.0
	 *
	 * @param callable $instance {@see CBox_Plugins::register_plugin()} for spec.
	 */
	public static function init( $instance ) {
		self::register_required_plugins( $instance );
		self::register_dependency_plugins( $instance );
		self::register_recommended_plugins( $instance );
		self::register_optional_plugins( $instance );
	}

	/**
	 * Register required plugins.
	 *
	 * @since 1.1.0
	 *
	 * @param callable $instance {@see CBox_Plugins::register_plugin()}.
	 */
	protected static function register_required_plugins( $instance ) {
		// BuddyPress
		$instance( array(
			'plugin_name'       => 'BuddyPress',
			'cbox_name'         => __( 'BuddyPress', 'cbox' ),
			'cbox_description'  => __( 'BuddyPress provides the core functionality of Commons In A Box, including groups and user profiles.', 'cbox' ),
			'version'           => '2.8.2',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-plugin',
			'admin_settings'    => 'options-general.php?page=bp-components',
			'network_settings'  => 'settings.php?page=bp-components'
		) );

		// bbPress
		$instance( array(
			'plugin_name'       => 'bbPress',
			'cbox_name'         => __( 'bbPress Forums', 'cbox' ),
			'cbox_description'  => __( 'Sitewide and group-specific discussion forums.', 'cbox' ),
			'version'           => '2.5.12',
			'download_url'      => 'http://downloads.wordpress.org/plugin/bbpress.2.5.12.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/bbpress',
			'admin_settings'    => 'options-general.php?page=bbpress',
			'network_settings'  => 'root-blog-only',
			'network'           => false
		) );

		// BuddyPress Docs
		$instance( array(
			'plugin_name'       => 'BuddyPress Docs',
			'cbox_name'         => __( 'Docs', 'cbox' ),
			'cbox_description'  => __( 'Allows your members to collaborate on wiki-style Docs.', 'cbox' ),
			'version'           => '1.9.3',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => 'http://downloads.wordpress.org/plugin/buddypress-docs.1.9.3.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-docs',
			'admin_settings'    => 'edit.php?post_type=bp_doc',
			'network_settings'  => 'root-blog-only',
			'network'           => false
		) );

		// BP Group Documents
		$instance( array(
			'plugin_name'       => 'BP Group Documents',
			'cbox_name'         => __( 'Group Documents', 'cbox' ),
			'cbox_description'  => __( 'Allow your members to attach documents to groups.', 'cbox' ),
			'version'           => '1.10',
			'depends'           => 'BuddyPress (>=2.7)',
			'download_url'      => 'http://downloads.wordpress.org/plugin/bp-group-documents.1.10.zip',
			'documentation_url' => '', // @todo
			'admin_settings'    => '', // @todo
			'network_settings'  => '', // @todo
			'network'          => false
		) );

		// BuddyPress Group Email Subscription
		$instance( array(
			'plugin_name'       => 'BuddyPress Group Email Subscription',
			'cbox_name'         => __( 'Group Email Subscription', 'cbox' ),
			'cbox_description'  => __( 'Allows your community members to receive email notifications of activity within their groups.', 'cbox' ),
			'depends'           => 'BuddyPress (>=1.5)',
			'version'           => '3.7.0',
			'download_url'      => 'http://downloads.wordpress.org/plugin/buddypress-group-email-subscription.3.7.0.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-group-email-subscription',
			'admin_settings'    => 'admin.php?page=ass_admin_options', // this doesn't work for BP_ENABLE_MULTIBLOG
			'network_settings'  => 'root-blog-only'
		) );

		/*
		// This is custom-developed and is only included in the main openlab repo
		// We'll break it out into its own.
		// BP Customizable Group Categories
		$instance( array(
			'plugin_name'       => 'BP Customizable Group Categories',
			'cbox_name'         => __( 'BP Customizable Group Categories', 'cbox' ),
			'cbox_description'  => __( 'Categories for BuddyPress Groups', 'cbox' ),
			'version'           => '1.0.0',
			'download_url'      => '', // @todo
			'documentation_url' => '', // @todo
			'network'           => false
		) );
		*/

		// Invite Anyone
		$instance( array(
			'plugin_name'       => 'Invite Anyone',
			'cbox_name'         => __( 'Invite Anyone', 'cbox' ),
			'cbox_description'  => __( 'An enhanced interface for inviting existing community members to groups, as well as a powerful tool for sending invitations, via email, to potential members.', 'cbox' ),
			'version'           => '1.3.16',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => 'http://downloads.wordpress.org/plugin/invite-anyone.1.3.16.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/invite-anyone',
			'admin_settings'    => 'admin.php?page=invite-anyone',
			'network_settings'  => 'admin.php?page=invite-anyone',
			'network'           => false
		) );

		// CAC Featured Content
		$instance( array(
			'plugin_name'       => 'CAC Featured Content',
			'cbox_name'         => __( 'Featured Content Widget', 'cbox' ),
			'cbox_description'  => __( 'Provides a widget that allows you to select among five different content types to feature in a widget area.', 'cbox' ),
			'version'           => '1.0.7',
			'download_url'      => 'http://downloads.wordpress.org/plugin/cac-featured-content.1.0.7.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/cac-featured-content',
		) );

		// Post Gallery Widget
		$instance( array(
			'plugin_name'       => 'Rotating Post Gallery',
			'cbox_name'         => __( 'Post Gallery Widget', 'cbox' ),
			'cbox_description'  => __( 'Adds a rotating slider to your frontpage.', 'cbox' ),
			'version'           => '0.3.1.1',
			'download_url'      => 'http://downloads.wordpress.org/plugin/post-gallery-widget.0.3.1.1.zip',
			'network'           => false
		) );

		// Multisite-only.
		if ( is_network_admin() ) {
			// More Privacy Options
			$instance( array(
				'plugin_name'       => 'More Privacy Options',
				'cbox_name'         => __( 'More Privacy Options', 'cbox' ),
				'cbox_description'  => __( 'Adds more blog privacy options for your users.', 'cbox' ),
				'version'           => '4.6',
				'download_url'      => 'http://downloads.wordpress.org/plugin/more-privacy-options.zip',
				'documentation_url' => 'http://commonsinabox.org/documentation/plugins/more-privacy-options',
				'network_settings'  => 'settings.php#menu'
			) );
		}
	}

	/**
	 * Register dependency plugins.
	 *
	 * The reason why this is done is Plugin Dependencies (PD) does not know the
	 * download URL for dependent plugins. So if a dependent plugin is deemed
	 * incompatible by PD (either not installed or incompatible version), we can
	 * easily install or upgrade that plugin.
	 *
	 * This is designed to avoid pinging the WP.org Plugin Repo API multiple times
	 * to grab the download URL, and is much more efficient for our usage.
	 *
	 * @since 1.1.0
	 *
	 * @param callable $instance {@see CBox_Plugins::register_plugin()}.
	 */
	protected static function register_dependency_plugins( $instance ) {
		// BuddyPress
		$instance( array(
			'plugin_name'  => 'BuddyPress',
			'type'         => 'dependency',
			'download_url' => 'http://downloads.wordpress.org/plugin/buddypress.2.8.2.zip'
		) );

		// Event Organiser
		$instance( array(
			'plugin_name'  => 'Event Organiser',
			'type'         => 'dependency',
			'download_url' => 'http://downloads.wordpress.org/plugin/event-organiser.3.1.7.zip',
			'network'      => false
		) );
	}

	/**
	 * Register recommended plugins.
	 *
	 * @since 1.1.0
	 *
	 * @param callable $instance {@see CBox_Plugins::register_plugin()}.
	 */
	protected static function register_recommended_plugins( $instance ) {
		// BP Event Organiser
		$instance( array(
			'plugin_name'       => 'BuddyPress Event Organiser',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Events', 'cbox' ),
			'cbox_description'  => __( 'Allows your members to create a calendar for themselves and to attach specific events to groups.', 'cbox' ),
			'version'           => '0.2',
			'depends'           => 'BuddyPress (>=1.5), Event Organiser (>=3.1)',
			'download_url'      => 'https://github.com/cuny-academic-commons/bp-event-organiser/archive/1.1.x.zip',
			'network'           => false
		) );
	}

	/**
	 * Register optional plugins.
	 *
	 * @since 1.1.0
	 *
	 * @param callable $instance {@see CBox_Plugins::register_plugin()}.
	 */
	protected static function register_optional_plugins( $instance ) {
		// BuddyPress Reply By Email
		// @todo Still need to add it in the wp.org plugin repo! Using Github for now.
		$instance( array(
			'plugin_name'       => 'BuddyPress Reply By Email',
			'type'              => 'optional',
			'cbox_name'         => __( 'Reply By Email', 'cbox' ),
			'cbox_description'  => __( "Reply to content from all over the community from the comfort of your email inbox", 'cbox' ),
			'version'           => '1.0-RC4',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => 'https://github.com/r-a-y/bp-reply-by-email/archive/1.0-RC4.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-reply-by-email',
			'admin_settings'    => is_multisite() ? 'options-general.php?page=bp-rbe' : 'admin.php?page=bp-rbe',
			'network_settings'  => 'root-blog-only'
		) );

		// DiRT Directory Client
		$instance( array(
			'plugin_name'       => 'DiRT Directory Client',
			'type'              => 'optional',
			'cbox_name'         => __( 'DiRT Directory Client', 'cbox' ),
			'version'           => '1.1.0',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => 'http://downloads.wordpress.org/plugin/dirt-directory-client.1.1.0.zip',
			'network'           => false
		) );

		/** @TODO THESE SHOULD BE INSTALLED ONLY
		$instance( array(
			'plugin_name'       => 'Anthologize',
			'type'              => 'site_tools',
			'version'           => '0.7.7',
		) );

		$instance( array(
			'plugin_name'       => 'Braille',
			'type'              => 'site_tools',
			'version'           => '3.7.18',
		) );

		$instance( array(
			'plugin_name'       => 'PressForward',
			'type'              => 'site_tools',
			'version'           => '4.2.1',
		) );

		$instance( array(
			'plugin_name'       => 'WP Grade Comments',
			'type'              => 'site_tools',
			'version'           => '1.1.1',
		) );
		*/
	}
}