<?php
/**
 * Single pillar (service category) page — /services/{pillar}.
 * Ported from ../jbnewgen/src/app/(frontend)/services/[pillar]/page.tsx and
 * the seven components it composes: ServiceHero, ChallengeGrid, ServiceList,
 * WhyGrid, FeatureGrid, StepTimeline, ServiceCTA.
 *
 * full_title/icon/blurb/subtext/hero_image are Carbon Fields on the pillar CPT
 * (inc/fields-pillar.php); hero/challenge/servicesIntro/why/features/steps/cta
 * are not modelled as fields yet, so they come verbatim from
 * jbnewgen_pillar_static() (inc/data/pillars.php). Services are real `service`
 * CPT posts (jbnewgen_pillar_services()), not the static array.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$jb_pillar_id = get_the_ID();
	$jb_slug      = get_post_field( 'post_name', $jb_pillar_id );
	$jb_static    = jbnewgen_pillar_static( $jb_slug );

	if ( ! $jb_static ) {
		// A pillar exists in the CMS with no matching static record -- render
		// what the CMS does have rather than 404ing a published page.
		$jb_static = array(
			'hero'          => array( 'badge' => '', 'lead' => get_the_title(), 'accent' => '', 'tail' => '', 'sub' => '' ),
			'challenge'     => null,
			'servicesIntro' => array( 'label' => __( 'Our Services', 'jbnewgen' ), 'title' => get_the_title(), 'intro' => '' ),
			'why'           => null,
			'features'      => array(),
			'steps'         => null,
			'cta'           => null,
		);
	}

	$jb_hero_id  = function_exists( 'carbon_get_post_meta' ) ? (int) carbon_get_post_meta( $jb_pillar_id, 'hero_image' ) : 0;
	$jb_hero_url = $jb_hero_id ? wp_get_attachment_image_url( $jb_hero_id, 'full' ) : '';
	$jb_full     = function_exists( 'carbon_get_post_meta' ) ? (string) carbon_get_post_meta( $jb_pillar_id, 'full_title' ) : '';
	$jb_services = jbnewgen_pillar_services( $jb_pillar_id );
	?>

	<section class="relative isolate overflow-hidden bg-ink-950 text-white">
		<?php if ( $jb_hero_url ) : ?>
			<img src="<?php echo esc_url( $jb_hero_url ); ?>" alt="" class="absolute inset-0 h-full w-full object-cover object-center" fetchpriority="high">
			<div class="absolute inset-0 bg-ink-950/[0.28]"></div>
			<div class="absolute inset-0" style="background:linear-gradient(90deg,rgba(9,11,16,0.34) 0%,rgba(9,11,16,0.14) 55%,rgba(9,11,16,0.04) 100%)"></div>
		<?php endif; ?>
		<div class="mesh pointer-events-none absolute inset-0 <?php echo $jb_hero_url ? 'opacity-30' : 'opacity-50'; ?>"></div>
		<div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-[7px] bg-flame-500/15 blur-3xl"></div>
		<div class="pointer-events-none absolute -bottom-28 left-1/4 h-72 w-72 rounded-[7px] bg-flame-500/10 blur-3xl"></div>

		<div class="relative mx-auto max-w-4xl px-5 py-24 sm:px-8 sm:py-28">
			<span class="inline-flex items-center rounded-full border border-flame-500/30 bg-flame-500/15 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.14em] text-flame-300"><?php echo esc_html( $jb_static['hero']['badge'] ); ?></span>
			<h1 class="mt-7 text-balance text-4xl font-bold leading-[1.08] tracking-tight sm:text-5xl md:text-[3.25rem]">
				<?php echo esc_html( $jb_static['hero']['lead'] ); ?> <span class="text-flame-400"><?php echo esc_html( $jb_static['hero']['accent'] ); ?></span><?php echo esc_html( $jb_static['hero']['tail'] ); ?>
			</h1>
			<p class="mt-6 text-lg leading-relaxed text-ink-200"><?php echo esc_html( $jb_static['hero']['sub'] ); ?></p>
			<div class="mt-9 flex flex-wrap gap-3">
				<a href="<?php echo esc_url( home_url( '/quote' ) ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] bg-flame-500 px-6 text-base font-semibold text-white transition-colors hover:bg-flame-600"><?php esc_html_e( 'Book a Free Strategy Call', 'jbnewgen' ); ?></a>
				<a href="#services" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] border border-white/30 bg-white/10 px-6 text-base font-semibold text-white backdrop-blur transition-colors hover:bg-white/20"><?php esc_html_e( 'See What We Do', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowRight', 18 ); ?></a>
			</div>
		</div>
	</section>

	<?php if ( ! empty( $jb_static['challenge'] ) ) : $jb_c = $jb_static['challenge']; ?>
		<section class="bg-ink-50/60 py-20 sm:py-28">
			<div class="mx-auto max-w-6xl px-5 sm:px-8">
				<p class="mb-4 text-xs font-semibold uppercase tracking-[0.14em] text-flame-600"><?php echo esc_html( $jb_c['label'] ); ?></p>
				<h2 class="max-w-3xl text-balance text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl md:text-[2.5rem] md:leading-[1.12]"><?php echo esc_html( $jb_c['title'] ); ?></h2>
				<?php if ( ! empty( $jb_c['intro'] ) ) : ?><p class="mt-5 text-lg text-ink-500" data-reveal data-reveal-delay="150"><?php echo esc_html( $jb_c['intro'] ); ?></p><?php endif; ?>
				<div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
					<?php foreach ( $jb_c['items'] as $jb_i => $jb_item ) : ?>
						<div class="card h-full rounded-2xl border border-ink-100 bg-white p-6" data-reveal data-reveal-delay="<?php echo (int) ( ( $jb_i % 4 ) * 70 ); ?>">
							<span class="mb-4 grid h-11 w-11 place-items-center rounded-xl bg-flame-500/10 text-flame-600 ring-1 ring-inset ring-flame-500/15"><?php jbnewgen_icon( $jb_item['icon'], 20 ); ?></span>
							<h3 class="font-semibold text-ink-900"><?php echo esc_html( $jb_item['title'] ); ?></h3>
							<p class="mt-2 text-sm leading-relaxed text-ink-500"><?php echo esc_html( $jb_item['body'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section id="services" class="scroll-mt-24 bg-white py-20 sm:py-28">
		<div class="mx-auto max-w-5xl px-5 sm:px-8">
			<div class="grid items-end gap-6 md:grid-cols-2 md:gap-12">
				<div>
					<p class="mb-4 text-xs font-semibold uppercase tracking-[0.14em] text-flame-600"><?php echo esc_html( $jb_static['servicesIntro']['label'] ); ?></p>
					<h2 class="text-balance text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl"><?php echo esc_html( $jb_static['servicesIntro']['title'] ); ?></h2>
				</div>
				<p class="text-ink-500" data-reveal data-reveal-delay="150"><?php echo esc_html( $jb_static['servicesIntro']['intro'] ); ?></p>
			</div>

			<div class="mt-14 border-t border-ink-100">
				<?php foreach ( $jb_services as $jb_i => $jb_s ) : ?>
					<?php
					$jb_tag_rows = function_exists( 'carbon_get_post_meta' ) ? (array) carbon_get_post_meta( $jb_s->ID, 'tags' ) : array();
					$jb_summary  = function_exists( 'carbon_get_post_meta' ) ? (string) carbon_get_post_meta( $jb_s->ID, 'summary' ) : '';
					?>
					<a href="<?php echo esc_url( home_url( '/services/' . $jb_slug . '/' . $jb_s->post_name ) ); ?>" class="group grid grid-cols-[3rem_1fr] gap-x-5 border-b border-ink-100 py-9 transition-colors sm:grid-cols-[3.5rem_1fr]" data-reveal>
						<span class="pt-1 font-mono text-sm font-semibold text-flame-600"><?php echo esc_html( str_pad( (string) ( $jb_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<div class="min-w-0">
							<h3 class="flex items-center gap-2 text-lg font-bold text-ink-900 transition-colors group-hover:text-flame-600 sm:text-xl">
								<?php echo esc_html( $jb_s->post_title ); ?>
								<?php jbnewgen_icon( 'arrowRight', 17, 'shrink-0 text-flame-500 opacity-0 transition-all duration-200 group-hover:translate-x-0.5 group-hover:opacity-100' ); ?>
							</h3>
							<p class="mt-2 text-ink-500"><?php echo esc_html( $jb_summary ); ?></p>
							<div class="mt-4 flex flex-wrap gap-2">
								<?php foreach ( $jb_tag_rows as $jb_tag ) : ?>
									<span class="rounded-full border border-ink-100 bg-ink-50 px-3 py-1 text-xs font-medium text-ink-600"><?php echo esc_html( $jb_tag['label'] ); ?></span>
								<?php endforeach; ?>
							</div>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php
	$jb_render_why = function () use ( $jb_static ) {
		if ( empty( $jb_static['why'] ) ) {
			return;
		}
		$jb_w = $jb_static['why'];
		?>
		<section class="relative overflow-hidden bg-ink-950 py-20 text-white sm:py-28">
			<div class="mesh pointer-events-none absolute inset-0 opacity-50"></div>
			<div class="pointer-events-none absolute -bottom-24 -right-16 h-80 w-80 rounded-[7px] bg-flame-500/12 blur-3xl"></div>
			<div class="relative mx-auto max-w-6xl px-5 sm:px-8">
				<p class="mb-4 text-sm font-semibold uppercase tracking-[0.14em] text-flame-300"><?php echo esc_html( $jb_w['label'] ); ?></p>
				<h2 class="max-w-3xl text-balance text-3xl font-bold tracking-tight sm:text-4xl md:text-[2.5rem] md:leading-[1.12]"><?php echo esc_html( $jb_w['title'] ); ?></h2>
				<?php if ( ! empty( $jb_w['intro'] ) ) : ?><p class="mt-5 text-lg text-ink-300" data-reveal data-reveal-delay="150"><?php echo esc_html( $jb_w['intro'] ); ?></p><?php endif; ?>
				<div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
					<?php foreach ( $jb_w['items'] as $jb_i => $jb_item ) : ?>
						<div class="h-full rounded-[7px] border border-white/10 bg-white/[0.05] p-6" data-reveal data-reveal-delay="<?php echo (int) ( ( $jb_i % 4 ) * 70 ); ?>">
							<span class="mb-4 grid h-8 w-8 place-items-center rounded-full border border-flame-500/40 bg-flame-500/20 text-flame-300"><?php jbnewgen_icon( 'check', 15 ); ?></span>
							<h3 class="font-semibold text-white"><?php echo esc_html( $jb_item['title'] ); ?></h3>
							<p class="mt-2 text-sm leading-relaxed text-ink-300"><?php echo esc_html( $jb_item['body'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	};

	$jb_render_features = function () use ( $jb_static ) {
		if ( empty( $jb_static['features'] ) ) {
			return;
		}
		foreach ( $jb_static['features'] as $jb_f ) :
			$jb_tone_cream = ! isset( $jb_f['tone'] ) || 'cream' === $jb_f['tone'];
			$jb_n          = min( 4, max( 2, count( $jb_f['items'] ) ) );
			$jb_cols_map   = array( 2 => 'sm:grid-cols-2', 3 => 'sm:grid-cols-2 lg:grid-cols-3', 4 => 'sm:grid-cols-2 lg:grid-cols-4' );
			?>
			<section class="<?php echo $jb_tone_cream ? 'bg-ink-50/60' : 'bg-white'; ?> py-20 sm:py-28">
				<div class="mx-auto max-w-6xl px-5 sm:px-8">
					<p class="mb-4 text-xs font-semibold uppercase tracking-[0.14em] text-flame-600"><?php echo esc_html( $jb_f['label'] ); ?></p>
					<h2 class="max-w-3xl text-balance text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl md:text-[2.5rem] md:leading-[1.12]"><?php echo esc_html( $jb_f['title'] ); ?></h2>
					<?php if ( ! empty( $jb_f['intro'] ) ) : ?><p class="mt-5 text-lg text-ink-500" data-reveal data-reveal-delay="150"><?php echo esc_html( $jb_f['intro'] ); ?></p><?php endif; ?>
					<div class="mt-12 grid gap-4 <?php echo esc_attr( $jb_cols_map[ $jb_n ] ); ?>">
						<?php foreach ( $jb_f['items'] as $jb_i => $jb_item ) : ?>
							<div class="card flex h-full gap-4 rounded-2xl border border-ink-100 bg-white p-6" data-reveal data-reveal-delay="<?php echo (int) ( ( $jb_i % 4 ) * 70 ); ?>">
								<?php if ( ! empty( $jb_item['icon'] ) ) : ?>
									<span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-flame-500/10 text-flame-600 ring-1 ring-inset ring-flame-500/15"><?php jbnewgen_icon( $jb_item['icon'], 20 ); ?></span>
								<?php endif; ?>
								<div class="min-w-0">
									<?php if ( ! empty( $jb_item['tag'] ) ) : ?>
										<span class="mb-2 inline-block rounded-full border border-flame-500/20 bg-flame-500/10 px-2.5 py-0.5 text-[0.7rem] font-semibold uppercase tracking-wider text-flame-600"><?php echo esc_html( $jb_item['tag'] ); ?></span>
									<?php endif; ?>
									<h3 class="font-semibold text-ink-900"><?php echo esc_html( $jb_item['title'] ); ?></h3>
									<p class="mt-2 text-sm leading-relaxed text-ink-500"><?php echo esc_html( $jb_item['body'] ); ?></p>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php
		endforeach;
	};

	if ( ! isset( $jb_static['whyBeforeFeatures'] ) || $jb_static['whyBeforeFeatures'] ) {
		$jb_render_why();
		$jb_render_features();
	} else {
		$jb_render_features();
		$jb_render_why();
	}
	?>

	<?php if ( ! empty( $jb_static['steps'] ) ) : $jb_st = $jb_static['steps']; $jb_total = count( $jb_st['items'] ); ?>
		<section class="bg-white py-20 sm:py-28">
			<div class="mx-auto max-w-5xl px-5 sm:px-8">
				<p class="mb-4 text-xs font-semibold uppercase tracking-[0.14em] text-flame-600"><?php echo esc_html( $jb_st['label'] ); ?></p>
				<h2 class="text-balance text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl"><?php echo esc_html( $jb_st['title'] ); ?></h2>
				<?php if ( ! empty( $jb_st['intro'] ) ) : ?><p class="mt-5 text-lg text-ink-500" data-reveal data-reveal-delay="150"><?php echo esc_html( $jb_st['intro'] ); ?></p><?php endif; ?>
				<div class="mt-14">
					<?php foreach ( $jb_st['items'] as $jb_i => $jb_step ) : ?>
						<div class="grid grid-cols-[3rem_1fr] gap-x-5" data-reveal>
							<div class="flex flex-col items-center">
								<span class="grid h-12 w-12 shrink-0 place-items-center rounded-full border-2 border-flame-500 bg-white font-mono text-sm font-bold text-flame-600"><?php echo (int) ( $jb_i + 1 ); ?></span>
								<?php if ( $jb_i < $jb_total - 1 ) : ?><span class="w-0.5 flex-1 bg-ink-100"></span><?php endif; ?>
							</div>
							<div class="<?php echo $jb_i < $jb_total - 1 ? 'pb-10 pt-2' : 'pt-2'; ?>">
								<h3 class="text-lg font-bold text-ink-900"><?php echo esc_html( $jb_step['title'] ); ?></h3>
								<p class="mt-2 leading-relaxed text-ink-500"><?php echo esc_html( $jb_step['body'] ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $jb_static['cta'] ) ) : $jb_cta = $jb_static['cta']; ?>
		<section class="bg-ink-50/60 py-20 sm:py-28">
			<div class="mx-auto max-w-4xl px-5 text-center sm:px-8">
				<div class="mx-auto max-w-2xl">
					<p class="mb-4 text-xs font-semibold uppercase tracking-[0.14em] text-flame-600"><?php echo esc_html( $jb_cta['label'] ); ?></p>
					<h2 class="text-balance text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl"><?php echo esc_html( $jb_cta['title'] ); ?></h2>
					<p class="mt-5 text-pretty text-lg text-ink-500" data-reveal data-reveal-delay="150"><?php echo esc_html( $jb_cta['body'] ); ?></p>
					<div class="mt-9 flex flex-wrap justify-center gap-3" data-reveal data-reveal-delay="250">
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
