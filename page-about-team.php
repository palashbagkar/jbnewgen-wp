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
			<span class="inline-flex items-center rounded-[7px] border border-flame-500/30 bg-flame-500/15 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.14em] text-flame-300"><?php esc_html_e( 'JB NewGen Enterprises', 'jbnewgen' ); ?></span>
			<h1 class="mt-7 text-5xl font-bold tracking-tight sm:text-6xl md:text-[4rem] md:leading-[1.02]"><?php esc_html_e( 'Core Team', 'jbnewgen' ); ?></h1>
			<p class="mt-6 max-w-2xl text-pretty text-lg font-medium text-flame-300 sm:text-xl"><?php echo esc_html( $jb_team['introTitle'] ); ?></p>
			<p class="mt-5 max-w-2xl text-pretty leading-relaxed text-ink-200"><?php echo esc_html( $jb_team['introBody'] ); ?></p>
		</div>
	</div>
</section>

<?php
// The React version caps the grid at 4 columns and only ever adds rows past
// that -- COLUMN_CLASS in team/page.tsx.
$jb_team_cols = max( 1, min( 4, count( $jb_team['members'] ) ) );
?>
<section class="mx-auto max-w-7xl px-5 py-20 sm:px-8 sm:py-28">
	<div class="grid gap-6 sm:grid-cols-2 jb-team-grid" style="--jb-team-cols:<?php echo (int) $jb_team_cols; ?>">
		<?php foreach ( $jb_team['members'] as $jb_i => $jb_m ) : ?>
			<div class="flex h-full flex-col overflow-hidden rounded-2xl border border-ink-100 bg-white" data-reveal data-reveal-delay="<?php echo (int) ( ( $jb_i % $jb_team_cols ) * 70 ); ?>">
				<div class="relative aspect-[7/9] w-full overflow-hidden bg-ink-50">
					<?php if ( ! empty( $jb_m['photo'] ) ) : ?>
						<img src="<?php echo esc_url( $jb_m['photo'] ); ?>" alt="<?php echo esc_attr( $jb_m['name'] . ' - ' . $jb_m['role'] . ', JB NewGen' ); ?>" class="absolute inset-0 h-full w-full object-cover object-top" loading="lazy">
					<?php endif; ?>
					<?php if ( ! empty( $jb_m['linkedin'] ) ) : ?>
						<a href="<?php echo esc_url( $jb_m['linkedin'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $jb_m['name'] . ' on LinkedIn' ); ?>" class="absolute bottom-3 right-3 grid h-10 w-10 place-items-center rounded-full bg-flame-500 text-white shadow-lg shadow-ink-950/25 ring-1 ring-inset ring-white/25 transition-transform duration-200 hover:scale-110">
							<?php jbnewgen_icon( 'linkedin', 18 ); ?>
						</a>
					<?php endif; ?>
				</div>
				<div class="flex flex-1 flex-col p-6">
					<h2 class="text-lg font-bold text-ink-900"><?php echo esc_html( $jb_m['name'] ); ?></h2>
					<p class="mt-1 text-sm font-semibold uppercase tracking-[0.12em] text-flame-600"><?php echo esc_html( $jb_m['role'] ); ?></p>
					<p class="mt-4 text-sm leading-relaxed text-ink-500"><?php echo esc_html( $jb_m['bio'] ); ?></p>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<?php
jbnewgen_nav_grid( array(
	'title'   => __( 'More about JB NewGen', 'jbnewgen' ),
	'caption' => __( 'The rest of the About cluster', 'jbnewgen' ),
	'columns' => 3,
	'items'   => array(
		array( 'label' => __( 'The Company', 'jbnewgen' ), 'href' => '/about/company', 'desc' => __( 'JB NewGen Enterprises', 'jbnewgen' ), 'icon' => 'globe' ),
		array( 'label' => __( 'Our CEO', 'jbnewgen' ), 'href' => '/about/ceo', 'desc' => __( 'Joyjeet Bose · 35+ years experience', 'jbnewgen' ), 'icon' => 'users' ),
		array( 'label' => __( 'Careers', 'jbnewgen' ), 'href' => '/careers', 'icon' => 'briefcase' ),
	),
) );

jbnewgen_next_step();

get_footer();
