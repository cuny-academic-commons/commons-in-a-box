<?php
/**
 * Custom Profile Filters for BuddyPress Mods
 *
 * The following are modifications that CBOX does to the CPF plugin.
 *
 * @since 1.0.5
 *
 * @package Commons_In_A_Box
 * @subpackage Frontend
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// setup globals for bbPress
cbox()->plugins->cpf = new stdClass;
cbox()->plugins->cpf->is_setup = function_exists( 'custom_profile_filters_for_buddypress_init' );

/**
 * Hotfixes and workarounds for CPF.
 *
 * This class is autoloaded.
 *
 * @since 1.0.5
 */
class CBox_CPF_Rehook_Social_Fields {
	/**
	 * Init method.
	 */
	public static function init() {
		new self();
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'bp_init', array( $this, 'rehook' ), 20 );
	}

	/**
	 * Remove the default social networking link filter, and add our own
	 *
	 * This is managed in a callback so that the removal and re-adding can
	 * happen after bp_init
	 *
	 * @since 1.0.5
	 */
	public function rehook() {
		remove_filter( 'bp_get_the_profile_field_value', 'cpfb_add_social_networking_links', 1 );
		add_filter( 'bp_get_the_profile_field_value', array( $this, 'add_social_networking_links' ), 11 );
	}

	/**
	 * Replaces the default cpbf_add_social_networking_links
	 *
	 * Our replacement version strips BP's autolink tags before generating
	 * the link markup
	 *
	 * @since 1.0.5
	 */
	public function add_social_networking_links( $field_value ) {
		global $bp, $social_networking_fields;

		$bp_this_field_name = bp_get_the_profile_field_name();

		if ( isset ( $social_networking_fields[$bp_this_field_name] ) ) {
			$sp = strpos ( $field_value, $social_networking_fields[$bp_this_field_name] );
			if ( $sp === false ) {
				$url = str_replace( '***', strip_tags( $field_value ), $social_networking_fields[$bp_this_field_name] );
				$field_value = '<a href="http://' . $url . '">' . strip_tags( $field_value ) . '</a>';
			}
			return $field_value;
		}

		return $field_value;
	}
}

