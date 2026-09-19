<?php
/**
 * Single insight article — /insights/{slug}. Ported from
 * ../jbnewgen/src/app/(frontend)/insights/[slug]/page.tsx and
 * components/insights/ArticleBody.tsx.
 *
 * Renders from inc/data/articleBodies.php (the exact source
 * ../jbnewgen/src/lib/insightsBodies.ts) rather than post_content, so section
 * headings/lead-paragraph styling matches ArticleBody.tsx precisely.
 * post_content still holds the same copy as plain HTML for feeds/search.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$jb_post_id = get_the_ID();
	$jb_slug    = get_post_field( 'post_name', $jb_post_id );
	$jb_terms   = get_the_terms( $jb_post_id, 'insight_category' );
	$jb_term    = ( $jb_terms && ! is_wp_error( $jb_terms ) ) ? $jb_terms[0] : null;
	$jb_cover   = jbnewgen_insight_cover_url( $jb_post_id );

	$jb_trail = array(
		array( 'label' => __( 'Home', 'jbnewgen' ), 'href' => '/' ),
		array( 'label' => __( 'Insights', 'jbnewgen' ), 'href' => '/insights' ),
	);
	if ( $jb_term ) {
		$jb_trail[] = array( 'label' => $jb_term->name, 'href' => '/insights/category/' . $jb_term->slug );
	}
	$jb_trail[] = array( 'label' => __( 'Article', 'jbnewgen' ), 'href' => '/insights/' . $jb_slug );

	jbnewgen_page_header( array(
		'eyebrow' => __( 'Insights · Article', 'jbnewgen' ),
		'title'   => get_the_title(),
		'trail'   => $jb_trail,
		'note'    => get_the_excerpt(),
	) );
	?>

	<div class="mx-auto flex max-w-5xl flex-wrap items-center gap-3 px-5 pt-8 sm:px-8">
		<?php if ( $jb_term ) : ?>
			<a href="<?php echo esc_url( home_url( '/insights/category/' . $jb_term->slug ) ); ?>" class="inline-flex items-center gap-1.5 rounded-[7px] bg-flame-500/10 px-3 py-1.5 text-sm font-semibold text-flame-700 ring-1 ring-inset ring-flame-500/20 transition-colors hover:bg-flame-500/15"><?php jbnewgen_icon( 'folder', 15 ); ?><?php echo esc_html( $jb_term->name ); ?></a>
		<?php endif; ?>
		<span class="inline-flex items-center rounded-[7px] bg-white px-3 py-1.5 ring-1 ring-inset ring-ink-100"><?php jbnewgen_article_meta( $jb_post_id ); ?></span>
	</div>

	<?php if ( $jb_cover ) : ?>
		<div class="mx-auto max-w-5xl px-5 pt-12 sm:px-8 sm:pt-16">
			<div class="relative aspect-[16/8] overflow-hidden rounded-2xl border border-ink-100 bg-ink-100">
				<img src="<?php echo esc_url( $jb_cover ); ?>" alt="" class="absolute inset-0 h-full w-full object-cover">
			</div>
		</div>
	<?php endif; ?>

	<article class="py-14 sm:py-16">
		<div class="mx-auto max-w-[46rem] space-y-5 px-5 sm:px-8">
			<?php
			$jb_bodies   = include get_template_directory() . '/inc/data/articleBodies.php';
			$jb_sections = isset( $jb_bodies[ $jb_slug ] ) ? $jb_bodies[ $jb_slug ] : array();
			foreach ( $jb_sections as $jb_i => $jb_section ) :
				?>
				<section class="space-y-5">
					<?php if ( ! empty( $jb_section['heading'] ) ) : ?>
						<?php if ( isset( $jb_section['level'] ) && 3 === (int) $jb_section['level'] ) : ?>
							<h3 class="pt-4 text-xl font-bold tracking-tight text-ink-900"><?php echo esc_html( $jb_section['heading'] ); ?></h3>
						<?php else : ?>
							<h2 class="flex items-center gap-3 pt-8 text-2xl font-bold tracking-tight text-ink-900"><span class="h-5 w-1.5 shrink-0 rounded-full bg-flame-500" aria-hidden="true"></span><?php echo esc_html( $jb_section['heading'] ); ?></h2>
						<?php endif; ?>
					<?php endif; ?>
					<?php
					$jb_lead = 0 === $jb_i;
					foreach ( $jb_section['paragraphs'] as $jb_p ) :
						if ( 0 === strpos( $jb_p, '• ' ) ) :
							?>
							<ul class="my-5 space-y-2.5 pl-1">
								<li class="flex gap-3 text-ink-600">
									<span class="mt-2.5 h-1.5 w-1.5 shrink-0 rounded-full bg-flame-500" aria-hidden="true"></span>
									<span class="leading-relaxed"><?php echo esc_html( substr( $jb_p, 2 ) ); ?></span>
								</li>
							</ul>
							<?php
						else :
							?>
							<p class="<?php echo $jb_lead ? 'text-lg leading-relaxed text-ink-700' : 'text-[1.02rem] leading-[1.8] text-ink-600'; ?>"><?php echo esc_html( $jb_p ); ?></p>
							<?php
						endif;
					endforeach;
					?>
				</section>
			<?php endforeach; ?>
		</div>
	</article>

	<?php
	$jb_all = get_posts( array(
		'post_type'        => 'insight',
		'post_status'      => 'publish',
		'numberposts'      => -1,
		'exclude'          => array( $jb_post_id ),
		'suppress_filters' => false,
	) );
	$jb_same  = array();
	$jb_other = array();
	foreach ( $jb_all as $jb_a ) {
		$jb_a_terms = get_the_terms( $jb_a, 'insight_category' );
		$jb_a_term  = ( $jb_a_terms && ! is_wp_error( $jb_a_terms ) ) ? $jb_a_terms[0] : null;
		if ( $jb_term && $jb_a_term && $jb_a_term->term_id === $jb_term->term_id ) {
			$jb_same[] = $jb_a;
		} else {
			$jb_other[] = $jb_a;
		}
	}
	$jb_related = array_slice( array_merge( $jb_same, $jb_other ), 0, 3 );
	?>
	<div class="border-t border-ink-100">
		<?php jbnewgen_article_grid( $jb_related, __( 'Keep reading', 'jbnewgen' ), __( 'More from the JB NewGen desk', 'jbnewgen' ), 3 ); ?>
	</div>

	<?php jbnewgen_next_step(); ?>

<?php endwhile; ?>

<?php get_footer(); ?>
