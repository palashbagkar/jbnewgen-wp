<?php
/**
 * Template Name: About — The Company
 *
 * Ported from ../jbnewgen/src/app/(frontend)/about/company/page.tsx. hero
 * body/who-title/who-intro/stats come from the CMS (jbnewgen_about_company_view,
 * options-about.php); diffs/story/network/services/CEO quote/SAAS section are
 * not modelled as Carbon Fields yet, so they come verbatim from
 * jbnewgen_about_company_extra() (inc/data/company-*.php).
 */

defined( 'ABSPATH' ) || exit;

get_header();

$jb_view  = jbnewgen_about_company_view();
$jb_extra = jbnewgen_about_company_extra();
?>

<section class="relative isolate overflow-hidden bg-ink-950 text-white">
	<div class="mesh pointer-events-none absolute inset-0 opacity-50"></div>
	<div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-[7px] bg-flame-500/15 blur-3xl"></div>
	<div class="pointer-events-none absolute -bottom-24 left-1/4 h-72 w-72 rounded-[7px] bg-flame-500/10 blur-3xl"></div>

	<div class="relative mx-auto max-w-7xl px-5 pb-20 pt-14 sm:px-8 sm:pb-28 sm:pt-20">
		<nav aria-label="<?php esc_attr_e( 'Breadcrumb', 'jbnewgen' ); ?>" class="mb-8">
			<ol class="flex flex-wrap items-center gap-1.5 text-sm text-white/45">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="transition-colors hover:text-white"><?php esc_html_e( 'Home', 'jbnewgen' ); ?></a></li>
				<li class="flex items-center gap-1.5"><?php jbnewgen_icon( 'chevronDown', 14, '-rotate-90 text-white/25' ); ?><a href="<?php echo esc_url( home_url( '/about' ) ); ?>" class="transition-colors hover:text-white"><?php esc_html_e( 'About', 'jbnewgen' ); ?></a></li>
				<li class="flex items-center gap-1.5"><?php jbnewgen_icon( 'chevronDown', 14, '-rotate-90 text-white/25' ); ?><span class="text-white/80" aria-current="page"><?php esc_html_e( 'The Company', 'jbnewgen' ); ?></span></li>
			</ol>
		</nav>

		<div class="max-w-3xl">
			<span class="inline-flex items-center rounded-[7px] border border-flame-500/30 bg-flame-500/15 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.14em] text-flame-300"><?php esc_html_e( 'About JB NewGen Enterprises', 'jbnewgen' ); ?></span>
			<h1 class="mt-7 text-balance text-4xl font-bold tracking-tight sm:text-5xl md:text-[3.4rem] md:leading-[1.05]">
				<?php esc_html_e( 'India’s on-ground partner for ', 'jbnewgen' ); ?><span class="text-flame-400"><?php esc_html_e( 'global startups entering India', 'jbnewgen' ); ?></span>
			</h1>
			<p class="mt-6 max-w-xl text-pretty text-base leading-relaxed text-ink-200 sm:text-lg"><?php echo esc_html( $jb_view['heroBody'] ); ?></p>
			<div class="mt-9 flex flex-wrap items-center gap-3">
				<a href="<?php echo esc_url( home_url( '/quote' ) ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] bg-flame-500 px-6 text-base font-semibold text-white transition-colors hover:bg-flame-600"><?php esc_html_e( 'Book a Free India Entry Call', 'jbnewgen' ); ?></a>
				<a href="#story" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] border border-white/30 bg-white/10 px-6 text-base font-semibold text-white backdrop-blur transition-colors hover:bg-white/20"><?php esc_html_e( 'Our Story', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowRight', 18 ); ?></a>
			</div>
		</div>
	</div>
</section>

