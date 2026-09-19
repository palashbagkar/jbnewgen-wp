<?php
/**
 * Template Name: About — Our CEO
 *
 * Ported from ../jbnewgen/src/app/(frontend)/about/ceo/page.tsx. Role, photo,
 * creds and profile paragraphs come from the CMS (jbnewgen_about_ceo_view,
 * options-about.php); career/expertise/"why it matters" are not modelled as
 * Carbon Fields yet, so they come verbatim from jbnewgen_about_ceo_extra()
 * (inc/data/ceo-*.php).
 */

defined( 'ABSPATH' ) || exit;

get_header();

$jb_ceo   = jbnewgen_about_ceo_view();
$jb_extra = jbnewgen_about_ceo_extra();
?>

<section class="relative isolate overflow-hidden bg-ink-950 text-white">
	<div class="mesh pointer-events-none absolute inset-0 opacity-50"></div>
	<div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-[7px] bg-flame-500/12 blur-3xl"></div>

	<div class="relative mx-auto grid max-w-7xl items-end gap-10 px-5 pt-14 sm:px-8 sm:pt-20 lg:grid-cols-[1fr_360px] lg:gap-16">
		<div class="pb-16 sm:pb-20">
			<nav aria-label="<?php esc_attr_e( 'Breadcrumb', 'jbnewgen' ); ?>" class="mb-8">
				<ol class="flex flex-wrap items-center gap-1.5 text-sm text-white/45">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="transition-colors hover:text-white"><?php esc_html_e( 'Home', 'jbnewgen' ); ?></a></li>
					<li class="flex items-center gap-1.5"><?php jbnewgen_icon( 'chevronDown', 14, '-rotate-90 text-white/25' ); ?><a href="<?php echo esc_url( home_url( '/about' ) ); ?>" class="transition-colors hover:text-white"><?php esc_html_e( 'About', 'jbnewgen' ); ?></a></li>
					<li class="flex items-center gap-1.5"><?php jbnewgen_icon( 'chevronDown', 14, '-rotate-90 text-white/25' ); ?><span class="text-white/80" aria-current="page"><?php esc_html_e( 'Our CEO', 'jbnewgen' ); ?></span></li>
				</ol>
			</nav>

			<span class="inline-flex items-center rounded-[7px] border border-flame-500/30 bg-flame-500/15 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.14em] text-flame-300"><?php echo esc_html( $jb_ceo['role'] ); ?></span>
			<h1 class="mt-7 text-5xl font-bold tracking-tight sm:text-6xl md:text-[4rem] md:leading-[1.02]"><?php esc_html_e( 'Joyjeet Bose', 'jbnewgen' ); ?></h1>
			<p class="mt-3 text-lg font-medium text-flame-300 sm:text-xl"><?php echo esc_html( $jb_ceo['heroSubtitle'] ); ?></p>
			<p class="mt-6 max-w-xl text-pretty leading-relaxed text-ink-200 sm:text-lg"><?php echo esc_html( $jb_ceo['heroBody'] ); ?></p>
			<div class="mt-9 flex flex-wrap gap-3">
				<a href="<?php echo esc_url( home_url( '/quote' ) ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] bg-flame-500 px-6 text-base font-semibold text-white transition-colors hover:bg-flame-600"><?php esc_html_e( 'Book a Call with Joyjeet', 'jbnewgen' ); ?></a>
				<a href="#story" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] border border-white/30 bg-white/10 px-6 text-base font-semibold text-white backdrop-blur transition-colors hover:bg-white/20"><?php esc_html_e( 'Read His Story', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowRight', 18 ); ?></a>
			</div>
		</div>

		<?php if ( $jb_ceo['photo'] ) : ?>
			<div class="relative hidden self-end lg:block">
				<div class="relative aspect-[3/4] w-full overflow-hidden rounded-t-2xl">
					<img src="<?php echo esc_url( $jb_ceo['photo'] ); ?>" alt="<?php esc_attr_e( 'Joyjeet Bose, Founder & CEO of JB NewGen', 'jbnewgen' ); ?>" class="absolute inset-0 h-full w-full object-cover object-top">
					<?php if ( $jb_ceo['linkedin'] ) : ?>
						<a href="<?php echo esc_url( $jb_ceo['linkedin'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Joyjeet Bose on LinkedIn', 'jbnewgen' ); ?>" class="absolute bottom-4 right-4 grid h-11 w-11 place-items-center rounded-full bg-flame-500 text-white shadow-lg shadow-ink-950/40 ring-1 ring-inset ring-white/25 transition-transform duration-200 hover:scale-110">
							<?php jbnewgen_icon( 'linkedin', 20 ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>

<section class="border-y border-ink-100 bg-ink-50/50">
	<div class="mx-auto max-w-7xl px-5 py-12 sm:px-8">
		<dl class="grid grid-cols-2 gap-x-6 gap-y-10 lg:grid-cols-4">
			<?php foreach ( $jb_ceo['creds'] as $jb_i => $jb_c ) : ?>
				<div class="text-center" data-reveal data-reveal-delay="<?php echo (int) ( $jb_i * 80 ); ?>">
					<dt class="text-4xl font-bold tracking-tight text-flame-600 sm:text-5xl"><?php echo esc_html( $jb_c['value'] ); ?></dt>
					<dd class="mx-auto mt-3 max-w-[15rem] text-sm font-medium text-ink-700"><?php echo esc_html( $jb_c['label'] ); ?></dd>
				</div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>

<section id="story" class="mx-auto max-w-7xl px-5 py-20 sm:px-8 sm:py-28">
	<div class="grid gap-12 lg:grid-cols-[1.15fr_1fr] lg:gap-16">
		<div>
			<p class="text-xs font-semibold uppercase tracking-[0.14em] text-flame-600"><?php esc_html_e( 'The Story Behind the Expertise', 'jbnewgen' ); ?></p>
			<h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl"><?php esc_html_e( 'What 35 years of building India actually looks like', 'jbnewgen' ); ?></h2>
			<p class="mt-6 text-pretty leading-relaxed text-ink-500"><?php echo esc_html( $jb_ceo['profileP1'] ); ?></p>

			<figure class="my-8 rounded-2xl bg-ink-50/70 p-7 ring-1 ring-inset ring-ink-100">
				<?php jbnewgen_icon( 'quote', 32, 'text-flame-500/70' ); ?>
				<blockquote class="mt-3 text-pretty text-lg font-medium italic leading-relaxed text-ink-800"><?php echo esc_html( $jb_ceo['profileQuote'] ); ?></blockquote>
				<figcaption class="mt-4 text-sm font-semibold text-ink-500"><?php echo esc_html( $jb_ceo['profileQuoteAttr'] ); ?></figcaption>
			</figure>

			<div class="space-y-5 text-pretty leading-relaxed text-ink-500">
				<?php foreach ( $jb_ceo['profileRest'] as $jb_p ) : ?>
					<p><?php echo esc_html( $jb_p ); ?></p>
				<?php endforeach; ?>
			</div>
		</div>

		<div>
			<p class="mb-5 text-xs font-semibold uppercase tracking-[0.14em] text-flame-600"><?php esc_html_e( 'Career Highlights', 'jbnewgen' ); ?></p>
			<div class="space-y-4">
				<?php foreach ( $jb_extra['career'] as $jb_i => $jb_c ) : ?>
					<div class="flex gap-4 rounded-2xl border border-ink-100 bg-white p-5" data-reveal data-reveal-delay="<?php echo (int) ( $jb_i * 70 ); ?>">
						<span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-flame-500/10 text-flame-600 ring-1 ring-inset ring-flame-500/15"><?php jbnewgen_icon( $jb_c['icon'], 20 ); ?></span>
						<div>
							<h3 class="font-bold text-ink-900"><?php echo esc_html( $jb_c['org'] ); ?></h3>
							<p class="mt-1 text-sm leading-relaxed text-ink-500"><?php echo esc_html( $jb_c['body'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<section class="bg-ink-50/50 py-20 sm:py-28">
	<div class="mx-auto max-w-7xl px-5 sm:px-8">
		<p class="text-xs font-semibold uppercase tracking-[0.14em] text-flame-600"><?php esc_html_e( 'Areas of Expertise', 'jbnewgen' ); ?></p>
		<h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl"><?php esc_html_e( 'The specific expertise Joyjeet brings to every India entry engagement', 'jbnewgen' ); ?></h2>
		<p class="mt-5 max-w-2xl text-pretty text-lg text-ink-500"><?php esc_html_e( 'Built over 35 years of doing - not advising. Each area below reflects a domain where Joyjeet has operated at scale, not a framework he has studied.', 'jbnewgen' ); ?></p>
		<div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
			<?php foreach ( $jb_extra['expertise'] as $jb_i => $jb_e ) : ?>
				<div class="flex flex-col gap-4 rounded-2xl border border-ink-100 bg-white p-7" data-reveal data-reveal-delay="<?php echo (int) ( ( $jb_i % 3 ) * 70 ); ?>">
					<span class="grid h-12 w-12 place-items-center rounded-xl bg-flame-500/10 text-flame-600 ring-1 ring-inset ring-flame-500/15"><?php jbnewgen_icon( $jb_e['icon'], 22 ); ?></span>
					<h3 class="text-base font-bold text-ink-900"><?php echo esc_html( $jb_e['title'] ); ?></h3>
					<p class="text-sm leading-relaxed text-ink-500"><?php echo esc_html( $jb_e['body'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="relative overflow-hidden bg-ink-950 py-20 text-white sm:py-28">
	<div class="mesh pointer-events-none absolute inset-0 opacity-50"></div>
	<div class="pointer-events-none absolute -right-20 bottom-10 h-80 w-80 rounded-[7px] bg-flame-500/15 blur-3xl"></div>
	<div class="relative mx-auto max-w-7xl px-5 sm:px-8">
		<p class="text-[0.8125rem] font-semibold uppercase tracking-[0.14em] text-flame-300"><?php esc_html_e( 'Why It Matters for Your India Entry', 'jbnewgen' ); ?></p>
		<h2 class="mt-4 max-w-3xl text-balance text-3xl font-bold sm:text-4xl md:text-[2.5rem] md:leading-[1.1]"><?php esc_html_e( 'What Joyjeet’s experience means for a global startup entering India', 'jbnewgen' ); ?></h2>
		<p class="mt-5 max-w-xl leading-relaxed text-ink-300"><?php esc_html_e( "The gap between knowing India and being able to execute in India is vast. Here's what Joyjeet's 35-year track record directly delivers to every engagement.", 'jbnewgen' ); ?></p>
		<div class="mt-12 grid gap-5 sm:grid-cols-2">
			<?php foreach ( $jb_extra['brings'] as $jb_i => $jb_b ) : ?>
				<div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7" data-reveal data-reveal-delay="<?php echo (int) ( ( $jb_i % 2 ) * 80 ); ?>">
					<span class="grid h-9 w-9 place-items-center rounded-full bg-flame-500/20 text-flame-300 ring-1 ring-inset ring-flame-500/40"><?php jbnewgen_icon( 'check', 16 ); ?></span>
					<h3 class="mt-5 text-base font-bold text-white"><?php echo esc_html( $jb_b['title'] ); ?></h3>
					<p class="mt-2 text-sm leading-relaxed text-ink-300"><?php echo esc_html( $jb_b['body'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="bg-ink-50/50 py-20 sm:py-28">
	<div class="mx-auto max-w-2xl px-5 text-center sm:px-8">
		<p class="text-[0.8125rem] font-semibold uppercase tracking-[0.14em] text-flame-600"><?php esc_html_e( 'Talk to Joyjeet', 'jbnewgen' ); ?></p>
		<h2 class="mt-4 text-balance text-3xl font-bold text-ink-900 sm:text-4xl md:text-[2.7rem] md:leading-[1.08]"><?php esc_html_e( 'Book a direct strategy call with our founder', 'jbnewgen' ); ?></h2>
		<p class="mx-auto mt-5 max-w-xl text-pretty leading-relaxed text-ink-500 sm:text-lg"><?php esc_html_e( 'The first conversation is free, direct, and honest. Joyjeet will tell you what he sees in your India opportunity, what he thinks the real risks are, and what it would actually take to build something meaningful in the market.', 'jbnewgen' ); ?></p>
		<div class="mt-8 flex flex-wrap justify-center gap-3">
			<a href="<?php echo esc_url( home_url( '/quote' ) ); ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] bg-flame-500 px-6 text-base font-semibold text-white transition-colors hover:bg-flame-600"><?php esc_html_e( 'Book a Call with Joyjeet', 'jbnewgen' ); ?></a>
			<a href="mailto:sales@jbnewgen.com" class="inline-flex h-12 items-center justify-center gap-2 rounded-[7px] border border-ink-200 px-6 text-base font-semibold text-ink-800 transition-colors hover:border-flame-500 hover:text-flame-600"><?php esc_html_e( 'Email Directly', 'jbnewgen' ); ?><?php jbnewgen_icon( 'mail', 18 ); ?></a>
		</div>
		<p class="mt-6 text-sm text-ink-500">
			<a href="<?php echo esc_url( home_url( '/about/company' ) ); ?>" class="font-medium text-flame-600 hover:underline"><?php esc_html_e( 'Read the JB NewGen company story →', 'jbnewgen' ); ?></a>
		</p>
		<p class="mt-8 text-sm text-ink-400"><?php esc_html_e( 'JB NewGen Enterprises Pvt. Ltd. · Mumbai, India · sales@jbnewgen.com · +91 6362864230', 'jbnewgen' ); ?></p>
	</div>
</section>

<?php get_footer(); ?>
