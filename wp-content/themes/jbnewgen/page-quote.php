<?php
/**
 * Template Name: Get a Quote
 *
 * Ported from ../jbnewgen/src/app/(frontend)/quote/page.tsx and
 * components/quote/QuoteForm.tsx. The React version posted JSON to /quote/send
 * over fetch with client-side validation; this posts a normal form to
 * admin-post.php (handled in inc/forms.php) and redirects back with a query
 * flag, since there is no JS runtime here to keep the request on-page.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$jb_site    = jbnewgen_site_info();
$jb_pillars = jbnewgen_nav_pillars();
$jb_sent    = isset( $_GET['sent'] ) && '1' === $_GET['sent'];
$jb_error   = isset( $_GET['quote_error'] ) ? sanitize_text_field( wp_unslash( $_GET['quote_error'] ) ) : '';

$jb_services = array(
	__( 'Business Consultancy', 'jbnewgen' ),
	__( 'Digital Transformation', 'jbnewgen' ),
	__( 'Digital Marketing', 'jbnewgen' ),
	__( 'CPaaS & Omnichannel', 'jbnewgen' ),
);
$jb_reach = array(
	array( 'icon' => 'phone', 'label' => __( 'Call for help', 'jbnewgen' ), 'value' => $jb_site['phone'], 'href' => $jb_site['phone_href'] ),
	array( 'icon' => 'mail', 'label' => __( 'Mail us for information', 'jbnewgen' ), 'value' => $jb_site['emails']['sales'], 'href' => 'mailto:' . $jb_site['emails']['sales'] ),
	array( 'icon' => 'mapPin', 'label' => __( 'Head office address', 'jbnewgen' ), 'value' => '504 Challenger Tower III, Thakur Village, Kandivali (E), Mumbai – 400 101', 'href' => 'https://maps.google.com/?q=504+Challenger+Tower+III+Kandivali+East+Mumbai+400101' ),
	array( 'icon' => 'clock', 'label' => __( 'Business hours', 'jbnewgen' ), 'value' => 'Mon – Sat: 8 am – 5 pm · Sunday: Closed', 'href' => $jb_site['phone_href'] ),
);

jbnewgen_page_header( array(
	'eyebrow' => __( 'Get a quote', 'jbnewgen' ),
	'title'   => __( 'Get a quote now', 'jbnewgen' ),
	'trail'   => array(
		array( 'label' => __( 'Home', 'jbnewgen' ), 'href' => '/' ),
		array( 'label' => __( 'Get a quote', 'jbnewgen' ), 'href' => '/quote' ),
	),
	'note'    => __( 'Tell us what you are building and what you need in India. We read every request and reply personally.', 'jbnewgen' ),
) );
?>

<div class="mx-auto max-w-7xl px-5 sm:px-8">
	<div class="-mt-4 flex flex-wrap gap-3 pb-4">
		<a href="<?php echo esc_url( $jb_site['phone_href'] ); ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-[7px] bg-flame-500 px-5 text-sm font-semibold text-white transition-colors hover:bg-flame-600"><?php jbnewgen_icon( 'phone', 16 ); ?><?php echo esc_html( sprintf( __( 'Call %s', 'jbnewgen' ), $jb_site['phone'] ) ); ?></a>
		<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-[7px] px-5 text-sm font-semibold text-ink-700 ring-1 ring-inset ring-ink-200 transition-colors hover:bg-ink-50"><?php esc_html_e( 'Contact form', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowUpRight', 16 ); ?></a>
	</div>
</div>

<section class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-16">
	<div class="grid gap-10 lg:grid-cols-[1.5fr_1fr] lg:gap-14">
		<div>
			<p class="mb-4 text-xs font-semibold uppercase tracking-[0.14em] text-flame-600"><?php esc_html_e( 'Request a quote', 'jbnewgen' ); ?></p>
			<h2 class="text-balance text-3xl font-bold tracking-tight text-ink-900 sm:text-4xl"><?php esc_html_e( 'Get in touch for any kind of help and informations', 'jbnewgen' ); ?></h2>

			<div class="mt-8">
				<?php if ( $jb_sent ) : ?>
					<div class="rounded-2xl border border-ink-100 bg-white p-8 text-center sm:p-12">
						<span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-flame-500/10 text-flame-600 ring-1 ring-inset ring-flame-500/20"><?php jbnewgen_icon( 'check', 26 ); ?></span>
						<h3 class="mt-5 text-2xl font-bold text-ink-900"><?php esc_html_e( 'Thank you - your request is in.', 'jbnewgen' ); ?></h3>
						<p class="mx-auto mt-3 max-w-md text-ink-500"><?php esc_html_e( 'Our team has your details and will come back to you shortly. If it is urgent, call us and we will pick it up right away.', 'jbnewgen' ); ?></p>
						<a href="<?php echo esc_url( home_url( '/quote' ) ); ?>" class="mt-7 inline-flex h-11 items-center justify-center gap-2 rounded-[7px] px-6 font-semibold text-ink-800 ring-1 ring-inset ring-ink-200 transition-colors hover:bg-ink-50"><?php esc_html_e( 'Send another request', 'jbnewgen' ); ?></a>
					</div>
				<?php else : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="relative rounded-2xl border border-ink-100 bg-white p-6 shadow-[0_1px_2px_rgba(8,21,39,0.04),0_20px_50px_-34px_rgba(8,21,39,0.4)] sm:p-8">
						<input type="hidden" name="action" value="jbnewgen_quote">
						<?php wp_nonce_field( 'jbnewgen_quote', 'jbnewgen_quote_nonce' ); ?>

						<?php if ( $jb_error ) : ?>
							<p role="alert" class="mb-4 rounded-[7px] bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
								<?php echo 'fields' === $jb_error ? esc_html__( 'Please fill in every required field.', 'jbnewgen' ) : esc_html__( 'Something went wrong. Please try again.', 'jbnewgen' ); ?>
							</p>
						<?php endif; ?>

						<div class="grid gap-4 sm:grid-cols-2">
							<div>
								<label for="q-first" class="mb-1.5 block text-sm font-semibold text-ink-800"><?php esc_html_e( 'First name', 'jbnewgen' ); ?> <span class="text-flame-600">*</span></label>
								<input id="q-first" name="first_name" autocomplete="given-name" required placeholder="Jane" class="h-12 w-full rounded-[7px] border border-ink-200 bg-white px-4 text-ink-900 placeholder:text-ink-400 transition-colors focus:border-flame-500 focus:outline-none">
							</div>
							<div>
								<label for="q-last" class="mb-1.5 block text-sm font-semibold text-ink-800"><?php esc_html_e( 'Last name', 'jbnewgen' ); ?> <span class="text-flame-600">*</span></label>
								<input id="q-last" name="last_name" autocomplete="family-name" required placeholder="Doe" class="h-12 w-full rounded-[7px] border border-ink-200 bg-white px-4 text-ink-900 placeholder:text-ink-400 transition-colors focus:border-flame-500 focus:outline-none">
							</div>
							<div>
								<label for="q-email" class="mb-1.5 block text-sm font-semibold text-ink-800"><?php esc_html_e( 'Your mail', 'jbnewgen' ); ?> <span class="text-flame-600">*</span></label>
								<input id="q-email" name="your_email" type="email" autocomplete="email" required placeholder="you@company.com" class="h-12 w-full rounded-[7px] border border-ink-200 bg-white px-4 text-ink-900 placeholder:text-ink-400 transition-colors focus:border-flame-500 focus:outline-none">
							</div>
							<div>
								<label for="q-phone" class="mb-1.5 block text-sm font-semibold text-ink-800"><?php esc_html_e( 'Phone number', 'jbnewgen' ); ?> <span class="text-flame-600">*</span></label>
								<input id="q-phone" name="your_phone" type="tel" autocomplete="tel" required placeholder="+1 555 000 0000" class="h-12 w-full rounded-[7px] border border-ink-200 bg-white px-4 text-ink-900 placeholder:text-ink-400 transition-colors focus:border-flame-500 focus:outline-none">
							</div>
							<div>
								<label for="q-web" class="mb-1.5 block text-sm font-semibold text-ink-800"><?php esc_html_e( 'Web address', 'jbnewgen' ); ?> <span class="text-flame-600">*</span></label>
								<input id="q-web" name="your_web_address" autocomplete="url" required placeholder="company.com" class="h-12 w-full rounded-[7px] border border-ink-200 bg-white px-4 text-ink-900 placeholder:text-ink-400 transition-colors focus:border-flame-500 focus:outline-none">
							</div>
							<div>
								<label for="q-service" class="mb-1.5 block text-sm font-semibold text-ink-800"><?php esc_html_e( 'Services', 'jbnewgen' ); ?> <span class="text-flame-600">*</span></label>
								<select id="q-service" name="your_service" required class="h-12 w-full appearance-none rounded-[7px] border border-ink-200 bg-white px-4 text-ink-900 transition-colors focus:border-flame-500 focus:outline-none">
									<option value=""><?php esc_html_e( 'Services', 'jbnewgen' ); ?></option>
									<?php foreach ( $jb_services as $jb_s ) : ?>
										<option value="<?php echo esc_attr( $jb_s ); ?>"><?php echo esc_html( $jb_s ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>

						<div class="mt-4">
							<label for="q-message" class="mb-1.5 block text-sm font-semibold text-ink-800"><?php esc_html_e( 'Message', 'jbnewgen' ); ?> <span class="text-flame-600">*</span></label>
							<textarea id="q-message" name="your_message" required rows="6" maxlength="2000" placeholder="<?php esc_attr_e( 'Tell us what you are building and what you need in India.', 'jbnewgen' ); ?>" class="w-full resize-y rounded-[7px] border border-ink-200 bg-white px-4 py-3 text-ink-900 placeholder:text-ink-400 transition-colors focus:border-flame-500 focus:outline-none"></textarea>
						</div>

						<div aria-hidden="true" class="absolute h-0 w-0 overflow-hidden opacity-0">
							<label for="q-company"><?php esc_html_e( 'Company (leave blank)', 'jbnewgen' ); ?></label>
							<input id="q-company" name="company" tabindex="-1" autocomplete="off">
						</div>

						<div class="mt-6 flex flex-wrap items-center gap-4">
							<button type="submit" class="inline-flex h-[3.25rem] items-center justify-center gap-2 rounded-[7px] bg-flame-500 px-8 font-semibold text-white transition-colors duration-200 hover:bg-flame-600"><?php esc_html_e( 'Get a Quote', 'jbnewgen' ); ?><?php jbnewgen_icon( 'arrowRight', 18 ); ?></button>
							<p class="text-sm text-ink-400"><?php esc_html_e( 'Fields marked', 'jbnewgen' ); ?> <span class="text-flame-600">*</span> <?php esc_html_e( 'are required.', 'jbnewgen' ); ?></p>
						</div>
					</form>
				<?php endif; ?>
			</div>
		</div>

		<div class="relative overflow-hidden rounded-2xl bg-ink-950 p-7 text-white sm:p-8 lg:pt-16" data-reveal>
			<div class="mesh pointer-events-none absolute inset-0 opacity-40"></div>
			<div class="relative">
				<p class="text-sm font-semibold uppercase tracking-[0.14em] text-flame-300"><?php esc_html_e( 'Official info', 'jbnewgen' ); ?></p>
				<h3 class="mt-3 text-xl font-bold"><?php esc_html_e( 'Prefer to reach us directly?', 'jbnewgen' ); ?></h3>
				<ul class="mt-6 space-y-4">
					<?php foreach ( $jb_reach as $jb_r ) : ?>
						<li>
							<a href="<?php echo esc_url( $jb_r['href'] ); ?>" target="_blank" rel="noreferrer" class="group flex items-start gap-3.5 rounded-[7px] border border-white/10 bg-white/[0.05] p-4 transition-colors hover:border-flame-500/40">
								<span class="grid h-10 w-10 shrink-0 place-items-center rounded-[7px] bg-flame-500/15 text-flame-300 ring-1 ring-inset ring-flame-500/25"><?php jbnewgen_icon( $jb_r['icon'], 18 ); ?></span>
								<span class="min-w-0">
									<span class="block text-xs font-semibold uppercase tracking-wider text-ink-400"><?php echo esc_html( $jb_r['label'] ); ?></span>
									<span class="mt-1 block text-sm font-medium text-white"><?php echo esc_html( $jb_r['value'] ); ?></span>
								</span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</section>

<?php
$jb_items = array();
foreach ( $jb_pillars as $jb_p ) {
	$jb_blurb   = function_exists( 'carbon_get_post_meta' ) ? (string) carbon_get_post_meta( $jb_p['id'], 'blurb' ) : '';
	$jb_items[] = array( 'label' => $jb_p['short'], 'href' => $jb_p['url'], 'desc' => $jb_blurb, 'icon' => $jb_p['icon'] );
}
jbnewgen_nav_grid( array(
	'title'   => __( 'What do you need help with?', 'jbnewgen' ),
	'caption' => __( 'Jump to a service pillar', 'jbnewgen' ),
	'columns' => 4,
	'items'   => $jb_items,
) );

jbnewgen_next_step( __( 'Prefer to talk?', 'jbnewgen' ), array(
	array( 'label' => __( 'Contact us', 'jbnewgen' ), 'href' => '/contact', 'variant' => 'primary' ),
) );

get_footer();
