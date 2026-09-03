<?php
/**
 * Meta fields for the "pillar" CPT — mirrors ../jbnewgen/src/collections/ServicePillars.ts.
 *
 * Payload "short" -> native post_title (useAsTitle).
 * Payload "slug"  -> native post_name.
 * Payload "order" -> native menu_order (page-attributes support).
 */

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

add_action( 'carbon_fields_register_fields', function () {

	$container = Container::make( 'post_meta', __( 'Pillar Details', 'jbnewgen' ) )
		->where( 'post_type', '=', 'pillar' );

	$container->add_fields( array(

		Field::make( 'text', 'full_title', __( 'Full title', 'jbnewgen' ) )
			->set_required( true )
			->set_help_text( __( 'The long headline, e.g. "Tech Readiness Services".', 'jbnewgen' ) ),

		Field::make( 'select', 'icon', __( 'Icon', 'jbnewgen' ) )
			->set_options( jbnewgen_icon_options() )
			->set_default_value( 'compass' )
			->set_required( true )
			->set_width( 50 ),

		Field::make( 'text', 'blurb', __( 'One-line blurb', 'jbnewgen' ) )
			->set_required( true )
			->set_help_text( __( 'Short line under the pillar name on cards, e.g. "Amplify your brand and pipeline."', 'jbnewgen' ) )
			->set_width( 50 ),

		Field::make( 'textarea', 'subtext', __( 'Intro paragraph', 'jbnewgen' ) )
			->set_required( true )
			->set_help_text( __( 'The paragraph shown beside the pillar visual on the homepage.', 'jbnewgen' ) ),

		Field::make( 'separator', 'hero_photo_sep', __( 'Hero background photo', 'jbnewgen' ) )
			->set_help_text( __( 'The photo behind the dark banner at the top of this category page.', 'jbnewgen' ) ),

		Field::make( 'image', 'hero_image', __( 'Photo', 'jbnewgen' ) )
			->set_help_text( __( 'Leave blank to keep the photo the page ships with. Landscape images around 1600px wide work best.', 'jbnewgen' ) ),

		Field::make( 'checkbox', 'hero_image_hide', __( 'Show no photo', 'jbnewgen' ) )
			->set_default_value( false )
			->set_help_text( __( 'Tick this to remove the background photo entirely.', 'jbnewgen' ) ),

	) );

	jbnewgen_add_seo_tab( $container );

} );
