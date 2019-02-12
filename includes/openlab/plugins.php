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
		self::register_installonly_plugins( $instance );
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
		call_user_func( $instance, array(
			'plugin_name'       => 'BuddyPress',
			'cbox_name'         => __( 'BuddyPress', 'cbox' ),
			'cbox_description'  => __( 'BuddyPress provides the core functionality of Commons In A Box, including groups and user profiles.', 'cbox' ),
			'version'           => '4.1.0',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-plugin',
			'admin_settings'    => 'options-general.php?page=bp-components',
			'network_settings'  => 'settings.php?page=bp-components'
		) );

		// CBOX-OpenLab Core
		call_user_func( $instance, array(
			'plugin_name'       => 'CBOX-OpenLab Core',
			'cbox_name'         => __( 'OpenLab Core', 'cbox' ),
			'cbox_description'  => __( 'Core functionality for CBOX-OpenLab.', 'cbox' ),
			'version'           => '1.1.1-beta',
			'download_url'      => 'https://github.com/cuny-academic-commons/cbox-openlab-core/archive/1.1.1-beta.zip',
			//'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-plugin',
		) );

		// bbPress
		call_user_func( $instance, array(
			'plugin_name'       => 'bbPress',
			'cbox_name'         => __( 'bbPress Forums', 'cbox' ),
			'cbox_description'  => __( 'Sitewide and group-specific discussion forums.', 'cbox' ),
			'version'           => '2.5.14',
			'download_url'      => 'http://downloads.wordpress.org/plugin/bbpress.2.5.14.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/bbpress',
			'admin_settings'    => 'options-general.php?page=bbpress',
			'network_settings'  => 'root-blog-only',
			'network'           => false,
			'hide'              => get_current_blog_id() === cbox_get_main_site_id()
		) );

		// BuddyPress Docs
		call_user_func( $instance, array(
			'plugin_name'       => 'BuddyPress Docs',
			'cbox_name'         => __( 'Docs', 'cbox' ),
			'cbox_description'  => __( 'Allows your members to collaborate on wiki-style Docs.', 'cbox' ),
			'version'           => '2.1.2',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => 'http://downloads.wordpress.org/plugin/buddypress-docs.2.1.2.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-docs',
			'admin_settings'    => 'edit.php?post_type=bp_doc',
			'network_settings'  => 'root-blog-only',
			'network'           => false
		) );

		// BuddyPress Docs In Group
		call_user_func( $instance, array(
			'plugin_name'       => 'BuddyPress Docs In Group',
			'cbox_name'         => __( 'Docs in Group', 'cbox' ),
			'cbox_description'  => __( 'Put BuddyPress Docs into the Group context.', 'cbox' ),
			'version'           => '1.0.1',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => 'https://github.com/boonebgorges/buddypress-docs-in-group/archive/1.0.1.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-docs',
			'network_settings'  => 'root-blog-only',
			'network'           => false,
		) );

		// BP Group Documents
		call_user_func( $instance, array(
			'plugin_name'       => 'BP Group Documents',
			'cbox_name'         => __( 'Group Documents', 'cbox' ),
			'cbox_description'  => __( 'Allow your members to attach documents to groups.', 'cbox' ),
			'version'           => '1.12.2',
			'depends'           => 'BuddyPress (>=2.7)',
			'download_url'      => 'http://downloads.wordpress.org/plugin/bp-group-documents.1.12.2.zip',
			'documentation_url' => '', // @todo
			'network_settings'  => 'settings.php?page=bp-group-documents-settings',
			'network'          => false
		) );

		// BuddyPress Group Email Subscription
		call_user_func( $instance, array(
			'plugin_name'       => 'BuddyPress Group Email Subscription',
			'cbox_name'         => __( 'Group Email Subscription', 'cbox' ),
			'cbox_description'  => __( 'Allows your community members to receive email notifications of activity within their groups.', 'cbox' ),
			'depends'           => 'BuddyPress (>=1.5)',
			'version'           => '3.8.2',
			'download_url'      => 'http://downloads.wordpress.org/plugin/buddypress-group-email-subscription.3.8.2.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-group-email-subscription',
			'admin_settings'    => 'admin.php?page=ass_admin_options', // this doesn't work for BP_ENABLE_MULTIBLOG
			'network_settings'  => 'root-blog-only'
		) );

		/*
		// This is custom-developed and is only included in the main openlab repo
		// We'll break it out into its own.
		// BP Customizable Group Categories
		call_user_func( $instance, array(
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
		call_user_func( $instance, array(
			'plugin_name'       => 'Invite Anyone',
			'cbox_name'         => __( 'Invite Anyone', 'cbox' ),
			'cbox_description'  => __( 'An enhanced interface for inviting existing community members to groups, as well as a powerful tool for sending invitations, via email, to potential members.', 'cbox' ),
			'version'           => '1.4.0',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => 'http://downloads.wordpress.org/plugin/invite-anyone.1.4.0.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/invite-anyone',
			'admin_settings'    => 'admin.php?page=invite-anyone',
			'network_settings'  => 'admin.php?page=invite-anyone',
			'network'           => false
		) );

		// CAC Featured Content
		call_user_func( $instance, array(
			'plugin_name'       => 'CAC Featured Content',
			'cbox_name'         => __( 'Featured Content Widget', 'cbox' ),
			'cbox_description'  => __( 'Provides a widget that allows you to select among five different content types to feature in a widget area.', 'cbox' ),
			'version'           => '1.0.9',
			'download_url'      => 'http://downloads.wordpress.org/plugin/cac-featured-content.1.0.9.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/cac-featured-content',
		) );

		// More Privacy Options
		call_user_func( $instance, array(
			'plugin_name'       => 'More Privacy Options',
			'cbox_name'         => __( 'More Privacy Options', 'cbox' ),
			'cbox_description'  => __( 'Adds more blog privacy options for your users.', 'cbox' ),
			'version'           => '4.6',
			'download_url'      => 'http://downloads.wordpress.org/plugin/more-privacy-options.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/more-privacy-options',
			'network_settings'  => 'settings.php#menu'
		) );
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
		call_user_func( $instance, array(
			'plugin_name'  => 'BuddyPress',
			'type'         => 'dependency',
			'download_url' => 'http://downloads.wordpress.org/plugin/buddypress.4.1.0.zip'
		) );

		// Event Organiser
		call_user_func( $instance, array(
			'plugin_name'  => 'Event Organiser',
			'type'         => 'dependency',
			'version'      => '3.7.4',
			'download_url' => 'http://downloads.wordpress.org/plugin/event-organiser.3.7.4.zip',
			'network'      => false,
			'hide'         => get_current_blog_id() === cbox_get_main_site_id()
		) );

		// Braille
		call_user_func( $instance, array(
			'plugin_name'  => 'Braille',
			'type'         => 'dependency',
			'version'      => '0.0.6',
			'download_url' => 'http://downloads.wordpress.org/plugin/braille.0.0.6.zip',
			'network'      => false,
			'hide'         => get_current_blog_id() === cbox_get_main_site_id()
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
			'version'           => '1.1.0',
			'depends'           => 'BuddyPress (>=1.5), Event Organiser (>=3.1)',
			'download_url'      => 'https://github.com/cuny-academic-commons/bp-event-organiser/archive/1.1.0.zip',
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
		call_user_func( $instance, array(
			'plugin_name'       => 'BuddyPress Reply By Email',
			'type'              => 'optional',
			'cbox_name'         => __( 'Reply By Email', 'cbox' ),
			'cbox_description'  => __( "Reply to content from all over the community from the comfort of your email inbox", 'cbox' ),
			'version'           => '1.0-RC7',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => 'https://github.com/r-a-y/bp-reply-by-email/archive/1.0-RC7.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-reply-by-email',
			'admin_settings'    => is_multisite() ? 'options-general.php?page=bp-rbe' : 'admin.php?page=bp-rbe',
			'network_settings'  => 'root-blog-only'
		) );

		// BP Braille
		call_user_func( $instance, array(
			'plugin_name'       => 'BP Braille',
			'type'              => 'optional',
			'cbox_name'         => __( 'Braille Support', 'cbox' ),
			'cbox_description'  => __( 'An addon for the Braille plugin providing support for BuddyPress Group Forums and Private Messaging', 'cbox' ),
			'version'           => '0.2.0',
			'depends'           => 'Braille (>=0.0.3)',
			'download_url'      => 'http://github.com/hard-g/bp-braille/archive/master.zip',
			'documentation_url' => 'https://wordpress.org/plugins/braille',
			'network'           => false
		) );
	}

	/**
	 * Register install-only plugins.
	 *
	 * @since 1.1.0
	 *
	 * @param callable $instance {@see CBox_Plugins::register_plugin()}.
	 */
	protected static function register_installonly_plugins( $instance ) {
		call_user_func( $instance, array(
			'plugin_name'       => 'Anthologize',
			'type'              => 'install-only',
			'cbox_name'         => __( 'Anthologize', 'cbox' ),
			'cbox_description'  => __( 'Create ebooks from your blog posts or other external content.', 'cbox' ),
			'version'           => '0.8.0',
			'download_url'      => 'http://downloads.wordpress.org/plugin/anthologize.0.8.0.zip',
			'documentation_url' => 'https://wordpress.org/plugins/anthologize',
		) );

		call_user_func( $instance, array(
			'plugin_name'       => 'Braille',
			'type'              => 'install-only',
			'cbox_name'         => __( 'Braille', 'cbox' ),
			'cbox_description'  => __( 'Provides a number of Braille-related services to WordPress.', 'cbox' ),
			'documentation_url' => 'https://wordpress.org/plugins/braille',
		) );

		call_user_func( $instance, array(
			'plugin_name'       => 'PressForward',
			'type'              => 'install-only',
			'cbox_name'         => __( 'PressForward', 'cbox' ),
			'cbox_description'  => __( 'A plugin providing an editorial workflow for content aggregation and curation within the WordPress dashboard. Designed for bloggers and editorial teams wishing to collect, discuss, and share content from a variety of sources on the open web.', 'cbox' ),
			'version'           => '5.1.1',
			'download_url'      => 'http://downloads.wordpress.org/plugin/pressforward.5.1.1.zip',
			'documentation_url' => 'https://wordpress.org/plugins/pressforward',
		) );

		call_user_func( $instance, array(
			'plugin_name'       => 'WP Grade Comments',
			'type'              => 'install-only',
			'cbox_name'         => __( 'WP Grade Comments', 'cbox' ),
			'cbox_description'  => __( 'A plugin for instructors using their WordPress site in a course setting. Provides ability to give private feedback and/or grades to post authors, all without leaving the familiar commenting interface.', 'cbox' ),
			'version'           => '1.3.0',
			'download_url'      => 'http://downloads.wordpress.org/plugin/wp-grade-comments.1.3.0.zip',
			'documentation_url' => 'https://wordpress.org/plugins/wp-grade-comments',
		) );
	}
}
