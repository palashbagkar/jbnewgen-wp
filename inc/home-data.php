<?php
/**
 * Static homepage copy, carried over verbatim from the Next.js app.
 *
 * Every string here is published copy from ../jbnewgen/src/lib/content.ts --
 * the CEO block, the testimonials and the proof stats. It is reproduced
 * exactly, not paraphrased: CLAUDE.md RULE 4 forbids inventing anything the
 * site displays, and "tidying" a stat or a quote is inventing.
 *
 * It lives in PHP rather than the CMS because it is not in the CMS yet. The
 * homepage global covers the hero and the proof eyebrow; testimonials and the
 * founder band were hardcoded in the React components too. Moving them into
 * Carbon Fields is part of the content-migration phase -- until then this
 * keeps the page truthful instead of blank.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Founder details. Verbatim from content.ts `ceo`.
 *
 * @return array
 */
function jbnewgen_home_ceo() {
	return array(
		'name'      => 'Joyjeet Bose',
		'role'      => 'Founder & CEO',
		'initials'  => 'JB',
		'quote'     => "India is not one market. It's 28 states, 8 union territories, hundreds of languages, multiple tiers of distribution, and buyers who make decisions in ways that surprise every foreign company.",
		'bio_short' => 'Built national channel networks across telecom, technology and retail - at Tata Teleservices, the Airtel group and India\'s early internet sector.',
		'href'      => '/about/ceo',
		'photo'     => jbnewgen_seeded_image_url( 'images/clients/ceo.webp' ),
	);
}

/**
 * Look up an attachment previously imported by .wp-local/seed-content.php or
 * fix-homepage-media.php, keyed by the same relative path stashed in
 * _jbseed_source. Returns '' if it was never imported.
 *
 * @param string $source_key
 * @return string
 */
function jbnewgen_seeded_image_url( $source_key, $size = 'thumbnail' ) {
	$ids = get_posts( array(
		'post_type'   => 'attachment',
		'post_status' => 'inherit',
		'numberposts' => 1,
		'meta_key'    => '_jbseed_source',
		'meta_value'  => $source_key,
		'fields'      => 'ids',
	) );
	return $ids ? (string) wp_get_attachment_image_url( $ids[0], $size ) : '';
}

/**
 * Testimonials. Verbatim from content.ts `testimonials`.
 *
 * @return array
 */
function jbnewgen_home_testimonials() {
	return array(
		array(
			'quote' => 'The GTM & Ops Consultancy service helped us break into new markets with ease. Their tailored strategy ensured we reached the right audience. A game-changer for us!',
			'name'  => 'Ravi Sharma',
			'role'  => 'Agent Manager',
		),
		array(
			'quote' => "We were struggling with distribution inefficiencies, but the team's expert consultancy streamlined our channels. Our market penetration improved significantly!",
			'name'  => 'Priya Mehta',
			'role'  => 'Assistant Manager',
		),
		array(
			'quote' => 'Their approach to refining our business operations was truly innovative. We saw immediate improvements in resource utilization and cost savings.',
			'name'  => 'Amit Kumar',
			'role'  => 'Operations Lead',
		),
		array(
			'quote' => 'The digital transformation service allowed us to automate most of our manual processes. Our productivity has increased by 40% since then!',
			'name'  => 'Anjali Gupta',
			'role'  => 'Agent Manager',
		),
	);
}

/**
 * The hero and proof bar, read from the Homepage options screen with the
 * Next.js defaults behind them.
 *
 * homepage-data.ts had exactly this shape: every CMS value falls back to a
 * published default, so an empty field degrades to the current live copy
 * rather than to blank space.
 *
 * @return array
 */
