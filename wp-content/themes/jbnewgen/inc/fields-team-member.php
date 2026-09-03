<?php
/**
 * Meta fields for the "team_member" CPT — mirrors ../jbnewgen/src/collections/TeamMembers.ts.
 *
 * Payload "name"  -> native post_title.
 * Payload "order" -> native menu_order (page-attributes support).
 */

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

add_action( 'carbon_fields_register_fields', function () {

	Container::make( 'post_meta', __( 'Team Member Details', 'jbnewgen' ) )
		->where( 'post_type', '=', 'team_member' )
		->add_fields( array(

			Field::make( 'text', 'role', __( 'Title', 'jbnewgen' ) )
				->set_required( true )
				->set_help_text( __( 'Shown in orange caps under the name, e.g. "Technical Advisor".', 'jbnewgen' ) ),

			Field::make( 'textarea', 'bio', __( 'Short bio', 'jbnewgen' ) )
				->set_required( true )
				->set_help_text( __( 'The paragraph on the card. Two or three sentences reads best.', 'jbnewgen' ) ),

			Field::make( 'image', 'photo', __( 'Photo', 'jbnewgen' ) )
				->set_help_text( __( 'Portrait orientation. The card crops to a 27:40 frame from the top, so leave headroom above the face.', 'jbnewgen' ) ),

			Field::make( 'text', 'linkedin_url', __( 'LinkedIn profile URL', 'jbnewgen' ) )
				->set_attribute( 'type', 'url' )
				->set_help_text( __( 'Full address, e.g. https://www.linkedin.com/in/joyjeet-bose. Leave blank and the "+" badge simply will not appear on this card.', 'jbnewgen' ) ),

		) );

} );
