<?php
/**
 * Package: Classic Plugins class
 *
 * Part of the CLassic package.
 *
 * @package    Commons_In_A_Box
 * @subpackage Package
 * @since      1.1.0
 */

/**
 * Plugin manifest for the CBOX Classic package.
 *
 * @since 1.1.0
 */
class CBox_Plugins_Classic {
	/**
	 * Initiator.
	 *
	 * @since 1.1.0
	 *
	 * @param callable $instance {@see CBox_Plugins::register_plugin()} for spec.
	 */
	public static function init( $instance ) {
		self::register_required_plugins( $instance );
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
		call_user_func( $instance, array(
			'plugin_name'       => 'BuddyPress',
			'cbox_name'         => __( 'BuddyPress', 'commons-in-a-box' ),
			'cbox_description'  => __( 'BuddyPress provides the core functionality of Commons In A Box, including groups and user profiles.', 'commons-in-a-box' ),
			'version'           => '6.1.0',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-plugin',
			'admin_settings'    => 'options-general.php?page=bp-components',
			'network_settings'  => 'settings.php?page=bp-components'
		) );

		/**
		 * Register CBOX's dependency plugins internally.
		 *
		 * The reason why this is done is Plugin Dependencies (PD) does not know the download URL for dependent plugins.
		 * So if a dependent plugin is deemed incompatible by PD (either not installed or incompatible version),
		 * we can easily install or upgrade that plugin.
		 *
		 * This is designed to avoid pinging the WP.org Plugin Repo API multiple times to grab the download URL,
		 * and is much more efficient for our usage.
		 *
		 * @see CBox_Plugins::register_plugin()
		 */
		call_user_func( $instance, array(
			'plugin_name'  => 'BuddyPress',
			'type'         => 'dependency',
			'download_url' => 'http://downloads.wordpress.org/plugin/buddypress.6.1.0.zip'
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
		// BuddyPress Docs
		call_user_func( $instance, array(
			'plugin_name'       => 'BuddyPress Docs',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Docs', 'commons-in-a-box' ),
			'cbox_description'  => __( 'Allows your members to collaborate on wiki-style Docs.', 'commons-in-a-box' ),
			'version'           => '2.1.3',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => 'http://downloads.wordpress.org/plugin/buddypress-docs.2.1.3.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-docs',
			'admin_settings'    => 'edit.php?post_type=bp_doc',
			'network_settings'  => 'root-blog-only',
			'network'           => false
		) );

		// BuddyPress Docs Wiki
		call_user_func( $instance, array(
			'plugin_name'       => 'BuddyPress Docs Wiki add-on',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Wiki', 'commons-in-a-box' ),
			'cbox_description'  => __( 'A sitewide wiki, powered by BuddyPress Docs', 'commons-in-a-box' ),
			'version'           => '1.0.10',
			'depends'           => 'BuddyPress (>=1.5), BuddyPress Docs (>=1.2)',
			'download_url'      => 'http://github.com/boonebgorges/buddypress-docs-wiki/archive/1.0.10.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-docs-wiki',
			'network_settings'  => 'root-blog-only',
			'network'           => false
		) );

		// BuddyPress Group Email Subscription
		call_user_func( $instance, array(
			'plugin_name'       => 'BuddyPress Group Email Subscription',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Group Email Subscription', 'commons-in-a-box' ),
			'cbox_description'  => __( 'Allows your community members to receive email notifications of activity within their groups.', 'commons-in-a-box' ),
			'depends'           => 'BuddyPress (>=1.5)',
			'version'           => '3.9.4',
			'download_url'      => 'http://downloads.wordpress.org/plugin/buddypress-group-email-subscription.3.9.4.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-group-email-subscription',
			'admin_settings'    => 'options-general.php?page=ass_admin_options', // this doesn't work for BP_ENABLE_MULTIBLOG
			'network_settings'  => 'settings.php?page=ass_admin_options'
		) );

		// Invite Anyone
		call_user_func( $instance, array(
			'plugin_name'       => 'Invite Anyone',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Invite Anyone', 'commons-in-a-box' ),
			'cbox_description'  => __( 'An enhanced interface for inviting existing community members to groups, as well as a powerful tool for sending invitations, via email, to potential members.', 'commons-in-a-box' ),
			'version'           => '1.4.0',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => 'http://downloads.wordpress.org/plugin/invite-anyone.1.4.0.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/invite-anyone',
			'admin_settings'    => 'admin.php?page=invite-anyone',
			'network_settings'  => 'admin.php?page=invite-anyone',
			'network'           => false
		) );

		// Custom Profile Filters for BuddyPress
		call_user_func( $instance, array(
			'plugin_name'       => 'Custom Profile Filters for BuddyPress',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Custom Profile Filters', 'commons-in-a-box' ),
			'cbox_description'  => __( 'Let your members specify what profile content they\'d like to become search links, by wrapping the content in brackets.', 'commons-in-a-box' ),
			'depends'           => 'BuddyPress (>=1.2)',
			'version'           => '0.3.1',
			'download_url'      => 'http://downloads.wordpress.org/plugin/custom-profile-filters-for-buddypress.0.3.1.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/custom-profile-filters-for-buddypress',
			'network'           => false
		) );

		// bbPress
		call_user_func( $instance, array(
			'plugin_name'       => 'bbPress',
			'type'              => 'recommended',
			'cbox_name'         => __( 'bbPress Forums', 'commons-in-a-box' ),
			'cbox_description'  => __( 'Sitewide and group-specific discussion forums.', 'commons-in-a-box' ),
			'version'           => '2.6.4',
			'download_url'      => 'http://downloads.wordpress.org/plugin/bbpress.2.6.4.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/bbpress',
			'admin_settings'    => 'options-general.php?page=bbpress',
			'network_settings'  => 'root-blog-only',
			'network'           => false,
			'hide'              => get_current_blog_id() === cbox_get_main_site_id()
		) );

		// CAC Featured Content
		call_user_func( $instance, array(
			'plugin_name'       => 'CAC Featured Content',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Featured Content Widget', 'commons-in-a-box' ),
			'cbox_description'  => __( 'Provides a widget that allows you to select among five different content types to feature in a widget area.', 'commons-in-a-box' ),
			'version'           => '1.0.9',
			'download_url'      => 'http://downloads.wordpress.org/plugin/cac-featured-content.1.0.9.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/cac-featured-content',
		) );

		// BuddyPress Group Email Subscription
		call_user_func( $instance, array(
			'plugin_name'       => 'BP Group Announcements',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Group Announcements', 'commons-in-a-box' ),
			'cbox_description'  => __( 'Repurposes group activity updates, using an Announcements tab to groups.', 'commons-in-a-box' ),
			'depends'           => 'BuddyPress (>=1.5)',
			'version'           => '1.0.6',
			'download_url'      => 'http://github.com/cuny-academic-commons/bp-group-announcements/archive/1.0.6.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/bp-group-announcements',
			'network'           => false
		) );

		// Only show the following plugins if multisite is enabled.
		if ( is_multisite() ) :
			// More Privacy Options
			call_user_func( $instance, array(
				'plugin_name'       => 'More Privacy Options',
				'type'              => 'recommended',
				'cbox_name'         => __( 'More Privacy Options', 'commons-in-a-box' ),
				'cbox_description'  => __( 'Adds more blog privacy options for your users.', 'commons-in-a-box' ),
				'version'           => '4.6',
				'download_url'      => 'http://downloads.wordpress.org/plugin/more-privacy-options.zip',
				'documentation_url' => 'http://commonsinabox.org/documentation/plugins/more-privacy-options',
				'network_settings'  => 'settings.php#menu'
			) );

			// BP MPO Activity Filter
			call_user_func( $instance, array(
				'plugin_name'       => 'BP MPO Activity Filter',
				'type'              => 'recommended',
				'cbox_name'         => __( 'Activity Privacy', 'commons-in-a-box' ),
				'cbox_description'  => __( 'Works with More Privacy Options to keep private blog content out of public activity feeds.', 'commons-in-a-box' ),
				'version'           => '1.3.1',
				'download_url'      => 'http://downloads.wordpress.org/plugin/bp-mpo-activity-filter.1.3.1.zip',
				'documentation_url' => 'http://commonsinabox.org/documentation/plugins/bp-mpo-activity-filter',
			) );

			// BuddyPress GroupBlog
			call_user_func( $instance, array(
				'plugin_name'       => 'BP Groupblog',
				'type'              => 'recommended',
				'cbox_name'         => __( 'Group Blogs', 'commons-in-a-box' ),
				'cbox_description'  => 'Enables a BuddyPress group to be associated with a blog, by placing a Blog link in the group navigation and, optionally, syncing group membership with blog roles.',
				'depends'           => 'BuddyPress (>=1.6)',
				'version'           => '1.9.1',
				'download_url'      => 'http://downloads.wordpress.org/plugin/bp-groupblog.1.9.1.zip',
				'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-groupblog',
				'network_settings'  => 'settings.php?page=bp_groupblog_management_page'
			) );

		endif;
	}

	/**
	 * Register optional plugins.
	 *
	 * @since 1.1.0
	 *
	 * @param callable $instance {@see CBox_Plugins::register_plugin()}.
	 */
	protected static function register_optional_plugins( $instance ) {
		// BuddyPress External Group Blogs
		call_user_func( $instance, array(
			'plugin_name'       => 'External Group Blogs',
			'type'              => 'optional',
			'cbox_name'         => __( 'External RSS Feeds for Groups', 'commons-in-a-box' ),
			'cbox_description'  => __( 'Gives group creators and administrators the ability to attach external RSS feeds to groups.', 'commons-in-a-box' ),
			'depends'           => 'BuddyPress (>=1.5)',
			'version'           => '1.6.1',
			'download_url'      => 'http://github.com/cuny-academic-commons/external-group-blogs/archive/1.6.1.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-external-group-rss',
			'network'           => false
		) );

		// BuddyPress Reply By Email
		// @todo Still need to add it in the wp.org plugin repo! Using Github for now.
		call_user_func( $instance, array(
			'plugin_name'       => 'BuddyPress Reply By Email',
			'type'              => 'optional',
			'cbox_name'         => __( 'Reply By Email', 'commons-in-a-box' ),
			'cbox_description'  => __( "Reply to content from all over the community from the comfort of your email inbox", 'commons-in-a-box' ),
			'version'           => '1.0-RC8',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => 'https://github.com/r-a-y/bp-reply-by-email/archive/1.0-RC8.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-reply-by-email',
			'admin_settings'    => is_multisite() ? 'options-general.php?page=bp-rbe' : 'admin.php?page=bp-rbe',
			'network_settings'  => 'root-blog-only'
		) );
	}
}
