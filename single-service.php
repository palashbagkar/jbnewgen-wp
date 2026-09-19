<?php
/**
 * Single service page — /services/{pillar}/{service}, reached via the custom
 * rewrite rule in inc/rewrites.php (the "service" CPT has no rewrite of its
 * own since its address is nested under its pillar).
 *
 * Ported from ../jbnewgen/src/app/(frontend)/services/[pillar]/[service]/page.tsx
 * and components/services/ServiceDetail.tsx.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$jb_service_id = get_the_ID();
	$jb_pillar     = jbnewgen_service_pillar( $jb_service_id );

	if ( ! $jb_pillar ) {
		get_footer();
		return;
	}

	$jb_pillar_short = get_the_title( $jb_pillar );
	$jb_pillar_icon  = function_exists( 'carbon_get_post_meta' ) ? (string) carbon_get_post_meta( $jb_pillar->ID, 'icon' ) : '';
	$jb_summary      = function_exists( 'carbon_get_post_meta' ) ? (string) carbon_get_post_meta( $jb_service_id, 'summary' ) : '';
	$jb_lead         = function_exists( 'carbon_get_post_meta' ) ? (string) carbon_get_post_meta( $jb_service_id, 'lead' ) : '';
	$jb_body_rows    = function_exists( 'carbon_get_post_meta' ) ? (array) carbon_get_post_meta( $jb_service_id, 'body' ) : array();
	$jb_sub_rows     = function_exists( 'carbon_get_post_meta' ) ? (array) carbon_get_post_meta( $jb_service_id, 'subsections' ) : array();
	$jb_tag_rows     = function_exists( 'carbon_get_post_meta' ) ? (array) carbon_get_post_meta( $jb_service_id, 'tags' ) : array();

	$jb_trail = array(
		array( 'label' => __( 'Home', 'jbnewgen' ), 'href' => '/' ),
		array( 'label' => __( 'Services', 'jbnewgen' ), 'href' => '/services' ),
		array( 'label' => $jb_pillar_short, 'href' => '/services/' . $jb_pillar->post_name ),
		array( 'label' => get_the_title(), 'href' => '/services/' . $jb_pillar->post_name . '/' . get_post_field( 'post_name', $jb_service_id ) ),
	);
	?>

	<header class="relative overflow-hidden bg-ink-950 text-white">
		<div class="mesh pointer-events-none absolute inset-0 opacity-45"></div>
		<div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-[7px] bg-flame-500/12 blur-3xl"></div>

		<div class="relative mx-auto max-w-5xl px-5 py-20 sm:px-8 sm:py-24">
			<nav aria-label="<?php esc_attr_e( 'Breadcrumb', 'jbnewgen' ); ?>" class="mb-6">
				<ol class="flex flex-wrap items-center gap-1.5 text-sm text-white/70">
					<?php foreach ( $jb_trail as $jb_i => $jb_c ) : ?>
						<?php $jb_last = $jb_i === count( $jb_trail ) - 1; ?>
						<li class="flex items-center gap-1.5">
							<?php if ( $jb_i > 0 ) : ?><?php jbnewgen_icon( 'chevronDown', 14, '-rotate-90 text-white/40' ); ?><?php endif; ?>
							<?php if ( $jb_last ) : ?>
								<span class="font-medium text-white" aria-current="page"><?php echo esc_html( $jb_c['label'] ); ?></span>
							<?php else : ?>
								<a href="<?php echo esc_url( jbnewgen_href( $jb_c['href'] ) ); ?>" class="text-white/70 transition-colors hover:text-flame-300"><?php echo esc_html( $jb_c['label'] ); ?></a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ol>
			</nav>

			<h1 class="text-balance text-4xl font-bold leading-[1.08] tracking-tight text-flame-400 sm:text-5xl"><?php the_title(); ?></h1>
			<?php if ( $jb_summary ) : ?><p class="mt-5 text-lg text-ink-200"><?php echo esc_html( $jb_summary ); ?></p><?php endif; ?>
		</div>
	</header>

	<section class="bg-white py-16 sm:py-20">
		<div class="mx-auto max-w-5xl px-5 sm:px-8">
			<?php if ( $jb_lead ) : ?><p class="text-xl font-medium leading-relaxed text-ink-800"><?php echo esc_html( $jb_lead ); ?></p><?php endif; ?>

			<div class="mt-6 space-y-5">
				<?php foreach ( $jb_body_rows as $jb_row ) : ?>
					<p class="leading-relaxed text-ink-600"><?php echo esc_html( $jb_row['text'] ); ?></p>
				<?php endforeach; ?>
			</div>

			<?php if ( $jb_sub_rows ) : ?>
				<div class="mt-10 grid gap-4 sm:grid-cols-2">
					<?php foreach ( $jb_sub_rows as $jb_sub ) : ?>
						<div class="card h-full rounded-2xl border border-ink-100 bg-white p-6" data-reveal>
							<h3 class="font-semibold text-ink-900"><?php echo esc_html( $jb_sub['title'] ); ?></h3>
							<p class="mt-2 text-sm leading-relaxed text-ink-500"><?php echo esc_html( $jb_sub['body'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $jb_tag_rows ) : ?>
				<div class="mt-10 border-t border-ink-100 pt-6">
					<p class="mb-3 text-xs font-semibold uppercase tracking-[0.14em] text-flame-600"><?php esc_html_e( 'Focus areas', 'jbnewgen' ); ?></p>
					<div class="flex flex-wrap gap-2">
						<?php foreach ( $jb_tag_rows as $jb_tag ) : ?>
							<span class="rounded-full border border-ink-100 bg-ink-50 px-3 py-1 text-xs font-medium text-ink-600"><?php echo esc_html( $jb_tag['label'] ); ?></span>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php
	$jb_siblings = jbnewgen_pillar_services( $jb_pillar->ID );
	$jb_items    = array( array(
		'label' => $jb_pillar_short . ' - ' . __( 'Overview', 'jbnewgen' ),
		'href'  => '/services/' . $jb_pillar->post_name,
		'desc'  => __( 'Back to the pillar', 'jbnewgen' ),
		'icon'  => 'arrowRight',
	) );
	foreach ( $jb_siblings as $jb_sib ) {
		if ( $jb_sib->ID === $jb_service_id ) {
			continue;
		}
		$jb_items[] = array(
			'label' => $jb_sib->post_title,
			'href'  => '/services/' . $jb_pillar->post_name . '/' . $jb_sib->post_name,
			'icon'  => $jb_pillar_icon,
		);
	}
	jbnewgen_nav_grid( array(
		/* translators: %s: pillar name */
		'title'   => sprintf( __( 'More in %s', 'jbnewgen' ), $jb_pillar_short ),
		'caption' => __( 'Sibling services + back to the pillar overview', 'jbnewgen' ),
		'columns' => 3,
		'items'   => $jb_items,
	) );

	$jb_pillar_static = jbnewgen_pillar_static( $jb_pillar->post_name );
	if ( ! empty( $jb_pillar_static['cta'] ) ) :
		$jb_cta = $jb_pillar_static['cta'];
		?>
		<section class="bg-ink-50/60 py-20 sm:py-28">
			<div class="mx-auto max-w-4xl px-5 text-center sm:px-8">
				<div class="mx-auto max-w-2xl">
					<p class="mb-4 text-xs font-semibold uppercase tracking-[0.14em] text-flame-600"><?php echo esc_html( $jb_cta['label'] ); ?></p>
					<h2 class="text-balance text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl"><?php echo esc_html( $jb_cta['title'] ); ?></h2>
					<p class="mt-5 text-pretty text-lg text-ink-500"><?php echo esc_html( $jb_cta['body'] ); ?></p>
					<div class="mt-9 flex flex-wrap justify-center gap-3">
						<a href="<?php echo esc_url( home_url( '/quote' ) ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] border border-ink-300 px-6 text-base font-semibold text-ink-800 transition-colors hover:border-flame-500 hover:text-flame-600"><?php esc_html_e( 'Book Your Free Strategy Call', 'jbnewgen' ); ?></a>
						<a href="mailto:sales@jbnewgen.com" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] px-6 text-base font-semibold text-ink-600 transition-colors hover:text-flame-600"><?php esc_html_e( 'Email Us Directly', 'jbnewgen' ); ?><?php jbnewgen_icon( 'mail', 18 ); ?></a>
					</div>
				</div>
				<p class="mt-7 text-sm text-ink-400"><?php esc_html_e( 'JB NewGen Enterprises Private Limited · Mumbai, India · sales@jbnewgen.com · +91 6362864230', 'jbnewgen' ); ?></p>
			</div>
		</section>
	<?php endif; ?>

<?php endwhile; ?>

<?php get_footer(); ?>
