<?php
/**
 * Homepage. Ported from ../jbnewgen/src/app/(frontend)/page.tsx and the nine
 * components it composes, in the same order:
 *
 *   Hero · ProofBar · Problem · ServicesShowcase · Testimonials ·
 *   FounderBand · GetInTouch · FeaturedInsights · Newsletter
 *
 * Markup and classes are carried across unchanged so the design matches the
 * live site rather than approximating it. Three things necessarily differ:
 *
 *   - next/image becomes plain <img>. There is no build-time image pipeline,
 *     so sizes come from WordPress's own srcset where the image is an
 *     attachment.
 *   - Reveal becomes data-reveal, handled by the IntersectionObserver in
 *     site.js.
 *   - SplitHeading animated per letter via JS. Here the heading is plain text
 *     with the same animation applied to the whole line: the per-letter effect
 *     needs a span per character, which is a lot of DOM for a decorative
 *     flourish and hurts anyone using a screen reader.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$jb_home    = jbnewgen_home_view();
$jb_hero    = $jb_home['hero'];
$jb_proof   = $jb_home['proof'];
$jb_ceo     = jbnewgen_home_ceo();
$jb_quotes  = jbnewgen_home_testimonials();
$jb_pillars = jbnewgen_nav_pillars();
$jb_site    = jbnewgen_site_info();
?>

<?php // ===================== HERO ===================== ?>
<section id="hero" class="relative isolate -mt-16 flex min-h-svh items-center overflow-hidden">

	<?php if ( $jb_hero['image'] ) : ?>
		<img src="<?php echo esc_url( $jb_hero['image'] ); ?>" alt="" class="absolute inset-0 h-full w-full object-cover object-center" fetchpriority="high" decoding="async">
	<?php else : ?>
		<div class="mesh absolute inset-0 bg-ink-950"></div>
	<?php endif; ?>

	<?php
	// The still above stays as the fallback: it shows through if the video is
	// missing, while it decodes, and whenever the visitor has asked for
	// reduced motion -- .hero-video is display:none under that media query.
	$jb_video  = get_template_directory_uri() . '/assets/hero.mp4';
	$jb_poster = get_template_directory_uri() . '/assets/hero-poster.jpg';
	if ( file_exists( get_template_directory() . '/assets/hero.mp4' ) ) :
		?>
		<video class="hero-video absolute inset-0 h-full w-full object-cover object-center" poster="<?php echo esc_url( $jb_poster ); ?>" autoplay muted loop playsinline preload="metadata" aria-hidden="true" tabindex="-1">
			<source src="<?php echo esc_url( $jb_video ); ?>" type="video/mp4">
		</video>
	<?php endif; ?>

	<div class="absolute inset-0" style="background-color:rgba(23,63,116,0.2)"></div>
	<div class="absolute inset-0" style="background:linear-gradient(90deg,rgba(9,11,16,0.42) 0%,rgba(9,11,16,0.08) 48%,rgba(9,11,16,0) 74%)"></div>

	<div class="relative z-10 mx-auto w-full max-w-7xl px-5 py-28 sm:px-8">
		<div class="max-w-2xl">
			<h1 class="font-display text-4xl font-bold leading-[1.1] tracking-tight text-white [text-shadow:0_1px_20px_rgba(0,0,0,0.45)] sm:text-5xl md:text-[3.7rem]">
				<span class="animate-slide-in block"><?php echo esc_html( $jb_hero['heading_1'] ); ?></span>
				<span class="animate-slide-in mt-1.5 block font-serif text-[1.12em] font-normal italic tracking-normal text-flame-400" style="animation-delay:520ms"><?php echo esc_html( $jb_hero['heading_2'] ); ?></span>
			</h1>

			<p class="animate-slide-in mt-6 max-w-xl text-pretty text-base leading-relaxed text-ink-100 [text-shadow:0_1px_12px_rgba(0,0,0,0.4)] sm:text-lg" style="animation-delay:1150ms">
				<?php echo esc_html( $jb_hero['intro'] ); ?>
			</p>

			<div class="animate-rise mt-9 flex flex-wrap items-center gap-3" style="animation-delay:1500ms">
				<a href="<?php echo esc_url( jbnewgen_href( $jb_hero['cta_1_href'] ) ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] bg-flame-500 px-6 text-base font-semibold text-white transition-colors hover:bg-flame-600">
					<?php echo esc_html( $jb_hero['cta_1_label'] ); ?>
				</a>
				<a href="<?php echo esc_url( jbnewgen_href( $jb_hero['cta_2_href'] ) ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] border border-white/30 bg-white/10 px-6 text-base font-semibold text-white backdrop-blur transition-colors hover:bg-white/20">
					<?php echo esc_html( $jb_hero['cta_2_label'] ); ?><?php jbnewgen_icon( 'arrowRight', 18 ); ?>
				</a>
			</div>
		</div>
	</div>
</section>

<?php // ===================== PROOF BAR ===================== ?>
<section class="border-y border-ink-100 bg-ink-50/50">
	<div class="mx-auto max-w-7xl px-5 py-12 sm:px-8">
		<div class="mb-8 text-center" data-reveal>
			<p class="text-xs uppercase tracking-[0.2em] text-ink-400"><?php echo esc_html( $jb_proof['eyebrow'] ); ?></p>
		</div>
		<dl class="grid grid-cols-2 gap-x-6 gap-y-10 lg:grid-cols-4">
			<?php foreach ( $jb_proof['stats'] as $jb_i => $jb_stat ) : ?>
				<div class="text-center" data-reveal data-reveal-delay="<?php echo (int) ( $jb_i * 80 ); ?>">
					<dt class="text-gradient-brand text-4xl font-bold tracking-tight sm:text-5xl"><?php echo esc_html( $jb_stat['value'] ); ?></dt>
					<dd class="mx-auto mt-3 max-w-[15rem] text-sm font-medium text-ink-700">
						<?php echo esc_html( $jb_stat['label'] ); ?>
						<?php if ( ! empty( $jb_stat['sub'] ) ) : ?>
							<span class="mt-1 block text-xs font-normal text-ink-400"><?php echo esc_html( $jb_stat['sub'] ); ?></span>
						<?php endif; ?>
					</dd>
				</div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>

<?php // ===================== PROBLEM ===================== ?>
<section class="relative overflow-hidden py-20 sm:py-28">
	<div class="mx-auto grid max-w-7xl items-center gap-12 px-5 sm:px-8 lg:grid-cols-2 lg:gap-16">
		<div>
			<h2 class="text-balance text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl md:text-[2.7rem] md:leading-[1.08]" data-reveal>
				<?php esc_html_e( 'Most foreign companies', 'jbnewgen' ); ?> <span class="text-flame-600"><?php esc_html_e( 'underestimate', 'jbnewgen' ); ?></span> <?php esc_html_e( 'India.', 'jbnewgen' ); ?>
			</h2>
			<div data-reveal data-reveal-delay="250">
				<p class="mt-6 max-w-lg text-pretty text-lg leading-relaxed text-ink-500">
					<?php esc_html_e( 'It is not the size of the oportunity that is the problem but rather the difficulty of actually reaching it. Closing that gap, with a network that&rsquo;s already built, is the whole point of a distribution partner.', 'jbnewgen' ); ?>
				</p>
			</div>
		</div>

		<div data-reveal data-reveal-delay="150">
			<figure class="relative overflow-hidden rounded-3xl bg-ink-950 text-white shadow-[0_40px_80px_-44px_rgba(8,21,39,0.6)]">
				<div class="mesh pointer-events-none absolute inset-0 rounded-3xl opacity-60"></div>
				<?php if ( ! empty( $jb_ceo['photo'] ) ) : ?>
					<div class="absolute bottom-0 right-0 h-28 w-24 overflow-hidden rounded-tl-2xl">
						<img src="<?php echo esc_url( $jb_ceo['photo'] ); ?>" alt="<?php echo esc_attr( $jb_ceo['name'] ); ?>" class="h-full w-full object-cover object-top" loading="lazy">
					</div>
				<?php endif; ?>
				<div class="relative p-8 sm:p-10">
					<span class="text-flame-400/70 font-serif text-[44px] leading-none">&ldquo;</span>
					<blockquote class="mt-4 text-balance text-xl font-medium leading-relaxed sm:text-2xl"><?php echo esc_html( $jb_ceo['quote'] ); ?></blockquote>
					<div class="mt-8 flex items-center justify-end border-t border-white/10 <?php echo ! empty( $jb_ceo['photo'] ) ? 'pr-28' : ''; ?> pb-2 pt-10">
						<span class="text-right">
							<span class="block font-semibold text-white"><?php echo esc_html( $jb_ceo['name'] ); ?></span>
							<span class="block text-sm text-ink-300"><?php echo esc_html( $jb_ceo['role'] ); ?>, JB NewGen</span>
						</span>
					</div>
				</div>
			</figure>
		</div>
	</div>
</section>

<?php // ===================== SERVICES SHOWCASE ===================== ?>
<?php if ( $jb_pillars ) : ?>
<section id="jb-showcase" data-autoplay="6000" class="relative overflow-hidden bg-ink-950 py-20 text-white sm:py-28">
	<div class="mesh pointer-events-none absolute inset-0 opacity-50"></div>
	<div class="pointer-events-none absolute -left-20 top-1/3 h-72 w-72 rounded-[7px] bg-flame-500/15 blur-3xl"></div>
	<div class="pointer-events-none absolute -right-20 bottom-10 h-80 w-80 rounded-[7px] bg-azure-500/15 blur-3xl"></div>

	<div class="relative mx-auto max-w-7xl px-5 sm:px-8">
		<div class="mx-auto max-w-3xl text-center">
			<h2 class="text-balance text-3xl font-bold tracking-tight sm:text-4xl md:text-[2.7rem] md:leading-[1.1]" data-reveal><?php esc_html_e( 'Four capabilities. One India partner.', 'jbnewgen' ); ?></h2>
			<div data-reveal data-reveal-delay="250">
				<p class="mt-5 text-pretty text-lg text-ink-300"><?php esc_html_e( 'End to end - from strategy to the technology and communication that runs your operation on the ground.', 'jbnewgen' ); ?></p>
			</div>
		</div>

		<div class="mt-14">
			<div class="mx-auto grid max-w-4xl items-stretch gap-6 lg:grid-cols-[1.2fr_1fr] lg:gap-10">

				<div class="flex flex-col gap-5" data-reveal data-reveal-delay="150">
					<div id="jb-showcase-visual">
						<?php foreach ( $jb_pillars as $jb_i => $jb_pillar ) : ?>
							<div class="jb-pv-panel<?php echo 0 === $jb_i ? ' is-active' : ' hidden'; ?>" data-pillar-visual="<?php echo esc_attr( $jb_pillar['slug'] ); ?>">
								<?php jbnewgen_pillar_visual( $jb_pillar['slug'] ); ?>
							</div>
						<?php endforeach; ?>
					</div>

					<div id="jb-showcase-cta" class="relative">
						<?php foreach ( $jb_pillars as $jb_i => $jb_pillar ) : ?>
							<?php
							$jb_blurb = function_exists( 'carbon_get_post_meta' ) ? (string) carbon_get_post_meta( $jb_pillar['id'], 'blurb' ) : '';
							?>
							<a href="<?php echo esc_url( $jb_pillar['url'] ); ?>" class="group relative block overflow-hidden rounded-2xl border border-white/15 bg-white/[0.07] p-4 text-left transition-colors duration-300 hover:border-white/30 hover:bg-white/[0.1]<?php echo 0 === $jb_i ? ' is-active' : ' hidden'; ?>" data-pillar-cta="<?php echo esc_attr( $jb_pillar['slug'] ); ?>">
								<span class="flex items-center gap-3">
									<span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-flame-500 to-azure-500 text-white">
										<?php jbnewgen_icon( $jb_pillar['icon'] ? $jb_pillar['icon'] : 'arrowUpRight', 22 ); ?>
									</span>
									<span class="min-w-0">
										<span class="block font-semibold text-white"><?php esc_html_e( 'Explore', 'jbnewgen' ); ?> <?php echo esc_html( $jb_pillar['short'] ); ?></span>
										<span class="block truncate text-sm text-ink-400"><?php echo esc_html( $jb_blurb ); ?></span>
									</span>
									<?php jbnewgen_icon( 'arrowRight', 18, 'ml-auto shrink-0 text-flame-400 transition-transform group-hover:translate-x-0.5' ); ?>
								</span>
								<span class="absolute bottom-0 left-0 h-0.5 w-full bg-white/10">
									<span class="jb-pv-progress block h-full origin-left bg-flame-500" style="width:0%"></span>
								</span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="flex flex-col" data-reveal data-reveal-delay="220">
					<div id="jb-showcase-text" class="mx-auto flex w-full max-w-[22rem] flex-1 flex-col justify-center">
						<?php foreach ( $jb_pillars as $jb_i => $jb_pillar ) : ?>
							<?php
							$jb_subtext = function_exists( 'carbon_get_post_meta' ) ? (string) carbon_get_post_meta( $jb_pillar['id'], 'subtext' ) : '';
							$jb_full    = function_exists( 'carbon_get_post_meta' ) ? (string) carbon_get_post_meta( $jb_pillar['id'], 'full_title' ) : '';
							?>
							<div data-pillar-text="<?php echo esc_attr( $jb_pillar['slug'] ); ?>"<?php echo 0 === $jb_i ? '' : ' class="hidden"'; ?>>
								<h3 class="text-2xl font-bold leading-tight text-white sm:text-[1.7rem]"><?php echo esc_html( $jb_full ? $jb_full : $jb_pillar['title'] ); ?></h3>
								<p class="mt-5 text-pretty text-[0.95rem] leading-relaxed text-ink-300"><?php echo esc_html( $jb_subtext ); ?></p>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php // ===================== TESTIMONIALS ===================== ?>
<section class="py-20 sm:py-28">
	<div class="mx-auto max-w-7xl px-5 sm:px-8">
		<div class="mx-auto max-w-3xl text-center">
			<h2 class="text-balance text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl md:text-[2.7rem] md:leading-[1.1]" data-reveal><?php esc_html_e( 'Trusted to deliver growth that lasts', 'jbnewgen' ); ?></h2>
			<div data-reveal data-reveal-delay="250">
				<p class="mt-5 text-pretty text-lg text-ink-500"><?php esc_html_e( 'Our clients trust us to deliver comprehensive solutions that drive growth, streamline strategies, and ensure long-term success.', 'jbnewgen' ); ?></p>
			</div>
		</div>
	</div>

	<div class="mx-auto mt-14 max-w-7xl px-5 sm:px-8" data-reveal data-reveal-delay="150">
		<div class="relative overflow-hidden sm:[mask-image:linear-gradient(to_right,transparent,black_2%,black_98%,transparent)]">
			<?php
			// Duplicated so the marquee wraps seamlessly -- translateX(-50%)
			// lands exactly on the start of the second copy.
			$jb_loop = array_merge( $jb_quotes, $jb_quotes );
			?>
			<div class="jb-marquee animate-marquee-slow flex w-max gap-4 py-2">
				<?php foreach ( $jb_loop as $jb_i => $jb_t ) : ?>
					<?php
					$jb_parts    = preg_split( '/\s+/', $jb_t['name'] );
					$jb_initials = '';
					foreach ( $jb_parts as $jb_part ) {
						$jb_initials .= mb_substr( $jb_part, 0, 1 );
					}
					?>
					<figure class="jb-quote-card flex shrink-0 flex-col rounded-2xl border border-ink-100 bg-white px-5 py-4 shadow-[0_1px_2px_rgba(8,21,39,0.04)] transition-all duration-200 hover:border-flame-500 hover:ring-1 hover:ring-flame-500 sm:px-6">
						<div class="mb-2 flex items-center gap-1 text-flame-500">
							<?php for ( $jb_s = 0; $jb_s < 5; $jb_s++ ) : ?>
								<span aria-hidden="true">&#9733;</span>
							<?php endfor; ?>
						</div>
						<blockquote class="min-w-0 flex-1 break-words text-pretty text-sm leading-relaxed text-ink-700">&ldquo;<?php echo esc_html( $jb_t['quote'] ); ?>&rdquo;</blockquote>
						<figcaption class="mt-3 flex items-center gap-3 border-t border-ink-100 pt-3">
							<span class="grid h-8 w-8 shrink-0 place-items-center rounded-[7px] bg-gradient-to-br from-flame-500 to-flame-700 text-xs font-bold text-white"><?php echo esc_html( $jb_initials ); ?></span>
							<span class="min-w-0">
								<span class="block break-words text-sm font-semibold text-ink-900"><?php echo esc_html( $jb_t['name'] ); ?></span>
								<span class="block break-words text-xs text-ink-500"><?php echo esc_html( $jb_t['role'] ); ?></span>
							</span>
						</figcaption>
					</figure>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<?php // ===================== FOUNDER BAND ===================== ?>
<section class="pb-20 sm:pb-28">
	<div class="mx-auto max-w-7xl px-5 sm:px-8">
		<div class="relative overflow-hidden rounded-3xl border border-ink-100 bg-white shadow-[0_20px_60px_-40px_rgba(8,21,39,0.5)]" data-reveal>
			<div class="grid items-center gap-6 p-8 sm:gap-8 sm:p-10 md:grid-cols-[1fr_auto]">
				<div>
					<p class="text-xl font-bold text-ink-900"><?php echo esc_html( $jb_ceo['name'] ); ?></p>
					<p class="text-sm font-medium text-flame-600"><?php echo esc_html( $jb_ceo['role'] ); ?>, JB NewGen Enterprises</p>
					<p class="mt-3 max-w-2xl text-pretty text-ink-500"><?php echo esc_html( $jb_ceo['bio_short'] ); ?></p>
					<div class="mt-4 flex flex-wrap gap-2">
						<?php
						// Verbatim from FounderBand.tsx.
						$jb_chips = array(
							'Channel & distribution architecture',
							'GTM & revenue acceleration',
							'Tier 1 & Tier 2 expansion',
						);
						foreach ( $jb_chips as $jb_chip ) :
							?>
							<span class="rounded-[7px] bg-ink-50 px-3 py-1 text-xs font-medium text-ink-600 ring-1 ring-inset ring-ink-100"><?php echo esc_html( $jb_chip ); ?></span>
						<?php endforeach; ?>
					</div>
				</div>
				<a href="<?php echo esc_url( jbnewgen_href( $jb_ceo['href'] ) ); ?>" class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-[7px] border border-ink-200 px-5 text-sm font-semibold text-ink-800 transition-colors hover:border-flame-500 hover:text-flame-600">
					<?php esc_html_e( 'Meet Joyjeet', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowRight', 16 ); ?>
				</a>
			</div>
		</div>
	</div>
</section>

<?php // ===================== GET IN TOUCH ===================== ?>
<section class="relative overflow-hidden bg-ink-950 py-20 text-white sm:py-28">
	<div class="mesh pointer-events-none absolute inset-0 opacity-60"></div>
	<div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-[7px] bg-flame-500/20 blur-3xl"></div>
	<div class="pointer-events-none absolute -bottom-20 -left-10 h-64 w-64 rounded-[7px] bg-flame-500/20 blur-3xl"></div>

	<div class="relative mx-auto max-w-7xl px-5 sm:px-8">
		<div class="grid items-center gap-10 lg:grid-cols-[1.2fr_1fr]">
			<div>
				<h2 class="text-balance text-3xl font-bold text-white sm:text-4xl md:text-[2.7rem] md:leading-[1.08]" data-reveal><?php esc_html_e( 'Let&rsquo;s map your India entry', 'jbnewgen' ); ?></h2>
				<p class="mt-5 max-w-xl text-pretty text-lg text-ink-200"><?php esc_html_e( 'A no-obligation call that turns your ambition into a concrete, executable plan.', 'jbnewgen' ); ?></p>
				<div class="mt-8 flex flex-wrap gap-3">
					<a href="<?php echo esc_url( home_url( '/quote' ) ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] bg-flame-500 px-6 text-base font-semibold text-white transition-colors hover:bg-flame-600"><?php esc_html_e( 'Book a free consultation', 'jbnewgen' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] border border-white/30 bg-white/10 px-6 text-base font-semibold text-white transition-colors hover:bg-white/20"><?php esc_html_e( 'Contact us', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowUpRight', 18 ); ?></a>
				</div>
			</div>

			<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
				<a href="<?php echo esc_url( $jb_site['phone_href'] ); ?>" class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-5 transition-colors hover:border-flame-400/50" data-reveal data-reveal-delay="100">
					<span class="grid h-11 w-11 place-items-center rounded-xl bg-flame-500/15 text-flame-400"><?php jbnewgen_icon( 'phone', 20 ); ?></span>
					<span>
						<span class="block text-xs uppercase tracking-widest text-ink-400"><?php esc_html_e( 'Call us · Mon–Sat', 'jbnewgen' ); ?></span>
						<span class="block font-semibold text-white"><?php echo esc_html( $jb_site['phone'] ); ?></span>
					</span>
				</a>
				<a href="<?php echo esc_url( 'mailto:' . $jb_site['email'] ); ?>" class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-5 transition-colors hover:border-flame-400/50" data-reveal data-reveal-delay="180">
					<span class="grid h-11 w-11 place-items-center rounded-xl bg-flame-500/15 text-flame-400"><?php jbnewgen_icon( 'mail', 20 ); ?></span>
					<span>
						<span class="block text-xs uppercase tracking-widest text-ink-400"><?php esc_html_e( 'Email us', 'jbnewgen' ); ?></span>
						<span class="block font-semibold text-white"><?php echo esc_html( $jb_site['email'] ); ?></span>
					</span>
				</a>
			</div>
		</div>
	</div>
</section>

<?php // ===================== FEATURED INSIGHTS ===================== ?>
<?php
/*
 * Next.js `featuredArticles()` is `articles.slice(0, 3)` -- the first three
 * entries of the hand-curated content.ts array, not the three newest by
 * date. A plain get_posts() here sorts by post_date DESC and silently picks
 * a different (and differently-imaged) trio, so the first three slugs from
 * the same source data are looked up by name and re-ordered to match.
 */
