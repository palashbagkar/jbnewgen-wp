<?php
/**
 * Template Name: Contact
 *
 * Ported from ../jbnewgen/src/app/(frontend)/contact/page.tsx.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$jb_contact = jbnewgen_contact_view();
$jb_site    = jbnewgen_site_info();

$jb_details = array(
	array( 'icon' => 'phone', 'label' => __( 'Call us', 'jbnewgen' ), 'value' => $jb_site['phone'], 'href' => $jb_site['phone_href'], 'note' => __( 'Mon – Sat', 'jbnewgen' ) ),
	array( 'icon' => 'mail', 'label' => __( 'Email sales', 'jbnewgen' ), 'value' => $jb_site['emails']['sales'], 'href' => 'mailto:' . $jb_site['emails']['sales'] ),
	array( 'icon' => 'mail', 'label' => __( 'Email support', 'jbnewgen' ), 'value' => $jb_site['emails']['support'], 'href' => 'mailto:' . $jb_site['emails']['support'] ),
	array( 'icon' => 'mapPin', 'label' => __( 'Office', 'jbnewgen' ), 'value' => '504 Challenger Tower III, Thakur Village, Kandivali (E), Mumbai – 400 101', 'href' => 'https://maps.google.com/?q=504+Challenger+Tower+III+Kandivali+East+Mumbai+400101' ),
	array( 'icon' => 'clock', 'label' => __( 'Business hours', 'jbnewgen' ), 'value' => 'Mon – Sat: 8 am – 5 pm · Sunday: Closed', 'href' => $jb_site['phone_href'] ),
);
$jb_consult_areas = array(
	__( 'Business Consultancy', 'jbnewgen' ),
	__( 'Digital Transformation', 'jbnewgen' ),
	__( 'Digital Marketing', 'jbnewgen' ),
	__( 'CPaaS & Omnichannel', 'jbnewgen' ),
);
?>

<section class="relative isolate overflow-hidden bg-ink-950 text-white">
	<?php if ( $jb_contact['hero_image'] ) : ?>
		<img src="<?php echo esc_url( $jb_contact['hero_image'] ); ?>" alt="" class="absolute inset-0 h-full w-full object-cover object-center" fetchpriority="high">
		<div class="absolute inset-0 bg-ink-950/[0.72]"></div>
		<div class="absolute inset-0" style="background:linear-gradient(90deg,rgba(9,11,16,0.52) 0%,rgba(9,11,16,0.26) 55%,rgba(9,11,16,0.08) 100%)"></div>
	<?php endif; ?>
	<div class="mesh pointer-events-none absolute inset-0 <?php echo $jb_contact['hero_image'] ? 'opacity-30' : 'opacity-50'; ?>"></div>
	<div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-[7px] bg-flame-500/15 blur-3xl"></div>
	<div class="pointer-events-none absolute -bottom-24 left-1/4 h-72 w-72 rounded-[7px] bg-flame-500/10 blur-3xl"></div>

	<div class="relative mx-auto max-w-7xl px-5 pb-20 pt-14 sm:px-8 sm:pb-28 sm:pt-20">
		<nav aria-label="<?php esc_attr_e( 'Breadcrumb', 'jbnewgen' ); ?>" class="mb-8">
			<ol class="flex flex-wrap items-center gap-1.5 text-sm text-white/45">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="transition-colors hover:text-white"><?php esc_html_e( 'Home', 'jbnewgen' ); ?></a></li>
				<li class="flex items-center gap-1.5"><?php jbnewgen_icon( 'chevronDown', 14, '-rotate-90 text-white/25' ); ?><span class="text-white/80" aria-current="page"><?php esc_html_e( 'Contact', 'jbnewgen' ); ?></span></li>
			</ol>
		</nav>

		<div class="max-w-3xl">
			<span class="inline-flex items-center rounded-[7px] border border-flame-500/30 bg-flame-500/15 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.14em] text-flame-300"><?php esc_html_e( 'Contact', 'jbnewgen' ); ?></span>
			<h1 class="mt-7 text-balance text-4xl font-bold tracking-tight sm:text-5xl md:text-[3.4rem] md:leading-[1.05]"><?php esc_html_e( 'Make a free consultation with ', 'jbnewgen' ); ?><span class="text-flame-400"><?php esc_html_e( 'our expert team', 'jbnewgen' ); ?></span></h1>
			<p class="mt-6 max-w-2xl text-pretty text-base leading-relaxed text-ink-200 sm:text-lg"><?php esc_html_e( "We work with a passion for taking on challenges and creating scalable, sustainable growth for our clients' business. Every contact action below is a live link.", 'jbnewgen' ); ?></p>
			<div class="mt-9 flex flex-wrap items-center gap-3">
				<a href="<?php echo esc_url( $jb_site['phone_href'] ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] bg-flame-500 px-6 text-base font-semibold text-white transition-colors hover:bg-flame-600"><?php jbnewgen_icon( 'phone', 18 ); ?><?php echo esc_html( sprintf( __( 'Call %s', 'jbnewgen' ), $jb_site['phone'] ) ); ?></a>
				<a href="<?php echo esc_url( home_url( '/quote' ) ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] border border-white/30 bg-white/10 px-6 text-base font-semibold text-white backdrop-blur transition-colors hover:bg-white/20"><?php esc_html_e( 'Get a quote', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowRight', 18 ); ?></a>
			</div>
		</div>
	</div>
</section>

<section class="mx-auto max-w-7xl px-5 py-20 sm:px-8 sm:py-28">
	<div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
		<div>
			<p class="text-xs font-semibold uppercase tracking-[0.14em] text-flame-600"><?php esc_html_e( 'Reach Us', 'jbnewgen' ); ?></p>
			<h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl"><?php esc_html_e( 'One team, every channel open', 'jbnewgen' ); ?></h2>
			<p class="mt-5 text-pretty text-lg text-ink-500"><?php esc_html_e( 'Call, email, or drop by the Mumbai office. Whichever you pick, you reach the people who do the work.', 'jbnewgen' ); ?></p>
			<div class="mt-8 space-y-3">
				<?php foreach ( $jb_details as $jb_i => $jb_d ) : ?>
					<a href="<?php echo esc_url( $jb_d['href'] ); ?>" target="_blank" rel="noreferrer" class="group flex items-start gap-4 rounded-2xl border border-ink-100 bg-white p-5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_24px_50px_-28px_rgba(8,21,39,0.45)]" data-reveal data-reveal-delay="<?php echo (int) ( ( $jb_i % 3 ) * 60 ); ?>">
						<span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-flame-500/10 text-flame-600 ring-1 ring-inset ring-flame-500/15"><?php jbnewgen_icon( $jb_d['icon'], 20 ); ?></span>
						<span class="min-w-0 flex-1">
							<span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-ink-400">
								<?php echo esc_html( $jb_d['label'] ); ?>
								<?php if ( ! empty( $jb_d['note'] ) ) : ?><span class="rounded-full bg-ink-100 px-2 py-0.5 text-[0.65rem] normal-case tracking-normal text-ink-500"><?php echo esc_html( $jb_d['note'] ); ?></span><?php endif; ?>
							</span>
							<span class="mt-1 block font-semibold text-ink-900"><?php echo esc_html( $jb_d['value'] ); ?></span>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="relative overflow-hidden rounded-3xl bg-ink-950 p-8 text-white sm:p-10" data-reveal>
			<div class="mesh pointer-events-none absolute inset-0 opacity-40"></div>
			<div class="relative">
				<p class="text-sm font-semibold uppercase tracking-[0.14em] text-flame-300"><?php esc_html_e( 'Free Consultation', 'jbnewgen' ); ?></p>
				<h3 class="mt-3 text-2xl font-bold"><?php esc_html_e( 'Tell us what you need help with', 'jbnewgen' ); ?></h3>
				<p class="mt-3 leading-relaxed text-ink-300"><?php esc_html_e( "Book a free consultation with our expert team. Pick the area closest to your challenge and we'll take it from there.", 'jbnewgen' ); ?></p>
				<ul class="mt-7 grid gap-3 sm:grid-cols-2">
					<?php foreach ( $jb_consult_areas as $jb_area ) : ?>
						<li class="flex items-start gap-2.5 rounded-[7px] border border-white/10 bg-white/[0.05] px-4 py-3"><?php jbnewgen_icon( 'check', 16, 'mt-0.5 shrink-0 text-flame-300' ); ?><span class="text-sm font-medium text-white"><?php echo esc_html( $jb_area ); ?></span></li>
					<?php endforeach; ?>
				</ul>
				<div class="mt-8 flex flex-wrap gap-3">
					<a href="<?php echo esc_url( home_url( '/quote' ) ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] bg-flame-500 px-6 text-base font-semibold text-white transition-colors hover:bg-flame-600"><?php esc_html_e( 'Get a quote now', 'jbnewgen' ); ?></a>
					<a href="mailto:<?php echo esc_attr( $jb_site['emails']['sales'] ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] border border-white/30 bg-white/10 px-6 text-base font-semibold text-white backdrop-blur transition-colors hover:bg-white/20"><?php esc_html_e( 'Email us', 'jbnewgen' ); ?><?php jbnewgen_icon( 'mail', 18 ); ?></a>
				</div>
				<div class="mt-8 flex flex-wrap gap-2">
					<?php foreach ( $jb_site['socials'] as $jb_s ) : ?>
						<a href="<?php echo esc_url( $jb_s['href'] ); ?>" target="_blank" rel="noreferrer" aria-label="<?php echo esc_attr( $jb_s['label'] ); ?>" class="grid h-10 w-10 place-items-center rounded-[7px] border border-white/15 bg-white/[0.05] text-ink-200 transition-colors hover:border-flame-500/40 hover:text-flame-300"><?php jbnewgen_icon( $jb_s['icon'], 18 ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</section>

<?php
jbnewgen_nav_grid( array(
	'title'   => __( 'Reach us', 'jbnewgen' ),
	'caption' => __( 'Device & conversion links', 'jbnewgen' ),
	'columns' => 3,
	'items'   => array(
		array( 'label' => __( 'Call us', 'jbnewgen' ), 'href' => $jb_site['phone_href'], 'desc' => $jb_site['phone'], 'icon' => 'phone', 'external' => true ),
		array( 'label' => __( 'Email sales', 'jbnewgen' ), 'href' => 'mailto:' . $jb_site['emails']['sales'], 'desc' => $jb_site['emails']['sales'], 'icon' => 'mail', 'external' => true ),
		array( 'label' => __( 'Email support', 'jbnewgen' ), 'href' => 'mailto:' . $jb_site['emails']['support'], 'desc' => $jb_site['emails']['support'], 'icon' => 'mail', 'external' => true ),
		array( 'label' => __( 'Get a quote', 'jbnewgen' ), 'href' => '/quote', 'desc' => __( 'Start a project', 'jbnewgen' ), 'icon' => 'spark' ),
		array( 'label' => __( 'LinkedIn', 'jbnewgen' ), 'href' => $jb_site['socials'][0]['href'], 'desc' => __( 'Connect with us', 'jbnewgen' ), 'icon' => 'linkedin', 'external' => true ),
		array( 'label' => __( 'Careers', 'jbnewgen' ), 'href' => '/careers', 'desc' => __( 'Join the team', 'jbnewgen' ), 'icon' => 'briefcase' ),
	),
) );
?>

<section class="relative overflow-hidden bg-ink-950 py-20 text-white sm:py-28">
	<div class="mesh pointer-events-none absolute inset-0 opacity-60"></div>
	<div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-[7px] bg-flame-500/20 blur-3xl"></div>
	<div class="pointer-events-none absolute -bottom-20 -left-10 h-64 w-64 rounded-[7px] bg-flame-500/20 blur-3xl"></div>
	<div class="relative mx-auto max-w-2xl px-5 text-center sm:px-8">
		<p class="text-[0.8125rem] font-semibold uppercase tracking-[0.14em] text-flame-300"><?php esc_html_e( "Let's Talk", 'jbnewgen' ); ?></p>
		<h2 class="mt-4 text-balance text-3xl font-bold sm:text-4xl md:text-[2.7rem] md:leading-[1.08]"><?php esc_html_e( 'Ready to make India your next market?', 'jbnewgen' ); ?></h2>
		<p class="mx-auto mt-5 max-w-xl text-pretty leading-relaxed text-ink-200 sm:text-lg"><?php esc_html_e( 'Book a free 45-minute strategy call, or reach us directly - whatever works for you.', 'jbnewgen' ); ?></p>
		<div class="mt-8 flex flex-wrap justify-center gap-3">
			<a href="<?php echo esc_url( home_url( '/quote' ) ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] bg-flame-500 px-6 text-base font-semibold text-white transition-colors hover:bg-flame-600"><?php esc_html_e( 'Book Your Free Strategy Call', 'jbnewgen' ); ?></a>
			<a href="mailto:<?php echo esc_attr( $jb_site['emails']['sales'] ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] border border-white/30 bg-white/10 px-6 text-base font-semibold text-white backdrop-blur transition-colors hover:bg-white/20"><?php esc_html_e( 'Email Us Directly', 'jbnewgen' ); ?><?php jbnewgen_icon( 'mail', 18 ); ?></a>
		</div>
		<p class="mt-8 text-sm text-ink-400"><?php esc_html_e( 'JB NewGen Enterprises Private Limited · 504 Challenger Tower III, Thakur Village, Kandivali (E), Mumbai 400 101', 'jbnewgen' ); ?></p>
	</div>
</section>

<?php get_footer(); ?>
