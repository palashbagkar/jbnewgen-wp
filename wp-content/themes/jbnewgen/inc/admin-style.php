<?php
/**
 * Dark, minimal wp-admin theme — admin-only, never touches the public site.
 * Monochrome: #141414 background, #222222 boxes, #3c3c3c / #575757 borders,
 * white text. No WordPress branding left visible anywhere in the admin.
 */

add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_style(
		'jbnewgen-admin',
		get_template_directory_uri() . '/assets/admin.css',
		array(),
		filemtime( get_template_directory() . '/assets/admin.css' )
	);
} );

add_action( 'login_enqueue_scripts', function () {
	wp_enqueue_style(
		'jbnewgen-admin',
		get_template_directory_uri() . '/assets/admin.css',
		array(),
		filemtime( get_template_directory() . '/assets/admin.css' )
	);
} );

// The block editor renders its canvas in an iframe, which the admin
// stylesheet cannot reach — this injects styles into that document.
add_action( 'after_setup_theme', function () {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/editor.css' );
} );

// The default login logo links to wordpress.org — point it at the site instead.
add_filter( 'login_headerurl', function () {
	return home_url( '/' );
} );
add_filter( 'login_headertext', function () {
	return get_bloginfo( 'name' );
} );

// Strip every visible "WordPress" mention out of the admin.
add_filter( 'admin_footer_text', '__return_empty_string' );      // "Thank you for creating with WordPress."
add_filter( 'update_footer', '__return_empty_string', 11 );      // "Version X.X"

add_filter( 'gettext', function ( $translated, $original, $domain ) {
	if ( 'default' === $domain && 'Howdy, %s' === $original ) {
		return '%s';
	}
	return $translated;
}, 10, 3 );

/**
 * Admin bar: keep only the site name, "+ New" and the account menu.
 */
add_action( 'admin_bar_menu', function ( $bar ) {
	// 'command-palette' is the Ctrl+K launcher; 'search' is the older field.
	foreach ( array( 'wp-logo', 'search', 'command-palette', 'comments', 'updates' ) as $node ) {
		$bar->remove_node( $node );
	}

	// The site link is icon-only, so its visible text is dropped and the
	// accessible name moved to the title attribute.
	$site = $bar->get_node( 'site-name' );
	if ( $site ) {
		$site->title = '<span class="screen-reader-text">' . esc_html__( 'Visit site', 'jbnewgen' ) . '</span>';
		$site->meta['title'] = __( 'Visit site', 'jbnewgen' );
		$bar->add_node( $site );
	}

	// Same for "+ New" — but its plus is drawn by the .ab-icon span WordPress
	// puts in the title, so that span has to survive; only the label goes.
	$new = $bar->get_node( 'new-content' );
	if ( $new ) {
		$new->title = '<span class="ab-icon" aria-hidden="true"></span>'
			. '<span class="screen-reader-text">' . esc_html__( 'Add new', 'jbnewgen' ) . '</span>';
		$new->meta['title'] = __( 'Add new', 'jbnewgen' );
		$bar->add_node( $new );
	}

	// The account node keeps its avatar; the display name is dropped in CSS
	// so the greeting markup WordPress builds stays intact.
}, 999 );

// No Screen Options tab, no Help tab. Help tabs are registered on the
// load-{page} hook, so they have to be cleared later than 'current_screen'.
add_filter( 'screen_options_show_screen', '__return_false' );
add_action( 'admin_head', function () {
	$screen = get_current_screen();
	if ( $screen ) {
		$screen->remove_help_tabs();
	}
}, 999 );

/**
 * Sidebar: three plain sections instead of WordPress's icon list.
 * Headers are inert labels — CSS makes them non-interactive.
 */
