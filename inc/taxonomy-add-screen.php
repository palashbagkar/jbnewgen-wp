<?php
/**
 * Give taxonomies the same "list, then a separate add page" shape the post
 * types have.
 *
 * WordPress puts the add-term form and the term list side by side on one
 * screen. Every other content type here works as list -> "Add X" -> its own
 * page, and the term screen was the one place that broke the pattern.
 *
 * Rather than rebuild the form — which would mean duplicating its nonce, its
 * fields and its handler, and would silently rot when a field is added — the
 * same screen is shown in one of two modes, chosen by a query flag:
 *
 *   edit-tags.php?taxonomy=...                 the list, with an "Add" button
 *   edit-tags.php?taxonomy=...&jb_add=1        the form, on its own
 *
 * Core's own markup and handler do all the work in both cases; only which
 * column is visible changes.
 */

/** Taxonomies that get the split treatment. */
function jbnewgen_split_taxonomies() {
	return array( 'insight_category' );
}

/** True when the current request is the add-a-term mode. */
function jbnewgen_is_tax_add_screen() {
	if ( empty( $_GET['jb_add'] ) ) {
		return false;
	}
	$screen = get_current_screen();
	return $screen && ! empty( $screen->taxonomy )
		&& in_array( $screen->taxonomy, jbnewgen_split_taxonomies(), true );
}

/** Body classes so the stylesheet can pick a mode. */
add_filter( 'admin_body_class', function ( $classes ) {
	$screen = get_current_screen();
	if ( ! $screen || empty( $screen->taxonomy ) ) {
		return $classes;
	}
	if ( ! in_array( $screen->taxonomy, jbnewgen_split_taxonomies(), true ) ) {
		return $classes;
	}
	return $classes . ( jbnewgen_is_tax_add_screen() ? ' jb-tax-add' : ' jb-tax-list' );
} );

/**
 * The "Add Insight Category" button, and the heading for the add mode.
 *
 * Printed into the footer and moved next to the title by the same script that
 * relocates every other page-title action, so it lands in the data bar with
 * the rest of the list controls.
 */
add_action( 'admin_footer', function () {
	$screen = get_current_screen();
	if ( ! $screen || empty( $screen->taxonomy ) ) {
		return;
	}
	if ( ! in_array( $screen->taxonomy, jbnewgen_split_taxonomies(), true ) ) {
		return;
	}

	$tax = get_taxonomy( $screen->taxonomy );
	if ( ! $tax || ! current_user_can( $tax->cap->edit_terms ) ) {
		return;
	}

	$add_url  = add_query_arg( 'jb_add', '1' );
	$back_url = remove_query_arg( 'jb_add' );
	$is_add   = jbnewgen_is_tax_add_screen();
	?>
	<a id="jb-tax-action" class="page-title-action" style="display:none"
		href="<?php echo esc_url( $is_add ? $back_url : $add_url ); ?>">
		<?php
		echo esc_html( $is_add
			? sprintf( __( '&larr; %s', 'jbnewgen' ), $tax->labels->name )
			: $tax->labels->add_new_item );
		?>
	</a>
	<script>
	( function () {
		var btn  = document.getElementById( 'jb-tax-action' );
		var wrap = document.querySelector( '.wrap' );
		if ( ! btn || ! wrap ) { return; }

		var h1 = wrap.querySelector( 'h1' );
		if ( ! h1 ) { return; }

		// Core prints no page-title-action on this screen at all, so one is
		// inserted where core would have put it. The data-bar script then
		// finds it by the same selector it uses everywhere else.
		btn.style.display = '';
		h1.parentNode.insertBefore( btn, h1.nextSibling );

		<?php if ( $is_add ) : ?>
		// In add mode the form is the page, so its own heading is redundant
		// next to the page title.
		var formHeading = document.querySelector( '#col-left .form-wrap h2' );
		if ( formHeading ) { formHeading.remove(); }
		<?php endif; ?>
	}() );
	</script>
	<?php
}, 4 );
