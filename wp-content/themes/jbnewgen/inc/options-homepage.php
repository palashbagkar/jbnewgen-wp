<?php
/**
 * Options page: Homepage — mirrors ../jbnewgen/src/globals/Homepage.ts.
 * Default values copied verbatim from the Payload field defaults (the live copy).
 */

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

add_action( 'carbon_fields_register_fields', function () {

	Container::make( 'theme_options', __( 'Homepage', 'jbnewgen' ) )
		->set_icon( 'dashicons-admin-home' )

		->add_tab( __( 'Hero', 'jbnewgen' ), array(

			Field::make( 'text', 'hero_heading_line_1', __( 'Headline — line 1', 'jbnewgen' ) )
				->set_default_value( 'India is your next big market.' )
				->set_help_text( __( 'Bold white line. Keep it short — it sets the width of the block.', 'jbnewgen' ) ),

			Field::make( 'text', 'hero_heading_line_2', __( 'Headline — line 2 (orange italic)', 'jbnewgen' ) )
				->set_default_value( 'We make the entry seamless.' )
				->set_help_text( __( 'Rendered in the orange serif italic underneath line 1.', 'jbnewgen' ) ),

			Field::make( 'textarea', 'hero_intro', __( 'Intro paragraph', 'jbnewgen' ) )
				->set_default_value( 'Good planning is the key to success. We help US/EU/Global Startups establish and scale in India.' ),

			Field::make( 'image', 'hero_image', __( 'Background image', 'jbnewgen' ) )
				->set_help_text( __( 'Leave blank to keep the current background photo.', 'jbnewgen' ) ),

			Field::make( 'text', 'hero_cta_primary_label', __( 'Primary button — text', 'jbnewgen' ) )
				->set_default_value( 'Get a quote now' )
				->set_width( 50 ),

			Field::make( 'text', 'hero_cta_primary_href', __( 'Primary button — link', 'jbnewgen' ) )
				->set_default_value( '/quote' )
				->set_width( 50 ),

			Field::make( 'text', 'hero_cta_secondary_label', __( 'Second button — text', 'jbnewgen' ) )
				->set_default_value( 'See what we offer' )
				->set_width( 50 ),

			Field::make( 'text', 'hero_cta_secondary_href', __( 'Second button — link', 'jbnewgen' ) )
				->set_default_value( '/services' )
				->set_width( 50 ),

		) )

		->add_tab( __( 'Proof bar', 'jbnewgen' ), array(

			Field::make( 'text', 'proof_eyebrow', __( 'Small label above the numbers', 'jbnewgen' ) )
				->set_default_value( 'Founders marker connect and credentials - Distribution proven at scale' ),

			Field::make( 'complex', 'proof_stats', __( 'Statistics', 'jbnewgen' ) )
				->set_min( 1 )
				->set_max( 4 )
				->set_help_text( __( 'Four is the designed maximum — the row is built as a 4-column grid.', 'jbnewgen' ) )
				->add_fields( array(
					Field::make( 'text', 'stat_value', __( 'Number', 'jbnewgen' ) )
						->set_required( true )
						->set_help_text( __( 'e.g. "35+", "7,000+", "200-500%". This is the part that counts up.', 'jbnewgen' ) ),
					Field::make( 'text', 'label', __( 'Caption', 'jbnewgen' ) )->set_required( true ),
					Field::make( 'text', 'sub', __( 'Small print under the caption', 'jbnewgen' ) ),
				) )
				->set_header_template( '<%- stat_value %> — <%- label %>' ),

		) );

} );
