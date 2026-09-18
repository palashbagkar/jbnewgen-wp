<?php
/**
 * Fallback template.
 *
 * WordPress falls back here whenever nothing more specific matches, so this is
 * the last line of defence rather than a page anyone should routinely land on.
 * As the specific templates land -- front-page, single-service, archive-insight
 * and the rest -- this handles less and less.
 *
 * Until then it renders the loop plainly, so every route returns something
 * readable instead of a blank page.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="mx-auto max-w-7xl px-5 py-16 sm:px-8">

	<?php if ( have_posts() ) : ?>

		<?php if ( ! is_singular() ) : ?>
			<header class="mb-10">
				<h1 class="font-display text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl">
					<?php
					if ( is_search() ) {
						/* translators: %s: search query */
						printf( esc_html__( 'Results for &ldquo;%s&rdquo;', 'jbnewgen' ), esc_html( get_search_query() ) );
					} elseif ( is_archive() ) {
						echo esc_html( wp_strip_all_tags( get_the_archive_title() ) );
					} else {
						echo esc_html( get_bloginfo( 'name' ) );
					}
					?>
				</h1>
				<?php if ( is_archive() && get_the_archive_description() ) : ?>
					<div class="mt-3 max-w-2xl text-ink-600"><?php echo wp_kses_post( get_the_archive_description() ); ?></div>
				<?php endif; ?>
			</header>
		<?php endif; ?>

		<div class="<?php echo is_singular() ? '' : 'grid gap-6 sm:grid-cols-2 lg:grid-cols-3'; ?>">
			<?php while ( have_posts() ) : ?>
				<?php the_post(); ?>

				<?php if ( is_singular() ) : ?>
					<article <?php post_class( 'mx-auto max-w-3xl' ); ?>>
						<h1 class="font-display text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl"><?php the_title(); ?></h1>
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="mt-6 overflow-hidden rounded-[7px]"><?php the_post_thumbnail( 'large', array( 'class' => 'w-auto' ) ); ?></div>
						<?php endif; ?>
						<div class="jb-prose mt-8 text-ink-700"><?php the_content(); ?></div>
					</article>
				<?php else : ?>
					<article <?php post_class( 'card flex flex-col overflow-hidden' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>" class="block overflow-hidden"><?php the_post_thumbnail( 'medium_large', array( 'class' => 'w-auto' ) ); ?></a>
						<?php endif; ?>
						<div class="flex flex-1 flex-col p-5">
							<h2 class="font-display text-lg font-semibold leading-snug text-ink-900">
								<a href="<?php the_permalink(); ?>" class="transition-colors hover:text-flame-600"><?php the_title(); ?></a>
							</h2>
							<p class="mt-2 text-sm leading-relaxed text-ink-600"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
							<a href="<?php the_permalink(); ?>" class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-flame-600 hover:underline">
								<?php esc_html_e( 'Read more', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowRight', 15 ); ?>
							</a>
						</div>
					</article>
				<?php endif; ?>

			<?php endwhile; ?>
		</div>

		<?php
		the_posts_pagination( array(
			'class'     => 'mt-12 flex items-center justify-center gap-2 text-sm',
			'mid_size'  => 1,
			'prev_text' => esc_html__( 'Previous', 'jbnewgen' ),
			'next_text' => esc_html__( 'Next', 'jbnewgen' ),
		) );
		?>

	<?php else : ?>

		<div class="mx-auto max-w-xl py-16 text-center">
			<p class="eyebrow justify-center"><?php esc_html_e( 'Nothing here yet', 'jbnewgen' ); ?></p>
			<h1 class="mt-3 font-display text-3xl font-bold tracking-tight text-ink-900"><?php esc_html_e( 'No content found', 'jbnewgen' ); ?></h1>
			<p class="mt-3 text-ink-600"><?php esc_html_e( 'Try a search, or head back to the homepage.', 'jbnewgen' ); ?></p>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mt-6 inline-flex rounded-[7px] bg-flame-500 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-flame-600"><?php esc_html_e( 'Go home', 'jbnewgen' ); ?></a>
		</div>

	<?php endif; ?>
</div>

<?php
get_footer();
