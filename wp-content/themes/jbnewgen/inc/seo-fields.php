<?php
/**
 * Shared SEO field group, mirrored from ../jbnewgen/src/collections/seoFields.ts.
 * Attach with jbnewgen_add_seo_tab( $container ) inside a Carbon Fields Container definition.
 */

function jbnewgen_seo_fields() {
	return array(
		\Carbon_Fields\Field\Field::make( 'text', 'seo_title', __( 'Search title', 'jbnewgen' ) )
			->set_help_text( __( 'Google shows roughly 60 characters. Leave blank to use this page\'s own title.', 'jbnewgen' ) )
			->set_attribute( 'maxLength', 70 ),

		\Carbon_Fields\Field\Field::make( 'textarea', 'seo_description', __( 'Search description', 'jbnewgen' ) )
			->set_help_text( __( 'The grey summary under the blue link in Google. Around 155 characters shows in full. Leave blank to use the page summary.', 'jbnewgen' ) )
			->set_attribute( 'maxLength', 180 ),

		\Carbon_Fields\Field\Field::make( 'image', 'seo_image', __( 'Social share image', 'jbnewgen' ) )
			->set_help_text( __( 'Shown when the page is shared on LinkedIn, WhatsApp or X. Landscape, ideally 1200x630.', 'jbnewgen' ) ),
	);
}

/**
 * Adds a "Search engine listing (SEO)" tab to a Carbon Fields post-meta container.
 *
 * @param \Carbon_Fields\Container\Post_Meta_Container $container
 */
function jbnewgen_add_seo_tab( $container ) {
	$container->add_tab( __( 'SEO', 'jbnewgen' ), jbnewgen_seo_fields() );
}
