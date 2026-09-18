<?php
/**
 * Site footer. Ported from ../jbnewgen/src/components/site/Footer.tsx.
 *
 * Four link columns -- About, Services, Insights, Company -- where Services
 * and Insights are built from the CMS (pillars, categories) and the other two
 * are fixed routes. Same structure as the React version; the difference is
 * that adding a pillar in wp-admin now grows the footer column too.
 *
 * The Reveal wrapper from the original is a scroll-triggered fade. Here it is
 * a data attribute picked up by the IntersectionObserver in site.js, so the
 * markup stays plain and the content is present for crawlers either way.
 */

defined( 'ABSPATH' ) || exit;

$jb_site    = jbnewgen_site_info();
$jb_pillars = jbnewgen_nav_pillars();
$jb_about   = jbnewgen_about_children();
$jb_cats    = jbnewgen_nav_categories();
?>
</main>

<footer class="relative overflow-hidden bg-ink-950 pb-24 text-ink-200 sm:pb-0">
	<div class="mesh pointer-events-none absolute inset-0 opacity-40"></div>

	<div class="relative mx-auto max-w-7xl px-5 sm:px-8">
		<div class="grid gap-10 py-16 md:grid-cols-[1.3fr_2.6fr]">

			<div data-reveal="pop">
				<?php $jb_logo_w = jbnewgen_logo_url( 'logo_white' ); ?>
				<?php if ( $jb_logo_w ) : ?>
					<img class="h-16 w-auto shrink-0 object-contain" src="<?php echo esc_url( $jb_logo_w ); ?>" alt="<?php echo esc_attr( $jb_site['name'] ); ?>" width="220" height="56">
				<?php else : ?>
					<span class="font-display text-2xl font-bold tracking-tight text-white"><?php echo esc_html( $jb_site['name'] ); ?></span>
				<?php endif; ?>

				<p class="mt-5 max-w-xs text-sm leading-relaxed text-ink-300"><?php esc_html_e( 'Your India Go-To-Market partner.', 'jbnewgen' ); ?></p>

				<div class="mt-6 flex flex-col gap-2 text-sm">
					<a href="<?php echo esc_url( $jb_site['phone_href'] ); ?>" class="inline-flex items-center gap-2 hover:text-white">
						<?php jbnewgen_icon( 'phone', 16, 'text-flame-400' ); ?><?php echo esc_html( $jb_site['phone'] ); ?>
					</a>
					<a href="<?php echo esc_url( 'mailto:' . $jb_site['email'] ); ?>" class="inline-flex items-center gap-2 hover:text-white">
						<?php jbnewgen_icon( 'mail', 16, 'text-flame-400' ); ?><?php echo esc_html( $jb_site['email'] ); ?>
					</a>
				</div>

				<div class="mt-6 flex items-center gap-3">
					<?php foreach ( $jb_site['socials'] as $jb_social ) : ?>
						<a href="<?php echo esc_url( $jb_social['href'] ); ?>" aria-label="<?php echo esc_attr( $jb_social['label'] ); ?>" target="_blank" rel="noreferrer" class="grid h-10 w-10 place-items-center rounded-[7px] border border-white/15 text-ink-200 transition-colors hover:border-flame-400 hover:text-flame-400">
							<?php jbnewgen_icon( $jb_social['icon'], 18 ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="grid grid-cols-2 gap-8 sm:grid-cols-4" data-reveal data-reveal-delay="120">

				<div>
					<h4 class="text-sm font-semibold uppercase tracking-widest text-white"><?php esc_html_e( 'About', 'jbnewgen' ); ?></h4>
					<ul class="mt-5 space-y-3 text-sm">
						<?php foreach ( $jb_about as $jb_item ) : ?>
							<li><a href="<?php echo esc_url( $jb_item['href'] ); ?>" class="text-ink-300 transition-colors hover:text-white"><?php echo esc_html( $jb_item['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div>
					<h4 class="text-sm font-semibold uppercase tracking-widest text-white"><?php esc_html_e( 'Services', 'jbnewgen' ); ?></h4>
					<ul class="mt-5 space-y-3 text-sm">
						<li><a href="<?php echo esc_url( home_url( '/services' ) ); ?>" class="text-ink-300 transition-colors hover:text-white"><?php esc_html_e( 'All Services', 'jbnewgen' ); ?></a></li>
						<?php foreach ( $jb_pillars as $jb_pillar ) : ?>
							<li><a href="<?php echo esc_url( $jb_pillar['url'] ); ?>" class="text-ink-300 transition-colors hover:text-white"><?php echo esc_html( $jb_pillar['short'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div>
					<h4 class="text-sm font-semibold uppercase tracking-widest text-white"><?php esc_html_e( 'Insights', 'jbnewgen' ); ?></h4>
					<ul class="mt-5 space-y-3 text-sm">
						<li><a href="<?php echo esc_url( home_url( '/insights' ) ); ?>" class="text-ink-300 transition-colors hover:text-white"><?php esc_html_e( 'All Insights', 'jbnewgen' ); ?></a></li>
						<?php foreach ( $jb_cats as $jb_cat ) : ?>
							<li><a href="<?php echo esc_url( $jb_cat['href'] ); ?>" class="text-ink-300 transition-colors hover:text-white"><?php echo esc_html( $jb_cat['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div>
					<h4 class="text-sm font-semibold uppercase tracking-widest text-white"><?php esc_html_e( 'Company', 'jbnewgen' ); ?></h4>
					<ul class="mt-5 space-y-3 text-sm">
						<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-ink-300 transition-colors hover:text-white"><?php esc_html_e( 'Home', 'jbnewgen' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/careers' ) ); ?>" class="text-ink-300 transition-colors hover:text-white"><?php esc_html_e( 'Careers', 'jbnewgen' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="text-ink-300 transition-colors hover:text-white"><?php esc_html_e( 'Contact', 'jbnewgen' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/quote' ) ); ?>" class="text-ink-300 transition-colors hover:text-white"><?php esc_html_e( 'Get a quote', 'jbnewgen' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/search' ) ); ?>" class="text-ink-300 transition-colors hover:text-white"><?php esc_html_e( 'Search', 'jbnewgen' ); ?></a></li>
					</ul>
				</div>
			</div>
		</div>

		<div class="flex flex-col items-center justify-between gap-4 border-t border-white/10 py-6 text-sm text-ink-400 sm:flex-row">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $jb_site['legal_name'] ); ?>. <?php esc_html_e( 'All rights reserved.', 'jbnewgen' ); ?></p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