add_action( 'admin_menu', function () {
	global $menu;

	$sections = array(
		'jb-section-services' => __( 'Services', 'jbnewgen' ),
		'jb-section-careers'  => __( 'Careers', 'jbnewgen' ),
		'jb-section-insights' => __( 'Insights', 'jbnewgen' ),
		'jb-section-site'     => __( 'Site', 'jbnewgen' ),
	);

	foreach ( $sections as $slug => $label ) {
		$menu[] = array( $label, 'read', $slug, '', 'jb-menu-section', '', '' );
	}

	// Insight Categories is a taxonomy, so it lives under Insights by default.
	// Promote it to its own entry so it sits beside Insights in the section.
	$menu[] = array(
		__( 'Insight Categories', 'jbnewgen' ),
		'manage_categories',
		'edit-tags.php?taxonomy=insight_category&post_type=insight',
		'',
		'menu-top',
		'',
		'',
	);
	remove_submenu_page(
		'edit.php?post_type=insight',
		'edit-tags.php?taxonomy=insight_category&post_type=insight'
	);

	// The Dashboard's own sub-links.
	remove_submenu_page( 'index.php', 'index.php' );       // "Home"
	remove_submenu_page( 'index.php', 'update-core.php' ); // "Updates"
}, 999 );

// Keep the promoted taxonomy entry highlighted instead of "Insights".
add_filter( 'parent_file', function ( $parent ) {
	$screen = get_current_screen();
	if ( $screen && 'edit-insight_category' === $screen->id ) {
		return 'edit-tags.php?taxonomy=insight_category&post_type=insight';
	}
	return $parent;
} );

/**
 * WordPress repeats every top-level item as the first entry of its own
 * submenu ("Service Categories > Service Categories"), which reads as a
 * duplicated link. Drop any submenu entry that points at its own parent.
 * Carbon Fields options pages are skipped — they register their page through
 * that same self-referencing submenu entry, so removing it would 404 them.
 */
add_action( 'admin_menu', function () {
	global $submenu;

	foreach ( $submenu as $parent => $items ) {
		if ( 0 === strpos( $parent, 'crb_' ) ) {
			continue;
		}
		foreach ( $items as $i => $item ) {
			if ( isset( $item[2] ) && $item[2] === $parent ) {
				unset( $submenu[ $parent ][ $i ] );
			}
		}
	}
}, 1000 );

add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'menu_order', function () {
	// Anything not listed here keeps its default relative order and falls to
	// the bottom — that's where WordPress's own separators end up, and they
	// are hidden in CSS.
	return array(
		'index.php',

		'jb-section-services',
		'edit.php?post_type=pillar',
		'edit.php?post_type=service',

		'jb-section-careers',
		'edit.php?post_type=job',
		'edit.php?post_type=team_member',

		'jb-section-insights',
		'edit.php?post_type=insight',
		'edit-tags.php?taxonomy=insight_category&post_type=insight',

		'jb-section-site',
		'upload.php',
		'users.php',
		'crb_carbon_fields_container_homepage.php',
		'crb_carbon_fields_container_about.php',
		'crb_carbon_fields_container_site_settings.php',
		'edit.php',
		'edit.php?post_type=page',
		'edit-comments.php',
		'themes.php',
		'plugins.php',
		'tools.php',
		'options-general.php',
	);
} );

/**
 * Makes the three sidebar sections collapse/expand, remembering each one's
 * state in localStorage. Everything between one section header and the next
 * belongs to that section.
 */
