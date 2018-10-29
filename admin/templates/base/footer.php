
<ul class="subsubsub">
	<?php if ( defined( 'CBOX_SHOW_PACKAGE_SWITCH' ) && true === constant( 'CBOX_SHOW_PACKAGE_SWITCH' ) && count( cbox_get_packages() ) > 1 ) : ?>
		<li><a class="confirm" href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'admin.php?page=cbox&amp;cbox-package=0' ), 'cbox_select_package' ) ); ?>"><?php esc_html_e( 'Change packages', 'cbox' ); ?></a></li>
	<?php endif; ?>
</ul>