function jbnewgen_home_view() {
	$opt = function ( $key ) {
		return function_exists( 'carbon_get_theme_option' ) ? carbon_get_theme_option( $key ) : '';
	};

	$defaults = array(
		'heading_1'   => 'India is your next big market.',
		'heading_2'   => 'We make the entry seamless.',
		'intro'       => 'Good planning is the key to success. We help US/EU/Global Startups establish and scale in India.',
		'cta_1_label' => 'Get a quote now',
		'cta_1_href'  => '/quote',
		'cta_2_label' => 'See what we offer',
		'cta_2_href'  => '/services',
		'eyebrow'     => 'Founders marker connect and credentials - Distribution proven at scale',
	);

	// The four proof stats, verbatim from content.ts `proofStats`.
	$default_stats = array(
		array( 'value' => '35+',      'label' => 'Years in the India market',            'sub' => "Since India's early digital revolution" ),
		array( 'value' => '7,000+',   'label' => 'Channel partners built & managed',     'sub' => 'Nationwide distribution' ),
		array( 'value' => '30,000+',  'label' => 'Retail touchpoints established',       'sub' => 'Tier 1 & Tier 2 reach' ),
		array( 'value' => '200–500%', 'label' => 'Revenue growth delivered',             'sub' => 'For the business the founder has managed' ),
	);

	// Carbon's complex field returns rows keyed by the field names declared in
	// options-homepage.php. A row missing either half is dropped rather than
	// rendered as a stat with no number.
	$rows  = $opt( 'proof_stats' );
	$stats = array();
	if ( is_array( $rows ) ) {
		foreach ( $rows as $row ) {
			$value = isset( $row['stat_value'] ) ? trim( (string) $row['stat_value'] ) : '';
			$label = isset( $row['label'] ) ? trim( (string) $row['label'] ) : '';
			if ( '' !== $value && '' !== $label ) {
				$stats[] = array(
					'value' => $value,
					'label' => $label,
					'sub'   => isset( $row['sub'] ) ? (string) $row['sub'] : '',
				);
			}
		}
	}

	$hero_image_id = (int) $opt( 'hero_image' );

	$pick = function ( $key, $default ) use ( $opt ) {
		$v = trim( (string) $opt( $key ) );
		return '' !== $v ? $v : $default;
	};

	return array(
		'hero'  => array(
			'heading_1'   => $pick( 'hero_heading_line1', $defaults['heading_1'] ),
			'heading_2'   => $pick( 'hero_heading_line2', $defaults['heading_2'] ),
			'intro'       => $pick( 'hero_intro', $defaults['intro'] ),
			'image'       => $hero_image_id ? wp_get_attachment_image_url( $hero_image_id, 'full' ) : '',
			'cta_1_label' => $pick( 'hero_cta_primary_label', $defaults['cta_1_label'] ),
			'cta_1_href'  => $pick( 'hero_cta_primary_href', $defaults['cta_1_href'] ),
			'cta_2_label' => $pick( 'hero_cta_secondary_label', $defaults['cta_2_label'] ),
			'cta_2_href'  => $pick( 'hero_cta_secondary_href', $defaults['cta_2_href'] ),
		),
		'proof' => array(
			'eyebrow' => $pick( 'proof_eyebrow', $defaults['eyebrow'] ),
			'stats'   => $stats ? $stats : $default_stats,
		),
	);
}

/**
 * Resolve an href that may be a site-relative path from the CMS.
 *
 * @param string $href
 * @return string
 */
function jbnewgen_href( $href ) {
	$href = (string) $href;
	if ( '' === $href ) {
		return home_url( '/' );
	}
	if ( preg_match( '#^(https?:)?//#i', $href ) || 0 === strpos( $href, 'mailto:' ) || 0 === strpos( $href, 'tel:' ) ) {
		return $href;
	}
	return home_url( '/' . ltrim( $href, '/' ) );
}

/**
 * The "screen" chrome shared by all four PillarVisual mock-ups: three dots,
 * a mono label, padded body. Ported from PillarVisual.tsx's Screen().
 *
 * @param string $label
 * @param string $body_html Already-escaped inner markup.
 */
function jbnewgen_pillar_screen( $label, $body_html ) {
	?>
	<div class="relative overflow-hidden rounded-2xl border border-white/10 bg-ink-900/70 shadow-[0_30px_70px_-34px_rgba(0,0,0,0.75)]">
		<div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-flame-500/[0.06] via-transparent to-azure-500/[0.07]"></div>
		<div class="relative flex items-center gap-1.5 border-b border-white/5 bg-white/[0.02] px-4 py-2.5">
			<span class="h-2.5 w-2.5 rounded-[7px] bg-white/70"></span>
			<span class="h-2.5 w-2.5 rounded-[7px] bg-flame-500/70"></span>
			<span class="h-2.5 w-2.5 rounded-[7px] bg-azure-500/70"></span>
			<span class="ml-2 font-mono text-[0.7rem] uppercase tracking-widest text-ink-400"><?php echo esc_html( $label ); ?></span>
		</div>
		<div class="relative p-5"><?php echo $body_html; // phpcs:ignore -- pre-escaped by callers. ?></div>
	</div>
	<?php
}

