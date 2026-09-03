<?php
/**
 * Replaces the WordPress page heading with a small path box in the admin bar,
 * e.g. "dashboard / service-categories".
 *
 * The path is always at most two segments: "dashboard" plus the page itself.
 * The sidebar's grouping labels (services, careers, insights, site) are
 * deliberately NOT part of it — they are only a grouping device, not places
 * you can navigate to.
 *
 * The <h1> stays in the DOM but is visually hidden, so screen readers still
 * get a real page heading.
 */

/**
 * Every screen that gets its own path segment, keyed by how it is identified.
 */
function jbnewgen_breadcrumb_map() {
	return array(
		'post_type' => array(
			'pillar'      => 'service-categories',
			'service'     => 'services',
			'job'         => 'job-openings',
			'team_member' => 'core-team',
			'insight'     => 'insights',
			'post'        => 'posts',
			'page'        => 'pages',
			'attachment'  => 'media',
		),
		'taxonomy' => array(
			'insight_category' => 'insight-categories',
		),
		'base' => array(
			'upload'          => 'media',
			'media'           => 'media',
			'users'           => 'users',
			'user'            => 'users',
			'user-edit'       => 'users',
			'profile'         => 'users',
			'edit-comments'   => 'comments',
			'themes'          => 'appearance',
			'plugins'         => 'plugins',
			'tools'           => 'tools',
			'options-general' => 'settings',
		),
		// Carbon Fields options pages, matched on a fragment of the screen id.
		'options_page' => array(
			'homepage'      => 'homepage',
			'about'         => 'about-pages',
			'site_settings' => 'site-settings',
		),
	);
}

/**
 * Build the path segments for the current admin screen.
 *
 * @return array Either [ 'dashboard' ] or [ 'dashboard', '<page>' ].
 */
function jbnewgen_breadcrumb_segments() {
	$screen = get_current_screen();
	if ( ! $screen ) {
		return array();
	}

	if ( 'dashboard' === $screen->base ) {
		return array( 'dashboard' );
	}

	$map  = jbnewgen_breadcrumb_map();
	$page = '';

	// Carbon Fields options pages carry their id in the screen base.
	if ( false !== strpos( $screen->id, 'crb_carbon_fields_container_' ) ) {
		foreach ( $map['options_page'] as $needle => $slug ) {
			if ( false !== strpos( $screen->id, 'container_' . $needle ) ) {
				$page = $slug;
				break;
			}
		}
	}

	if ( ! $page && ! empty( $screen->taxonomy ) && isset( $map['taxonomy'][ $screen->taxonomy ] ) ) {
		$page = $map['taxonomy'][ $screen->taxonomy ];
	}

	if ( ! $page && ! empty( $screen->post_type ) && isset( $map['post_type'][ $screen->post_type ] ) ) {
		$page = $map['post_type'][ $screen->post_type ];
	}

	if ( ! $page && isset( $map['base'][ $screen->base ] ) ) {
		$page = $map['base'][ $screen->base ];
	}

	// Anything unmapped still gets a sensible second segment.
	if ( ! $page ) {
		$page = str_replace( '_', '-', sanitize_title( $screen->base ) );
	}

	return array( 'dashboard', $page );
}

/**
 * Where each segment points.
 */
function jbnewgen_breadcrumb_url( $segment ) {
	$targets = array(
		'dashboard'          => 'index.php',
		'service-categories' => 'edit.php?post_type=pillar',
		'services'           => 'edit.php?post_type=service',
		'job-openings'       => 'edit.php?post_type=job',
		'core-team'          => 'edit.php?post_type=team_member',
		'insights'           => 'edit.php?post_type=insight',
		'insight-categories' => 'edit-tags.php?taxonomy=insight_category&post_type=insight',
		'media'              => 'upload.php',
		'users'              => 'users.php',
		'posts'              => 'edit.php',
		'pages'              => 'edit.php?post_type=page',
		'comments'           => 'edit-comments.php',
		'appearance'         => 'themes.php',
		'plugins'            => 'plugins.php',
		'tools'              => 'tools.php',
		'settings'           => 'options-general.php',
		'homepage'           => 'admin.php?page=crb_carbon_fields_container_homepage.php',
		'about-pages'        => 'admin.php?page=crb_carbon_fields_container_about.php',
		'site-settings'      => 'admin.php?page=crb_carbon_fields_container_site_settings.php',
	);

	return isset( $targets[ $segment ] ) ? admin_url( $targets[ $segment ] ) : '';
}

/**
 * Build the path box markup.
 */
function jbnewgen_breadcrumb_html() {
	$segments = jbnewgen_breadcrumb_segments();
	if ( ! $segments ) {
		return '';
	}

	$last = count( $segments ) - 1;

	ob_start();

	foreach ( $segments as $i => $segment ) {
		if ( $i > 0 ) {
			echo '<span class="jb-path__sep">/</span>';
		}

		$url     = jbnewgen_breadcrumb_url( $segment );
		$classes = 'jb-path__seg' . ( $i === $last ? ' jb-path__seg--current' : '' );

		if ( $url ) {
			printf(
				'<a class="%s" href="%s"%s>%s</a>',
				esc_attr( $classes ),
				esc_url( $url ),
				$i === $last ? ' aria-current="page"' : '',
				esc_html( $segment )
			);
		} else {
			printf(
				'<span class="%s">%s</span>',
				esc_attr( $classes ),
				esc_html( $segment )
			);
		}
	}

	return ob_get_clean();
}

/**
 * Render the path box into the admin bar.
 */
add_action( 'admin_bar_menu', function ( $bar ) {
	$html = jbnewgen_breadcrumb_html();
	if ( ! $html ) {
		return;
	}

	$bar->add_node( array(
		'id'    => 'jb-path',
		'title' => $html,
		'meta'  => array( 'class' => 'jb-path-node' ),
	) );
}, 1001 );
