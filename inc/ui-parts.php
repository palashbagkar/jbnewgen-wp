<?php
/**
 * Shared markup for ui/Button.tsx (CONTEXT.md §6 "ui/"). Every template that
 * needs a styled link -- a service CTA, the conversion rail, a hero button --
 * had been hand-copying a slightly different guess at these classes instead
 * of sharing one definition, so "secondary" (solid navy) and "ghost" (outline)
 * drifted into looking like the same button on some pages. Ported verbatim
 * from Button.tsx's `variants`/`flatVariants`/`sizes` tables.
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param string $label
 * @param string $href
 * @param array  $args {
 *     @type string $variant primary|secondary|ghost|light. Default primary.
 *     @type string $size    sm|md|lg. Default md.
 *     @type string $icon    Icon name, or '' for none. Default 'arrowRight'.
 *     @type bool   $flat    Flat (no shadow/hover-lift) vs elevated. Default false.
 *     @type string $class   Extra classes appended.
 *     @type array  $attrs   Extra raw attributes, e.g. array('target' => '_blank').
 * }
 */
function jbnewgen_button( $label, $href, $args = array() ) {
	$variant = isset( $args['variant'] ) ? $args['variant'] : 'primary';
	$size    = isset( $args['size'] ) ? $args['size'] : 'md';
	$icon    = array_key_exists( 'icon', $args ) ? $args['icon'] : 'arrowRight';
	$flat    = ! empty( $args['flat'] );
	$class   = isset( $args['class'] ) ? $args['class'] : '';
	$attrs   = isset( $args['attrs'] ) ? $args['attrs'] : array();

	$base = 'group inline-flex items-center justify-center gap-2 rounded-[7px] font-semibold focus-visible:outline-none disabled:opacity-60 disabled:pointer-events-none whitespace-nowrap';

	$variants = array(
		'primary'   => 'transition-all duration-200 bg-flame-500 text-white shadow-[0_10px_30px_-10px_rgba(234,131,46,0.7)] hover:bg-flame-600 hover:shadow-[0_14px_34px_-8px_rgba(234,131,46,0.7)] hover:-translate-y-0.5',
		'secondary' => 'transition-all duration-200 bg-ink-900 text-white hover:bg-ink-800 hover:-translate-y-0.5 shadow-[0_10px_30px_-12px_rgba(8,21,39,0.6)]',
		'ghost'     => 'transition-all duration-200 bg-transparent text-ink-800 ring-1 ring-inset ring-ink-200 hover:ring-ink-400 hover:bg-ink-50',
		'light'     => 'transition-all duration-200 bg-white/10 text-white ring-1 ring-inset ring-white/25 backdrop-blur hover:bg-white/20',
	);
	$flat_variants = array(
		'primary'   => 'transition-colors duration-200 bg-flame-500 text-white hover:bg-flame-600',
		'secondary' => 'transition-colors duration-200 bg-ink-900 text-white hover:bg-ink-800',
		'ghost'     => 'transition-colors duration-200 bg-transparent text-ink-800 ring-1 ring-inset ring-ink-200 hover:bg-ink-50',
		'light'     => 'transition-colors duration-200 bg-white/10 text-white ring-1 ring-inset ring-white/25 backdrop-blur hover:bg-white/20',
	);
	$sizes = array(
		'sm' => 'h-9 px-4 text-sm',
		'md' => 'h-11 px-6 text-[0.95rem]',
		'lg' => 'h-13 px-8 text-base',
	);

	$table       = $flat ? $flat_variants : $variants;
	$variant_cls = isset( $table[ $variant ] ) ? $table[ $variant ] : $table['primary'];
	$size_cls    = isset( $sizes[ $size ] ) ? $sizes[ $size ] : $sizes['md'];

	$attr_str = '';
	foreach ( $attrs as $jb_k => $jb_v ) {
		$attr_str .= ' ' . esc_attr( $jb_k ) . '="' . esc_attr( $jb_v ) . '"';
	}

	$icon_cls = 'shrink-0' . ( $flat ? '' : ' transition-transform duration-200 group-hover:translate-x-0.5' );
	?>
	<a href="<?php echo esc_url( $href ); ?>" class="<?php echo esc_attr( trim( $base . ' ' . $variant_cls . ' ' . $size_cls . ' ' . $class ) ); ?>"<?php echo $attr_str; // phpcs:ignore -- pre-escaped above. ?>>
		<?php echo esc_html( $label ); ?>
		<?php if ( $icon ) : ?><?php jbnewgen_icon( $icon, 18, $icon_cls ); ?><?php endif; ?>
	</a>
	<?php
}
