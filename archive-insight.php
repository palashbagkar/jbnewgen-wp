<?php
/**
 * Insights hub — /insights. Ported from
 * ../jbnewgen/src/app/(frontend)/insights/page.tsx.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$jb_posts = get_posts( array(
	'post_type'        => 'insight',
	'post_status'      => 'publish',
	'numberposts'      => -1,
	'orderby'          => 'date',
	'order'            => 'DESC',
	'suppress_filters' => false,
) );
$jb_featured = $jb_posts ? array_shift( $jb_posts ) : null;

jbnewgen_page_header( array(
	'eyebrow' => __( 'Insights', 'jbnewgen' ),
	'title'   => __( 'Insights', 'jbnewgen' ),
	'trail'   => array(
		array( 'label' => __( 'Home', 'jbnewgen' ), 'href' => '/' ),
		array( 'label' => __( 'Insights', 'jbnewgen' ), 'href' => '/insights' ),
	),
	'note'    => __( 'Articles on business strategy, digital marketing, and digital transformation.', 'jbnewgen' ),
) );
?>

<?php if ( $jb_featured ) : ?>
	<section class="mx-auto max-w-7xl px-5 pt-12 sm:px-8 sm:pt-16">
		<?php jbnewgen_featured_article( $jb_featured ); ?>
	</section>
<?php endif; ?>

<?php jbnewgen_category_tabs( '', false ); ?>

<?php jbnewgen_article_grid( $jb_posts, __( 'Latest articles', 'jbnewgen' ), '', 3 ); ?>

<?php jbnewgen_next_step( __( "Have a question these articles didn't answer?", 'jbnewgen' ) ); ?>

<?php get_footer(); ?>
