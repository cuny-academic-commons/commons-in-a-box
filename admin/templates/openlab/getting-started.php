
<div id="getting-started" class="metabox-holder postbox">
	<div class="stuffbox">
		<h3><?php esc_html_e( 'Getting Started with Commons in A Box OpenLab', 'cbox' ); ?></h3>
	</div>

	<div class="inside">
	<p><?php printf( __( 'We\'ve assembled some links to get you started. Check out our <a href="%1$s" target="_blank">documentation</a> for detailed instructions. Questions? Post them on our <a href="%2$s" target="_blank">CBOX OpenLab Support Forum</a>.', 'cbox' ), 'http://commonsinabox.org/cbox-openlab-dashboard', 'http://commonsinabox.org/groups/openlab-help-support/forum/' ); ?></p>

	<ol>
	<li>
		<h4><a href="<?php echo cbox_admin_prop( 'url', 'admin.php?page=cbox-plugins' ); ?>"><?php esc_html_e( 'Plugins', 'cbox' ); ?></a></h4>
		<p><?php esc_html_e( 'Manage Community Feature plugins for your site and Member Site Plugins that you would like to make available to your members for their individual WordPress sites.', 'cbox' ); ?></p>
	</li>
	<li>
		<h4><a href="<?php echo cbox_admin_prop( 'url', 'admin.php?page=cbox-ol-member-settings' ); ?>"><?php esc_html_e( 'Member Settings', 'cbox' ); ?></a></h4>
		<p><?php esc_html_e( 'Modify member categories, their capabilities and permissions, who can create an account, and what types of accounts different members can create. These settings also include email domain whitelisting and registration codes to restrict access by account type.', 'cbox' ); ?></p>
	</li>
	<li>
		<h4><a href="<?php echo cbox_admin_prop( 'url', 'admin.php?page=cbox-ol-group-settings' ); ?>"><?php esc_html_e( 'Group Settings', 'cbox' ); ?></a></h4>
		<p><?php esc_html_e( 'Affect the appearance and functionality of groups across your site. You can change the features and site templates available to groups, as well as create and assign categories for groups that can be used to filter items on the group directory pages.', 'cbox' ); ?></p>
	</li>
	<li>
		<h4><a href="<?php echo cbox_admin_prop( 'url', 'admin.php?page=cbox-ol-academic-units' ); ?>"><?php esc_html_e( 'Academic Units', 'cbox' ); ?></a></h4>
		<p><?php esc_html_e( "Create unit-types that best describe how your institution is organized &ndash; for example, Departments that are located within Divisions &ndash; and define the units within each type. These units can be utilized as filters across your site's groups and for members to associate themselves in their profiles.", 'cbox' ); ?></p>
	</li>
	<li>
		<h4><a href="<?php echo cbox_admin_prop( 'url', 'admin.php?page=cbox-ol-brand-settings' ); ?>"><?php esc_html_e( 'Brand Settings', 'cbox' ); ?></a></h4>
		<p><?php esc_html_e( 'Customize your site including color scheme, logo, homepage layout, footer content, and widgets.', 'cbox' ); ?></p>
	</li>
	<li>
		<h4><a href="<?php echo cbox_admin_prop( 'url', 'admin.php?page=cbox-ol-communication-settings' ); ?>"><?php esc_html_e( 'Communication Settings', 'cbox' ); ?></a></h4>
		<p><?php esc_html_e( 'Manage email and other communication settings, including email template content and appearance and group email subscription settings.', 'cbox' ); ?></p>
	</li>
	</ol>
	</div>
</div>

<script>
jQuery(function($){
	var max = 0;
	resizeLi();

	function resizeLi() {
		if( $('#welcome-panel .wp-badge').css('display') !== 'none' ) {
			max = Math.max.apply(Math, $("#getting-started li").map(function() { return $(this).height(); }));
			$("#getting-started li").height(max + 20);
		}
	}

	$(window).on('resize', function(){
		$("#getting-started li").map(function() { return $(this).removeAttr('style'); } );
		resizeLi();
	});
});
</script>