<?php
/**
 * Taxonomies — mirrors ../jbnewgen/src/collections/Categories.ts.
 */

/**
 * Full taxonomy label set. Same reasoning as the post-type helper: anything
 * omitted falls back to the Category/Tag wording, which is why the screen was
 * reading "No categories found" instead of "No insight categories found".
 */
function jbnewgen_taxonomy_labels( $singular, $plural ) {
	$lower_singular = strtolower( $singular );
	$lower_plural   = strtolower( $plural );

	return array(
		'name'                       => $plural,
		'singular_name'              => $singular,
		'menu_name'                  => $plural,
		'all_items'                  => $plural,
		/* translators: %s: singular term name */
		'edit_item'                  => sprintf( __( 'Edit %s', 'jbnewgen' ), $singular ),
		'view_item'                  => sprintf( __( 'View %s', 'jbnewgen' ), $singular ),
		'update_item'                => sprintf( __( 'Update %s', 'jbnewgen' ), $singular ),
		'add_new_item'               => sprintf( __( 'Add %s', 'jbnewgen' ), $singular ),
		'new_item_name'              => sprintf( __( 'New %s name', 'jbnewgen' ), $singular ),
		'parent_item'                => sprintf( __( 'Parent %s', 'jbnewgen' ), $singular ),
		'parent_item_colon'          => sprintf( __( 'Parent %s:', 'jbnewgen' ), $singular ),
		'search_items'               => sprintf( __( 'Search %s', 'jbnewgen' ), $plural ),
		'popular_items'              => sprintf( __( 'Popular %s', 'jbnewgen' ), $plural ),
		'not_found'                  => sprintf( __( 'No %s found.', 'jbnewgen' ), $lower_plural ),
		'no_terms'                   => sprintf( __( 'No %s', 'jbnewgen' ), $lower_plural ),
		'name_field_description'     => sprintf( __( 'The name is how the %s appears on your site.', 'jbnewgen' ), $lower_singular ),
		'slug_field_description'     => __( 'The URL-friendly version of the name. Lowercase letters, numbers and hyphens only.', 'jbnewgen' ),
		'parent_field_description'   => sprintf( __( 'Assign a parent to create a hierarchy of %s.', 'jbnewgen' ), $lower_plural ),
		'desc_field_description'     => __( 'Optional. Some templates show this.', 'jbnewgen' ),
		'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'jbnewgen' ), $lower_plural ),
		'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'jbnewgen' ), $lower_plural ),
		'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', 'jbnewgen' ), $lower_plural ),
		'items_list_navigation'      => sprintf( __( '%s list navigation', 'jbnewgen' ), $plural ),
		'items_list'                 => sprintf( __( '%s list', 'jbnewgen' ), $plural ),
		'back_to_items'              => sprintf( __( '&larr; Go to %s', 'jbnewgen' ), $plural ),
	);
}

add_action( 'init', function () {

	register_taxonomy( 'insight_category', array( 'insight' ), array(
		'labels' => jbnewgen_taxonomy_labels( 'Insight Category', 'Insight Categories' ),
		// Categories.ts is just { title, slug } — WP terms already carry both
		// natively, nothing custom to add.
		'public'            => true,
		'show_in_rest'      => true,
		'hierarchical'      => true,
		'rewrite'           => array( 'slug' => 'insights/category' ),
	) );

}, 6 );
