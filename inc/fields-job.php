<?php
/**
 * Meta fields for the "job" CPT — mirrors ../jbnewgen/src/collections/Jobs.ts.
 *
 * Payload "title" -> native post_title.
 * Payload "order" -> native menu_order (page-attributes support).
 */

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

add_action( 'carbon_fields_register_fields', function () {

	Container::make( 'post_meta', __( 'Job Details', 'jbnewgen' ) )
		->where( 'post_type', '=', 'job' )
		->add_fields( array(

			Field::make( 'text', 'locations', __( 'Locations', 'jbnewgen' ) )
				->set_required( true )
				->set_help_text( __( 'e.g. "Bangalore - Chennai - Hyderabad - Mumbai - DNCR - Kolkata".', 'jbnewgen' ) ),

			Field::make( 'text', 'work_type', __( 'Work type', 'jbnewgen' ) )
				->set_required( true )
				->set_default_value( 'Remote' )
				->set_help_text( __( 'The badge on the role card, e.g. "Remote".', 'jbnewgen' ) )
				->set_width( 50 ),

			Field::make( 'select', 'icon', __( 'Icon', 'jbnewgen' ) )
				->set_options( jbnewgen_icon_options() )
				->set_default_value( 'briefcase' )
				->set_required( true )
				->set_width( 50 ),

			Field::make( 'textarea', 'body', __( 'Role description', 'jbnewgen' ) )
				->set_required( true ),

			Field::make( 'checkbox', 'active', __( 'Listed', 'jbnewgen' ) )
				->set_default_value( true )
				->set_help_text( __( 'Show this role on the Careers page.', 'jbnewgen' ) ),

		) );

} );
