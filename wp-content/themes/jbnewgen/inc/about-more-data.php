<?php
/**
 * The parts of /about/company and /about/ceo that go beyond what
 * inc/options-about.php models as Carbon Fields (hero copy, stats, creds,
 * profile paragraphs). Ported verbatim from the page-local consts in
 * ../jbnewgen/src/app/(frontend)/about/company/page.tsx and .../about/ceo/page.tsx
 * -- machine-extracted into inc/data/, same reasoning as inc/services-data.php.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Company hero copy (options-about.php "The Company" tab), falling back to
 * ../jbnewgen/src/lib/about-copy.ts `companyCopy` -- same pick-or-default
 * pattern as jbnewgen_home_view() in inc/home-data.php.
 *
 * @return array
 */
function jbnewgen_about_company_view() {
	$opt = function ( $key ) {
		return function_exists( 'carbon_get_theme_option' ) ? carbon_get_theme_option( $key ) : '';
	};
	$defaults = include get_template_directory() . '/inc/data/companyCopy.php';

	$rows  = $opt( 'company_who_intro' );
	$intro = array();
	if ( is_array( $rows ) ) {
		foreach ( $rows as $row ) {
			if ( ! empty( $row['text'] ) ) {
				$intro[] = $row['text'];
			}
		}
	}

	$stat_rows = $opt( 'company_stats' );
	$stats     = array();
	if ( is_array( $stat_rows ) ) {
		foreach ( $stat_rows as $row ) {
			if ( ! empty( $row['stat_value'] ) && ! empty( $row['desc'] ) ) {
				$stats[] = array( 'value' => $row['stat_value'], 'desc' => $row['desc'] );
			}
		}
	}

	$hero_body = trim( (string) $opt( 'company_hero_body' ) );
	$who_title = trim( (string) $opt( 'company_who_title' ) );

	return array(
		'heroBody' => '' !== $hero_body ? $hero_body : $defaults['heroBody'],
		'whoTitle' => '' !== $who_title ? $who_title : $defaults['whoTitle'],
		'whoIntro' => $intro ? $intro : $defaults['whoIntro'],
		'stats'    => $stats ? $stats : $defaults['stats'],
	);
}

/**
 * CEO hero + profile copy (options-about.php "Our CEO" tab), falling back to
 * ../jbnewgen/src/lib/about-copy.ts `ceoCopy`.
 *
 * @return array
 */
function jbnewgen_about_ceo_view() {
	$opt = function ( $key ) {
		return function_exists( 'carbon_get_theme_option' ) ? carbon_get_theme_option( $key ) : '';
	};
	$defaults = include get_template_directory() . '/inc/data/ceoCopy.php';

	$pick = function ( $key, $default ) use ( $opt ) {
		$v = trim( (string) $opt( $key ) );
		return '' !== $v ? $v : $default;
	};

	$cred_rows = $opt( 'ceo_creds' );
	$creds     = array();
	if ( is_array( $cred_rows ) ) {
		foreach ( $cred_rows as $row ) {
			if ( ! empty( $row['stat_value'] ) && ! empty( $row['label'] ) ) {
				$creds[] = array( 'value' => $row['stat_value'], 'label' => $row['label'] );
			}
		}
	}

	$rest_rows = $opt( 'ceo_profile_rest' );
	$rest      = array();
	if ( is_array( $rest_rows ) ) {
		foreach ( $rest_rows as $row ) {
			if ( ! empty( $row['text'] ) ) {
				$rest[] = $row['text'];
			}
		}
	}

	$photo_id = (int) $opt( 'ceo_photo' );

	return array(
		'role'             => $pick( 'ceo_role', $defaults['role'] ),
		'photo'            => $photo_id ? wp_get_attachment_image_url( $photo_id, 'large' ) : '',
		'linkedin'         => $pick( 'ceo_linkedin', $defaults['linkedin'] ),
		'heroSubtitle'     => $pick( 'ceo_hero_subtitle', $defaults['heroSubtitle'] ),
		'heroBody'         => $pick( 'ceo_hero_body', $defaults['heroBody'] ),
		'creds'            => $creds ? $creds : $defaults['creds'],
		'profileP1'        => $pick( 'ceo_profile_p1', $defaults['profileP1'] ),
		'profileQuote'     => $pick( 'ceo_profile_quote', $defaults['profileQuote'] ),
		'profileQuoteAttr' => $pick( 'ceo_profile_quote_attr', $defaults['profileQuoteAttr'] ),
		'profileRest'      => $rest ? $rest : $defaults['profileRest'],
	);
}

/**
 * Team intro copy (options-about.php "Core Team" tab) + the real published
 * team_member posts, falling back to coreTeam.php verbatim when the CPT is
 * still empty.
 *
 * @return array
 */
function jbnewgen_about_team_view() {
	$opt = function ( $key ) {
		return function_exists( 'carbon_get_theme_option' ) ? carbon_get_theme_option( $key ) : '';
	};

	$members = get_posts( array(
		'post_type'        => 'team_member',
		'post_status'      => 'publish',
		'numberposts'      => 50,
		'orderby'          => 'menu_order',
		'order'            => 'ASC',
		'suppress_filters' => false,
	) );

	$rows = array();
	foreach ( $members as $m ) {
		$photo_id = function_exists( 'carbon_get_post_meta' ) ? (int) carbon_get_post_meta( $m->ID, 'photo' ) : 0;
		$rows[]   = array(
			'name'     => $m->post_title,
			'role'     => function_exists( 'carbon_get_post_meta' ) ? (string) carbon_get_post_meta( $m->ID, 'role' ) : '',
			'bio'      => function_exists( 'carbon_get_post_meta' ) ? (string) carbon_get_post_meta( $m->ID, 'bio' ) : '',
			'photo'    => $photo_id ? wp_get_attachment_image_url( $photo_id, 'large' ) : '',
			'linkedin' => function_exists( 'carbon_get_post_meta' ) ? (string) carbon_get_post_meta( $m->ID, 'linkedin_url' ) : '',
		);
	}

	if ( ! $rows ) {
		foreach ( include get_template_directory() . '/inc/data/coreTeam.php' as $m ) {
			$rows[] = $m;
		}
	}

	$title = trim( (string) $opt( 'team_intro_title' ) );
	$body  = trim( (string) $opt( 'team_intro_body' ) );

	return array(
		'introTitle' => '' !== $title ? $title : 'We draw on our global network to assemble a team of experts.',
		'introBody'  => '' !== $body ? $body : 'We also bring a strong interest in coaching and capability building, with an emphasis on emotional intelligence and effective stakeholder relationships.',
		'members'    => $rows,
	);
}

function jbnewgen_about_company_extra() {
	$dir = get_template_directory() . '/inc/data/';
	return array(
		'diffs'          => include $dir . 'company-diffs.php',
		'story'          => include $dir . 'company-story.php',
		'network'        => include $dir . 'company-network.php',
		'services'       => include $dir . 'company-services.php',
		'bridgeFeatures' => include $dir . 'company-bridgeFeatures.php',
	);
}

function jbnewgen_about_ceo_extra() {
	$dir = get_template_directory() . '/inc/data/';
	return array(
		'career'    => include $dir . 'ceo-career.php',
		'expertise' => include $dir . 'ceo-expertise.php',
		'brings'    => include $dir . 'ceo-brings.php',
	);
}
