<?php

/**
 * API authorization success template
 */

$store = bp_api_get_oauth_store();

$consumer_key = isset( $_GET['consumer_key'] ) ? urldecode( $_GET['consumer_key'] ) : '';
$user_id = isset( $_GET['consumer_key'] ) ? (int) $_GET['user_id'] : 0;

$r = $store->getServer($consumer_key, $user_id);

$remote_uri = isset( $r['server_uri'] ) ? $r['server_uri'] : '';

get_header( 'buddypress' ); ?>

	<div id="content">
		<div class="padder">
			<h2><?php _e( 'Authorizion successful', 'cbox' ) ?></h2>

			<p><?php printf( __( 'Your account at %1$s is now connected to your account at %2$s', 'cbox' ), $remote_uri, bp_get_root_domain() ) ?></p>
		</div>
	</div>

<?php get_sidebar( 'buddypress' ); ?>
<?php get_footer( 'buddypress' ); ?>