/**
 * The four PillarVisual mock-ups, keyed by pillar slug exactly like the
 * switch in PillarVisual.tsx. The reveal-in motion each has when its panel
 * becomes active (bars filling, sparkline drawing, channel rows sliding in)
 * is driven by CSS off the .is-active class the showcase JS toggles -- see
 * ".jb-pv-*" rules in site.src.css -- so no per-frame JS is needed for it.
 *
 * @param string $slug
 */
function jbnewgen_pillar_visual( $slug ) {
	switch ( $slug ) {

		case 'tech-readiness':
			ob_start();
			?>
			<div class="grid grid-cols-2 gap-2.5">
				<div class="jb-pv-gate relative overflow-hidden rounded-xl border border-white/5 bg-white/[0.02] p-2.5">
					<div class="flex items-center gap-2">
						<span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-gradient-to-br from-flame-500/10 to-azure-500/10 text-azure-300 ring-1 ring-white/10"><?php jbnewgen_icon( 'creditCard', 14 ); ?></span>
						<div class="min-w-0 flex-1">
							<div class="truncate text-[0.7rem] font-bold leading-tight text-white">UPI Payments</div>
							<div class="mt-0.5 truncate text-[0.58rem] leading-none text-ink-400">Merchant gateway</div>
						</div>
					</div>
					<div class="mt-2.5 flex items-center justify-between border-t border-white/5 pt-1.5 text-[0.58rem]">
						<span class="flex items-center gap-1 font-medium text-emerald-400"><span class="h-1 w-1 rounded-full bg-emerald-500 animate-pulse"></span>Active</span>
						<span class="font-mono text-ink-400">99.98%</span>
					</div>
				</div>
				<div class="jb-pv-gate relative overflow-hidden rounded-xl border border-white/5 bg-white/[0.02] p-2.5">
					<div class="flex items-center gap-2">
						<span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-gradient-to-br from-flame-500/10 to-azure-500/10 text-azure-300 ring-1 ring-white/10"><?php jbnewgen_icon( 'shield', 14 ); ?></span>
						<div class="min-w-0 flex-1">
							<div class="truncate text-[0.7rem] font-bold leading-tight text-white">Aadhaar e-KYC</div>
							<div class="mt-0.5 truncate text-[0.58rem] leading-none text-ink-400">UIDAI auth API</div>
						</div>
					</div>
					<div class="mt-2.5 flex items-center justify-between border-t border-white/5 pt-1.5 text-[0.58rem]">
						<span class="flex items-center gap-1 font-medium text-emerald-400"><span class="h-1 w-1 rounded-full bg-emerald-500 animate-pulse"></span>Verified</span>
						<span class="font-mono text-ink-400">99.95%</span>
					</div>
				</div>
			</div>
			<div class="jb-pv-log mt-3.5 rounded-xl border border-white/5 bg-ink-950/80 p-2.5 font-mono text-[0.58rem] leading-normal text-emerald-400/80">
				<div class="mb-1 flex items-center justify-between border-b border-white/5 pb-1 text-[0.55rem] font-semibold uppercase tracking-wider text-ink-400">
					<span>Gateway Request Stream</span>
					<span class="flex items-center gap-1 font-bold text-emerald-500"><span class="h-1 w-1 rounded-full bg-emerald-500 animate-ping"></span>Live</span>
				</div>
				<div class="space-y-0.5 text-left">
					<div class="truncate"><span class="text-ink-400">[12:48:42]</span> <span class="font-semibold text-azure-400">POST</span> /api/v1/upi/pay <span class="font-bold text-emerald-400">200 OK</span> <span class="text-ink-500">12ms</span></div>
					<div class="truncate"><span class="text-ink-400">[12:48:44]</span> <span class="font-semibold text-azure-400">POST</span> /api/v2/kyc/verify <span class="font-bold text-emerald-400">200 OK</span> <span class="text-ink-500">45ms</span></div>
				</div>
			</div>
			<?php
			jbnewgen_pillar_screen( 'India Stack Gateway · Active APIs', ob_get_clean() );
			break;

		case 'digital-marketing':
			ob_start();
			?>
			<div class="relative overflow-hidden rounded-lg">
				<div class="graph-grid pointer-events-none absolute inset-0 opacity-70"></div>
				<svg viewBox="0 0 314 150" preserveAspectRatio="none" class="relative h-[150px] w-full">
					<defs>
						<linearGradient id="jbDemandArea" x1="0" y1="0" x2="0" y2="1">
							<stop offset="0%" stop-color="rgb(234,131,46)" stop-opacity="0.38"/>
							<stop offset="100%" stop-color="rgb(7,149,254)" stop-opacity="0.02"/>
						</linearGradient>
						<linearGradient id="jbDemandLine" x1="0" y1="0" x2="1" y2="0">
							<stop offset="0%" stop-color="rgb(234,131,46)"/>
							<stop offset="100%" stop-color="rgb(56,182,255)"/>
						</linearGradient>
					</defs>
					<polygon class="jb-pv-area" points="0,150 0,142 39.25,126 78.5,131 117.75,110.4 157,94.8 196.25,97.6 235.5,73.6 274.75,42.8 314,15.6 314,150" fill="url(#jbDemandArea)"/>
					<polyline class="jb-pv-line" points="0,142 39.25,126 78.5,131 117.75,110.4 157,94.8 196.25,97.6 235.5,73.6 274.75,42.8 314,15.6" fill="none" stroke="url(#jbDemandLine)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" pathLength="1"/>
					<circle class="jb-pv-dot" cx="314" cy="15.6" r="6" fill="rgb(242,143,61)" opacity="0.35"/>
					<circle class="jb-pv-dot" cx="314" cy="15.6" r="3" fill="rgb(242,143,61)"/>
				</svg>
			</div>
			<div class="mt-4 flex items-center gap-2 border-t border-white/10 pt-4 text-sm text-ink-300">
				<?php jbnewgen_icon( 'trending', 18, 'text-emerald-400' ); ?>
				A predictable, growing lead pipeline
			</div>
			<?php
			jbnewgen_pillar_screen( 'Visibility & demand generation', ob_get_clean() );
			break;

		case 'cpaas-omnichannel':
			ob_start();
			?>
			<div class="grid grid-cols-[1fr_auto_1.15fr] items-center gap-2">
				<div class="space-y-1.5">
					<div class="jb-pv-chan flex items-center gap-2 rounded-xl border border-white/10 bg-white/[0.03] px-2.5 py-1.5 ring-1 ring-inset ring-emerald-400/25">
						<?php jbnewgen_icon( 'chat', 16, 'text-emerald-400' ); ?>
						<span class="text-xs text-ink-100">WhatsApp</span>
					</div>
					<div class="jb-pv-chan flex items-center gap-2 rounded-xl border border-white/10 bg-white/[0.03] px-2.5 py-1.5 ring-1 ring-inset ring-azure-400/25">
						<?php jbnewgen_icon( 'mail', 16, 'text-azure-400' ); ?>
						<span class="text-xs text-ink-100">SMS &amp; RCS</span>
					</div>
					<div class="jb-pv-chan flex items-center gap-2 rounded-xl border border-white/10 bg-white/[0.03] px-2.5 py-1.5 ring-1 ring-inset ring-flame-400/25">
						<?php jbnewgen_icon( 'phone', 16, 'text-flame-400' ); ?>
						<span class="text-xs text-ink-100">Voice</span>
					</div>
				</div>
				<svg width="34" height="76" viewBox="0 0 34 76" class="overflow-visible">
					<path d="M0 12 C 17 12, 17 38, 34 38" fill="none" stroke="rgba(255,255,255,0.22)" stroke-width="1.5" stroke-dasharray="4 4" class="animate-dash"></path>
					<path d="M0 38 C 17 38, 17 38, 34 38" fill="none" stroke="rgba(255,255,255,0.22)" stroke-width="1.5" stroke-dasharray="4 4" class="animate-dash"></path>
					<path d="M0 64 C 17 64, 17 38, 34 38" fill="none" stroke="rgba(255,255,255,0.22)" stroke-width="1.5" stroke-dasharray="4 4" class="animate-dash"></path>
				</svg>
				<div class="jb-pv-inbox rounded-2xl border border-flame-400/20 bg-gradient-to-br from-flame-500/5 to-azure-500/5 p-2.5">
					<div class="flex items-center gap-1.5">
						<span class="grid h-6 w-6 place-items-center rounded-lg bg-gradient-to-br from-flame-500 to-azure-500 text-white"><?php jbnewgen_icon( 'gauge', 13 ); ?></span>
						<span class="text-xs font-semibold text-white">Unified inbox</span>
					</div>
					<div class="mt-2.5 space-y-1">
						<div class="flex items-center gap-1.5 rounded-lg bg-white/[0.04] px-1.5 py-1">
							<span class="h-1 w-1 shrink-0 rounded-full bg-emerald-400"></span>
							<div class="min-w-0 flex-1"><span class="block h-1 w-full rounded-full bg-white/15"></span><span class="mt-1 block h-1 w-2/3 rounded-full bg-white/10"></span></div>
						</div>
						<div class="flex items-center gap-1.5 rounded-lg bg-white/[0.04] px-1.5 py-1">
							<span class="h-1 w-1 shrink-0 rounded-full bg-azure-400"></span>
							<div class="min-w-0 flex-1"><span class="block h-1 w-full rounded-full bg-white/15"></span><span class="mt-1 block h-1 w-2/3 rounded-full bg-white/10"></span></div>
						</div>
						<div class="flex items-center gap-1.5 rounded-lg bg-white/[0.04] px-1.5 py-1">
							<span class="h-1 w-1 shrink-0 rounded-full bg-flame-400"></span>
							<div class="min-w-0 flex-1"><span class="block h-1 w-full rounded-full bg-white/15"></span><span class="mt-1 block h-1 w-2/3 rounded-full bg-white/10"></span></div>
						</div>
					</div>
				</div>
			</div>
			<?php
			jbnewgen_pillar_screen( 'Omnichannel · unified inbox', ob_get_clean() );
			break;

		case 'business-consultancy':
		default:
			ob_start();
			$jb_rows = array(
				array( 'k' => 'Metro', 'v' => 92 ),
				array( 'k' => 'Tier 1', 'v' => 78 ),
				array( 'k' => 'Tier 2', 'v' => 64 ),
				array( 'k' => 'Tier 3', 'v' => 44 ),
			);
			?>
			<div class="space-y-3.5">
				<?php foreach ( $jb_rows as $jb_row ) : ?>
					<div class="jb-pv-row flex items-center gap-3">
						<span class="w-12 shrink-0 text-xs font-medium text-ink-200"><?php echo esc_html( $jb_row['k'] ); ?></span>
						<div class="relative h-3 flex-1 overflow-hidden rounded-[7px] bg-white/[0.06] ring-1 ring-inset ring-white/5">
							<div class="jb-pv-bar-fill relative h-full overflow-hidden rounded-[7px] bg-gradient-to-r from-flame-500 via-flame-400 to-azure-500" style="--jb-bar:<?php echo (int) $jb_row['v']; ?>%">
								<span class="animate-shimmer absolute inset-y-0 left-0 w-1/2 -skew-x-12 bg-gradient-to-r from-transparent via-white/40 to-transparent"></span>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="mt-5 grid grid-cols-2 gap-3">
				<div class="relative overflow-hidden rounded-xl border border-white/10 bg-gradient-to-br from-flame-500/10 to-transparent px-4 py-3">
					<div class="text-gradient-brand text-xl font-bold">7,000+</div>
					<div class="text-xs text-ink-300">channel partners</div>
				</div>
				<div class="relative overflow-hidden rounded-xl border border-white/10 bg-gradient-to-br from-azure-500/10 to-transparent px-4 py-3">
					<div class="text-gradient-brand text-xl font-bold">30,000+</div>
					<div class="text-xs text-ink-300">retail touchpoints</div>
				</div>
			</div>
			<?php
			jbnewgen_pillar_screen( 'Channel reach across India', ob_get_clean() );
			break;
	}
}
