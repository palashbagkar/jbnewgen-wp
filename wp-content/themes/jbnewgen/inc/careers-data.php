<?php
/**
 * Careers and Contact hero copy, read from the Site Settings options screen
 * with the Next.js defaults behind them -- same pattern as jbnewgen_home_view()
 * in inc/home-data.php. heroBackgrounds default verbatim from
 * ../jbnewgen/src/lib/content.ts.
 */

defined( 'ABSPATH' ) || exit;

/**
 * @return array
 */
function jbnewgen_careers_view() {
	$opt = function ( $key ) {
		return function_exists( 'carbon_get_theme_option' ) ? carbon_get_theme_option( $key ) : '';
	};
	$hide = (bool) $opt( 'careers_hero_image_hide' );
	$img_id = (int) $opt( 'careers_hero_image' );
	return array(
		'hero_image' => $hide ? '' : ( $img_id ? wp_get_attachment_image_url( $img_id, 'full' ) : '' ),
		'intro'        => (string) $opt( 'careers_intro' ),
		'rolesEyebrow' => (string) $opt( 'careers_roles_eyebrow' ),
		'rolesTitle'   => (string) $opt( 'careers_roles_title' ),
		'rolesIntro'   => (string) $opt( 'careers_roles_intro' ),
		'address'      => (string) $opt( 'careers_address' ),
	);
}

/**
 * @return array
 */
function jbnewgen_contact_view() {
	$opt = function ( $key ) {
		return function_exists( 'carbon_get_theme_option' ) ? carbon_get_theme_option( $key ) : '';
	};
	$hide = (bool) $opt( 'contact_hero_image_hide' );
	$img_id = (int) $opt( 'contact_hero_image' );
	return array(
		'hero_image' => $hide ? '' : ( $img_id ? wp_get_attachment_image_url( $img_id, 'full' ) : '' ),
	);
}

/**
 * All active, published job openings, ordered for the Careers roles grid.
 *
 * @return WP_Post[]
 */
function jbnewgen_active_jobs() {
	$jobs = get_posts( array(
		'post_type'        => 'job',
		'post_status'      => 'publish',
		'numberposts'      => -1,
		'orderby'          => 'menu_order title',
		'order'            => 'ASC',
		'suppress_filters' => false,
	) );
	return array_values( array_filter( $jobs, function ( $j ) {
		return function_exists( 'carbon_get_post_meta' ) ? (bool) carbon_get_post_meta( $j->ID, 'active' ) : true;
	} ) );
}
