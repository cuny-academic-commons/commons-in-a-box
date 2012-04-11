<?php

class BP_API_Admin {
	function __construct() {
		$this->setup_hooks();
		
		$this->store = bp_api_get_oauth_store();
		
		if ( !class_exists( 'BBG_CPT_Sort' ) ) {
			require( CIAB_LIB_DIR . 'boones-sortable-columns/boones-sortable-columns.php' );
		}
		
		if ( !class_exists( 'BBG_CPT_Pag' ) ) {
			require( CIAB_LIB_DIR . 'boones-pagination/boones-pagination.php' );
		}
		
		if ( isset( $_POST['new_server'] ) ) {
			$this->process_new_server();
		}
	}
	
	function setup_hooks() {
		add_action( bp_core_admin_hook(), array( &$this, 'add_menu' ) );
	}
	
	function add_menu() {
		$pages = array();
		
		$pages[] = add_menu_page( 
			__( 'BP API', 'cbox' ),
			__( 'BP API', 'cbox' ),
			'delete_users', // todo - map cap?
			'bp_api',
			array( &$this, 'menu_screen_clients' )
		);
		
		$pages[] = add_submenu_page(
			'bp_api',
			__( 'Clients', 'cbox' ),
			__( 'Clients', 'cbox' ),
			'delete_users',
			'bp_api_clients',
			array( &$this, 'menu_screen_clients' )
		);
	
		$pages[] = add_submenu_page(
			'bp_api',
			__( 'Servers', 'cbox' ),
			__( 'Servers', 'cbox' ),
			'delete_users',
			'bp_api_servers',
			array( &$this, 'menu_screen_servers' )
		);
	}
	
	function menu_screen_clients() {
	
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
	
		
	function menu_screen_servers() {
	
		$cols = array(
			array(
				'name' => 'id',
				'title' => __( 'ID', 'cbox' )
			),
			array(
				'name' => 'user_id',
				'title' => __( 'User ID', 'cbox' )
			),
			array(
				'name' => 'consumer_key',
				'title' => __( 'Consumer Key', 'cbox' )
			),
			array(
				'name' => 'consumer_secret',
				'title' => __( 'Consumer Secret', 'cbox' )
			),
			array(
				'name' => 'signature_methods',
				'title' => __( 'Signature Methods', 'cbox' ),
			),
			array(
				'name' => 'server_uri',
				'title' => __( 'Server URI', 'cbox' )
			),
			array(
				'name' => 'server_uri_host',
				'title' => __( 'Server URI Host', 'cbox' )
			),
			array(
				'name' => 'server_uri_path',
				'title' => __( 'Server URI Path', 'cbox' ),
			),
			array(
				'name' => 'request_token_uri',
				'title' => __( 'Request Token URI', 'cbox' )
			),
			array(
				'name' => 'authorize_uri',
				'title' => __( 'Authorize URI', 'cbox' )
			),
			array(
				'name' => 'access_token_uri',
				'title' => __( 'Access Token URI', 'cbox' )
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
		
		$servers = $this->store->listServers( $query_args );
		
		$pagination->setup_query( $servers );
		
		?>
	
	<div class="wrap">
	
	<h2><?php _e( 'New Server', 'cbox' ) ?></h2>
	
	<form method="post">
		<label for="consumer_key"><?php _e( 'Consumer Key', 'cbox' ) ?>
			<input name="consumer_key" />
		</label><br />
		
		<label for="consumer_secret"><?php _e( 'Consumer Secret', 'cbox' ) ?>
			<input name="consumer_secret" />
		</label><br />
		
		<label for="server_uri"><?php _e( 'Server URI', 'cbox' ) ?>
			<input name="server_uri" />
		</label><br />
		
		<label for="signature_methods"><?php _e( 'Signature Methods', 'cbox' ) ?>
			<input name="signature_methods" />
		</label><br />
		
		<label for="request_token_uri"><?php _e( 'Request Token URI', 'cbox' ) ?>
			<input name="Request Token URI" />
		</label><br />
		
		<label for="authorize_uri"><?php _e( 'Authorize URI', 'cbox' ) ?>
			<input name="authorize_uri" />
		</label><br />
		
		<label for="access_token_uri"><?php _e( 'Access Token URI', 'cbox' ) ?>
			<input name="access_token_uri" />
		</label><br />
		
		<input name="new_server" value="<?php _e( 'Submit', 'cbox' ) ?>" type="submit" />
	</form>
	
	<h2><?php _e( 'Registered Servers', 'cbox' ) ?></h2>
	
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
		<?php foreach ( $servers->posts as $server ) : ?>
			<tr>
				<td class="id"><?php echo esc_html( $server['id'] ) ?></td>
				
				<td class="user_id"><?php echo esc_html( $server['user_id'] ) ?></td>
				
				<td class="consumer_key"><?php echo esc_html( $server['consumer_key'] ) ?></td>
				
				<td class="consumer_secret"><?php echo esc_html( $server['consumer_secret'] ) ?></td>
				
				<td class="signature_methods"><?php echo esc_html( $server['signature_methods'] ) ?></td>
				
				<td class="server_uri"><?php echo esc_html( $server['server_uri'] ) ?></td>
				
				<td class="server_uri_host"><?php echo esc_html( $server['server_uri_host'] ) ?></td>
				
				<td class="server_uri_path"><?php echo esc_html( $server['server_uri_path'] ) ?></td>
				
				<td class="request_token_uri"><?php echo esc_html( $server['request_token_uri'] ) ?></td>
				
				<td class="authorize_uri"><?php echo esc_html( $server['authorize_uri'] ) ?></td>
				
				<td class="access_token_uri"><?php echo esc_html( $server['access_token_uri'] ) ?></td>
				
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
	
	function process_new_server() {
			
		// The server description
		$server = array(
		    'consumer_key' => '',
		    'consumer_secret' => '',
		    'server_uri' => '',
		    'signature_methods' => array(),
		    'request_token_uri' => '',
		    'authorize_uri' => '',
		    'access_token_uri' => ''
		);
		
		foreach( $server as $skey => $sval ) {
			if ( isset( $_POST[$skey] ) ) {
				$server[$skey] = $_POST[$skey];
			}
		}
		
		$user_id = 0; // todo
		
		$consumer_key = $this->store->updateServer( $server, $user_id );
	}
}
new BP_API_Admin;

?>