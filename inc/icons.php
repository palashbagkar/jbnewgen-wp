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