$jb_article_slugs  = wp_list_pluck( include get_template_directory() . '/inc/data/articles.php', 'slug' );
$jb_featured_slugs = array_slice( $jb_article_slugs, 0, 3 );
$jb_insights       = array();
if ( $jb_featured_slugs ) {
	$jb_found = get_posts( array(
		'post_type'        => 'insight',
		'post_status'      => 'publish',
		'post_name__in'    => $jb_featured_slugs,
		'numberposts'      => 3,
		'suppress_filters' => false,
	) );
	$jb_by_slug = array();
	foreach ( $jb_found as $jb_fp ) {
		$jb_by_slug[ $jb_fp->post_name ] = $jb_fp;
	}
	foreach ( $jb_featured_slugs as $jb_slug ) {
		if ( isset( $jb_by_slug[ $jb_slug ] ) ) {
			$jb_insights[] = $jb_by_slug[ $jb_slug ];
		}
	}
}
?>
<?php if ( $jb_insights ) : ?>
<section class="border-t border-ink-100 py-20 sm:py-24">
	<div class="mx-auto max-w-7xl px-5 sm:px-8">
		<div class="mb-10 flex flex-wrap items-end justify-between gap-4">
			<h2 class="text-balance text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl" data-reveal><?php esc_html_e( 'From the JBNewGen desk', 'jbnewgen' ); ?></h2>
			<a href="<?php echo esc_url( home_url( '/insights' ) ); ?>" class="group inline-flex items-center gap-2 text-sm font-semibold text-flame-600">
				<?php esc_html_e( 'All insights', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowRight', 16, 'transition-transform group-hover:translate-x-0.5' ); ?>
			</a>
		</div>

		<div class="grid gap-6 md:grid-cols-3">
			<?php foreach ( $jb_insights as $jb_i => $jb_post ) : ?>
				<?php $jb_terms = get_the_terms( $jb_post->ID, 'insight_category' ); ?>
				<a href="<?php echo esc_url( get_permalink( $jb_post ) ); ?>" class="group flex h-full flex-col overflow-hidden rounded-2xl border border-ink-100 bg-white transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_28px_56px_-30px_rgba(8,21,39,0.45)]" data-reveal data-reveal-delay="<?php echo (int) ( $jb_i * 80 ); ?>">
					<div class="relative aspect-[16/10] overflow-hidden bg-ink-100">
						<?php if ( has_post_thumbnail( $jb_post ) ) : ?>
							<?php echo get_the_post_thumbnail( $jb_post, 'medium_large', array( 'class' => 'absolute inset-0 h-full w-full object-cover transition-transform duration-300 group-hover:scale-105', 'loading' => 'lazy' ) ); ?>
						<?php else : ?>
							<div class="mesh absolute inset-0 opacity-80"></div>
							<div class="dot-grid absolute inset-0 opacity-40"></div>
						<?php endif; ?>
						<?php if ( $jb_terms && ! is_wp_error( $jb_terms ) ) : ?>
							<span class="absolute left-4 top-4 rounded-[7px] bg-white/90 px-3 py-1 text-xs font-semibold text-ink-700 backdrop-blur"><?php echo esc_html( $jb_terms[0]->name ); ?></span>
						<?php endif; ?>
					</div>
					<div class="flex flex-1 flex-col p-6">
						<h3 class="text-lg font-bold leading-snug text-ink-900 group-hover:text-flame-600"><?php echo esc_html( get_the_title( $jb_post ) ); ?></h3>
						<span class="mt-auto inline-flex items-center gap-2 pt-5 text-sm font-semibold text-ink-700">
							<?php esc_html_e( 'Read article', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowRight', 15, 'text-flame-500 transition-transform group-hover:translate-x-0.5' ); ?>
						</span>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php // ===================== NEWSLETTER ===================== ?>
<section class="pb-20 sm:pb-24">
	<div class="mx-auto max-w-7xl px-5 sm:px-8">
		<div class="flex flex-col items-center justify-between gap-6 rounded-3xl border border-ink-100 bg-ink-50/60 px-6 py-10 sm:flex-row sm:px-10" data-reveal>
			<div class="max-w-md text-center sm:text-left">
				<h3 class="text-xl font-bold text-ink-900 sm:text-2xl"><?php esc_html_e( 'India market insights, once a month', 'jbnewgen' ); ?></h3>
				<p class="mt-2 text-ink-500"><?php esc_html_e( 'Sharp, practical notes for operators - no fluff, no spam. Unsubscribe anytime.', 'jbnewgen' ); ?></p>
			</div>
			<?php if ( isset( $_GET['subscribed'] ) ) : ?>
				<p class="flex w-full max-w-md items-center gap-2 rounded-[7px] bg-flame-500/10 px-5 py-3 text-sm font-semibold text-flame-700"><?php jbnewgen_icon( 'check', 16 ); ?><?php esc_html_e( "You're subscribed. Thanks for joining.", 'jbnewgen' ); ?></p>
			<?php else : ?>
				<form class="flex w-full max-w-md flex-col gap-3 sm:flex-row" aria-label="<?php esc_attr_e( 'Newsletter signup', 'jbnewgen' ); ?>" method="post">
					<?php wp_nonce_field( 'jb_newsletter', 'jb_newsletter_nonce' ); ?>
					<input type="email" name="jb_email" required placeholder="you@company.com" class="h-12 w-full rounded-[7px] border border-ink-200 bg-white px-5 text-ink-900 placeholder:text-ink-400 focus:border-flame-400 focus:outline-none">
					<button type="submit" class="inline-flex h-12 shrink-0 items-center justify-center gap-2 rounded-[7px] bg-flame-500 px-6 font-semibold text-white transition-colors hover:bg-flame-600">
						<?php esc_html_e( 'Subscribe', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowRight', 18 ); ?>
					</button>
				</form>
				<?php if ( isset( $_GET['newsletter_error'] ) ) : ?>
					<p class="mt-2 text-sm font-medium text-red-600"><?php esc_html_e( 'Please enter a valid email address.', 'jbnewgen' ); ?></p>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php
get_footer();
