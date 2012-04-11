<?php

class BP_API_Admin {
	function __construct() {
		$this->setup_hooks();
		
		require( CIAB_LIB_DIR . 'oauth-php/library/OAuthStore.php' );
		
		if ( !class_exists( 'BBG_CPT_Sort' ) ) {
			require( CIAB_LIB_DIR . 'boones-sortable-columns/boones-sortable-columns.php' );
		}
		
		if ( !class_exists( 'BBG_CPT_Pag' ) ) {
			require( CIAB_LIB_DIR . 'boones-pagination/boones-pagination.php' );
		}
	}
	
	function setup_hooks() {
		add_action( bp_core_admin_hook(), array( &$this, 'add_menu' ) );
	}
	
	function add_menu() {
		$page = add_menu_page( 
			__( 'BP API', 'cbox' ),
			__( 'BP API', 'cbox' ),
			'delete_users', // todo - map cap?
			'bp_api',
			array( &$this, 'menu_screen' )
		);
	}
	
	function menu_screen() {
		global $wpdb;
		
		$args        = array( 'conn' => $wpdb->dbh );
		$this->store = OAuthStore::instance(CIAB_PLUGIN_DIR . 'api/BP_OAuthStore.php', $args );
		
		$cols = array(
			array(
				'name' => 'id',
				'title' => __( 'ID', 'cbox' )
			),
			array(
				'name' => 'enabled',
				'title' => __( 'Enabled?', 'cbox' )
			),
			array(
				'name' => 'status',
				'title' => __( 'Status', 'cbox' )
			),
			array(
				'name' => 'issue_date',
				'title' => __( 'Issue Date', 'cbox' ),
				'default_order' => 'desc',
				'is_default' => true
			),
			array(
				'name' => 'application_uri',
				'title' => __( 'Application URI', 'cbox' )
			),
			array(
				'name' => 'application_title',
				'title' => __( 'Application Title', 'cbox' )
			),
			array(
				'name' => 'application_description',
				'title' => __( 'Application Description', 'cbox' ),
				'is_sortable' => false
			),
		);
		
		$sortable   = new BBG_CPT_Sort( $cols );
		$pagination = new BBG_CPT_Pag();
		
		$query_args = array(
			'paged'    => $pagination->get_paged,
			'per_page' => $pagination->get_per_page,
			'orderby'  => $sortable->get_orderby,
			'order'    => $sortable->get_order
		);
		
		$applications = $this->store->listConsumerApplications( $query_args );
		
		$pagination->setup_query( $applications );
		
		?>
	
	<div class="wrap">
	
	<h2><?php _e( 'Registered Consumers', 'cbox' ) ?></h2>
	
	<div class="pagination">
		<div class="currently-viewing">
			<?php $pagination->currently_viewing_text() ?>
		</div>

		<div class="pag-links">
			<?php $pagination->paginate_links() ?>
		</div>
	</div>

	<table class="widefat">
		<thead>
		<tr>
		<?php if ( $sortable->have_columns() ) : ?>
			<?php while ( $sortable->have_columns() ) : $sortable->the_column() ?>
				<?php $sortable->the_column_th() ?>
			<?php endwhile ?>
		<?php endif ?>
		<tr>
		</thead>

		<tbody>
		<?php foreach ( $applications->posts as $app  ) : ?>
			<tr>
				<td class="id"><?php echo esc_html( $app['id'] ) ?></td>
				
				<td class="enabled"><?php (bool) $app['enabled'] ? _e( 'Yes', 'cbox' ) : _e( 'No', 'cbox' ) ?></td>
				
				<td class="status"><?php echo esc_html( $app['status'] ) ?></td>
				
				<td class="issue_date"><?php echo esc_html( $app['issue_date'] ) ?></td>
				
				<td class="application_uri"><?php echo esc_html( $app['application_uri'] ) ?></td>
				
				<td class="application_title"><?php echo esc_html( $app['application_title'] ) ?></td>
				
				<td class="application_descr"><?php echo esc_html( $app['application_descr'] ) ?></td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>

	<div class="pagination">
		<div class="currently-viewing">
			<?php $pagination->currently_viewing_text() ?>
		</div>

		<div class="pag-links">
			<?php $pagination->paginate_links() ?>
		</div>
	</div>
	
	</div>
		
		<?php
	}
}
new BP_API_Admin;

?>