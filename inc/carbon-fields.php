<?php
/**
 * Bootstraps the bundled Carbon Fields library (theme-vendored, not a plugin).
 * See lib/carbon-fields/VENDORED.txt for provenance.
 */

spl_autoload_register( function ( $class ) {
	$prefix = 'Carbon_Fields\\';
	if ( strncmp( $prefix, $class, strlen( $prefix ) ) !== 0 ) {
		return;
	}
	$relative_class = substr( $class, strlen( $prefix ) );
	$file = get_template_directory() . '/lib/carbon-fields/core/' . str_replace( '\\', '/', $relative_class ) . '.php';
	if ( file_exists( $file ) ) {
		require $file;
	}
} );

add_action( 'after_setup_theme', function () {
	\Carbon_Fields\Carbon_Fields::boot();
} );
