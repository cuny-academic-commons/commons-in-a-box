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
		$instance( array(
			'plugin_name'       => 'BuddyPress',
			'cbox_name'         => __( 'BuddyPress', 'cbox' ),
			'cbox_description'  => __( 'BuddyPress provides the core functionality of Commons In A Box, including groups and user profiles.', 'cbox' ),
			'version'           => '3.2.0',
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
		$instance( array(
			'plugin_name'  => 'BuddyPress',
			'type'         => 'dependency',
			'download_url' => 'http://downloads.wordpress.org/plugin/buddypress.3.2.0.zip'
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
		$instance( array(
			'plugin_name'       => 'BuddyPress Docs',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Docs', 'cbox' ),
			'cbox_description'  => __( 'Allows your members to collaborate on wiki-style Docs.', 'cbox' ),
			'version'           => '2.1.1',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => 'http://downloads.wordpress.org/plugin/buddypress-docs.2.1.1.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-docs',
			'admin_settings'    => 'edit.php?post_type=bp_doc',
			'network_settings'  => 'root-blog-only',
			'network'           => false
		) );

		// BuddyPress Docs Wiki
		$instance( array(
			'plugin_name'       => 'BuddyPress Docs Wiki add-on',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Wiki', 'cbox' ),
			'cbox_description'  => __( 'A sitewide wiki, powered by BuddyPress Docs', 'cbox' ),
			'version'           => '1.0.10',
			'depends'           => 'BuddyPress (>=1.5), BuddyPress Docs (>=1.2)',
			'download_url'      => 'http://github.com/boonebgorges/buddypress-docs-wiki/archive/1.0.10.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-docs-wiki',
			'network_settings'  => 'root-blog-only',
			'network'           => false
		) );

		// BuddyPress Group Email Subscription
		$instance( array(
			'plugin_name'       => 'BuddyPress Group Email Subscription',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Group Email Subscription', 'cbox' ),
			'cbox_description'  => __( 'Allows your community members to receive email notifications of activity within their groups.', 'cbox' ),
			'depends'           => 'BuddyPress (>=1.5)',
			'version'           => '3.8.2',
			'download_url'      => 'http://downloads.wordpress.org/plugin/buddypress-group-email-subscription.3.8.2.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-group-email-subscription',
			'admin_settings'    => 'options-general.php?page=ass_admin_options', // this doesn't work for BP_ENABLE_MULTIBLOG
			'network_settings'  => 'settings.php?page=ass_admin_options'
		) );

		// Invite Anyone
		$instance( array(
			'plugin_name'       => 'Invite Anyone',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Invite Anyone', 'cbox' ),
			'cbox_description'  => __( 'An enhanced interface for inviting existing community members to groups, as well as a powerful tool for sending invitations, via email, to potential members.', 'cbox' ),
			'version'           => '1.3.20',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => 'http://downloads.wordpress.org/plugin/invite-anyone.1.3.20.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/invite-anyone',
			'admin_settings'    => 'admin.php?page=invite-anyone',
			'network_settings'  => 'admin.php?page=invite-anyone',
			'network'           => false
		) );

		// Custom Profile Filters for BuddyPress
		$instance( array(
			'plugin_name'       => 'Custom Profile Filters for BuddyPress',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Custom Profile Filters', 'cbox' ),
			'cbox_description'  => __( 'Let your members specify what profile content they\'d like to become search links, by wrapping the content in brackets.', 'cbox' ),
			'depends'           => 'BuddyPress (>=1.2)',
			'version'           => '0.3.1',
			'download_url'      => 'http://downloads.wordpress.org/plugin/custom-profile-filters-for-buddypress.0.3.1.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/custom-profile-filters-for-buddypress',
			'network'           => false
		) );

		// bbPress
		$instance( array(
			'plugin_name'       => 'bbPress',
			'type'              => 'recommended',
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

		// CAC Featured Content
		$instance( array(
			'plugin_name'       => 'CAC Featured Content',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Featured Content Widget', 'cbox' ),
			'cbox_description'  => __( 'Provides a widget that allows you to select among five different content types to feature in a widget area.', 'cbox' ),
			'version'           => '1.0.9',
			'download_url'      => 'http://downloads.wordpress.org/plugin/cac-featured-content.1.0.9.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/cac-featured-content',
		) );

		// BuddyPress Group Email Subscription
		$instance( array(
			'plugin_name'       => 'BP Group Announcements',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Group Announcements', 'cbox' ),
			'cbox_description'  => __( 'Repurposes group activity updates, using an Announcements tab to groups.', 'cbox' ),
			'depends'           => 'BuddyPress (>=1.5)',
			'version'           => '1.0.5',
			'download_url'      => 'http://github.com/cuny-academic-commons/bp-group-announcements/archive/1.0.5.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/bp-group-announcements',
			'network'           => false
		) );

		// Only show the following plugins if multisite is enabled.
		if ( is_multisite() ) :
			// More Privacy Options
			$instance( array(
				'plugin_name'       => 'More Privacy Options',
				'type'              => 'recommended',
				'cbox_name'         => __( 'More Privacy Options', 'cbox' ),
				'cbox_description'  => __( 'Adds more blog privacy options for your users.', 'cbox' ),
				'version'           => '4.6',
				'download_url'      => 'http://downloads.wordpress.org/plugin/more-privacy-options.zip',
				'documentation_url' => 'http://commonsinabox.org/documentation/plugins/more-privacy-options',
				'network_settings'  => 'settings.php#menu'
			) );

			// BP MPO Activity Filter
			$instance( array(
				'plugin_name'       => 'BP MPO Activity Filter',
				'type'              => 'recommended',
				'cbox_name'         => __( 'Activity Privacy', 'cbox' ),
				'cbox_description'  => __( 'Works with More Privacy Options to keep private blog content out of public activity feeds.', 'cbox' ),
				'version'           => '1.2.2',
				'download_url'      => 'http://downloads.wordpress.org/plugin/bp-mpo-activity-filter.1.2.2.zip',
				'documentation_url' => 'http://commonsinabox.org/documentation/plugins/bp-mpo-activity-filter',
			) );

			// BuddyPress GroupBlog
			$instance( array(
				'plugin_name'       => 'BP Groupblog',
				'type'              => 'recommended',
				'cbox_name'         => __( 'Group Blogs', 'cbox' ),
				'cbox_description'  => 'Enables a BuddyPress group to be associated with a blog, by placing a Blog link in the group navigation and, optionally, syncing group membership with blog roles.',
				'depends'           => 'BuddyPress (>=1.6)',
				'version'           => '1.9.0',
				'download_url'      => 'http://downloads.wordpress.org/plugin/bp-groupblog.1.9.0.zip',
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
		$instance( array(
			'plugin_name'       => 'External Group Blogs',
			'type'              => 'optional',
			'cbox_name'         => __( 'External RSS Feeds for Groups', 'cbox' ),
			'cbox_description'  => __( 'Gives group creators and administrators the ability to attach external RSS feeds to groups.', 'cbox' ),
			'depends'           => 'BuddyPress (>=1.5)',
			'version'           => '1.6.1',
			'download_url'      => 'http://github.com/cuny-academic-commons/external-group-blogs/archive/1.6.1.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-external-group-rss',
			'network'           => false
		) );

		// BuddyPress Reply By Email
		// @todo Still need to add it in the wp.org plugin repo! Using Github for now.
		$instance( array(
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
	}
}
