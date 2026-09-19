<?php
/**
 * Site header. Ported from ../jbnewgen/src/components/site/Header.tsx.
 *
 * The one piece of real behaviour is the homepage hero treatment: the bar
 * starts transparent over the hero video, blurs progressively as you scroll,
 * then fades to solid white once the hero is past. In React that was three
 * useState values driven by a scroll listener; it is the same arithmetic in
 * vanilla JS (assets/site.js). The solid bar is the DEFAULT in markup and the
 * transparent state is applied by script, so with JavaScript disabled the
 * header is readable rather than invisible over the video.
 *
 * Dropdown and mega panels open on hover AND focus-within, which is pure CSS
 * and therefore keyboard-reachable without any JS.
 */

defined( 'ABSPATH' ) || exit;

$jb_site     = jbnewgen_site_info();
$jb_pillars  = jbnewgen_nav_pillars();
$jb_about    = jbnewgen_about_children();
$jb_cats     = jbnewgen_nav_categories();
$jb_logo_b   = jbnewgen_logo_url( 'logo_black' );
$jb_logo_w   = jbnewgen_logo_url( 'logo_white' );
$jb_logo_alt = function_exists( 'carbon_get_theme_option' ) ? (string) carbon_get_theme_option( 'logo_alt' ) : '';
$jb_logo_alt = $jb_logo_alt ? $jb_logo_alt : $jb_site['name'];

$jb_badge_on    = function_exists( 'carbon_get_theme_option' ) ? (bool) carbon_get_theme_option( 'header_badge_enabled' ) : false;
$jb_badge_label = $jb_badge_on ? (string) carbon_get_theme_option( 'header_badge_label' ) : '';
$jb_badge_href  = $jb_badge_on ? (string) carbon_get_theme_option( 'header_badge_href' ) : '';

// One grid column per pillar, capped at 4 so a fifth wraps instead of crushing
// the columns. Mirrors the inline gridTemplateColumns in Header.tsx.
$jb_mega_cols = max( 1, min( 4, count( $jb_pillars ) ) );

/**
 * The shared nav-link classes. Active state is flame; the colour that reacts
 * to the hero (white over video, ink over white) is handled by .jb-navlink in
 * site.src.css rather than duplicated on every link.
 */
$jb_link = 'jb-navlink rounded-[7px] px-4 py-2 text-[0.95rem] font-medium transition-colors';
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'antialiased' ); ?>>
<?php wp_body_open(); ?>

<a class="sr-only focus:not-sr-only focus:absolute focus:z-[100] focus:m-3 focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:text-ink-900" href="#main"><?php esc_html_e( 'Skip to content', 'jbnewgen' ); ?></a>

