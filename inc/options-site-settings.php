<?php
/**
 * Options page: Site Settings — mirrors ../jbnewgen/src/globals/SiteSettings.ts.
 */

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

add_action( 'carbon_fields_register_fields', function () {

	Container::make( 'theme_options', __( 'Site Settings', 'jbnewgen' ) )
		->set_icon( 'dashicons-admin-generic' )

		->add_tab( __( 'Logo', 'jbnewgen' ), array(

			Field::make( 'image', 'logo_tab', __( 'Tab / favicon logo', 'jbnewgen' ) )
				->set_help_text( __( 'SVG shown as the browser-tab icon (favicon). Square works best.', 'jbnewgen' ) ),

			Field::make( 'image', 'logo_white', __( 'White logo (for dark backgrounds)', 'jbnewgen' ) )
				->set_help_text( __( 'SVG with white text — used on the dark header (over hero) and the footer.', 'jbnewgen' ) ),

			Field::make( 'image', 'logo_black', __( 'Black logo (for light backgrounds)', 'jbnewgen' ) )
				->set_help_text( __( 'SVG with black text — used on the solid white header.', 'jbnewgen' ) ),

			Field::make( 'text', 'logo_alt', __( 'Logo alt text', 'jbnewgen' ) )
				->set_help_text( __( 'Accessible name for the logo image. E.g. "JB NewGen".', 'jbnewgen' ) ),

		) )

		->add_tab( __( 'Header badge', 'jbnewgen' ), array(

			Field::make( 'checkbox', 'header_badge_enabled', __( 'Show the badge', 'jbnewgen' ) )
				->set_default_value( true )
				->set_help_text( __( 'Untick to hide the pill from the header on every page.', 'jbnewgen' ) ),

			Field::make( 'text', 'header_badge_label', __( 'Badge text', 'jbnewgen' ) )
				->set_help_text( __( 'Keep it to one or two words so the ribbon stays uncluttered. Currently "Coming Soon".', 'jbnewgen' ) ),

			Field::make( 'text', 'header_badge_href', __( 'Badge link', 'jbnewgen' ) )
				->set_help_text( __( 'Where clicking the pill goes. Use a path such as /about/company, and add #saas to land on a section of that page.', 'jbnewgen' ) ),

		) )

		->add_tab( __( 'Careers page', 'jbnewgen' ), array(

			Field::make( 'image', 'careers_hero_image', __( 'Hero background photo', 'jbnewgen' ) )
				->set_help_text( __( 'The photo behind the dark banner at the top of /careers. Leave blank to keep the photo the page ships with. Landscape, around 1600px wide.', 'jbnewgen' ) ),

			Field::make( 'checkbox', 'careers_hero_image_hide', __( 'Show no photo', 'jbnewgen' ) )
				->set_default_value( false )
				->set_help_text( __( 'Tick to remove the background photo — the banner falls back to the plain dark panel.', 'jbnewgen' ) ),

			Field::make( 'textarea', 'careers_intro', __( 'Hero paragraph', 'jbnewgen' ) )
				->set_help_text( __( 'The paragraph under the Careers headline — mentions how many roles are open.', 'jbnewgen' ) )
				->set_default_value( "Looking to join JB NewGen? Now it's easier than ever. With Quick Apply, you can explore our 5 current job openings and apply to all relevant positions in just a few clicks." ),

			Field::make( 'text', 'careers_roles_eyebrow', __( 'Open-roles eyebrow', 'jbnewgen' ) )
				->set_help_text( __( 'Small label above the role list. Currently "Open Roles".', 'jbnewgen' ) )
				->set_default_value( 'Open Roles' ),

			Field::make( 'text', 'careers_roles_title', __( 'Open-roles heading', 'jbnewgen' ) )
				->set_help_text( __( 'Currently "5 current openings".', 'jbnewgen' ) )
				->set_default_value( '5 current openings' ),

			Field::make( 'textarea', 'careers_roles_intro', __( 'Open-roles intro', 'jbnewgen' ) )
				->set_default_value( "Every role below is remote-friendly across our India footprint. Apply to all relevant positions in just a few clicks." ),

			Field::make( 'textarea', 'careers_address', __( 'Footer address line', 'jbnewgen' ) )
				->set_help_text( __( 'The single-line address printed at the bottom of the Careers page.', 'jbnewgen' ) )
				->set_default_value( 'JB NewGen Enterprises Pvt. Ltd. · 504 Challenger Tower III, Thakur Village, Kandivali (E), Mumbai 400 101 · support@jbnewgen.com · +91 6362864230' ),

		) )

		->add_tab( __( 'Contact page', 'jbnewgen' ), array(

			Field::make( 'image', 'contact_hero_image', __( 'Hero background photo', 'jbnewgen' ) )
				->set_help_text( __( 'The photo behind the dark banner at the top of /contact. Leave blank to keep the photo the page ships with. Landscape, around 1600px wide.', 'jbnewgen' ) ),

			Field::make( 'checkbox', 'contact_hero_image_hide', __( 'Show no photo', 'jbnewgen' ) )
				->set_default_value( false )
				->set_help_text( __( 'Tick to remove the background photo — the banner falls back to the plain dark panel.', 'jbnewgen' ) ),

		) );

} );
