<?php
/**
 * URL routing for single services.
 *
 * The "service" CPT has no rewrite of its own (see inc/post-types.php) because
 * its real address is nested under its pillar -- /services/{pillar}/{service} --
 * mirroring ../jbnewgen/src/app/(frontend)/services/[pillar]/[service]/page.tsx.
 * A CPT rewrite alone can't express that nesting, so this adds the rule by
 * hand and carries the pillar slug through as a query var purely so
 * single-service.php can validate it and 404 on a mismatched pillar rather
 * than silently rendering the service under the wrong breadcrumb.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	add_rewrite_rule(
		'^services/([^/]+)/([^/]+)/?$',
		'index.php?post_type=service&name=$matches[2]&jb_pillar_slug=$matches[1]',
		'top'
	);

	// The `insight_category` taxonomy (inc/taxonomies.php) registers at
	// priority 6, after the `insight` CPT at priority 5 -- so the CPT's own
	// generated rules (including its `insights/{x}/{y}` attachment-lookup
	// catch-all) land ahead of the taxonomy's rules in $wp_rewrite and win
	// the match first, 404ing every /insights/category/{slug} URL. Adding
	// the same pattern at 'top' priority forces it to be tried first
	// regardless of that ordering.
	add_rewrite_rule(
		'^insights/category/([^/]+)/?$',
		'index.php?insight_category=$matches[1]',
		'top'
	);
} );

add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'jb_pillar_slug';
	return $vars;
} );