add_action( 'admin_footer', function () {
	?>
	<script>
	( function () {
		var menu = document.getElementById( 'adminmenu' );
		if ( ! menu ) { return; }

		var groups = [];
		var current = null;

		Array.prototype.forEach.call( menu.children, function ( li ) {
			if ( li.classList.contains( 'jb-menu-section' ) ) {
				current = { header: li, items: [] };
				groups.push( current );
			} else if ( current ) {
				current.items.push( li );
			}
		} );

		groups.forEach( function ( group ) {
			var link = group.header.querySelector( 'a' );
			if ( ! link ) { return; }

			var key = 'jbSection:' + ( group.header.id || link.textContent.trim() );

			function apply( collapsed ) {
				group.items.forEach( function ( li ) {
					li.style.display = collapsed ? 'none' : '';
				} );
				group.header.classList.toggle( 'is-collapsed', collapsed );
				link.setAttribute( 'aria-expanded', collapsed ? 'false' : 'true' );
			}

			// Never hide the section that holds the page you are on.
			var hasCurrent = group.items.some( function ( li ) {
				return li.classList.contains( 'current' ) ||
					li.classList.contains( 'wp-has-current-submenu' );
			} );

			apply( ! hasCurrent && window.localStorage.getItem( key ) === '1' );

			link.setAttribute( 'role', 'button' );
			link.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				var collapsed = ! group.header.classList.contains( 'is-collapsed' );
				window.localStorage.setItem( key, collapsed ? '1' : '0' );
				apply( collapsed );
			} );
		} );

		// Admin-bar toggle. On desktop it proxies WordPress's own collapse
		// button so the folded state is saved against the user account. At
		// the responsive breakpoint WordPress uses a different mechanism —
		// the `wp-responsive-open` body class — so drive that instead.
		var proxy = document.querySelector( '#wp-admin-bar-jb-collapse a' );
		var real  = document.getElementById( 'collapse-button' );

		// WordPress puts `wp-responsive-open` on #wpwrap, not <body> — its own
		// rule is `.auto-fold .wp-responsive-open #adminmenuwrap { display: block }`,
		// a descendant selector. Setting it on <body> leaves the menu hidden.
		var wrap = document.getElementById( 'wpwrap' );

		if ( proxy && wrap ) {
			proxy.addEventListener( 'click', function ( event ) {
				event.preventDefault();

				if ( window.matchMedia( '(max-width: 782px)' ).matches ) {
					var open = wrap.classList.toggle( 'wp-responsive-open' );
					// #wpadminbar is a sibling of #wpwrap, so the class above
					// cannot reach the toggle icon — mirror it onto <body>.
					document.body.classList.toggle( 'jb-drawer-open', open );
					return;
				}

				if ( real ) { real.click(); }
			} );
		}

		// Tapping a link or the background closes the drawer again.
		document.addEventListener( 'click', function ( event ) {
			if ( ! wrap || ! wrap.classList.contains( 'wp-responsive-open' ) ) { return; }
			if ( ! window.matchMedia( '(max-width: 782px)' ).matches ) { return; }
			if ( ! event.target || ! event.target.closest ) { return; }

			var inMenu   = event.target.closest( '#adminmenumain' );
			var inToggle = event.target.closest( '#wp-admin-bar-jb-collapse' );
			var isLink   = event.target.closest( '#adminmenu a' );

			if ( ( ! inMenu && ! inToggle ) || isLink ) {
				wrap.classList.remove( 'wp-responsive-open' );
				document.body.classList.remove( 'jb-drawer-open' );
			}
		} );
	}() );
	</script>
	<?php
} );

/**
 * Print the post type's description under the page heading, the way Payload
 * shows its `admin.description`. WordPress stores the value but never
 * displays it on the list or edit screens.
 */
add_action( 'all_admin_notices', function () {
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->base, array( 'edit', 'post' ), true ) ) {
		return;
	}

	$obj = get_post_type_object( $screen->post_type );
	if ( ! $obj || empty( $obj->description ) ) {
		return;
	}

	printf(
		'<p class="jb-screen-desc">%s</p>',
		esc_html( $obj->description )
	);
} );

/**
 * Move the description under the page heading.
 *
 * It is printed on `all_admin_notices`, which WordPress fires *before* the
 * .wrap element that holds the <h1> — so it lands above the title and no
 * amount of CSS can reorder it across that boundary. Relocating it in the DOM
 * is the reliable fix.
 */
add_action( 'admin_footer', function () {
	?>
	<script>
	( function () {
		var desc = document.querySelector( '.jb-screen-desc' );
		if ( ! desc ) { return; }

		var wrap = document.querySelector( '#wpbody-content .wrap' );
		if ( ! wrap ) { return; }

		// List screens mark the end of the heading row with <hr class="wp-header-end">;
		// edit screens just have the <h1>.
		var anchor = wrap.querySelector( '.wp-header-end' ) || wrap.querySelector( 'h1' );
		if ( ! anchor ) { return; }

		anchor.parentNode.insertBefore( desc, anchor.nextSibling );
	}() );
	</script>
	<?php
} );
