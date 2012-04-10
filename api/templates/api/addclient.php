<?php

/**
 * BuddyPress API Add Client template
 */

get_header( 'buddypress' ); ?>

	<div id="content">
		<div class="padder">

		<form action="" method="post" id="add-client-form" class="dir-form">

			<h3><?php _e( 'Add Client', 'cbox' ); ?></h3>

			<?php do_action( 'template_notices' ); ?>

			<label for="requester_name"><?php _e( 'Requester Name', 'cbox' ) ?>
				<input name="requester_name" />
			</label><br />
			
			<label for="requester_email"><?php _e( 'Requester Email', 'cbox' ) ?>
				<input name="requester_email" />
			</label><br />
			
			<label for="callback_uri"><?php _e( 'Callback URI', 'cbox' ) ?>
				<input name="callback_uri" />
			</label><br />
			
			<label for="application_uri"><?php _e( 'Application URI', 'cbox' ) ?>
				<input name="application_uri" />
			</label><br />
			
			<label for="application_title"><?php _e( 'Application Title', 'cbox' ) ?>
				<input name="application_title" />
			</label><br />
			
			<label for="application_descr"><?php _e( 'Application Description', 'cbox' ) ?>
				<input name="application_descr" />
			</label><br />
			
			<label for="application_notes"><?php _e( 'Application Notes', 'cbox' ) ?>
				<input name="application_notes" />
			</label><br />
			
			<label for="application_type"><?php _e( 'Application Type', 'cbox' ) ?>
				<input name="application_type" />
			</label><br />
			
			<label for="application_commercial"><?php _e( 'Application Commercial', 'cbox' ) ?>
				<input name="application_commercial" />
			</label><br />
			
			<?php wp_nonce_field( 'add_client' ); ?>
			
			<input type="submit" value="<?php _e( 'Submit', 'cbox' ) ?>" />

		</form><!-- #add-client-form -->

		</div><!-- .padder -->
	</div><!-- #content -->

<?php get_sidebar( 'buddypress' ); ?>
<?php get_footer( 'buddypress' ); ?>

