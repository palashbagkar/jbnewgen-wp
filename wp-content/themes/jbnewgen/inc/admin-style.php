<?php
/**
 * Dark, minimal wp-admin theme — admin-only, never touches the public site.
 * Monochrome: #141414 background, #222222 boxes, #3c3c3c / #575757 borders,
 * white text. No WordPress branding left visible anywhere in the admin.
 */

/**
 * Slate (vendored, GPLv2 — see lib/slate/VENDORED.txt) supplies the layout;
 * admin.css repaints it in Payload's palette. admin.css declares Slate as a
 * dependency so it always loads second and wins.
 *
 * Slate's own dynamic.php and its "Slate Pro" footer filter are deliberately
 * not loaded — the first injects blue/red accents inline on admin_head, which
 * would land after these stylesheets and override the palette.
 */
function jbnewgen_admin_assets() {
	$dir = get_template_directory();
	$uri = get_template_directory_uri();

	wp_enqueue_style(
		'slate-admin',
		$uri . '/lib/slate/slate.css',
		array(),
		filemtime( $dir . '/lib/slate/slate.css' )
	);

	wp_enqueue_style(
		'jbnewgen-admin',
		$uri . '/assets/admin.css',
		array( 'slate-admin' ),
		filemtime( $dir . '/assets/admin.css' )
	);
}
add_action( 'admin_enqueue_scripts', 'jbnewgen_admin_assets' );
add_action( 'login_enqueue_scripts', 'jbnewgen_admin_assets' );

/**
 * Kill the white flash between page loads.
 *
 * The stylesheets above are external files. Between unloading one admin screen
 * and painting the next, the browser has no CSS yet and paints its own default
 * canvas — which is white — so every navigation and every refresh strobes.
 *
 * Two things fix it, and both have to be inline in <head> to beat the network:
 *
 *   color-scheme: dark   tells the browser what colour its OWN canvas should
 *                        be, which is the one it paints before any author CSS
 *                        exists. This is the half that actually stops the
 *                        flash; a background rule alone still needs the
 *                        document's CSSOM.
 *   background #141414   pins the root to the page colour so the handover to
 *                        admin.css is seamless rather than a second flash.
 *
 * Priority 0 so it is the first thing in the head, ahead of anything a plugin
 * prints there.
 */
function jbnewgen_admin_paint_early() {
	echo '<meta name="color-scheme" content="dark">' . PHP_EOL;
	echo '<style id="jb-preflight">:root{color-scheme:dark}html,body{background-color:#141414}</style>' . PHP_EOL;
}
add_action( 'admin_head', 'jbnewgen_admin_paint_early', 0 );
add_action( 'login_head', 'jbnewgen_admin_paint_early', 0 );

/**
 * The admin colour schemes go.
 *
 * WordPress ships eight of them and offers the choice on every profile screen.
 * This admin has exactly one palette, deliberately, and every one of those
 * schemes repaints it into something else — the picker is an offer to break
 * the design. Core guards that whole row with
 * `has_action( 'admin_color_scheme_picker' )`, so unhooking the renderer
 * removes the row, its heading and its preview swatches together rather than
 * leaving an empty table cell behind.
 */
add_action( 'admin_init', function () {
	remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
} );

/**
 * Nickname stops being required.
 *
 * edit_user() in wp-admin/includes/user.php rejects an empty nickname on every
 * profile save. The field is a leftover from WordPress's blogging past — it is
 * only ever a candidate for the public display name, and display_name is
 * already set and editable on its own. Making someone invent a second name
 * before they can change their email is a dead end.
 *
 * The check fires just before this action, so the error is cleared here and
 * the nickname falls back to the username — exactly what WordPress does for a
 * brand-new account. $user arrives by reference, so the fallback sticks.
 */
add_action( 'user_profile_update_errors', function ( $errors, $update, $user ) {
	if ( ! $errors->get_error_message( 'nickname' ) ) {
		return;
	}

	$errors->remove( 'nickname' );

	if ( empty( $user->nickname ) ) {
		$user->nickname = $user->user_login;
	}
}, 10, 3 );

add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_script(
		'slate-admin',
		get_template_directory_uri() . '/lib/slate/slate.js',
		array( 'jquery' ),
		filemtime( get_template_directory() . '/lib/slate/slate.js' ),
		true
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
	global $menu, $submenu;

	$sections = array(
		'jb-section-services' => __( 'Services', 'jbnewgen' ),
		'jb-section-careers'  => __( 'Careers', 'jbnewgen' ),
		'jb-section-insights' => __( 'Insights', 'jbnewgen' ),
		'jb-section-access'   => __( 'Users & Roles', 'jbnewgen' ),
		'jb-section-profile'  => __( 'Profile', 'jbnewgen' ),
		'jb-section-site'     => __( 'Site', 'jbnewgen' ),
		'jb-section-support'  => __( 'Support', 'jbnewgen' ),
	);

	foreach ( $sections as $slug => $label ) {
		$menu[] = array( $label, 'read', $slug, '', 'jb-menu-section', '', '' );
	}

	/*
	 * Profile is its own section rather than a child of Users. Users and Roles
	 * answer "who has access and what may they do"; Profile is the signed-in
	 * person's own account, which is a different question and belongs under
	 * its own heading.
	 *
	 * WordPress adds profile.php as a top-level entry by itself, but only for
	 * people who cannot list users; for everyone else it is a child of Users,
	 * which is where it had been showing up. The guard adds it for that second
	 * group only, so the entry never appears twice.
	 */
	if ( current_user_can( 'list_users' ) ) {
		$menu[] = array(
			__( 'Profile', 'jbnewgen' ),
			'read',
			'profile.php',
			'',
			'menu-top',
			'',
			'',
		);
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
	/*
	 * WordPress registers taxonomy submenu slugs with an HTML-encoded
	 * ampersand — "edit-tags.php?taxonomy=insight_category&amp;post_type=insight" —
	 * so remove_submenu_page() compared that against the plain "&" written
	 * here, never matched, and the entry survived under Insights alongside
	 * the promoted copy. It went unnoticed while "Add New" sat above it;
	 * once that was dropped it became the first child, and WordPress points
	 * a parent at its first child, so "Insights" started opening the
	 * categories screen. Compare on the decoded slug instead.
	 */
	$jb_insight_tax = 'edit-tags.php?taxonomy=insight_category&post_type=insight';
	if ( ! empty( $submenu['edit.php?post_type=insight'] ) ) {
		foreach ( $submenu['edit.php?post_type=insight'] as $i => $item ) {
			if ( isset( $item[2] ) && html_entity_decode( $item[2] ) === $jb_insight_tax ) {
				unset( $submenu['edit.php?post_type=insight'][ $i ] );
			}
		}
	}

	// The Dashboard's own sub-links.
	remove_submenu_page( 'index.php', 'index.php' );       // "Home"
	remove_submenu_page( 'index.php', 'update-core.php' ); // "Updates"

	// Dashboard leaves the list entirely — it is the house icon in the rail
	// now. Dropping it from the menu_order array was not enough: anything not
	// named in that array keeps its default order and simply falls to the
	// bottom, so "Dashboard" was still rendering, just last.
	// This removes the nav entry only; index.php itself stays reachable, which
	// is what the rail button links to.
	remove_menu_page( 'index.php' );

	/*
	 * Four screens WordPress ships that this site has no use for:
	 *
	 *   Posts       the built-in blog. Articles are the Insight type instead
	 *   Pages       built-in database pages. The front end is hand-written PHP
	 *               templates fed by the option screens, not by Pages
	 *   Comments    no post type here declares `comments` support
	 *   Appearance  one hand-written theme, and nothing on that screen does
	 *               anything to it but risk it
	 *
	 * This hides the entries. It does not revoke the capability — the screens
	 * stay reachable by URL for anyone holding the cap, which is what
	 * inc/roles.php governs.
	 */
	remove_menu_page( 'edit.php' );
	remove_menu_page( 'edit.php?post_type=page' );
	remove_menu_page( 'edit-comments.php' );
	remove_menu_page( 'themes.php' );
}, 999 );

