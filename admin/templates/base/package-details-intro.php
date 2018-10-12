
<?php if ( cbox_get_package_prop( 'network', $_GET[ 'cbox-package-details' ] ) ) : ?>

	<p><?php printf( __( 'Welcome to CBOX %1$s! If this is a new WordPress Multisite installation, just click Install to continue. If you are adding CBOX %1$s to an existing installation, please consult the <a href="%2$s">documentation</a> before continuing.', 'cbox' ), cbox_get_package_prop( 'name', $_GET[ 'cbox-package-details' ] ), 'http://commonsinabox.org/installing-cbox' ); ?></p>

<?php else : ?>

	<p><?php printf( __( 'Welcome to CBOX %1$s! If this is a new WordPress installation, just click Install to continue. If you are adding CBOX %1$s to an existing installation, please consult the <a href="%2$s">documentation</a> before continuing.', 'cbox' ), cbox_get_package_prop( 'name', $_GET[ 'cbox-package-details' ] ), 'http://commonsinabox.org/installing-cbox' ); ?></p>

<?php endif; ?>