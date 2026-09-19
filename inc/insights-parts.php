<?php
/**
 * Shared markup for the Insights templates -- ArticleCard, ArticleMeta,
 * ArticleGrid, FeaturedArticle, CategoryTabs (CONTEXT.md §6 "insights/").
 * Ported verbatim from ../jbnewgen/src/components/insights/*.tsx.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fallback icon per category slug, matching Next.js ArticleCard.tsx's
 * `categoryIcon` map, for the placeholder art shown when a post has no cover.
 */
function jbnewgen_category_icon( $slug ) {
	$map = array(
		'business-strategy'      => 'compass',
		'digital-marketing'      => 'megaphone',
		'digital-transformation' => 'cpu',
	);
	return isset( $map[ $slug ] ) ? $map[ $slug ] : 'bulb';
}

/**
 * @param int|WP_Post $post
 */
function jbnewgen_article_meta( $post ) {
	?>
	<div class="flex items-center gap-2.5 text-xs font-medium text-ink-400">
		<span><?php echo esc_html( get_the_date( 'F j, Y', $post ) ); ?></span>
		<span class="h-1 w-1 rounded-full bg-ink-200" aria-hidden="true"></span>
		<span class="inline-flex items-center gap-1"><?php jbnewgen_icon( 'clock', 13 ); ?><?php echo (int) jbnewgen_insight_read_mins( $post ); ?> <?php esc_html_e( 'min read', 'jbnewgen' ); ?></span>
	</div>
	<?php
}

/**
 * @param int|WP_Post $post
 * @param int         $index
 */
