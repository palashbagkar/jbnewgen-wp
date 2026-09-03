<?php
/**
 * Meta fields for the "insight" CPT — mirrors ../jbnewgen/src/collections/Insights.ts.
 *
 * Payload "title"     -> native post_title.
 * Payload "slug"      -> native post_name.
 * Payload "category"  -> insight_category taxonomy (inc/taxonomies.php), not a meta field.
 * Payload "date"      -> native post date.
 * Payload "excerpt"   -> native post excerpt.
 * Payload "body"      -> native content editor.
 *
 * OPEN DECISION (flagged, not assumed): Insights.ts embeds a ChartBlock in the Lexical
 * body (BlocksFeature) — charts inside article text. WordPress has no equivalent block
 * modeled here. Rebuild-as-a-block vs. drop is still pending (CONTEXT.md §7). Nothing
 * chart-related is built in this file; revisit before Phase 6/7 content migration.
 */

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

add_action( 'carbon_fields_register_fields', function () {

	$container = Container::make( 'post_meta', __( 'Insight Details', 'jbnewgen' ) )
		->where( 'post_type', '=', 'insight' );

	$container->add_fields( array(

		Field::make( 'image', 'cover_image', __( 'Cover image', 'jbnewgen' ) ),

		Field::make( 'text', 'read_mins', __( 'Read time (mins)', 'jbnewgen' ) )
			->set_attribute( 'type', 'number' )
			->set_width( 50 ),

	) );

	jbnewgen_add_seo_tab( $container );

} );
