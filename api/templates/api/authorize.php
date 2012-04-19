<?php

/**
 * API authorization template
 */

get_header( 'buddypress' ); ?>

	<div id="content">
		<div class="padder">
			<h2><?php _e( 'Authorize', 'cbox' ) ?></h2>

			<form method="post" action="">
				<p><?php printf( __( 'Give permission to %s to access your %s account?', 'cbox' ), '[remote site]', '[local site]' ) ?></p>

				<input type="hidden" name="oauth_callback" value="<?php echo esc_attr( urldecode( $_GET['callback_uri'] ) ) ?>" />
				<input type="submit" name="allow" value="<?php _e( 'Allow', 'cbox' ) ?>" />
			</form>
		</div>
	</div>

<?php get_sidebar( 'buddypress' ); ?>
<?php get_footer( 'buddypress' ); ?>