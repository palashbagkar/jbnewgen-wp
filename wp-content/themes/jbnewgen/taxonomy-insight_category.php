<?php
/**
 * Insight category archive — /insights/category/{cat}. Ported from
 * ../jbnewgen/src/app/(frontend)/insights/category/[cat]/page.tsx.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$jb_term  = get_queried_object();
$jb_posts = get_posts( array(
	'post_type'        => 'insight',
	'post_status'      => 'publish',
	'numberposts'      => -1,
	'orderby'          => 'date',
	'order'            => 'DESC',
	'suppress_filters' => false,
	'tax_query'        => array( array(
		'taxonomy' => 'insight_category',
		'field'    => 'term_id',
		'terms'    => $jb_term->term_id,
	) ),
) );

jbnewgen_page_header( array(
	'eyebrow' => __( 'Insights', 'jbnewgen' ),
	'title'   => $jb_term->name,
	'trail'   => array(
		array( 'label' => __( 'Home', 'jbnewgen' ), 'href' => '/' ),
		array( 'label' => __( 'Insights', 'jbnewgen' ), 'href' => '/insights' ),
		array( 'label' => $jb_term->name, 'href' => '/insights/category/' . $jb_term->slug ),
	),
	/* translators: 1: article count, 2: category name */
	'note'    => sprintf( _n( '%1$d article in %2$s.', '%1$d articles in %2$s.', count( $jb_posts ), 'jbnewgen' ), count( $jb_posts ), $jb_term->name ),
) );
?>

<?php jbnewgen_article_grid( $jb_posts, '', '', 3 ); ?>

<?php jbnewgen_category_tabs( $jb_term->slug, true ); ?>

<div class="pt-6"></div>
<?php jbnewgen_next_step(); ?>

<?php get_footer(); ?>