function jbnewgen_article_card( $post, $index = 0 ) {
	$post  = get_post( $post );
	$terms = get_the_terms( $post, 'insight_category' );
	$term  = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
	$cover = jbnewgen_insight_cover_url( $post );
	?>
	<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="group flex h-full flex-col overflow-hidden rounded-2xl border border-ink-100 bg-white transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_28px_56px_-30px_rgba(8,21,39,0.45)]" data-reveal data-reveal-delay="<?php echo (int) ( ( $index % 3 ) * 80 ); ?>">
		<div class="relative aspect-[16/10] overflow-hidden bg-ink-100">
			<?php if ( $cover ) : ?>
				<img src="<?php echo esc_url( $cover ); ?>" alt="" class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">
			<?php else : ?>
				<div class="mesh absolute inset-0 opacity-80"></div>
				<div class="dot-grid absolute inset-0 opacity-40"></div>
				<span class="absolute bottom-4 right-5 text-ink-900/10"><?php jbnewgen_icon( $term ? jbnewgen_category_icon( $term->slug ) : 'bulb', 64 ); ?></span>
			<?php endif; ?>
			<span class="absolute left-4 top-4 rounded-[7px] bg-white/90 px-3 py-1 text-xs font-semibold text-ink-700 backdrop-blur"><?php echo esc_html( $term ? $term->name : __( 'Insight', 'jbnewgen' ) ); ?></span>
		</div>
		<div class="flex flex-1 flex-col p-6">
			<div class="mb-3"><?php jbnewgen_article_meta( $post ); ?></div>
			<h3 class="text-lg font-bold leading-snug text-ink-900 transition-colors group-hover:text-flame-600"><?php echo esc_html( get_the_title( $post ) ); ?></h3>
			<p class="mt-2 line-clamp-3 text-sm leading-relaxed text-ink-500"><?php echo esc_html( get_the_excerpt( $post ) ); ?></p>
			<span class="mt-auto inline-flex items-center gap-2 pt-5 text-sm font-semibold text-ink-700"><?php esc_html_e( 'Read article', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowRight', 15, 'text-flame-500 transition-transform group-hover:translate-x-0.5' ); ?></span>
		</div>
	</a>
	<?php
}

/**
 * @param WP_Post $post
 */
function jbnewgen_featured_article( $post ) {
	$terms = get_the_terms( $post, 'insight_category' );
	$term  = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
	$cover = jbnewgen_insight_cover_url( $post );
	?>
	<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="group grid overflow-hidden rounded-2xl border border-ink-100 bg-white transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_40px_80px_-38px_rgba(8,21,39,0.5)] md:grid-cols-2" data-reveal>
		<div class="relative aspect-[16/10] overflow-hidden bg-ink-100 md:aspect-auto md:min-h-[340px]">
			<?php if ( $cover ) : ?>
				<img src="<?php echo esc_url( $cover ); ?>" alt="" class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
			<?php else : ?>
				<div class="mesh absolute inset-0 opacity-90"></div>
				<div class="dot-grid absolute inset-0 opacity-40"></div>
				<span class="absolute bottom-5 right-6 text-ink-900/10"><?php jbnewgen_icon( $term ? jbnewgen_category_icon( $term->slug ) : 'bulb', 120 ); ?></span>
			<?php endif; ?>
			<span class="absolute left-5 top-5 inline-flex items-center gap-1.5 rounded-[7px] bg-flame-500 px-3 py-1 text-xs font-semibold text-white shadow-[0_10px_24px_-10px_rgba(234,131,46,0.8)]"><?php jbnewgen_icon( 'spark', 13 ); ?><?php esc_html_e( 'Latest', 'jbnewgen' ); ?></span>
		</div>
		<div class="flex flex-col justify-center gap-4 p-8 sm:p-10">
			<div class="flex items-center gap-3">
				<span class="rounded-[7px] bg-ink-50 px-3 py-1 text-xs font-semibold text-ink-600"><?php echo esc_html( $term ? $term->name : __( 'Insight', 'jbnewgen' ) ); ?></span>
				<?php jbnewgen_article_meta( $post ); ?>
			</div>
			<h2 class="text-balance text-2xl font-bold leading-tight text-ink-900 transition-colors group-hover:text-flame-600 sm:text-3xl"><?php echo esc_html( get_the_title( $post ) ); ?></h2>
			<p class="line-clamp-3 text-pretty leading-relaxed text-ink-500"><?php echo esc_html( get_the_excerpt( $post ) ); ?></p>
			<span class="mt-1 inline-flex items-center gap-2 text-sm font-semibold text-flame-600"><?php esc_html_e( 'Read the article', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowRight', 16, 'transition-transform group-hover:translate-x-0.5' ); ?></span>
		</div>
	</a>
	<?php
}

/**
 * @param WP_Post[] $posts
 */
function jbnewgen_article_grid( $posts, $title = '', $caption = '', $columns = 3 ) {
	if ( ! $posts ) {
		return;
	}
	?>
	<section class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-16">
		<?php if ( $title || $caption ) : ?>
			<div class="mb-8 flex flex-wrap items-end justify-between gap-3">
				<div>
					<?php if ( $title ) : ?><h2 class="text-xl font-bold tracking-tight text-ink-900 sm:text-2xl"><?php echo esc_html( $title ); ?></h2><?php endif; ?>
					<?php if ( $caption ) : ?><p class="mt-1 text-sm text-ink-500"><?php echo esc_html( $caption ); ?></p><?php endif; ?>
				</div>
				<span class="font-mono text-xs uppercase tracking-widest text-ink-400"><?php echo (int) count( $posts ); ?> <?php echo 1 === count( $posts ) ? esc_html__( 'article', 'jbnewgen' ) : esc_html__( 'articles', 'jbnewgen' ); ?></span>
			</div>
		<?php endif; ?>
		<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 <?php echo 3 === $columns ? 'lg:grid-cols-3' : ''; ?>">
			<?php foreach ( $posts as $jb_i => $jb_p ) : ?>
				<?php jbnewgen_article_card( $jb_p, $jb_i ); ?>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
}

/**
 * @param string $active_slug term slug of the active category, or '' for "All"
 */
function jbnewgen_category_tabs( $active_slug = '', $include_all = true ) {
	$terms = get_terms( array( 'taxonomy' => 'insight_category', 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) ) {
		$terms = array();
	}
	?>
	<section class="mx-auto max-w-7xl px-5 pt-6 sm:px-8">
		<p class="mb-4 font-mono text-xs uppercase tracking-widest text-ink-400"><?php esc_html_e( 'Browse by topic', 'jbnewgen' ); ?></p>
		<div class="flex flex-wrap gap-3">
			<?php if ( $include_all ) : ?>
				<a href="<?php echo esc_url( home_url( '/insights' ) ); ?>" class="inline-flex items-center gap-2 rounded-[7px] border px-4 py-2 text-sm font-semibold transition-all duration-200 <?php echo '' === $active_slug ? 'border-transparent bg-ink-900 text-white' : 'border-ink-200 bg-white text-ink-700 hover:-translate-y-0.5 hover:border-ink-300 hover:text-flame-600'; ?>">
					<?php jbnewgen_icon( 'folder', 16 ); ?><?php esc_html_e( 'All Insights', 'jbnewgen' ); ?>
				</a>
			<?php endif; ?>
			<?php foreach ( $terms as $jb_term ) : ?>
				<?php $jb_active = $jb_term->slug === $active_slug; ?>
				<a href="<?php echo esc_url( home_url( '/insights/category/' . $jb_term->slug ) ); ?>" <?php echo $jb_active ? 'aria-current="page"' : ''; ?> class="group inline-flex items-center gap-2 rounded-[7px] border px-4 py-2 text-sm font-semibold transition-all duration-200 <?php echo $jb_active ? 'border-transparent bg-flame-500 text-white shadow-[0_10px_24px_-12px_rgba(234,131,46,0.8)]' : 'border-ink-200 bg-white text-ink-700 hover:-translate-y-0.5 hover:border-ink-300 hover:text-flame-600'; ?>">
					<?php jbnewgen_icon( jbnewgen_category_icon( $jb_term->slug ), 16 ); ?><?php echo esc_html( $jb_term->name ); ?>
					<span class="rounded-full px-1.5 text-xs font-bold <?php echo $jb_active ? 'bg-white/20 text-white' : 'bg-ink-50 text-ink-400 group-hover:text-flame-600'; ?>"><?php echo (int) $jb_term->count; ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
}