<section class="mx-auto max-w-7xl px-5 py-20 sm:px-8 sm:py-28">
	<div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
		<div>
			<p class="text-xs font-semibold uppercase tracking-[0.14em] text-flame-600"><?php esc_html_e( 'Who We Are', 'jbnewgen' ); ?></p>
			<h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl"><?php echo esc_html( $jb_view['whoTitle'] ); ?></h2>
			<div class="mt-6 space-y-4 leading-relaxed text-ink-500">
				<?php foreach ( $jb_view['whoIntro'] as $jb_p ) : ?>
					<p><?php echo esc_html( $jb_p ); ?></p>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="grid gap-4 sm:grid-cols-2">
			<?php foreach ( $jb_view['stats'] as $jb_i => $jb_s ) : ?>
				<div class="card flex flex-col rounded-2xl border border-ink-100 p-6" data-reveal data-reveal-delay="<?php echo (int) ( ( $jb_i % 2 ) * 80 ); ?>">
					<div class="text-4xl font-bold tracking-tight text-flame-600"><?php echo esc_html( $jb_s['value'] ); ?></div>
					<p class="mt-3 text-sm leading-relaxed text-ink-500"><?php echo esc_html( $jb_s['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="bg-ink-50/50 py-20 sm:py-28">
	<div class="mx-auto max-w-7xl px-5 sm:px-8">
		<div class="max-w-3xl">
			<p class="text-xs font-semibold uppercase tracking-[0.14em] text-flame-600"><?php esc_html_e( 'What Makes Us Different', 'jbnewgen' ); ?></p>
			<h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl"><?php esc_html_e( 'Four things that separate JB NewGen from every other India consultancy', 'jbnewgen' ); ?></h2>
			<p class="mt-5 text-pretty text-lg text-ink-500"><?php esc_html_e( "Most India consultancies give you frameworks. We give you outcomes. Here's what that actually means in practice.", 'jbnewgen' ); ?></p>
		</div>
		<div class="mt-12 grid gap-5 sm:grid-cols-2">
			<?php foreach ( $jb_extra['diffs'] as $jb_i => $jb_d ) : ?>
				<div class="group flex flex-col gap-4 rounded-2xl border border-ink-100 bg-white p-7 transition-shadow duration-300 hover:shadow-[0_24px_50px_-28px_rgba(8,21,39,0.45)]" data-reveal data-reveal-delay="<?php echo (int) ( ( $jb_i % 2 ) * 80 ); ?>">
					<span class="grid h-12 w-12 place-items-center rounded-xl bg-flame-500/10 text-flame-600 ring-1 ring-inset ring-flame-500/15"><?php jbnewgen_icon( $jb_d['icon'], 22 ); ?></span>
					<h3 class="text-lg font-bold text-ink-900"><?php echo esc_html( $jb_d['title'] ); ?></h3>
					<p class="text-sm leading-relaxed text-ink-500"><?php echo esc_html( $jb_d['body'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section id="story" class="mx-auto max-w-7xl px-5 py-20 sm:px-8 sm:py-28">
	<p class="text-xs font-semibold uppercase tracking-[0.14em] text-flame-600"><?php esc_html_e( 'Our Story', 'jbnewgen' ); ?></p>
	<h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl"><?php esc_html_e( "Built from 35 years of doing - not advising", 'jbnewgen' ); ?></h2>
	<div class="mt-6 max-w-3xl space-y-5 leading-relaxed text-ink-500">
		<?php foreach ( $jb_extra['story'] as $jb_p ) : ?>
			<p><?php echo esc_html( $jb_p ); ?></p>
		<?php endforeach; ?>
	</div>

	<div class="mt-14 grid grid-cols-2 gap-x-6 gap-y-10 rounded-2xl border border-ink-100 bg-ink-50/60 px-6 py-10 sm:grid-cols-3 lg:grid-cols-5">
		<?php foreach ( $jb_extra['network'] as $jb_i => $jb_n ) : ?>
			<div class="text-center" data-reveal data-reveal-delay="<?php echo (int) ( ( $jb_i % 5 ) * 60 ); ?>">
				<div class="text-3xl font-bold tracking-tight text-flame-600 sm:text-4xl"><?php echo esc_html( $jb_n['value'] ); ?></div>
				<p class="mx-auto mt-2 max-w-[12rem] text-xs leading-relaxed text-ink-500"><?php echo esc_html( $jb_n['label'] ); ?></p>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<section class="bg-ink-50/50 py-20 sm:py-28">
	<div class="mx-auto max-w-7xl px-5 sm:px-8">
		<div class="max-w-3xl">
			<p class="text-xs font-semibold uppercase tracking-[0.14em] text-flame-600"><?php esc_html_e( 'What We Do', 'jbnewgen' ); ?></p>
			<h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl"><?php esc_html_e( 'Four integrated services - one India entry outcome', 'jbnewgen' ); ?></h2>
			<p class="mt-5 text-pretty text-lg text-ink-500"><?php esc_html_e( 'Every service we offer is designed to support one goal: getting your company to market in India faster, more compliantly, and with a stronger foundation than you could build alone.', 'jbnewgen' ); ?></p>
		</div>
		<div class="mt-12 grid gap-5 sm:grid-cols-2">
			<?php foreach ( $jb_extra['services'] as $jb_i => $jb_s ) : ?>
				<a href="<?php echo esc_url( jbnewgen_href( $jb_s['href'] ) ); ?>" class="group relative flex h-full flex-col gap-4 overflow-hidden rounded-2xl border border-ink-100 bg-white p-7 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_24px_50px_-28px_rgba(8,21,39,0.45)]" data-reveal data-reveal-delay="<?php echo (int) ( ( $jb_i % 2 ) * 80 ); ?>">
					<span class="pointer-events-none absolute inset-x-0 top-0 h-0.5 origin-left scale-x-0 bg-flame-500 transition-transform duration-300 group-hover:scale-x-100"></span>
					<div class="flex items-center gap-3">
						<span class="grid h-11 w-11 place-items-center rounded-xl bg-flame-500/10 text-flame-600 ring-1 ring-inset ring-flame-500/15"><?php jbnewgen_icon( $jb_s['icon'], 20 ); ?></span>
						<span class="font-mono text-xs uppercase tracking-widest text-flame-600"><?php echo esc_html( $jb_s['num'] ); ?></span>
					</div>
					<h3 class="text-lg font-bold text-ink-900"><?php echo esc_html( $jb_s['title'] ); ?></h3>
					<p class="text-sm leading-relaxed text-ink-500"><?php echo esc_html( $jb_s['body'] ); ?></p>
					<span class="mt-auto inline-flex items-center gap-1.5 text-sm font-semibold text-flame-600"><?php esc_html_e( 'Learn more', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowRight', 15, 'transition-transform duration-200 group-hover:translate-x-0.5' ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="relative overflow-hidden bg-ink-950 py-20 text-white sm:py-28">
	<div class="mesh pointer-events-none absolute inset-0 opacity-50"></div>
	<div class="pointer-events-none absolute -left-16 top-10 h-64 w-64 rounded-[7px] bg-flame-500/15 blur-3xl"></div>
	<div class="relative mx-auto max-w-3xl px-5 text-center sm:px-8">
		<?php jbnewgen_icon( 'quote', 48, 'mx-auto text-flame-400/70' ); ?>
		<blockquote class="mt-6 text-balance text-xl font-medium leading-relaxed sm:text-2xl md:text-[1.7rem]"><?php esc_html_e( "India is not a market you figure out from the outside. Every foreign company that has built something meaningful here did it with on-ground partners who understood the culture, the channels, and the complexity. That's what JB NewGen is - the India team you don't have yet, working as if your success is our own.", 'jbnewgen' ); ?></blockquote>
		<div class="mt-8 text-sm font-semibold uppercase tracking-[0.08em] text-flame-300"><?php esc_html_e( 'Joyjeet Bose - Founder & CEO, JB NewGen Enterprises', 'jbnewgen' ); ?></div>
		<div class="mt-8">
			<a href="<?php echo esc_url( home_url( '/about/ceo' ) ); ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-[7px] border border-white/30 bg-white/10 px-5 text-sm font-semibold text-white backdrop-blur transition-colors hover:bg-white/20"><?php esc_html_e( 'Meet Joyjeet Bose', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowRight', 16 ); ?></a>
		</div>
	</div>
</section>

<section id="saas" class="scroll-mt-20 bg-ink-50/50 py-20 sm:py-28">
	<div class="mx-auto grid max-w-7xl items-center gap-10 px-5 sm:px-8 lg:grid-cols-2 lg:gap-16">
		<div>
			<p class="mb-5 text-xs font-semibold uppercase tracking-[0.14em] text-flame-600"><?php esc_html_e( 'Coming Soon', 'jbnewgen' ); ?></p>
			<h2 class="text-balance text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl"><?php esc_html_e( '“SAAS-Sales as a Service” - India’s marketplace for global startups, local partners, and enterprise buyers', 'jbnewgen' ); ?></h2>
			<p class="mt-5 text-pretty text-lg text-ink-500"><?php esc_html_e( '“SAAS-Sales as a Service” is a curated marketplace community connecting global startups seeking India entry with vetted Indian channel partners, specialist agencies, and enterprise CIOs and CTOs actively evaluating new technologies.', 'jbnewgen' ); ?></p>
			<ul class="mt-8 space-y-4">
				<?php foreach ( $jb_extra['bridgeFeatures'] as $jb_f ) : ?>
					<li class="flex gap-3">
						<span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-flame-500"></span>
						<p class="leading-relaxed text-ink-600"><span class="font-semibold text-ink-900"><?php echo esc_html( $jb_f['lead'] ); ?></span> <?php echo esc_html( $jb_f['rest'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div>
			<div class="relative overflow-hidden rounded-3xl bg-ink-950 p-8 text-white sm:p-10" data-reveal>
				<div class="mesh pointer-events-none absolute inset-0 opacity-40"></div>
				<div class="relative">
					<h3 class="text-xl font-bold"><?php esc_html_e( 'Join the “SAAS-Sales as a Service” founding community', 'jbnewgen' ); ?></h3>
					<p class="mt-3 leading-relaxed text-ink-300"><?php esc_html_e( "We're onboarding our first cohort of global startups and India partners. Founding members get priority matching, direct introductions from the JB NewGen team, and early access to community events.", 'jbnewgen' ); ?></p>
					<div class="mt-7 flex flex-wrap items-center gap-3">
						<a href="mailto:sales@jbnewgen.com?subject=SAAS-Sales%20as%20a%20Service%20-%20Founding%20Member%20Interest" class="inline-flex h-11 items-center justify-center gap-2 rounded-[7px] bg-flame-500 px-5 text-sm font-semibold text-white transition-colors hover:bg-flame-600"><?php esc_html_e( 'Express Interest', 'jbnewgen' ); ?></a>
						<a href="https://www.salesasaservice.in" target="_blank" rel="noreferrer" class="inline-flex h-11 items-center justify-center gap-2 rounded-[7px] border border-white/30 bg-white/10 px-5 text-sm font-semibold text-white backdrop-blur transition-colors hover:bg-white/20"><?php esc_html_e( 'www.salesasaservice.in', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowUpRight', 16 ); ?></a>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="relative overflow-hidden bg-ink-950 py-20 text-white sm:py-28">
	<div class="mesh pointer-events-none absolute inset-0 opacity-60"></div>
	<div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-[7px] bg-flame-500/20 blur-3xl"></div>
	<div class="pointer-events-none absolute -bottom-20 -left-10 h-64 w-64 rounded-[7px] bg-flame-500/20 blur-3xl"></div>
	<div class="relative mx-auto max-w-2xl px-5 text-center sm:px-8">
		<p class="text-[0.8125rem] font-semibold uppercase tracking-[0.14em] text-flame-300"><?php esc_html_e( 'Work With Us', 'jbnewgen' ); ?></p>
		<h2 class="mt-4 text-balance text-3xl font-bold sm:text-4xl md:text-[2.7rem] md:leading-[1.08]"><?php esc_html_e( 'Ready to make India your next market?', 'jbnewgen' ); ?></h2>
		<p class="mx-auto mt-5 max-w-xl text-pretty leading-relaxed text-ink-200 sm:text-lg"><?php esc_html_e( "Book a free 45-minute India Entry Strategy call with our team. We'll be direct with you about the opportunity, the complexity, and exactly what it takes to build something real in India.", 'jbnewgen' ); ?></p>
		<div class="mt-8 flex flex-wrap justify-center gap-3">
			<a href="<?php echo esc_url( home_url( '/quote' ) ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] bg-flame-500 px-6 text-base font-semibold text-white transition-colors hover:bg-flame-600"><?php esc_html_e( 'Book Your Free Strategy Call', 'jbnewgen' ); ?></a>
			<a href="mailto:sales@jbnewgen.com" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] border border-white/30 bg-white/10 px-6 text-base font-semibold text-white backdrop-blur transition-colors hover:bg-white/20"><?php esc_html_e( 'Email Us Directly', 'jbnewgen' ); ?><?php jbnewgen_icon( 'mail', 18 ); ?></a>
		</div>
		<p class="mt-8 text-sm text-ink-400"><?php esc_html_e( 'JB NewGen Enterprises Pvt. Ltd. · 504 Challenger Tower III, Kandivali (E), Mumbai 400 101 · sales@jbnewgen.com · +91 6362864230', 'jbnewgen' ); ?></p>
	</div>
</section>

<?php get_footer(); ?>
