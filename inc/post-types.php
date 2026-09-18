<?php
/**
 * Custom Post Types — mirrors Payload collections in ../jbnewgen/src/collections/.
 * See CONTEXT.md §5 for the collection-to-CPT mapping.
 *
 * Every type declares its own `capability_type` rather than inheriting the
 * generic `post` capabilities. Sharing `post` caps means a role can only ever
 * be granted "edit all content" or "edit none" — "may edit Job Openings but
 * not Services" is not expressible. Per-type caps (edit_jobs, publish_services,
 * …) are what inc/roles.php hands out, and what makes the role builder able to
 * offer a checkbox per content type. `map_meta_cap` is required alongside it so
 * WordPress still resolves per-post checks like "is this your own draft?".
 */

/**
 * Build a complete label set from a singular/plural pair.
 *
 * WordPress falls back to the *Post* labels for anything not supplied, which
 * is why the admin was saying "View Posts", "No posts found" and "Search
 * Posts" on the Insights and Core Team screens. Supplying the full set is the
 * only way to stop that — there is no global "call them X" switch.
 *
 * @param string $singular e.g. "Insight"
 * @param string $plural   e.g. "Insights"
 */
function jbnewgen_post_type_labels( $singular, $plural, $sentence_plural = '' ) {
	$lower_singular = strtolower( $singular );
	// Some plurals are collective nouns ("Core Team") that read badly mid
	// sentence — "No core team found." — so they can supply their own form.
	$lower_plural = $sentence_plural ? $sentence_plural : strtolower( $plural );

	return array(
		'name'                     => $plural,
		'singular_name'            => $singular,
		'menu_name'                => $plural,
		'name_admin_bar'           => $singular,
		'add_new'                  => __( 'Add New', 'jbnewgen' ),
		/* translators: %s: singular item name */
		'add_new_item'             => sprintf( __( 'Add %s', 'jbnewgen' ), $singular ),
		'edit_item'                => sprintf( __( 'Edit %s', 'jbnewgen' ), $singular ),
		'new_item'                 => sprintf( __( 'New %s', 'jbnewgen' ), $singular ),
		'view_item'                => sprintf( __( 'View %s', 'jbnewgen' ), $singular ),
		'view_items'               => sprintf( __( 'View %s', 'jbnewgen' ), $plural ),
		'search_items'             => sprintf( __( 'Search %s', 'jbnewgen' ), $plural ),
		'not_found'                => sprintf( __( 'No %s found.', 'jbnewgen' ), $lower_plural ),
		'not_found_in_trash'       => sprintf( __( 'No %s found in Trash.', 'jbnewgen' ), $lower_plural ),
		'parent_item_colon'        => sprintf( __( 'Parent %s:', 'jbnewgen' ), $singular ),
		'all_items'                => $plural,
		'archives'                 => sprintf( __( '%s Archives', 'jbnewgen' ), $singular ),
		'attributes'               => sprintf( __( '%s Attributes', 'jbnewgen' ), $singular ),
		'insert_into_item'         => sprintf( __( 'Insert into %s', 'jbnewgen' ), $lower_singular ),
		'uploaded_to_this_item'    => sprintf( __( 'Uploaded to this %s', 'jbnewgen' ), $lower_singular ),
		'filter_items_list'        => sprintf( __( 'Filter %s list', 'jbnewgen' ), $lower_plural ),
		'items_list_navigation'    => sprintf( __( '%s list navigation', 'jbnewgen' ), $plural ),
		'items_list'               => sprintf( __( '%s list', 'jbnewgen' ), $plural ),
		'item_published'           => sprintf( __( '%s published.', 'jbnewgen' ), $singular ),
		'item_published_privately' => sprintf( __( '%s published privately.', 'jbnewgen' ), $singular ),
		'item_reverted_to_draft'   => sprintf( __( '%s reverted to draft.', 'jbnewgen' ), $singular ),
		'item_scheduled'           => sprintf( __( '%s scheduled.', 'jbnewgen' ), $singular ),
		'item_updated'             => sprintf( __( '%s updated.', 'jbnewgen' ), $singular ),
		'item_link'                => sprintf( __( '%s Link', 'jbnewgen' ), $singular ),
		'item_link_description'    => sprintf( __( 'A link to a %s.', 'jbnewgen' ), $lower_singular ),
		'featured_image'           => __( 'Cover image', 'jbnewgen' ),
		'set_featured_image'       => __( 'Set cover image', 'jbnewgen' ),
		'remove_featured_image'    => __( 'Remove cover image', 'jbnewgen' ),
		'use_featured_image'       => __( 'Use as cover image', 'jbnewgen' ),
	);
}