<header id="jb-header" class="sticky top-0 z-50" data-hero="<?php echo is_front_page() ? '1' : '0'; ?>">
	<div class="relative">
		<div id="jb-header-wash" class="pointer-events-none absolute inset-0"></div>
		<div id="jb-header-solid" class="pointer-events-none absolute inset-0 bg-white transition-opacity duration-500"></div>

		<div class="relative z-10 mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-5 py-2 sm:px-8">

			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $jb_site['name'] . ' home' ); ?>" class="shrink-0">
				<?php if ( $jb_logo_w ) : ?>
					<img class="jb-logo-light h-12 w-auto shrink-0 object-contain" src="<?php echo esc_url( $jb_logo_w ); ?>" alt="<?php echo esc_attr( $jb_logo_alt ); ?>" width="220" height="56">
				<?php endif; ?>
				<?php if ( $jb_logo_b ) : ?>
					<img class="jb-logo-dark h-12 w-auto shrink-0 object-contain" src="<?php echo esc_url( $jb_logo_b ); ?>" alt="<?php echo esc_attr( $jb_logo_alt ); ?>" width="220" height="56">
				<?php endif; ?>
				<?php if ( ! $jb_logo_w && ! $jb_logo_b ) : ?>
					<span class="font-display text-xl font-bold tracking-tight"><?php echo esc_html( $jb_site['name'] ); ?></span>
				<?php endif; ?>
			</a>

			<nav class="hidden items-center gap-0.5 lg:flex" aria-label="<?php esc_attr_e( 'Primary', 'jbnewgen' ); ?>">

				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="<?php echo esc_attr( $jb_link ); ?><?php echo jbnewgen_nav_is_active( home_url( '/' ) ) ? ' is-active' : ''; ?>"><?php esc_html_e( 'Home', 'jbnewgen' ); ?></a>

				<div class="group static lg:relative">
					<a href="<?php echo esc_url( home_url( '/about' ) ); ?>" class="<?php echo esc_attr( $jb_link ); ?> inline-flex items-center gap-1<?php echo jbnewgen_nav_is_active( home_url( '/about' ) ) ? ' is-active' : ''; ?>">
						<?php esc_html_e( 'About', 'jbnewgen' ); ?>
						<?php jbnewgen_icon( 'chevronDown', 15, 'transition-transform duration-200 group-hover:rotate-180' ); ?>
					</a>
					<div class="invisible absolute left-0 top-full z-40 w-80 pt-3 opacity-0 transition-all duration-200 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
						<div class="card overflow-hidden p-2">
							<?php foreach ( $jb_about as $jb_child ) : ?>
								<a href="<?php echo esc_url( $jb_child['href'] ); ?>" class="flex items-start gap-3 rounded-xl px-3 py-2.5 transition-colors hover:bg-ink-50">
									<span class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-flame-500/15 text-flame-600"><?php jbnewgen_icon( 'arrowUpRight', 16 ); ?></span>
									<span>
										<span class="block text-sm font-semibold text-ink-900"><?php echo esc_html( $jb_child['label'] ); ?></span>
										<?php if ( ! empty( $jb_child['desc'] ) ) : ?>
											<span class="mt-0.5 block text-xs text-ink-500"><?php echo esc_html( $jb_child['desc'] ); ?></span>
										<?php endif; ?>
									</span>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="group static lg:relative">
					<a href="<?php echo esc_url( home_url( '/services' ) ); ?>" class="<?php echo esc_attr( $jb_link ); ?> inline-flex items-center gap-1<?php echo jbnewgen_nav_is_active( home_url( '/services' ) ) ? ' is-active' : ''; ?>">
						<?php esc_html_e( 'Services', 'jbnewgen' ); ?>
						<?php jbnewgen_icon( 'chevronDown', 15, 'transition-transform duration-200 group-hover:rotate-180' ); ?>
					</a>
					<?php if ( $jb_pillars ) : ?>
						<div class="invisible absolute left-1/2 top-full z-40 w-[min(64rem,94vw)] -translate-x-1/2 pt-3 opacity-0 transition-all duration-200 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
							<div class="card p-5">
								<div class="mb-4 flex items-center justify-end">
									<a href="<?php echo esc_url( home_url( '/services' ) ); ?>" class="text-xs font-semibold text-flame-600 hover:underline"><?php esc_html_e( 'View all services', 'jbnewgen' ); ?> &rarr;</a>
								</div>
								<div class="jb-mega-grid grid gap-3" style="--jb-mega-cols:<?php echo (int) $jb_mega_cols; ?>">
									<?php foreach ( $jb_pillars as $jb_pillar ) : ?>
										<div class="rounded-xl bg-ink-50/60 p-3">
											<a href="<?php echo esc_url( $jb_pillar['url'] ); ?>" class="group/hub flex items-center gap-2">
												<span class="grid h-5 w-5 shrink-0 place-items-center rounded-md bg-white text-flame-600"><?php jbnewgen_icon( $jb_pillar['icon'] ? $jb_pillar['icon'] : 'arrowUpRight', 13 ); ?></span>
												<span class="text-[0.8rem] font-bold leading-tight text-ink-900 group-hover/hub:text-flame-600"><?php echo esc_html( $jb_pillar['short'] ); ?></span>
											</a>
											<?php if ( $jb_pillar['services'] ) : ?>
												<ul class="mt-3 space-y-1 border-l border-ink-200 pl-3">
													<?php foreach ( $jb_pillar['services'] as $jb_service ) : ?>
														<li><a href="<?php echo esc_url( $jb_service['url'] ); ?>" class="block rounded-md px-2 py-1 text-[0.78rem] text-ink-600 transition-colors hover:bg-white hover:text-flame-600"><?php echo esc_html( $jb_service['title'] ); ?></a></li>
													<?php endforeach; ?>
												</ul>
											<?php endif; ?>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>

				<div class="group static lg:relative">
					<a href="<?php echo esc_url( home_url( '/insights' ) ); ?>" class="<?php echo esc_attr( $jb_link ); ?> inline-flex items-center gap-1<?php echo jbnewgen_nav_is_active( home_url( '/insights' ) ) ? ' is-active' : ''; ?>">
						<?php esc_html_e( 'Insights', 'jbnewgen' ); ?>
						<?php jbnewgen_icon( 'chevronDown', 15, 'transition-transform duration-200 group-hover:rotate-180' ); ?>
					</a>
					<?php if ( $jb_cats ) : ?>
						<div class="invisible absolute left-0 top-full z-40 w-80 pt-3 opacity-0 transition-all duration-200 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
							<div class="card overflow-hidden p-2">
								<?php foreach ( $jb_cats as $jb_cat ) : ?>
									<a href="<?php echo esc_url( $jb_cat['href'] ); ?>" class="flex items-start gap-3 rounded-xl px-3 py-2.5 transition-colors hover:bg-ink-50">
										<span class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-flame-500/15 text-flame-600"><?php jbnewgen_icon( 'arrowUpRight', 16 ); ?></span>
										<span>
											<span class="block text-sm font-semibold text-ink-900"><?php echo esc_html( $jb_cat['label'] ); ?></span>
											<?php if ( ! empty( $jb_cat['desc'] ) ) : ?>
												<span class="mt-0.5 block text-xs text-ink-500"><?php echo esc_html( $jb_cat['desc'] ); ?></span>
											<?php endif; ?>
										</span>
									</a>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>

				<a href="<?php echo esc_url( home_url( '/careers' ) ); ?>" class="<?php echo esc_attr( $jb_link ); ?><?php echo jbnewgen_nav_is_active( home_url( '/careers' ) ) ? ' is-active' : ''; ?>"><?php esc_html_e( 'Careers', 'jbnewgen' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="<?php echo esc_attr( $jb_link ); ?><?php echo jbnewgen_nav_is_active( home_url( '/contact' ) ) ? ' is-active' : ''; ?>"><?php esc_html_e( 'Contact', 'jbnewgen' ); ?></a>

				<?php if ( $jb_badge_on && $jb_badge_label ) : ?>
					<a href="<?php echo esc_url( $jb_badge_href ? $jb_badge_href : home_url( '/' ) ); ?>" class="jb-navlink ml-2 inline-flex items-center gap-1.5 px-2 py-1 text-[0.7rem] font-semibold uppercase tracking-[0.12em] transition-colors">
						<?php jbnewgen_icon( 'ribbon', 13, 'shrink-0 text-flame-500' ); ?>
						<?php echo esc_html( $jb_badge_label ); ?>
					</a>
				<?php endif; ?>
			</nav>

			<div class="flex items-center gap-2">
				<a href="<?php echo esc_url( home_url( '/search' ) ); ?>" aria-label="<?php esc_attr_e( 'Search', 'jbnewgen' ); ?>" class="jb-navlink grid h-10 w-10 place-items-center rounded-[7px] transition-colors"><?php jbnewgen_icon( 'search', 18 ); ?></a>
				<button type="button" id="jb-menu-toggle" class="jb-navlink grid h-10 w-10 place-items-center rounded-[7px] lg:hidden" aria-expanded="false" aria-controls="jb-mobile-nav" aria-label="<?php esc_attr_e( 'Menu', 'jbnewgen' ); ?>"><?php jbnewgen_icon( 'menu', 20 ); ?></button>
			</div>
		</div>
	</div>

	<div id="jb-mobile-nav" class="hidden border-t border-ink-200 bg-white lg:hidden">
		<nav class="mx-auto max-w-7xl px-5 py-4 sm:px-8" aria-label="<?php esc_attr_e( 'Mobile', 'jbnewgen' ); ?>">
			<ul class="space-y-1 text-[0.95rem]">
				<li><a class="block rounded-md px-3 py-2 font-medium text-ink-700 hover:bg-ink-50" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'jbnewgen' ); ?></a></li>
				<li><a class="block rounded-md px-3 py-2 font-medium text-ink-700 hover:bg-ink-50" href="<?php echo esc_url( home_url( '/about' ) ); ?>"><?php esc_html_e( 'About', 'jbnewgen' ); ?></a></li>
				<li>
					<a class="block rounded-md px-3 py-2 font-medium text-ink-700 hover:bg-ink-50" href="<?php echo esc_url( home_url( '/services' ) ); ?>"><?php esc_html_e( 'Services', 'jbnewgen' ); ?></a>
					<?php if ( $jb_pillars ) : ?>
						<ul class="ml-3 border-l border-ink-200 pl-3">
							<?php foreach ( $jb_pillars as $jb_pillar ) : ?>
								<li><a class="block rounded-md px-3 py-1.5 text-sm text-ink-600 hover:bg-ink-50" href="<?php echo esc_url( $jb_pillar['url'] ); ?>"><?php echo esc_html( $jb_pillar['short'] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</li>
				<li><a class="block rounded-md px-3 py-2 font-medium text-ink-700 hover:bg-ink-50" href="<?php echo esc_url( home_url( '/insights' ) ); ?>"><?php esc_html_e( 'Insights', 'jbnewgen' ); ?></a></li>
				<li><a class="block rounded-md px-3 py-2 font-medium text-ink-700 hover:bg-ink-50" href="<?php echo esc_url( home_url( '/careers' ) ); ?>"><?php esc_html_e( 'Careers', 'jbnewgen' ); ?></a></li>
				<li><a class="block rounded-md px-3 py-2 font-medium text-ink-700 hover:bg-ink-50" href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Contact', 'jbnewgen' ); ?></a></li>
				<li class="pt-2"><a class="block rounded-[7px] bg-flame-500 px-4 py-2 text-center font-semibold text-white" href="<?php echo esc_url( home_url( '/quote' ) ); ?>"><?php esc_html_e( 'Get a quote', 'jbnewgen' ); ?></a></li>
			</ul>
		</nav>
	</div>
</header>

<main id="main">
