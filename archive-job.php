<?php
/**
 * Careers — /careers. The `job` CPT has `has_archive` + rewrite slug
 * 'careers' (inc/post-types.php), so this archive template IS the Careers
 * page -- there is no separate page-careers.php. Ported from
 * ../jbnewgen/src/app/(frontend)/careers/page.tsx.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$jb_careers = jbnewgen_careers_view();
$jb_jobs    = jbnewgen_active_jobs();
$jb_apply   = function ( $role = '' ) {
	$subject = $role ? sprintf( 'Careers - Application: %s', $role ) : 'Careers - Quick Apply';
	return 'mailto:support@jbnewgen.com?subject=' . rawurlencode( $subject );
};
?>

<section class="relative isolate overflow-hidden bg-ink-950 text-white">
	<?php if ( $jb_careers['hero_image'] ) : ?>
		<img src="<?php echo esc_url( $jb_careers['hero_image'] ); ?>" alt="" class="absolute inset-0 h-full w-full object-cover object-center" fetchpriority="high">
		<div class="absolute inset-0 bg-ink-950/[0.72]"></div>
		<div class="absolute inset-0" style="background:linear-gradient(90deg,rgba(9,11,16,0.52) 0%,rgba(9,11,16,0.26) 55%,rgba(9,11,16,0.08) 100%)"></div>
	<?php endif; ?>
	<div class="mesh pointer-events-none absolute inset-0 <?php echo $jb_careers['hero_image'] ? 'opacity-30' : 'opacity-50'; ?>"></div>
	<div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-[7px] bg-flame-500/15 blur-3xl"></div>
	<div class="pointer-events-none absolute -bottom-24 left-1/4 h-72 w-72 rounded-[7px] bg-flame-500/10 blur-3xl"></div>

	<div class="relative mx-auto max-w-7xl px-5 pb-20 pt-14 sm:px-8 sm:pb-28 sm:pt-20">
		<nav aria-label="<?php esc_attr_e( 'Breadcrumb', 'jbnewgen' ); ?>" class="mb-8">
			<ol class="flex flex-wrap items-center gap-1.5 text-sm text-white/45">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="transition-colors hover:text-white"><?php esc_html_e( 'Home', 'jbnewgen' ); ?></a></li>
				<li class="flex items-center gap-1.5"><?php jbnewgen_icon( 'chevronDown', 14, '-rotate-90 text-white/25' ); ?><span class="text-white/80" aria-current="page"><?php esc_html_e( 'Careers', 'jbnewgen' ); ?></span></li>
			</ol>
		</nav>

		<div class="max-w-3xl">
			<span class="inline-flex items-center rounded-[7px] border border-flame-500/30 bg-flame-500/15 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.14em] text-flame-300"><?php esc_html_e( 'Join the team', 'jbnewgen' ); ?></span>
			<h1 class="mt-7 text-balance text-4xl font-bold tracking-tight sm:text-5xl md:text-[3.4rem] md:leading-[1.05]"><?php esc_html_e( "Build India's growth story ", 'jbnewgen' ); ?><span class="text-flame-400"><?php esc_html_e( 'with JB NewGen', 'jbnewgen' ); ?></span></h1>
			<p class="mt-6 max-w-2xl text-pretty text-base leading-relaxed text-ink-200 sm:text-lg"><?php echo esc_html( $jb_careers['intro'] ); ?></p>
			<div class="mt-9 flex flex-wrap items-center gap-3">
				<a href="#roles" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] bg-flame-500 px-6 text-base font-semibold text-white transition-colors hover:bg-flame-600"><?php esc_html_e( 'View open roles', 'jbnewgen' ); ?></a>
				<a href="<?php echo esc_url( $jb_apply() ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] border border-white/30 bg-white/10 px-6 text-base font-semibold text-white backdrop-blur transition-colors hover:bg-white/20"><?php esc_html_e( 'Quick Apply', 'jbnewgen' ); ?><?php jbnewgen_icon( 'mail', 18 ); ?></a>
			</div>
		</div>
	</div>
</section>

<section id="roles" class="mx-auto max-w-7xl px-5 py-20 sm:px-8 sm:py-28">
	<p class="text-xs font-semibold uppercase tracking-[0.14em] text-flame-600"><?php echo esc_html( $jb_careers['rolesEyebrow'] ); ?></p>
	<h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl"><?php echo esc_html( $jb_careers['rolesTitle'] ); ?></h2>
	<p class="mt-5 max-w-2xl text-pretty text-lg text-ink-500"><?php echo esc_html( $jb_careers['rolesIntro'] ); ?></p>

	<div class="mt-12 grid gap-5 sm:grid-cols-2">
		<?php foreach ( $jb_jobs as $jb_i => $jb_job ) : ?>
			<?php
			$jb_locations = function_exists( 'carbon_get_post_meta' ) ? (string) carbon_get_post_meta( $jb_job->ID, 'locations' ) : '';
			$jb_type      = function_exists( 'carbon_get_post_meta' ) ? (string) carbon_get_post_meta( $jb_job->ID, 'work_type' ) : '';
			$jb_icon      = function_exists( 'carbon_get_post_meta' ) ? (string) carbon_get_post_meta( $jb_job->ID, 'icon' ) : 'briefcase';
			$jb_body      = function_exists( 'carbon_get_post_meta' ) ? (string) carbon_get_post_meta( $jb_job->ID, 'role_body' ) : '';
			?>
			<div class="group flex h-full flex-col gap-4 rounded-2xl border border-ink-100 bg-white p-7 transition-shadow duration-300 hover:shadow-[0_24px_50px_-28px_rgba(8,21,39,0.45)]" data-reveal data-reveal-delay="<?php echo (int) ( ( $jb_i % 2 ) * 80 ); ?>">
				<div class="flex items-center justify-between gap-3">
					<span class="grid h-12 w-12 place-items-center rounded-xl bg-flame-500/10 text-flame-600 ring-1 ring-inset ring-flame-500/15"><?php jbnewgen_icon( $jb_icon, 22 ); ?></span>
					<span class="rounded-full border border-flame-500/20 bg-flame-500/10 px-3 py-1 text-[0.7rem] font-semibold uppercase tracking-wider text-flame-600"><?php echo esc_html( $jb_type ); ?></span>
				</div>
				<h3 class="text-lg font-bold text-ink-900"><?php echo esc_html( $jb_job->post_title ); ?></h3>
				<p class="flex items-start gap-2 text-sm text-ink-500"><?php jbnewgen_icon( 'mapPin', 16, 'mt-0.5 shrink-0 text-flame-500' ); ?><?php echo esc_html( $jb_locations ); ?></p>
				<p class="text-sm leading-relaxed text-ink-500"><?php echo esc_html( $jb_body ); ?></p>
				<a href="<?php echo esc_url( $jb_apply( $jb_job->post_title ) ); ?>" class="mt-auto inline-flex items-center gap-1.5 text-sm font-semibold text-flame-600"><?php esc_html_e( 'Apply for this role', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowRight', 15, 'transition-transform duration-200 group-hover:translate-x-0.5' ); ?></a>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<section class="relative overflow-hidden bg-ink-950 py-20 text-white sm:py-28">
	<div class="mesh pointer-events-none absolute inset-0 opacity-50"></div>
	<div class="pointer-events-none absolute -bottom-24 -right-16 h-80 w-80 rounded-[7px] bg-flame-500/12 blur-3xl"></div>
	<div class="relative mx-auto max-w-6xl px-5 sm:px-8">
		<p class="mb-4 text-sm font-semibold uppercase tracking-[0.14em] text-flame-300"><?php esc_html_e( "Who You'd Join", 'jbnewgen' ); ?></p>
		<h2 class="max-w-3xl text-balance text-3xl font-bold tracking-tight sm:text-4xl md:text-[2.5rem] md:leading-[1.12]"><?php esc_html_e( "India's on-ground GTM partner for global startups", 'jbnewgen' ); ?></h2>
		<div class="mt-12 grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-4">
			<?php
			$jb_stats = array(
				array( 'value' => '35+', 'label' => __( 'Years in the India market', 'jbnewgen' ) ),
				array( 'value' => '7,000+', 'label' => __( 'Channel partners built & managed', 'jbnewgen' ) ),
				array( 'value' => '30,000+', 'label' => __( 'Retail touchpoints established', 'jbnewgen' ) ),
				array( 'value' => '200–500%', 'label' => __( 'Revenue growth delivered', 'jbnewgen' ) ),
			);
			foreach ( $jb_stats as $jb_i => $jb_s ) :
				?>
				<div class="text-center" data-reveal data-reveal-delay="<?php echo (int) ( ( $jb_i % 4 ) * 60 ); ?>">
					<div class="text-3xl font-bold tracking-tight text-flame-400 sm:text-4xl"><?php echo esc_html( $jb_s['value'] ); ?></div>
					<p class="mx-auto mt-2 max-w-[12rem] text-xs leading-relaxed text-ink-300"><?php echo esc_html( $jb_s['label'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
jbnewgen_nav_grid( array(
	'title'   => __( 'Get to know us', 'jbnewgen' ),
	'caption' => __( 'Cross-links from Careers', 'jbnewgen' ),
	'columns' => 3,
	'items'   => array(
		array( 'label' => __( 'Our CEO', 'jbnewgen' ), 'href' => '/about/ceo', 'desc' => 'Joyjeet Bose · 35+ years', 'icon' => 'users' ),
		array( 'label' => __( 'The Company', 'jbnewgen' ), 'href' => '/about/company', 'desc' => "Who you'd work with", 'icon' => 'briefcase' ),
		array( 'label' => __( 'Contact', 'jbnewgen' ), 'href' => '/contact', 'desc' => 'Reach the team', 'icon' => 'mail' ),
	),
) );
?>

<section class="relative overflow-hidden bg-ink-950 py-20 text-white sm:py-28">
	<div class="mesh pointer-events-none absolute inset-0 opacity-60"></div>
	<div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-[7px] bg-flame-500/20 blur-3xl"></div>
	<div class="pointer-events-none absolute -bottom-20 -left-10 h-64 w-64 rounded-[7px] bg-flame-500/20 blur-3xl"></div>
	<div class="relative mx-auto max-w-2xl px-5 text-center sm:px-8">
		<p class="text-[0.8125rem] font-semibold uppercase tracking-[0.14em] text-flame-300"><?php esc_html_e( 'Ready to apply?', 'jbnewgen' ); ?></p>
		<h2 class="mt-4 text-balance text-3xl font-bold sm:text-4xl md:text-[2.7rem] md:leading-[1.08]"><?php esc_html_e( 'One application. Every relevant role.', 'jbnewgen' ); ?></h2>
		<p class="mx-auto mt-5 max-w-xl text-pretty leading-relaxed text-ink-200 sm:text-lg"><?php esc_html_e( "Send us your details and we'll match you to the openings you're a fit for. Prefer to talk first? Reach the team directly.", 'jbnewgen' ); ?></p>
		<div class="mt-8 flex flex-wrap justify-center gap-3">
			<a href="<?php echo esc_url( $jb_apply() ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] bg-flame-500 px-6 text-base font-semibold text-white transition-colors hover:bg-flame-600"><?php esc_html_e( 'Quick Apply', 'jbnewgen' ); ?><?php jbnewgen_icon( 'mail', 18 ); ?></a>
			<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] border border-white/30 bg-white/10 px-6 text-base font-semibold text-white backdrop-blur transition-colors hover:bg-white/20"><?php esc_html_e( 'Contact us', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowUpRight', 18 ); ?></a>
		</div>
		<p class="mt-8 text-sm text-ink-400"><?php echo esc_html( $jb_careers['address'] ); ?></p>
	</div>
</section>

<?php get_footer(); ?>