// Keep the promoted taxonomy entry highlighted instead of "Insights".
add_filter( 'parent_file', function ( $parent ) {
	$screen = get_current_screen();
	if ( $screen && 'edit-insight_category' === $screen->id ) {
		return 'edit-tags.php?taxonomy=insight_category&post_type=insight';
	}

	/*
	 * Profile lit up Users as well as itself, and the reason is a stray
	 * condition in wp-admin/user-edit.php:
	 *
	 *   if ( current_user_can( 'edit_users' ) && ! is_user_admin() )
	 *       $parent_file = 'users.php'
	 *
	 * `is_user_admin()` is the multisite user dashboard, which is false on a
	 * single site — so for any administrator viewing their OWN profile the
	 * test passes and the parent is reported as Users. (The line above it
	 * guards on IS_PROFILE_PAGE, which is what this one meant to do.)
	 * WordPress then marked Users current from $parent_file and Profile
	 * current from $self, and highlighted both.
	 */
	if ( $screen && 'profile' === $screen->base ) {
		return 'profile.php';
	}

	return $parent;
} );

// ...and the same screen's submenu pointer, so nothing under Users lights up.
add_filter( 'submenu_file', function ( $file ) {
	$screen = get_current_screen();
	if ( $screen && 'profile' === $screen->base ) {
		return 'profile.php';
	}
	return $file;
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

/**
 * The sidebar is two levels deep on purpose — sections (Services, Careers,
 * Insights, Site) and the list screens under them. "Add New" is an action you
 * perform on a list screen, not a place you navigate to, and every list screen
 * already carries that button beside its heading. Leaving it in the sidebar
 * made each list screen sprout a child entry the moment you opened it, which
 * is the one thing a stable navigation must not do.
 *
 * Runs after the self-referencing-entry pass above (priority 1000), so a
 * parent whose only remaining child was "Add New" ends up with an empty
 * submenu array and WordPress stops marking it `wp-has-submenu`.
 */
add_action( 'admin_menu', function () {
	global $submenu;

	// Every "create one of these" screen in the admin. Custom post types all
	// route through post-new.php; Media, Users and Plugins each have their
	// own, which is why filtering on post-new.php alone left "Add Media File"
	// and "Add User" sitting in the Site section.
	$add_screens = array( 'post-new.php', 'media-new.php', 'user-new.php', 'plugin-install.php', 'theme-install.php' );

	// Users keeps no children. Roles registers itself top-level in
	// inc/roles.php, and Profile is its own section — neither is a kind of
	// user, so neither hangs off the user list.
	remove_submenu_page( 'users.php', 'profile.php' );

	foreach ( $submenu as $parent => $items ) {
		foreach ( $items as $i => $item ) {
			if ( isset( $item[2] ) && in_array( strtok( $item[2], '?' ), $add_screens, true ) ) {
				unset( $submenu[ $parent ][ $i ] );
			}
		}
	}
}, 1001 );

add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'menu_order', function () {
	// Anything not listed here keeps its default relative order and falls to
	// the bottom — that's where WordPress's own separators end up. All but the
	// last of them is hidden in CSS; the survivor draws the hairline that
	// closes the menu.
	return array(
		// 'index.php' is deliberately absent — the Dashboard is the house icon
		// in the rail, not a row in the list.
		'jb-section-services',
		'edit.php?post_type=pillar',
		'edit.php?post_type=service',

		'jb-section-careers',
		'edit.php?post_type=job',
		'edit.php?post_type=team_member',

		'jb-section-insights',
		'edit.php?post_type=insight',
		'edit-tags.php?taxonomy=insight_category&post_type=insight',

		'jb-section-access',
		'users.php',
		'jbnewgen-roles',

		'jb-section-profile',
		'profile.php',

		'jb-section-site',
		'upload.php',
		'crb_carbon_fields_container_homepage.php',
		'crb_carbon_fields_container_about.php',
		'crb_carbon_fields_container_site_settings.php',
		'plugins.php',
		'tools.php',
		'options-general.php',

		// Report is for everyone; Issues is registered with jb_view_issues, so
		// WordPress drops it from the menu for anybody who is not a developer
		// and this entry simply has nothing to order.
		//
		// Both stay IN the menu rather than living only on the rail. A page
		// registered with add_menu_page() and then removed from $menu becomes
		// unreachable — user_can_access_admin_page() resolves `?page=` by
		// walking the menu arrays and refuses anything it cannot find there.
		// That is the same trap documented in inc/roles.php.
		'jb-section-support',
		'jbnewgen-report',
		'jbnewgen-issues',
	);
} );

/**
 * Makes the three sidebar sections collapse/expand, remembering each one's
 * state in localStorage. Everything between one section header and the next
 * belongs to that section.
 */
/*
 * Sections fold without JavaScript touching layout.
 *
 * The previous version hid the items from a script. However early that ran it
 * was still after the browser had laid the menu out, so every refresh showed
 * one frame of the expanded menu — and hooking `adminmenu` to run earlier was
 * worse, because core fires that action INSIDE <ul id="adminmenu">, which put
 * a <script> element in among the <li>s.
 *
 * So the work is split three ways:
 *   PHP   tags each item with the section it belongs to (below)
 *   head  a tiny script copies the saved state onto <html> before <body>
 *         exists, so the first paint is already correct
 *   CSS   hides `li.jb-in-<section>` when <html> says that section is shut
 *
 * The only thing left for the footer script is the click handler.
 */
add_action( 'admin_menu', function () {
	global $menu;

	// Which section each top-level entry belongs to. Anything unlisted falls
	// to "site", which is where the menu_order filter puts it too.
	$sections = array(
		'edit.php?post_type=pillar'      => 'services',
		'edit.php?post_type=service'     => 'services',
		'edit.php?post_type=job'         => 'careers',
		'edit.php?post_type=team_member' => 'careers',
		'edit.php?post_type=insight'     => 'insights',

		// Position alone is not enough here. `menu_order` is applied in
		// wp-admin/includes/menu.php AFTER admin_menu has run, so this loop
		// still sees the pre-sort array — the running `$current` below works
		// only because everything it misses falls through to "site", which is
		// where it sorts to anyway. Users and Roles do not, so they are named.
		'users.php'      => 'access',
		'jbnewgen-roles' => 'access',
		'profile.php'    => 'profile',

		'jbnewgen-report' => 'support',
		'jbnewgen-issues' => 'support',
	);

	$current = '';
	foreach ( $menu as $i => $item ) {
		if ( empty( $item[2] ) ) {
			continue;
		}

		// WordPress's own separators are unlisted in the menu_order filter, so
		// they all fall to the bottom of the list — which put them inside
		// whatever the last section happened to be. Tagged `jb-in-site`, the
		// hairline they draw folded away with the Site entries and read as a
		// rule belonging to that group. A separator belongs to no section:
		// left untagged it stays put whatever is open.
		if ( isset( $item[4] ) && false !== strpos( $item[4], 'wp-menu-separator' ) ) {
			continue;
		}

		// Section headers themselves carry the marker that starts a run.
		if ( isset( $item[4] ) && false !== strpos( $item[4], 'jb-menu-section' ) ) {
			$current = str_replace( 'jb-section-', '', $item[2] );
			$menu[ $i ][4] .= ' jb-section-' . $current;
			continue;
		}

		$slug = $item[2];
		if ( isset( $sections[ $slug ] ) ) {
			$section = $sections[ $slug ];
		} elseif ( false !== strpos( $slug, 'insight_category' ) ) {
			$section = 'insights';
		} elseif ( 'index.php' === $slug ) {
			continue;
		} else {
			$section = $current ? $current : 'site';
		}

		$menu[ $i ][4] = trim( ( isset( $menu[ $i ][4] ) ? $menu[ $i ][4] : '' ) . ' jb-in-' . $section );
	}
}, 1002 );

/*
 * Runs in <head>, before <body> is parsed, so the collapsed sections are never
 * painted open. Reads the same localStorage keys the click handler writes.
 */
add_action( 'admin_head', function () {
	?>
	<script>
	( function () {
		try {
			var shut = [];
			// All six. `access` and `profile` were missing, so those two headers
			// wrote a localStorage key on click and then never read it back —
			// they folded for exactly as long as the page stayed open.
			[ 'services', 'careers', 'insights', 'access', 'profile', 'site', 'support' ].forEach( function ( key ) {
				if ( window.localStorage.getItem( 'jbSection:' + key ) === '1' ) { shut.push( key ); }
			} );
			document.documentElement.setAttribute( 'data-jb-shut', shut.join( ' ' ) );
		} catch ( e ) {}
	}() );
	</script>
	<?php
}, 1 );

add_action( 'admin_footer', function () {
	?>
	<script>
	( function () {
		var menu = document.getElementById( 'adminmenu' );
		var root = document.documentElement;
		if ( ! menu ) { return; }

		function shutList() {
			var raw = root.getAttribute( 'data-jb-shut' ) || '';
			return raw.split( ' ' ).filter( Boolean );
		}

		Array.prototype.forEach.call( menu.querySelectorAll( 'li.jb-menu-section' ), function ( header ) {
			var link = header.querySelector( 'a' );
			if ( ! link ) { return; }

			var match = ( header.className.match( /jb-section-([a-z0-9_-]+)/ ) || [] )[1];
			if ( ! match ) { return; }

			function sync() {
				var shut = shutList().indexOf( match ) > -1;
				header.classList.toggle( 'is-collapsed', shut );
				link.setAttribute( 'aria-expanded', shut ? 'false' : 'true' );
			}
			sync();

			link.setAttribute( 'role', 'button' );
			link.addEventListener( 'click', function ( event ) {
				event.preventDefault();

				var shut = shutList();
				var at   = shut.indexOf( match );
				if ( at > -1 ) { shut.splice( at, 1 ); } else { shut.push( match ); }

				root.setAttribute( 'data-jb-shut', shut.join( ' ' ) );
				try {
					window.localStorage.setItem( 'jbSection:' + match, at > -1 ? '0' : '1' );
				} catch ( e ) {}
				sync();
			} );
		} );

		// Below 783px WordPress opens the menu as an overlay via
		// `wp-responsive-open` on #wpwrap — not on <body>, its own rule is a
		// descendant selector. Tapping a link or the background puts it away.
		var wrap = document.getElementById( 'wpwrap' );
		document.addEventListener( 'click', function ( event ) {
			if ( ! wrap || ! wrap.classList.contains( 'wp-responsive-open' ) ) { return; }
			if ( ! window.matchMedia( '(max-width: 782px)' ).matches ) { return; }
			if ( ! event.target || ! event.target.closest ) { return; }

			var inMenu   = event.target.closest( '#adminmenumain' );
			var inToggle = event.target.closest( '#jb-rail' );
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
 * "You have unsaved changes" — on refresh, on close, on navigating away.
 *
 * WordPress guards the two block editors and nothing else, so every other form
 * in this admin — Site Settings, the role builder, a Service, the Report form —
 * lost whatever was typed to a stray Ctrl+R with no warning at all.
 *
 * The rules, and why each one is there:
 *
 *   only when dirty   a prompt on every page you leave is a prompt people
 *                     learn to dismiss without reading, and then it is not a
 *                     safeguard. The listener is attached on the first real
 *                     change and removed again if the form is put back.
 *   not on submit     saving IS leaving the page. Pressing Save and being
 *                     asked whether you meant it is the same dialog for the
 *                     opposite answer.
 *   not in Gutenberg  the block editor has its own, and two dialogs stacked
 *                     is worse than one.
 *
 * The browser decides the wording. Chrome, Firefox and Safari all ignore any
 * string handed to them here and show their own "Leave site? Changes you made
 * may not be saved." — a returned string is only the signal that a prompt is
 * wanted.
 */
add_action( 'admin_footer', function () {
	?>
	<script>
	( function () {
		if ( document.body.classList.contains( 'block-editor-page' ) ) { return; }

		var scope = document.getElementById( 'wpbody-content' );
		if ( ! scope ) { return; }

		var forms = scope.querySelectorAll( 'form' );
		if ( ! forms.length ) { return; }

		var dirty    = false;
		var leaving  = false;
		var baseline = new WeakMap();

		function valueOf( field ) {
			if ( field.type === 'checkbox' || field.type === 'radio' ) { return field.checked ? '1' : '0'; }
			if ( field.multiple && field.selectedOptions ) {
				return Array.prototype.map.call( field.selectedOptions, function ( o ) { return o.value; } ).join( ' ' );
			}
			return field.value;
		}

		function fields() {
			return scope.querySelectorAll( 'form input:not([type=hidden]):not([type=submit]):not([type=button]), form textarea, form select' );
		}

		Array.prototype.forEach.call( fields(), function ( field ) {
			baseline.set( field, valueOf( field ) );
		} );

		function onBeforeUnload( event ) {
			if ( ! dirty || leaving ) { return; }
			event.preventDefault();
			// Legacy browsers read the return value; current ones only check
			// that preventDefault was called, and print their own wording.
			event.returnValue = '';
			return '';
		}

		function recheck() {
			var changed = false;
			Array.prototype.forEach.call( fields(), function ( field ) {
				if ( ! baseline.has( field ) ) {
					// Added after load — a repeater row, a new complex field.
					baseline.set( field, valueOf( field ) );
					changed = true;
					return;
				}
				if ( baseline.get( field ) !== valueOf( field ) ) { changed = true; }
			} );

			if ( changed === dirty ) { return; }
			dirty = changed;

			if ( dirty ) {
				window.addEventListener( 'beforeunload', onBeforeUnload );
			} else {
				window.removeEventListener( 'beforeunload', onBeforeUnload );
			}
		}

		scope.addEventListener( 'input', recheck, true );
		scope.addEventListener( 'change', recheck, true );

		Array.prototype.forEach.call( forms, function ( form ) {
			form.addEventListener( 'submit', function () { leaving = true; } );
		} );

		// A scripted submit fires no submit event — the data bar's search runs
		// through HTMLFormElement.prototype.submit for exactly that reason —
		// so the flag has to be set from the method as well.
		var nativeSubmit = HTMLFormElement.prototype.submit;
		HTMLFormElement.prototype.submit = function () {
			leaving = true;
			return nativeSubmit.apply( this, arguments );
		};
	}() );
	</script>
	<?php
}, 98 );

/**
 * The screens this site removed stay removed, by URL as well as by menu.
 *
 * Posts, Pages, Comments and Appearance are dropped from the navigation in the
 * admin_menu pass above, which hides them without closing them — the URLs
 * still resolve for anyone holding the capability, and inc/roles.php now hands
 * `edit_posts` to every role for the menu-access reason documented there. So
 * the hiding has to be backed by a real block, or "Viewer" would be one typed
 * URL away from writing a blog post.
 *
 * A redirect to the dashboard rather than wp_die(): nobody arrives at
 * /wp-admin/edit.php on purpose in this admin, so the honest response is to
 * put them back where they were going, not to accuse them of something.
 *
 * `post.php` and `post-new.php` are matched on the post type of the actual
 * entry, not on the URL — an Insight is edited through the same post.php as a
 * blog post, and only the type tells them apart.
 */
function jbnewgen_block_unused_screens() {
	global $pagenow;

	// The two built-in content types. `attachment` is deliberately absent —
	// Media is a screen this admin keeps.
	$unused = array( 'post', 'page' );
	$type   = '';

	if ( 'edit.php' === $pagenow || 'post-new.php' === $pagenow ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : 'post';
	} elseif ( 'post.php' === $pagenow ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$type = isset( $_GET['post'] ) ? (string) get_post_type( (int) $_GET['post'] ) : '';
	} elseif ( in_array( $pagenow, array( 'edit-comments.php', 'comment.php', 'themes.php', 'theme-editor.php', 'site-editor.php', 'customize.php', 'edit-tags.php' ), true ) ) {
		// edit-tags.php is shared with the Insight Categories screen, so it is
		// blocked only for the built-in taxonomies.
		if ( 'edit-tags.php' === $pagenow ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$tax = isset( $_GET['taxonomy'] ) ? sanitize_key( wp_unslash( $_GET['taxonomy'] ) ) : '';
			if ( ! in_array( $tax, array( 'category', 'post_tag', 'link_category' ), true ) ) {
				return;
			}
		}
		wp_safe_redirect( admin_url() );
		exit;
	}

	if ( $type && in_array( $type, $unused, true ) ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
}
add_action( 'admin_init', 'jbnewgen_block_unused_screens' );

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
		// Both of these are printed on all_admin_notices and both belong under
		// the heading: the type description, and the read-only banner a Viewer
		// gets (inc/roles.php). Moved in the order they should end up in.
		var moved = document.querySelectorAll( '.jb-readonly-note, .jb-screen-desc' );
		if ( ! moved.length ) { return; }

		var wrap = document.querySelector( '#wpbody-content .wrap' );
		if ( ! wrap ) { return; }

		// List screens mark the end of the heading row with <hr class="wp-header-end">;
		// edit screens just have the <h1>.
		var anchor = wrap.querySelector( '.wp-header-end' ) || wrap.querySelector( 'h1' );
		if ( ! anchor ) { return; }

		Array.prototype.forEach.call( moved, function ( node ) {
			anchor.parentNode.insertBefore( node, anchor.nextSibling );
			anchor = node;
		} );
	}() );
	</script>
	<?php
} );

/**
 * The media search box says what it is.
 *
 * Core ships the label as a real <label> and the field with no placeholder,
 * and the label is hidden here because it wrapped to two lines beside the
 * input. That left an unlabelled empty box floating at the right-hand end of
 * the toolbar — the one control on the screen you had to click to find out
 * what it did. The label stays in the markup for screen readers; the
 * placeholder is for everyone else.
 */
add_action( 'admin_footer', function () {
	$screen = get_current_screen();
	if ( ! $screen || 'upload' !== $screen->base ) {
		return;
	}

	// The mode is a user option, not a query arg, so it has to be resolved in
	// PHP the same way wp-admin/upload.php resolves it.
	$mode = get_user_option( 'media_library_mode', get_current_user_id() );
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$mode = isset( $_GET['mode'] ) ? sanitize_key( wp_unslash( $_GET['mode'] ) ) : ( $mode ? $mode : 'grid' );
	?>
	<script>
	( function () {
		if ( <?php echo wp_json_encode( 'grid' === $mode ); ?> ) {
			document.body.classList.add( 'jb-media-grid' );
		}

		/*
		 * Grid mode has no `.tablenav.top`, so the data-bar script has nowhere
		 * to move the Add button to and it stayed stranded above the panel —
		 * the one screen where it is not level with the controls it belongs
		 * with. The grid's own toolbar is that row; it is built by Backbone
		 * after load, which is why this is retried rather than run once.
		 */
		function seatAddButton() {
			if ( ! document.body.classList.contains( 'jb-media-grid' ) ) { return true; }

			var action = document.querySelector( '.wrap > .page-title-action' );
			var bar    = document.querySelector( '.media-toolbar.wp-filter .media-toolbar-secondary' );
			if ( ! action || ! bar ) { return false; }

			action.classList.add( 'jb-inline-action' );
			bar.insertBefore( action, bar.firstChild );
			document.body.classList.add( 'jb-action-moved' );
			return true;
		}

		function label() {
			// Grid mode only. In list mode the search is the data bar's
			// expanding magnifier, which is collapsed until you click it — a
			// placeholder there prints the words underneath the glyph.
			if ( ! document.body.classList.contains( 'jb-media-grid' ) ) { return; }
			var field = document.getElementById( 'media-search-input' );
			if ( field && ! field.placeholder ) {
				field.placeholder = <?php echo wp_json_encode( __( 'Search media', 'jbnewgen' ) ); ?>;
			}
		}
		// The grid view builds its toolbar from Backbone after load, so what is
		// on screen at first paint is not what ends up there.
		var tries = 0;
		( function settle() {
			label();
			if ( seatAddButton() || ++tries > 20 ) { return; }
			window.setTimeout( settle, 150 );
		}() );
	}() );
	</script>
	<?php
} );

/**
 * A heading with nothing under it is not a section.
 *
 * The profile screen's rows are stripped in CSS (see PROFILE, PROPERLY in
 * assets/admin.css) because core prints them inline with no filter between
 * them. That leaves the <h2> above each group behind, and an empty
 * `.form-table` under it drawing one hairline — "About Yourself" became a
 * heading and a horizontal rule with no content at all.
 *
 * CSS cannot express "a table whose every row is hidden": `:has(tr)` still
 * matches a table full of `display: none` rows, and there is no selector for
 * computed visibility. Six lines of script can ask the question directly, and
 * it stays correct if a row is added back later.
 */
add_action( 'admin_footer', function () {
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->base, array( 'profile', 'user-edit' ), true ) ) {
		return;
	}
	?>
	<script>
	( function () {
		var form = document.getElementById( 'your-profile' );
		if ( ! form ) { return; }

		Array.prototype.forEach.call( form.children, function ( node ) {
			if ( node.tagName !== 'TABLE' ) { return; }

			var alive = Array.prototype.some.call( node.querySelectorAll( 'tr' ), function ( row ) {
				return row.offsetParent !== null;
			} );
			if ( alive ) { return; }

			node.style.display = 'none';
			var heading = node.previousElementSibling;
			if ( heading && heading.tagName === 'H2' ) { heading.style.display = 'none'; }
		} );
	}() );
	</script>
	<?php
}, 97 );

/**
 * A read-only screen whose fields still accept typing is a screen that lies.
 *
 * The capability rules in inc/roles.php refuse the write, and the CSS hides
 * Update and Trash — but the inputs themselves still took a cursor, so a
 * Viewer could rewrite a whole page and only find out at the end. `readonly`
 * rather than `disabled`: a disabled field cannot be selected or copied, and
 * reading and copying is the entire purpose of the role.
 *
 * Selects and checkboxes have no readonly attribute in HTML, so those are
 * disabled — there is nothing in a closed dropdown to copy.
 */
add_action( 'admin_footer', function () {
	if ( ! function_exists( 'jbnewgen_viewer_is_read_only' ) || ! jbnewgen_viewer_is_read_only() ) {
		return;
	}
	?>
	<script>
	( function () {
		var scope = document.getElementById( 'wpbody-content' );
		if ( ! scope ) { return; }

		Array.prototype.forEach.call(
			scope.querySelectorAll( 'input:not([type=hidden]), textarea' ),
			function ( field ) {
				if ( field.type === 'checkbox' || field.type === 'radio' || field.type === 'file' ) {
					field.disabled = true;
				} else if ( field.type !== 'submit' && field.type !== 'button' ) {
					field.readOnly = true;
				}
			}
		);
		Array.prototype.forEach.call( scope.querySelectorAll( 'select' ), function ( field ) {
			field.disabled = true;
		} );
	}() );
	</script>
	<?php
}, 99 );

/**
 * WordPress's own marketing widget on the dashboard ("WordPress Events and
 * News") is branding, not content — the same reason the logo and footer text
 * are stripped. The rest of the default widgets are left alone.
 */
add_action( 'wp_dashboard_setup', function () {
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
}, 999 );

/**
 * The admin bar is removed outright.
 *
 * In wp-admin the bar is printed by wp_admin_bar_render() on in_admin_header;
 * unhooking that is a real removal, not a `display:none` that leaves the
 * markup, its scripts and its hover handlers behind. Everything worth keeping
 * off it — dashboard, visit site, add new, profile — now lives in the rail,
 * which is why the popovers that kept mis-rendering inside a flexed toolbar
 * no longer exist to mis-render.
 */
add_action( 'init', function () {
	remove_action( 'in_admin_header', 'wp_admin_bar_render', 0 );
	add_filter( 'show_admin_bar', '__return_false' );
} );

/**
 * The fixed rail: a 48px strip down the far left that the sidebar cannot take
 * with it when it collapses.
 *
 *   top     toggle, dashboard, visit site
 *   bottom  profile then log out — the two account actions kept together and
 *           away from navigation, so neither is ever a mis-click for the other
 *
 * "Add new" is deliberately NOT here: it is an action you take on content, not
 * a place, so it lives on the Dashboard beside the page title.
 *
 * Printed into the footer rather than into #adminmenu because the rail is
 * position:fixed — nested inside the menu it would inherit the menu's width
 * and be clipped to nothing the moment the sidebar folded.
 */
function jbnewgen_admin_rail() {
	$user = wp_get_current_user();

	// WordPress has no local avatar upload — get_avatar() always resolves to
	// Gravatar, which returns its generic silhouette for any address without
	// an account (and nothing at all offline). The initial is rendered
	// underneath and the image is dropped on error, so the fallback is the
	// user's own letter rather than a stranger's placeholder.
	//
	// `default => 404` asked Gravatar to answer 404 for an address with no
	// account, which is what the onerror below listened for — but a 404 is a
	// 404, and every admin page logged one to the console. `blank` returns a
	// transparent 1x1 with a 200 instead: the initial underneath shows
	// straight through it, and a real gravatar still covers it.
	$initial   = strtoupper( mb_substr( $user->display_name ? $user->display_name : $user->user_login, 0, 1 ) );
	$avatar_url = get_avatar_url( $user->ID, array( 'size' => 64, 'default' => 'blank' ) );
	?>
	<div id="jb-rail" role="navigation" aria-label="<?php esc_attr_e( 'Primary', 'jbnewgen' ); ?>">

		<button type="button" id="jb-toggle" class="jb-rail__btn"
			aria-label="<?php esc_attr_e( 'Toggle menu', 'jbnewgen' ); ?>"
			title="<?php esc_attr_e( 'Toggle menu', 'jbnewgen' ); ?>" aria-expanded="true">
			<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
				<rect x="2.5" y="3.5" width="15" height="13" rx="2.5" />
				<line x1="8" y1="3.5" x2="8" y2="16.5" />
			</svg>
		</button>

		<a href="<?php echo esc_url( admin_url( 'index.php' ) ); ?>" id="jb-dashboard" class="jb-rail__btn"
			aria-label="<?php esc_attr_e( 'Dashboard', 'jbnewgen' ); ?>"
			title="<?php esc_attr_e( 'Dashboard', 'jbnewgen' ); ?>">
			<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
				<path d="M3 8.5 10 3l7 5.5V16a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 3 16z" />
			</svg>
		</a>

		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" id="jb-visit" class="jb-rail__btn" target="_blank" rel="noopener"
			aria-label="<?php esc_attr_e( 'Visit site', 'jbnewgen' ); ?>"
			title="<?php esc_attr_e( 'Visit site', 'jbnewgen' ); ?>">
			<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
				<circle cx="10" cy="10" r="7.5" />
				<ellipse cx="10" cy="10" rx="3.2" ry="7.5" />
				<line x1="2.5" y1="10" x2="17.5" y2="10" />
			</svg>
		</a>

		<?php
		/*
		 * Report is on the rail for everybody, permanently, and that placement
		 * is the point: a feedback channel you have to go looking for is a
		 * feedback channel nobody uses. It sits with navigation rather than
		 * with the account actions at the foot because it is a place you go,
		 * not something you do to your own account.
		 */
		?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=jbnewgen-report' ) ); ?>" id="jb-report" class="jb-rail__btn"
			aria-label="<?php esc_attr_e( 'Report a problem', 'jbnewgen' ); ?>"
			title="<?php esc_attr_e( 'Report a problem', 'jbnewgen' ); ?>">
			<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
				<path d="M3 5.5A1.5 1.5 0 0 1 4.5 4h11A1.5 1.5 0 0 1 17 5.5v7a1.5 1.5 0 0 1-1.5 1.5H8l-4 3v-3H4.5A1.5 1.5 0 0 1 3 12.5z" />
				<line x1="10" y1="6.75" x2="10" y2="9.75" />
				<line x1="10" y1="11.4" x2="10" y2="11.5" />
			</svg>
		</a>

		<?php
		// Issues is the developer's queue and nobody else's business — the
		// capability is checked here as well as on the screen, so the icon is
		// absent rather than present-and-refused.
		if ( current_user_can( 'jb_view_issues' ) ) :
			$jb_open = function_exists( 'jbnewgen_report_open_count' ) ? jbnewgen_report_open_count() : 0;
			?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=jbnewgen-issues' ) ); ?>" id="jb-issues"
				class="jb-rail__btn<?php echo $jb_open ? ' has-count' : ''; ?>"
				aria-label="<?php esc_attr_e( 'Issues', 'jbnewgen' ); ?>"
				title="<?php esc_attr_e( 'Issues', 'jbnewgen' ); ?>">
				<?php
				/*
				 * An inbox, not a list.
				 *
				 * The rail is six glyphs in a 48px strip and every one has to
				 * be told apart at 18px. The first attempt was a bordered box
				 * with lines in it, which is the shape of a list — the shape
				 * Services, Insights, Media and every other list screen in
				 * this admin already wears — so as the icon for "the queue of
				 * things that are wrong" it read as a duplicate. The second
				 * was a flag, and a pennant's notch closes up at this size
				 * into an oval.
				 *
				 * An inbox tray is wide and bottom-heavy, which is a
				 * silhouette nothing else in the rail has, and its shoulders
				 * slope away from the top-right corner — where the count
				 * badge sits.
				 */
				?>
				<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
					<path d="M3.2 11.4 6.1 3.6h7.8l2.9 7.8" />
					<path d="M3.2 11.4h3.6l1 2.1h4.4l1-2.1h3.6v3.5a1.6 1.6 0 0 1-1.6 1.6H4.8a1.6 1.6 0 0 1-1.6-1.6z" />
				</svg>
				<?php if ( $jb_open ) : ?>
					<span class="jb-rail__count" aria-hidden="true"><?php echo esc_html( (string) min( 99, $jb_open ) ); ?></span>
					<span class="screen-reader-text">
						<?php
						printf(
							/* translators: %s: number of open reports */
							esc_html( _n( '%s report still open', '%s reports still open', $jb_open, 'jbnewgen' ) ),
							esc_html( number_format_i18n( $jb_open ) )
						);
						?>
					</span>
				<?php endif; ?>
			</a>
		<?php endif; ?>

		<a href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>" id="jb-profile" class="jb-rail__btn jb-rail__btn--avatar"
			aria-label="<?php esc_attr_e( 'Profile', 'jbnewgen' ); ?>"
			title="<?php echo esc_attr( $user->display_name ); ?>">
			<span class="jb-avatar" aria-hidden="true">
				<span class="jb-avatar__initial"><?php echo esc_html( $initial ); ?></span>
				<?php if ( $avatar_url ) : ?>
					<img class="jb-avatar__img" src="<?php echo esc_url( $avatar_url ); ?>" alt=""
						onerror="this.remove();" />
				<?php endif; ?>
			</span>
		</a>

		<a href="<?php echo esc_url( wp_logout_url() ); ?>" id="jb-logout" class="jb-rail__btn"
			aria-label="<?php esc_attr_e( 'Log out', 'jbnewgen' ); ?>"
			title="<?php esc_attr_e( 'Log out', 'jbnewgen' ); ?>">
			<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
				<path d="M12.5 3.5h3a1.5 1.5 0 0 1 1.5 1.5v10a1.5 1.5 0 0 1-1.5 1.5h-3" />
				<line x1="3" y1="10" x2="11.5" y2="10" />
				<polyline points="8.5,6.5 12,10 8.5,13.5" />
			</svg>
		</a>
	</div>

	<script>
	( function () {
		var toggle = document.getElementById( 'jb-toggle' );
		var real   = document.getElementById( 'collapse-button' );
		var wrap   = document.getElementById( 'wpwrap' );

		if ( toggle ) {
			var sync = function () {
				toggle.setAttribute( 'aria-expanded',
					document.body.classList.contains( 'folded' ) ? 'false' : 'true' );
			};
			sync();

			toggle.addEventListener( 'click', function ( event ) {
				event.preventDefault();

				// Below 783px WordPress opens the menu as an overlay via
				// `wp-responsive-open` on #wpwrap — not on <body>, its own rule
				// is a descendant selector — rather than via the folded class.
				if ( window.matchMedia( '(max-width: 782px)' ).matches && wrap ) {
					var open = wrap.classList.toggle( 'wp-responsive-open' );
					document.body.classList.toggle( 'jb-drawer-open', open );
					return;
				}

				if ( real ) { real.click(); }
				window.setTimeout( sync, 50 );
			} );
		}

		// On a list screen the "Add X" button ships beside the page title.
		// It belongs with the controls that act on the list, so it is moved
		// into the top tablenav row, level with search. Moving the node keeps
		// core's own href, nonce and classes intact — restyling a copy would
		// not.
		// Everything that acts on the list is collected into one row — the
		// data bar. WordPress scatters those controls across three places:
		// the Add button beside the page title, the status links and search
		// box above the table, and a duplicate bulk-action row below it.
		//
		// The nodes are MOVED, not rebuilt. Each one carries its own name,
		// nonce and form association; a restyled copy would submit nothing.
		var nav = document.querySelector( '.tablenav.top' );
		if ( nav ) {
			var action = document.querySelector( '.wrap > .page-title-action' );
			if ( action ) {
				action.classList.add( 'jb-inline-action' );
				nav.insertBefore( action, nav.firstChild );
				document.body.classList.add( 'jb-action-moved' );
			}

			// Order inside the bar is set in CSS, so these can be appended in
			// any sequence — what matters is that they end up in the same
			// flex container.
			// Which form the box belonged to has to be read BEFORE the move.
			// On taxonomy screens the search sits in its own GET form while
			// the nav row lives inside the POST bulk form, so re-parenting
			// silently re-associates the field with the wrong one.
			var search     = document.querySelector( '.search-box' );
			var searchForm = search ? search.closest( 'form' ) : null;
			if ( search ) { nav.appendChild( search ); }

			var sub = document.querySelector( '.subsubsub' );
			if ( sub ) { nav.appendChild( sub ); }

			// Media in list mode is the one screen that keeps its filters
			// somewhere else: core prints the view switch, "All media items"
			// and "All dates" into a `.wp-filter` bar ABOVE the table, and
			// leaves Add and search in the nav row below it — two boxes, one
			// job, with a seam between them. Moved in, it becomes the same
			// single data bar every other list screen has.
			var filterBar = document.querySelector( '.wp-filter' );
			if ( filterBar && document.body.classList.contains( 'upload-php' ) ) {
				nav.insertBefore( filterBar, nav.firstChild );
				document.body.classList.add( 'jb-media-databar' );
			}

			document.body.classList.add( 'jb-databar' );

			// ---------------------------------------------------------------
			// Selection-aware bulk actions.
			//
			// WordPress asks you to pick an action from a dropdown and then
			// press Apply — two steps, and the dropdown is the only place the
			// available actions are written down. This drives that same
			// select + submit pair from plain buttons that only appear once
			// something is ticked, so the bar is empty of them while you read.
			//
			// The native controls are hidden, never removed: they carry the
			// form's nonce and the field names the request needs.
			// ---------------------------------------------------------------
			var select = nav.querySelector( 'select[name="action"]' );
			var doBtn  = nav.querySelector( '#doaction' );
			var table  = document.querySelector( '.wp-list-table' );

			if ( select && doBtn && table ) {
				var picker = document.createElement( 'div' );
				picker.className = 'jb-sel';
				picker.innerHTML =
					'<span class="jb-sel__count"></span>' +
					'<span class="jb-sel__actions"></span>' +
					'<button type="button" class="jb-sel__clear">Clear</button>';
				nav.appendChild( picker );

				var actions = picker.querySelector( '.jb-sel__actions' );
				var countEl = picker.querySelector( '.jb-sel__count' );

				// One button per real option in the native select, so adding a
				// bulk action anywhere else still shows up here.
				Array.prototype.forEach.call( select.options, function ( opt ) {
					if ( opt.value === '-1' ) { return; }
					var b = document.createElement( 'button' );
					b.type = 'button';
					b.className = 'jb-sel__btn' + ( opt.value === 'trash' ? ' jb-sel__btn--danger' : '' );
					// Core labels the inline-edit action "Bulk edit", so stripping
					// the prefix leaves a lowercase word sitting among Title Case
					// siblings. Re-capitalise whatever the first letter ends up
					// being rather than special-casing that one string.
					var label = opt.text.replace( /^Bulk /, '' );
					b.textContent = label.charAt( 0 ).toUpperCase() + label.slice( 1 );
					b.addEventListener( 'click', function () {
						if ( opt.value === 'trash' &&
							! window.confirm( 'Move the selected items to Trash?' ) ) { return; }
						select.value = opt.value;
						doBtn.click();
					} );
					actions.appendChild( b );
				} );

				var boxes = function () {
					return table.querySelectorAll( 'tbody input[type="checkbox"]' );
				};

				function refresh() {
					var n = 0;
					Array.prototype.forEach.call( boxes(), function ( b ) { if ( b.checked ) { n++; } } );
					countEl.textContent = n + ( n === 1 ? ' item selected' : ' items selected' );
					document.body.classList.toggle( 'jb-has-selection', n > 0 );
				}

				table.addEventListener( 'change', function ( e ) {
					if ( e.target && e.target.type === 'checkbox' ) { refresh(); }
				} );
				// the header "select all" lives outside tbody
				document.addEventListener( 'change', function ( e ) {
					if ( e.target && e.target.id && e.target.id.indexOf( 'cb-select-all' ) === 0 ) {
						window.setTimeout( refresh, 0 );
					}
				} );

				picker.querySelector( '.jb-sel__clear' ).addEventListener( 'click', function () {
					Array.prototype.forEach.call(
						document.querySelectorAll( '.wp-list-table input[type="checkbox"]' ),
						function ( b ) { b.checked = false; }
					);
					refresh();
				} );

				refresh();
			}

			// The status links become the filter. The date dropdown and its
			// Filter button go: nobody narrows four services by month, but
			// "what isn't live yet" is asked constantly.
			var dateFilter = nav.querySelector( '#filter-by-date' );
			if ( dateFilter && dateFilter.closest( '.actions' ) ) {
				dateFilter.closest( '.actions' ).classList.add( 'jb-date-filter' );
			}

			// ---------------------------------------------------------------
			// Search: the magnifier IS the button.
			//
			// Two faults, one cause. A second submit button faded in 400ms
			// after you started typing — a control that arrives on a timer is
			// a control you cannot aim at. And pressing Enter raised "Please
			// select a bulk action to perform", because moving .search-box
			// into the nav row put it AFTER #doaction in tree order: #doaction
			// became the form's default submit button, so a plain search ran
			// core's bulk-action guard in wp-admin/js/common.js.
			//
			// Both go together. The native submit is dropped, the magnifier
			// becomes a real button, and the form is submitted through
			// HTMLFormElement.prototype.submit — a scripted submit fires no
			// submit event at all, so the guard never sees it. Called off the
			// prototype rather than as form.submit() because a field named
			// "submit" shadows the method on its own form.
			// ---------------------------------------------------------------
			if ( search && searchForm ) {
				var field  = search.querySelector( 'input[type="search"]' );
				var native = search.querySelector( 'input[type="submit"]' );
				if ( native ) { native.remove(); }

				if ( field ) {
					// It has left its original form; say so explicitly rather
					// than relying on where it happens to sit now.
					if ( ! searchForm.id ) { searchForm.id = 'jb-search-form'; }
					field.setAttribute( 'form', searchForm.id );

					var go = document.createElement( 'button' );
					go.type = 'button';
					go.className = 'jb-search__go';
					go.setAttribute( 'aria-label', field.getAttribute( 'placeholder' ) || 'Search' );
					search.insertBefore( go, search.firstChild );

					var run = function () {
						// A search is not a bulk action; leave the select at
						// its no-op value so nothing is carried along.
						if ( select ) { select.value = '-1'; }

						// An empty box is not a search for nothing, it is the
						// list. Submitting `s=` asks the screen to match the
						// empty string, which list tables answer with "no items
						// found"; dropping the parameter asks for everything
						// back instead. `paged` goes with it — the cursor
						// belonged to the result set being left behind.
						if ( ! field.value.trim() ) {
							var url = new URL( window.location.href );
							if ( ! url.searchParams.has( 's' ) ) {
								// Nothing was filtered and nothing was typed:
								// there is no navigation to make.
								search.classList.remove( 'is-open' );
								field.blur();
								return;
							}
							url.searchParams.delete( 's' );
							url.searchParams.delete( 'paged' );
							window.location.href = url.toString();
							return;
						}

						HTMLFormElement.prototype.submit.call( searchForm );
					};

					// Closed, the button opens the field. Open, it searches.
					go.addEventListener( 'click', function ( event ) {
						event.preventDefault();
						if ( ! search.classList.contains( 'is-open' ) ) {
							search.classList.add( 'is-open' );
							field.focus();
							return;
						}
						run();
					} );

					field.addEventListener( 'keydown', function ( event ) {
						if ( 'Enter' === event.key ) {
							event.preventDefault();
							run();
						}
					} );

					field.addEventListener( 'focus', function () { search.classList.add( 'is-open' ); } );
					field.addEventListener( 'blur', function () {
						if ( ! field.value ) { search.classList.remove( 'is-open' ); }
					} );

					// A search that is already running stays open and readable.
					if ( field.value ) { search.classList.add( 'is-open' ); }
				}
			}
		}
	}() );
	</script>
	<?php
}
add_action( 'admin_footer', 'jbnewgen_admin_rail' );

/**
 * "Add new" on the Dashboard, level with the page title.
 *
 * Rendered only on index.php. The flyout opens on click rather than hover:
 * hover menus are a mouse trap, and click is what touch needs anyway.
 * The list is built from the same source WordPress's own "+ New" used —
 * registered post types this user may actually create.
 */
function jbnewgen_dashboard_new_button() {
	$screen = get_current_screen();
	if ( ! $screen || 'dashboard' !== $screen->id ) {
		return;
	}

	$types = array();
	foreach ( get_post_types( array( 'show_ui' => true ), 'objects' ) as $type ) {
		if ( 'attachment' === $type->name ) {
			continue;
		}
		if ( ! current_user_can( $type->cap->create_posts ) ) {
			continue;
		}
		$types[] = $type;
	}
	if ( ! $types ) {
		return;
	}
	?>
	<div id="jb-newwrap" class="jb-newwrap" hidden>
		<button type="button" id="jb-new" class="button jb-new__btn"
			aria-expanded="false" aria-controls="jb-new-menu">
			<span class="jb-new__plus" aria-hidden="true"></span>
			<?php esc_html_e( 'Add new', 'jbnewgen' ); ?>
		</button>
		<div class="jb-rail__menu" id="jb-new-menu" hidden>
			<?php foreach ( $types as $type ) : ?>
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . $type->name ) ); ?>">
					<?php echo esc_html( $type->labels->singular_name ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
	<script>
	( function () {
		var wrapEl = document.getElementById( 'jb-newwrap' );
		var h1     = document.querySelector( '.wrap h1' );
		if ( ! wrapEl || ! h1 ) { return; }

		// Sit it on the title's own line, hard right. Done by moving the node
		// next to the heading rather than by absolutely positioning it, so it
		// cannot drift out of alignment when the title wraps.
		// Into the header row built by inc/admin-breadcrumb.php — a flex line
		// bottom-aligned to the path box. Absolute positioning was tried first
		// and could not hold the button level with the path: the box it was
		// measured against is the whole screen, not the header, so "bottom"
		// meant the bottom of the page.
		var head = document.querySelector( '.jb-head' );
		if ( head ) {
			head.appendChild( wrapEl );
		} else {
			h1.parentNode.insertBefore( wrapEl, h1.nextSibling );
		}
		document.body.classList.add( 'jb-has-newbtn' );
		wrapEl.hidden = false;

		var btn  = document.getElementById( 'jb-new' );
		var menu = document.getElementById( 'jb-new-menu' );
		if ( ! btn || ! menu ) { return; }

		var close = function () {
			menu.hidden = true;
			btn.setAttribute( 'aria-expanded', 'false' );
		};

		btn.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			event.stopPropagation();
			var open = menu.hidden;
			menu.hidden = ! open;
			btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );

		document.addEventListener( 'click', function ( event ) {
			if ( ! menu.hidden && ! wrapEl.contains( event.target ) ) { close(); }
		} );
		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key ) { close(); }
		} );
	}() );
	</script>
	<?php
}
add_action( 'admin_footer', 'jbnewgen_dashboard_new_button' );
