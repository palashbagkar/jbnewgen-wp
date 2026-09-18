<?php
/**
 * Navigation and site-identity data for the public templates.
 *
 * The Next.js app assembled this from src/lib/content.ts (the `site` and `nav`
 * constants) plus getNavPillars() over the CMS. Here the shape is the same but
 * the source differs: pillars and services come from the CPTs, so editing a
 * service in wp-admin reorders the mega menu, which is the whole point of the
 * port.
 *
 * The contact block below is still hardcoded, exactly as it was in content.ts.
 * It is copied verbatim -- phone, emails and socials are real published details
 * and are never to be invented or "tidied". Moving them into Site Settings is
 * part of the content-migration phase; leaving them here now keeps the footer
 * truthful in the meantime.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Published company details. Verbatim from ../jbnewgen/src/lib/content.ts.
 *
 * @return array
 */
function jbnewgen_site_info() {
	return array(
		'name'       => 'JBNewGen',
		'legal_name' => 'JB NewGen Enterprises Private Limited',
		'tagline'    => 'Empowering Digital Future',
		'phone'      => '+91 6362864230',
		'phone_href' => 'tel:+916362864230',
		'email'      => 'sales@jbnewgen.com',
		'socials'    => array(
			array( 'label' => 'LinkedIn',  'href' => 'https://www.linkedin.com/company/jb-newgen-enterprises', 'icon' => 'linkedin' ),
			array( 'label' => 'Facebook',  'href' => 'https://www.facebook.com/profile.php?id=61566375428662', 'icon' => 'facebook' ),
			array( 'label' => 'Instagram', 'href' => 'https://www.instagram.com/jb_newgen/',                   'icon' => 'instagram' ),
		),
	);
}

/**
 * The About dropdown. Static routes, so static entries -- matching
 * aboutChildren in content.ts.
 *
 * @return array
 */
function jbnewgen_about_children() {
	return array(
		array( 'label' => 'The Company', 'href' => home_url( '/about/company' ), 'desc' => 'JB NewGen Enterprises' ),
		array( 'label' => 'Our CEO',     'href' => home_url( '/about/ceo' ),     'desc' => 'Joyjeet Bose · 35+ years experience' ),
		array( 'label' => 'Core Team',   'href' => home_url( '/about/team' ),    'desc' => 'The people behind JB NewGen' ),
	);
}

/**
 * Pillars, each with its services, for the Services mega menu.
 *
 * Services attach to a pillar through a Carbon Fields association, which stores
 * the awkward string "post|pillar|<id>" rather than a plain ID -- so the lookup
 * parses that rather than comparing meta directly. Results are cached for the
 * request because the header and the footer both ask for them.
 *
 * @return array
 */
function jbnewgen_nav_pillars() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$pillars = get_posts( array(
		'post_type'        => 'pillar',
		'post_status'      => 'publish',
		'numberposts'      => -1,
		'orderby'          => 'menu_order title',
		'order'            => 'ASC',
		'suppress_filters' => false,
	) );

	$services = get_posts( array(
		'post_type'        => 'service',
		'post_status'      => 'publish',
		'numberposts'      => -1,
		'orderby'          => 'menu_order title',
		'order'            => 'ASC',
		'suppress_filters' => false,
	) );

	// Bucket services by the pillar they point at, in one pass.
	$by_pillar = array();
	foreach ( $services as $service ) {
		$assoc = function_exists( 'carbon_get_post_meta' )
			? carbon_get_post_meta( $service->ID, 'pillar' )
			: array();
		if ( ! is_array( $assoc ) ) {
			continue;
		}
		foreach ( $assoc as $entry ) {
			$pid = isset( $entry['id'] ) ? (int) $entry['id'] : 0;
			if ( $pid ) {
				$by_pillar[ $pid ][] = $service;
			}
		}
	}

	$out = array();
	foreach ( $pillars as $pillar ) {
		$children = isset( $by_pillar[ $pillar->ID ] ) ? $by_pillar[ $pillar->ID ] : array();
		$out[]    = array(
			'id'       => $pillar->ID,
			'slug'     => $pillar->post_name,
			'short'    => $pillar->post_title,
			'icon'     => function_exists( 'carbon_get_post_meta' ) ? (string) carbon_get_post_meta( $pillar->ID, 'icon' ) : '',
			'url'      => home_url( '/services/' . $pillar->post_name ),
			'services' => array_map(
				function ( $s ) use ( $pillar ) {
					return array(
						'title' => $s->post_title,
						'url'   => home_url( '/services/' . $pillar->post_name . '/' . $s->post_name ),
					);
				},
				$children
			),
		);
	}

	$cache = $out;
	return $cache;
}

/**
 * Insight categories for the Insights dropdown and the footer column.
 *
 * @return array
 */
function jbnewgen_nav_categories() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$terms = get_terms( array(
		'taxonomy'   => 'insight_category',
		'hide_empty' => false,
	) );

	$cache = array();
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$cache[] = array(
				'label' => $term->name,
				'href'  => home_url( '/insights/category/' . $term->slug ),
				'desc'  => $term->description,
			);
		}
	}
	return $cache;
}

/**
 * A logo URL from Site Settings, or '' when none is set.
 *
 * Carbon Fields image fields store an attachment ID.
 *
 * @param string $which logo_white | logo_black | logo_tab
 * @return string
 */
function jbnewgen_logo_url( $which ) {
	if ( ! function_exists( 'carbon_get_theme_option' ) ) {
		return '';
	}
	$id = (int) carbon_get_theme_option( $which );
	if ( ! $id ) {
		return '';
	}
	$src = wp_get_attachment_image_url( $id, 'full' );
	return $src ? $src : '';
}

/**
 * Is this URL the page being viewed? Used to mark the active nav item.
 *
 * @param string $url
 * @return bool
 */
function jbnewgen_nav_is_active( $url ) {
	$current = untrailingslashit( home_url( add_query_arg( array() ) ) );
	$target  = untrailingslashit( $url );
	if ( $target === untrailingslashit( home_url( '/' ) ) ) {
		return is_front_page();
	}
	return $current === $target || 0 === strpos( $current, $target . '/' );
}
