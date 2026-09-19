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
	);
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
