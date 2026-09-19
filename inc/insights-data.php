<?php
/**
 * Small read helpers for the Insights templates. The content itself lives on
 * the `insight` CPT posts (seeded by .wp-local/seed-content.php from
 * ../jbnewgen/src/lib/content.ts `articles` + insightsBodies.ts) — this file
 * only smooths over the two meta fields inc/fields-insight.php adds on top of
 * WordPress's native title/content/excerpt/date/category.
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param int|WP_Post $post
 * @return string attachment URL, or '' when no cover is set
 */
function jbnewgen_insight_cover_url( $post ) {
	$id = get_post_thumbnail_id( $post );
	if ( $id ) {
		$src = wp_get_attachment_image_url( $id, 'medium_large' );
		if ( $src ) {
			return $src;
		}
	}
	$meta_id = function_exists( 'carbon_get_post_meta' ) ? (int) carbon_get_post_meta( get_post( $post )->ID, 'cover_image' ) : 0;
	return $meta_id ? (string) wp_get_attachment_image_url( $meta_id, 'medium_large' ) : '';
}

/**
 * @param int|WP_Post $post
 * @return int
 */
function jbnewgen_insight_read_mins( $post ) {
	$mins = function_exists( 'carbon_get_post_meta' ) ? (int) carbon_get_post_meta( get_post( $post )->ID, 'read_mins' ) : 0;
	return $mins > 0 ? $mins : 5;
}
