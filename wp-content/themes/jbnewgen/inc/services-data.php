<?php
/**
 * Static per-pillar copy that has no Carbon Fields home yet: hero framing,
 * the challenge/why/features/steps blocks, and the CTA. Ported verbatim from
 * the `pillars` array in ../jbnewgen/src/lib/content.ts (see inc/data/pillars.php,
 * machine-extracted from that file so the copy is byte-identical). full_title,
 * icon, blurb, subtext and hero_image DO live in Carbon Fields (inc/fields-pillar.php)
 * because those are the fields Phase 4 already modelled -- this file is only
 * the remainder, same split as inc/home-data.php.
 */

defined( 'ABSPATH' ) || exit;

/**
 * The full static pillar record (hero/challenge/servicesIntro/why/features/steps/cta),
 * keyed by slug. Does not include `services` -- those are real `service` CPT
 * posts now (seeded by .wp-local/seed-content.php), fetched with jbnewgen_pillar_services().
 *
 * @param string $slug
 * @return array|null
 */
function jbnewgen_pillar_static( $slug ) {
	static $all = null;
	if ( null === $all ) {
		$all = array();
		foreach ( include get_template_directory() . '/inc/data/pillars.php' as $p ) {
			$all[ $p['slug'] ] = $p;
		}
	}
	return isset( $all[ $slug ] ) ? $all[ $slug ] : null;
}

/**
 * Services belonging to one pillar, as real published `service` posts,
 * ordered the same way the mega menu orders them.
 *
 * @param int $pillar_id
 * @return WP_Post[]
 */
function jbnewgen_pillar_services( $pillar_id ) {
	$services = get_posts( array(
		'post_type'        => 'service',
		'post_status'      => 'publish',
		'numberposts'      => -1,
		'orderby'          => 'menu_order title',
		'order'            => 'ASC',
		'suppress_filters' => false,
	) );
	return array_values( array_filter( $services, function ( $s ) use ( $pillar_id ) {
		$assoc = function_exists( 'carbon_get_post_meta' ) ? carbon_get_post_meta( $s->ID, 'pillar' ) : array();
		foreach ( (array) $assoc as $entry ) {
			if ( isset( $entry['id'] ) && (int) $entry['id'] === (int) $pillar_id ) {
				return true;
			}
		}
		return false;
	} ) );
}

/**
 * The pillar a service belongs to (its first/only association), or null.
 *
 * @param int $service_id
 * @return WP_Post|null
 */
function jbnewgen_service_pillar( $service_id ) {
	$assoc = function_exists( 'carbon_get_post_meta' ) ? carbon_get_post_meta( $service_id, 'pillar' ) : array();
	foreach ( (array) $assoc as $entry ) {
		if ( ! empty( $entry['id'] ) ) {
			$post = get_post( (int) $entry['id'] );
			if ( $post ) {
				return $post;
			}
		}
	}
	return null;
}
