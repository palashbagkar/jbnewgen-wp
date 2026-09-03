<?php
/**
 * Meta fields for the "service" CPT — mirrors ../jbnewgen/src/collections/Services.ts.
 *
 * Payload "title" -> native post_title.
 * Payload "slug"  -> native post_name.
 * Payload "order" -> native menu_order (page-attributes support).
 */

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

add_action( 'carbon_fields_register_fields', function () {

	$container = Container::make( 'post_meta', __( 'Service Details', 'jbnewgen' ) )
		->where( 'post_type', '=', 'service' );

	$container->add_fields( array(

		Field::make( 'association', 'pillar', __( 'Service category', 'jbnewgen' ) )
			->set_types( array(
				array( 'type' => 'post', 'post_type' => 'pillar', 'label' => __( 'Pillar', 'jbnewgen' ) ),
			) )
			->set_max( 1 )
			->set_required( true )
			->set_help_text( __( 'Which pillar this service belongs to.', 'jbnewgen' ) ),

		Field::make( 'textarea', 'summary', __( 'Summary', 'jbnewgen' ) )
			->set_required( true )
			->set_help_text( __( 'One-line summary shown in the category service list and under the page title.', 'jbnewgen' ) ),

		Field::make( 'textarea', 'lead', __( 'Lead', 'jbnewgen' ) )
			->set_help_text( __( 'Optional emphasised opening sentence on the service page.', 'jbnewgen' ) ),

		Field::make( 'complex', 'body', __( 'Description paragraphs', 'jbnewgen' ) )
			->set_help_text( __( 'The main write-up. One entry per paragraph.', 'jbnewgen' ) )
			->add_fields( array(
				Field::make( 'textarea', 'text', __( 'Paragraph', 'jbnewgen' ) )->set_required( true ),
			) )
			->set_header_template( '<%- text %>' ),

		Field::make( 'complex', 'subsections', __( 'Sub-service cards', 'jbnewgen' ) )
			->set_help_text( __( 'Optional nested cards (used by Expert Advisory for its functional specialists).', 'jbnewgen' ) )
			->add_fields( array(
				Field::make( 'text', 'title', __( 'Title', 'jbnewgen' ) )->set_required( true ),
				Field::make( 'textarea', 'body', __( 'Body', 'jbnewgen' ) )->set_required( true ),
			) )
			->set_header_template( '<%- title %>' ),

		Field::make( 'complex', 'tags', __( 'Focus areas', 'jbnewgen' ) )
			->set_help_text( __( 'The chips shown on the category list and at the foot of the service page.', 'jbnewgen' ) )
			->add_fields( array(
				Field::make( 'text', 'label', __( 'Focus area', 'jbnewgen' ) )->set_required( true ),
			) )
			->set_header_template( '<%- label %>' ),

	) );

	jbnewgen_add_seo_tab( $container );

} );
