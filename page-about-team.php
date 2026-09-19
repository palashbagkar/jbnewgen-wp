<?php
/**
 * Template Name: About — Core Team
 *
 * Ported from ../jbnewgen/src/app/(frontend)/about/team/page.tsx. Members
 * come from the team_member CPT (jbnewgen_about_team_view), falling back to
 * coreTeam.php verbatim if the CPT is ever emptied.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$jb_team = jbnewgen_about_team_view();
?>

<section class="relative isolate overflow-hidden bg-ink-950 text-white">
	<div class="mesh pointer-events-none absolute inset-0 opacity-50"></div>
	<div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-[7px] bg-flame-500/15 blur-3xl"></div>

	<div class="relative mx-auto max-w-7xl px-5 pb-20 pt-14 sm:px-8 sm:pb-28 sm:pt-20">
		<nav aria-label="<?php esc_attr_e( 'Breadcrumb', 'jbnewgen' ); ?>" class="mb-8">
			<ol class="flex flex-wrap items-center gap-1.5 text-sm text-white/45">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="transition-colors hover:text-white"><?php esc_html_e( 'Home', 'jbnewgen' ); ?></a></li>
				<li class="flex items-center gap-1.5"><?php jbnewgen_icon( 'chevronDown', 14, '-rotate-90 text-white/25' ); ?><a href="<?php echo esc_url( home_url( '/about' ) ); ?>" class="transition-colors hover:text-white"><?php esc_html_e( 'About', 'jbnewgen' ); ?></a></li>
				<li class="flex items-center gap-1.5"><?php jbnewgen_icon( 'chevronDown', 14, '-rotate-90 text-white/25' ); ?><span class="text-white/80" aria-current="page"><?php esc_html_e( 'Core Team', 'jbnewgen' ); ?></span></li>
			</ol>
		</nav>

		<div class="max-w-2xl">
			<span class="inline-flex items-center rounded-[7px] border border-flame-500/30 bg-flame-500/15 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.14em] text-flame-300"><?php esc_html_e( 'Core Team', 'jbnewgen' ); ?></span>
			<h1 class="mt-7 text-balance text-4xl font-bold tracking-tight sm:text-5xl md:text-[3.4rem] md:leading-[1.05]"><?php echo esc_html( $jb_team['introTitle'] ); ?></h1>
			<p class="mt-6 max-w-xl text-pretty text-base leading-relaxed text-ink-200 sm:text-lg"><?php echo esc_html( $jb_team['introBody'] ); ?></p>
		</div>
	</div>
</section>

<section class="mx-auto max-w-7xl px-5 py-20 sm:px-8 sm:py-28">
	<div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
		<?php foreach ( $jb_team['members'] as $jb_i => $jb_m ) : ?>
			<div class="group overflow-hidden rounded-2xl border border-ink-100 bg-white transition-shadow duration-300 hover:shadow-[0_24px_50px_-28px_rgba(8,21,39,0.45)]" data-reveal data-reveal-delay="<?php echo (int) ( ( $jb_i % 3 ) * 80 ); ?>">
				<div class="relative aspect-[27/40] overflow-hidden bg-ink-100">
					<?php if ( ! empty( $jb_m['photo'] ) ) : ?>
						<img src="<?php echo esc_url( $jb_m['photo'] ); ?>" alt="<?php echo esc_attr( $jb_m['name'] ); ?>" class="absolute inset-0 h-full w-full object-cover object-top" loading="lazy">
					<?php else : ?>
						<div class="mesh absolute inset-0 opacity-70"></div>
					<?php endif; ?>
					<?php if ( ! empty( $jb_m['linkedin'] ) ) : ?>
						<a href="<?php echo esc_url( $jb_m['linkedin'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $jb_m['name'] . ' on LinkedIn' ); ?>" class="absolute bottom-3 right-3 grid h-10 w-10 place-items-center rounded-full bg-flame-500 text-white shadow-lg ring-1 ring-inset ring-white/25 transition-transform duration-200 hover:scale-110">
							<?php jbnewgen_icon( 'linkedin', 18 ); ?>
						</a>
					<?php endif; ?>
				</div>
				<div class="p-6">
					<h3 class="text-lg font-bold text-ink-900"><?php echo esc_html( $jb_m['name'] ); ?></h3>
					<p class="mt-0.5 text-sm font-semibold uppercase tracking-wide text-flame-600"><?php echo esc_html( $jb_m['role'] ); ?></p>
					<p class="mt-3 text-sm leading-relaxed text-ink-500"><?php echo esc_html( $jb_m['bio'] ); ?></p>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<section class="relative overflow-hidden bg-ink-950 py-20 text-white sm:py-28">
	<div class="mesh pointer-events-none absolute inset-0 opacity-60"></div>
	<div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-[7px] bg-flame-500/20 blur-3xl"></div>
	<div class="relative mx-auto max-w-2xl px-5 text-center sm:px-8">
		<p class="text-[0.8125rem] font-semibold uppercase tracking-[0.14em] text-flame-300"><?php esc_html_e( "Let's Talk", 'jbnewgen' ); ?></p>
		<h2 class="mt-4 text-balance text-3xl font-bold sm:text-4xl md:text-[2.7rem] md:leading-[1.08]"><?php esc_html_e( 'Want to work with this team?', 'jbnewgen' ); ?></h2>
		<p class="mx-auto mt-5 max-w-xl text-pretty leading-relaxed text-ink-200 sm:text-lg"><?php esc_html_e( 'Book a free strategy call, or explore what JB NewGen does end to end.', 'jbnewgen' ); ?></p>
		<div class="mt-8 flex flex-wrap justify-center gap-3">
			<a href="<?php echo esc_url( home_url( '/quote' ) ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] bg-flame-500 px-6 text-base font-semibold text-white transition-colors hover:bg-flame-600"><?php esc_html_e( 'Book a free consultation', 'jbnewgen' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/about/company' ) ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] border border-white/30 bg-white/10 px-6 text-base font-semibold text-white backdrop-blur transition-colors hover:bg-white/20"><?php esc_html_e( 'The Company', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowRight', 18 ); ?></a>
		</div>
	</div>
</section>

<?php get_footer(); ?>
