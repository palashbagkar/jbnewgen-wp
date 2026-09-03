<?php
/**
 * Shared icon set, mirrored from ../jbnewgen/src/collections/iconOptions.ts (ICON_OPTIONS).
 * Used by any select field where the Next.js site let editors pick an icon.
 */

function jbnewgen_icon_options() {
	$icons = array(
		'compass', 'cpu', 'megaphone', 'chat', 'route', 'network', 'target', 'shield',
		'bulb', 'spark', 'handshake', 'creditCard', 'folder', 'phone', 'mail', 'mapPin',
		'clock', 'users', 'briefcase', 'globe', 'trending', 'gauge',
	);
	return array_combine( $icons, $icons );
}
