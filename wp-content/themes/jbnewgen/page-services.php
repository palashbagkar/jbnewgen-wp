<?php
/**
 * Template Name: Services (hub)
 *
 * Ported from ../jbnewgen/src/app/(frontend)/services/page.tsx.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$jb_pillars  = jbnewgen_nav_pillars();
$jb_sub_count = 0;
foreach ( $jb_pillars as $jb_p ) {
	$jb_sub_count += count( $jb_p['services'] );
}

jbnewgen_page_header( array(
	'eyebrow' => __( 'Services', 'jbnewgen' ),
	'title'   => __( 'What we do', 'jbnewgen' ),
	'trail'   => array(
		array( 'label' => __( 'Home', 'jbnewgen' ), 'href' => '/' ),
		array( 'label' => __( 'Services', 'jbnewgen' ), 'href' => '/services' ),
	),
	/* translators: 1: pillar count, 2: sub-service count */
	'note'    => sprintf( __( 'Hub page. %1$d service pillars and %2$d sub-services - each with its own page.', 'jbnewgen' ), count( $jb_pillars ), $jb_sub_count ),
) );

$jb_items = array();
foreach ( $jb_pillars as $jb_p ) {
	$jb_items[] = array(
		'label' => $jb_p['short'],
		'href'  => $jb_p['url'],
		/* translators: %d: number of services in this pillar */
		'desc'  => sprintf( _n( '%d service', '%d services', count( $jb_p['services'] ), 'jbnewgen' ), count( $jb_p['services'] ) ),
		'icon'  => $jb_p['icon'],
	);
}
jbnewgen_nav_grid( array(
	'title'   => __( 'Service pillars', 'jbnewgen' ),
	'caption' => __( 'Open a pillar to see its sub-services', 'jbnewgen' ),
	'columns' => 4,
	'items'   => $jb_items,
) );

jbnewgen_next_step();

get_footer();
