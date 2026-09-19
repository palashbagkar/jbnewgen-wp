<?php
/**
 * Inline SVG icons for the public templates.
 *
 * Ported from ../jbnewgen/src/components/ui/Icon.tsx, which is a lookup table
 * of stroke paths rendered at a requested size. Only the icons the templates
 * actually reference live here; the rest are added as their templates land,
 * rather than shipping 40 unused paths to every visitor.
 *
 * Inline rather than a sprite sheet or an icon font: these are small, they
 * inherit currentColor so the nav hover states work without extra rules, and
 * they cost no extra request.
 */

defined( 'ABSPATH' ) || exit;

/**
 * The path data. Stroke-based, 24x24 viewBox, matching Icon.tsx.
 *
 * @return array
 */
function jbnewgen_icon_paths() {
	return array(
		'chevronDown'  => '<path d="m6 9 6 6 6-6"/>',
		'arrowUpRight' => '<path d="M7 17 17 7"/><path d="M7 7h10v10"/>',
		'arrowRight'   => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
		'search'       => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
		'menu'         => '<path d="M3 6h18"/><path d="M3 12h18"/><path d="M3 18h18"/>',
		'close'        => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
		'phone'        => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>',
		'mail'         => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
		'linkedin'     => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/>',
		'facebook'     => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
		'instagram'    => '<rect width="20" height="20" x="2" y="2" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>',

		// The rest, ported verbatim from ../jbnewgen/src/components/ui/Icon.tsx
		// as the service/about/insights templates need them.
		'compass'      => '<circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2 5-5 2 2-5 5-2Z"/>',
		'cpu'          => '<rect x="6" y="6" width="12" height="12" rx="2"/><path d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3"/><rect x="9.5" y="9.5" width="5" height="5" rx="1"/>',
		'megaphone'    => '<path d="M3 11v2a1 1 0 0 0 1 1h2l9 5V5L6 10H4a1 1 0 0 0-1 1Z"/><path d="M18 8a4 4 0 0 1 0 8"/>',
		'chat'         => '<path d="M20 15a2 2 0 0 1-2 2H8l-4 4V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2Z"/><path d="M8 10h8M8 13h5"/>',
		'route'        => '<circle cx="6" cy="19" r="2.5"/><circle cx="18" cy="5" r="2.5"/><path d="M8.5 19H15a3.5 3.5 0 0 0 0-7H9a3.5 3.5 0 0 1 0-7h6.5"/>',
		'network'      => '<rect x="9" y="3" width="6" height="5" rx="1"/><rect x="3" y="16" width="6" height="5" rx="1"/><rect x="15" y="16" width="6" height="5" rx="1"/><path d="M12 8v4M12 12H6v4M12 12h6v4"/>',
		'target'       => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.4" fill="currentColor" stroke="none"/>',
		'shield'       => '<path d="M12 3 5 6v5c0 4.5 3 7.6 7 9 4-1.4 7-4.5 7-9V6l-7-3Z"/><path d="m9 12 2 2 4-4"/>',
		'bulb'         => '<path d="M9 18h6M10 21h4"/><path d="M12 3a6 6 0 0 0-4 10.5c.7.7 1 1.3 1 2.5h6c0-1.2.3-1.8 1-2.5A6 6 0 0 0 12 3Z"/>',
		'spark'        => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M18 6l-2.5 2.5M8.5 15.5 6 18"/>',
		'handshake'    => '<path d="m11 17 2 2a1 1 0 0 0 1.4 0l3.6-3.6a1 1 0 0 0 0-1.4L15 11"/><path d="M18 14 21 11M3 11l3-3 4 1 3 3-2 2a1.5 1.5 0 0 1-2 0l-1-1"/><path d="M6 8 3 5"/>',
		'creditCard'   => '<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>',
		'folder'       => '<path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/>',
		'check'        => '<path d="m4 12 5 5L20 6"/>',
		'plus'         => '<path d="M12 5v14M5 12h14"/>',
		'mapPin'       => '<path d="M12 21s7-6.2 7-11a7 7 0 0 0-14 0c0 4.8 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/>',
		'clock'        => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'quote'        => '<path d="M10 7H6a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h2v1a2 2 0 0 1-2 2H5m15-9h-4a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h2v1a2 2 0 0 1-2 2h-1"/>',
		'star'         => '<path d="m12 3 2.6 5.3 5.9.9-4.3 4.1 1 5.8-5.2-2.7L6.8 19l1-5.8L3.5 9.2l5.9-.9L12 3Z"/>',
		'users'        => '<circle cx="9" cy="8" r="3"/><path d="M3 20a6 6 0 0 1 12 0"/><path d="M16 5.5a3 3 0 0 1 0 5M21 20a6 6 0 0 0-4-5.6"/>',
		'briefcase'    => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18"/>',
		'globe'        => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.4 4 5.6 4 9s-1.5 6.6-4 9c-2.5-2.4-4-5.6-4-9s1.5-6.6 4-9Z"/>',
		'trending'     => '<path d="M3 17 10 10l4 4 7-7M14 6h7v7"/>',
		'gauge'        => '<path d="M4 15a8 8 0 1 1 16 0"/><path d="m12 15 4-4"/><circle cx="12" cy="15" r="1.2" fill="currentColor" stroke="none"/>',
		'ribbon'       => '<path d="M6 3h12v18l-6-4.5L6 21V3Z"/>',
	);
}

/**
 * Echo one icon.
 *
 * An unknown name prints nothing rather than a broken glyph -- pillar icons
 * come from a CMS select, so a renamed option should degrade quietly instead
 * of putting a missing-image box in the middle of the mega menu.
 *
 * @param string $name
 * @param int    $size  px, applied to both width and height
 * @param string $class extra classes
 */
function jbnewgen_icon( $name, $size = 16, $class = '' ) {
	$paths = jbnewgen_icon_paths();
	if ( ! isset( $paths[ $name ] ) ) {
		return;
	}
	printf(
		'<svg class="%s" width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%s</svg>',
		esc_attr( $class ),
		(int) $size,
		(int) $size,
		$paths[ $name ] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed literal path data from the table above.
	);
}