add_action( 'init', function () {

	register_post_type( 'pillar', array(
		'description' => __( 'The pillars that group services. Reorder with the &ldquo;Order&rdquo; field.', 'jbnewgen' ),
		'labels' => jbnewgen_post_type_labels( 'Service Category', 'Service Categories' ),
		// Mirrors ServicePillars.ts: 4 pillars, ordered via the "order" field -> native menu_order.
		'capability_type' => array( 'pillar', 'pillars' ),
		'map_meta_cap'    => true,
		// WordPress derives `create_posts` from `edit_posts` unless it is named,
		// so "may open an entry" and "may make one" were the same permission —
		// which is why the read-only Viewer role still rendered an Add button.
		'capabilities'    => array( 'create_posts' => 'create_pillars' ),
		'public'       => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-networking',
		// 'revisions' because every one of these collections carries
		// versions: { maxPerDoc: 20 } in Payload — the live admin keeps a
		// history for all five, not only for Insights.
		'supports'     => array( 'title', 'page-attributes', 'revisions' ),
		'has_archive'  => false,
		'rewrite'      => array( 'slug' => 'services' ),
	) );

	register_post_type( 'service', array(
		'description' => __( 'Every service, its description and its focus areas.', 'jbnewgen' ),
		'labels' => jbnewgen_post_type_labels( 'Service', 'Services' ),
		// Mirrors Services.ts. "pillar" (required, child-of relationship) is a Carbon
		// Fields association field, not a taxonomy — see inc/fields-service.php.
		'capability_type' => array( 'service', 'services' ),
		'map_meta_cap'    => true,
		// WordPress derives `create_posts` from `edit_posts` unless it is named,
		// so "may open an entry" and "may make one" were the same permission —
		// which is why the read-only Viewer role still rendered an Add button.
		'capabilities'    => array( 'create_posts' => 'create_services' ),
		'public'       => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-list-view',
		// 'revisions' because every one of these collections carries
		// versions: { maxPerDoc: 20 } in Payload — the live admin keeps a
		// history for all five, not only for Insights.
		'supports'     => array( 'title', 'page-attributes', 'revisions' ),
		'has_archive'  => false,
	) );

	register_post_type( 'insight', array(
		'description' => __( 'Articles & archived insights.', 'jbnewgen' ),
		'labels' => jbnewgen_post_type_labels( 'Insight', 'Insights' ),
		// Mirrors Insights.ts. date -> native post date, body -> native editor
		// (block/rich-text), excerpt -> native excerpt. category is the
		// insight_category taxonomy, registered in inc/taxonomies.php.
		// NOTE: Insights.ts also has a ChartBlock embedded in body (Lexical
		// BlocksFeature) with no WordPress equivalent modeled here — open
		// decision, see CONTEXT.md §7 and the Phase 4 handoff notes.
		'capability_type' => array( 'insight', 'insights' ),
		'map_meta_cap'    => true,
		// WordPress derives `create_posts` from `edit_posts` unless it is named,
		// so "may open an entry" and "may make one" were the same permission —
		// which is why the read-only Viewer role still rendered an Add button.
		'capabilities'    => array( 'create_posts' => 'create_insights' ),
		'public'       => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-lightbulb',
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
		'has_archive'  => true,
		'rewrite'      => array( 'slug' => 'insights' ),
	) );

	register_post_type( 'team_member', array(
		'description' => __( 'The people shown on the Core Team page. Reorder with the &ldquo;Order&rdquo; field — lower numbers appear first.', 'jbnewgen' ),
		'labels' => jbnewgen_post_type_labels( 'Team Member', 'Core Team', 'team members' ),
		// Mirrors TeamMembers.ts. name -> native post title, order -> native menu_order.
		'capability_type' => array( 'team_member', 'team_members' ),
		'map_meta_cap'    => true,
		// WordPress derives `create_posts` from `edit_posts` unless it is named,
		// so "may open an entry" and "may make one" were the same permission —
		// which is why the read-only Viewer role still rendered an Add button.
		'capabilities'    => array( 'create_posts' => 'create_team_members' ),
		'public'       => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-groups',
		// 'revisions' because every one of these collections carries
		// versions: { maxPerDoc: 20 } in Payload — the live admin keeps a
		// history for all five, not only for Insights.
		'supports'     => array( 'title', 'page-attributes', 'revisions' ),
		'has_archive'  => false,
	) );

	register_post_type( 'job', array(
		'description' => __( 'Open roles on the Careers page. Untick &ldquo;Listed&rdquo; to hide a role without deleting it.', 'jbnewgen' ),
		'labels' => jbnewgen_post_type_labels( 'Job Opening', 'Job Openings' ),
		// Mirrors Jobs.ts. title -> native post title, order -> native menu_order.
		'capability_type' => array( 'job', 'jobs' ),
		'map_meta_cap'    => true,
		// WordPress derives `create_posts` from `edit_posts` unless it is named,
		// so "may open an entry" and "may make one" were the same permission —
		// which is why the read-only Viewer role still rendered an Add button.
		'capabilities'    => array( 'create_posts' => 'create_jobs' ),
		'public'       => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-briefcase',
		// 'revisions' because every one of these collections carries
		// versions: { maxPerDoc: 20 } in Payload — the live admin keeps a
		// history for all five, not only for Insights.
		'supports'     => array( 'title', 'page-attributes', 'revisions' ),
		'has_archive'  => true,
		'rewrite'      => array( 'slug' => 'careers' ),
	) );

}, 5 );
