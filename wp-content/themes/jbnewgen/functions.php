<?php

require_once get_template_directory() . '/inc/carbon-fields.php';
require_once get_template_directory() . '/inc/admin-style.php';
require_once get_template_directory() . '/inc/mail-resend.php';
require_once get_template_directory() . '/inc/two-factor.php';
require_once get_template_directory() . '/inc/icon-options.php';
require_once get_template_directory() . '/inc/seo-fields.php';
require_once get_template_directory() . '/inc/post-types.php';
require_once get_template_directory() . '/inc/roles.php';
require_once get_template_directory() . '/inc/reports.php';
require_once get_template_directory() . '/inc/issues.php';
require_once get_template_directory() . '/inc/admin-breadcrumb.php';
require_once get_template_directory() . '/inc/block-chart.php';
require_once get_template_directory() . '/inc/svg-upload.php';
require_once get_template_directory() . '/inc/admin-columns.php';
require_once get_template_directory() . '/inc/admin-dashboard.php';
require_once get_template_directory() . '/inc/taxonomies.php';
require_once get_template_directory() . '/inc/taxonomy-add-screen.php';
require_once get_template_directory() . '/inc/fields-pillar.php';
require_once get_template_directory() . '/inc/fields-service.php';
require_once get_template_directory() . '/inc/fields-insight.php';
require_once get_template_directory() . '/inc/fields-team-member.php';
require_once get_template_directory() . '/inc/fields-job.php';
require_once get_template_directory() . '/inc/options-homepage.php';
require_once get_template_directory() . '/inc/options-about.php';
require_once get_template_directory() . '/inc/options-site-settings.php';

// --- Public site -----------------------------------------------------------
require_once get_template_directory() . '/inc/icons.php';
require_once get_template_directory() . '/inc/nav.php';
require_once get_template_directory() . '/inc/home-data.php';

/**
 * Theme supports for the public templates.
 */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
} );

/**
 * Front-end assets.
 *
 * assets/site.css is COMPILED OUTPUT, committed to the repo -- the server has
 * no build step. Rebuild it with ./.wp-local/tailwind/build.sh after editing a
 * template, or new utility classes will not exist in the stylesheet.
 *
 * filemtime() is the cache buster: the version changes only when the file
 * really changes, so a redeploy that does not touch the CSS keeps it cached.
 */
add_action( 'wp_enqueue_scripts', function () {
	$dir = get_template_directory();
	$uri = get_template_directory_uri();

	// Inter, Space Grotesk, Instrument Serif -- the three families the Next.js
	// app loaded through next/font. Self-hosting them is a migration task; until
	// the woff2 files are in the theme this uses the same families from Google,
	// preconnected so the request starts early.
	wp_enqueue_style(
		'jbnewgen-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap',
		array(),
		null
	);

	$css = $dir . '/assets/site.css';
	wp_enqueue_style(
		'jbnewgen-site',
		$uri . '/assets/site.css',
		array( 'jbnewgen-fonts' ),
		file_exists( $css ) ? filemtime( $css ) : null
	);

	$js = $dir . '/assets/site.js';
	wp_enqueue_script(
		'jbnewgen-site',
		$uri . '/assets/site.js',
		array(),
		file_exists( $js ) ? filemtime( $js ) : null,
		true
	);
}, 10 );

/**
 * Point the font stack at the families actually loaded above.
 *
 * globals.css referenced --font-inter / --font-display / --font-instrument,
 * which next/font defined at build time. Nothing defines them here, so without
 * this every token would fall through to its generic fallback and the site
 * would render in system sans.
 */
add_action( 'wp_head', function () {
	?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<style id="jbnewgen-font-vars">
		:root {
			--font-inter: "Inter";
			--font-display: "Space Grotesk";
			--font-instrument: "Instrument Serif";
		}
	</style>
	<?php
}, 1 );
