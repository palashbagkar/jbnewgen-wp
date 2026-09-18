<?php
/**
 * The path box under the page title.
 *
 *   dashboard/
 *   services/ services
 *   services/ service-categories
 *   careers/ core-team/ add-team-member
 *
 * Up to three segments, always lowercase and hyphenated:
 *
 *   1  the sidebar section  — services, careers, insights, site
 *   2  the screen within it — services, service-categories, job-openings, …
 *   3  the action, if any   — add-team-member, edit-service
 *
 * Section and screen repeat when a type is named after its section
 * ("services/ services") — that repetition is intentional and reads as a path,
 * so it is not collapsed.
 *
 * A single segment carries a trailing slash ("dashboard/"); multi-segment
 * paths do not. The slash is a separator, and one is what marks a lone segment
 * as a path rather than a word.
 *
 * Segment text is derived from each type's own registered labels through
 * sanitize_title(), not from a table of strings — "Core Team" becomes
 * "core-team" and "Add Team Member" becomes "add-team-member" on its own, so
 * renaming a type in inc/post-types.php renames it here too.
 */

/**
 * Which sidebar section a screen belongs to. Mirrors the grouping built in
 * inc/admin-style.php — add a section there, add it here.
 *
 * @return array<string,string> object slug => section slug
 */
function jbnewgen_path_sections() {
	return array(
		// post types
		'pillar'      => 'services',
		'service'     => 'services',
		'job'         => 'careers',
		'team_member' => 'careers',
		'insight'     => 'insights',
		// taxonomies
		'insight_category' => 'insights',
	);
}

/**
 * The path segments for the current screen, already slugified.
 *
 * @return string[] one to three segments, or [] when the screen is unknown
 */
function jbnewgen_path_segments() {
	$screen = get_current_screen();
	if ( ! $screen ) {
		return array();
	}

	if ( 'dashboard' === $screen->base ) {
		return array( 'dashboard' );
	}

	$sections = jbnewgen_path_sections();

	// --- taxonomy screens -------------------------------------------------
	if ( ! empty( $screen->taxonomy ) && isset( $sections[ $screen->taxonomy ] ) ) {
		$tax      = get_taxonomy( $screen->taxonomy );
		$segments = array(
			$sections[ $screen->taxonomy ],
			sanitize_title( $tax ? $tax->labels->name : $screen->taxonomy ),
		);

		// The add-a-term mode is its own page (see inc/taxonomy-add-screen.php),
		// so it earns a third segment like every other add screen.
		if ( $tax && ! empty( $_GET['jb_add'] ) ) {
			$segments[] = sanitize_title( $tax->labels->add_new_item );
		}

		return $segments;
	}

	// --- post type screens ------------------------------------------------
	if ( ! empty( $screen->post_type ) && isset( $sections[ $screen->post_type ] ) ) {
		$object  = get_post_type_object( $screen->post_type );
		$section = $sections[ $screen->post_type ];

		if ( ! $object ) {
			return array( $section, sanitize_title( $screen->post_type ) );
		}

		$segments = array( $section, sanitize_title( $object->labels->name ) );

		// A third segment only when you are doing something to an entry
		// rather than looking at the list of them.
		if ( 'add' === $screen->action ) {
			$segments[] = sanitize_title( $object->labels->add_new_item );
		} elseif ( 'post' === $screen->base ) {
			$segments[] = sanitize_title( $object->labels->edit_item );
		}

		return $segments;
	}

	// --- who has access ---------------------------------------------------

	// Users and Roles are their own section in the sidebar, so the path says
	// so. Profile is neither — it is the signed-in person's own account,
	// reached from the rail rather than from the menu, and a path that read
	// "users-roles/ profile" would claim it sits somewhere it does not.
	$access = array(
		'users'     => 'users',
		'user'      => 'users',
		'user-edit' => 'users',
	);

	if ( isset( $access[ $screen->base ] ) ) {
		return array( 'users-roles', $access[ $screen->base ] );
	}

	if ( 'profile' === $screen->base ) {
		return array( 'profile' );
	}

	// --- everything else lives under site ---------------------------------
	$site = array(
		'upload'        => 'media',
		'media'         => 'media',
		'plugins'       => 'plugins',
		'tools'         => 'tools',
	);

	if ( isset( $site[ $screen->base ] ) ) {
		return array( 'site', $site[ $screen->base ] );
	}

	// Carbon Fields options pages carry their container id in the screen id.
	$options = array(
		'container_homepage'      => 'homepage',
		'container_about'         => 'about-pages',
		'container_site_settings' => 'site-settings',
	);
	foreach ( $options as $needle => $slug ) {
		if ( false !== strpos( $screen->id, $needle ) ) {
			return array( 'site', $slug );
		}
	}

	if ( false !== strpos( $screen->id, 'jbnewgen-roles' ) ) {
		return array( 'users-roles', 'roles' );
	}

	if ( false !== strpos( $screen->id, 'jbnewgen-report' ) ) {
		return array( 'support', 'report' );
	}

	if ( false !== strpos( $screen->id, 'jbnewgen-issues' ) ) {
		// The detail view is a third segment for the same reason an edit
		// screen earns one: you are looking at a thing, not at the list.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return empty( $_GET['report'] )
			? array( 'support', 'issues' )
			: array( 'support', 'issues', 'report-' . (int) $_GET['report'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	return array();
}

/**
 * Render the path box into the footer, then move it under the page title.
 *
 * Moved with JavaScript because WordPress fires no hook between the <h1> it
 * prints and the screen content that follows — there is nowhere to hook.
 * Printing here and relocating leaves core's heading markup untouched, which
 * matters because that h1 is still the accessible page heading.
 */
add_action( 'admin_footer', function () {
	$segments = jbnewgen_path_segments();
	if ( ! $segments ) {
		return;
	}

	$last = count( $segments ) - 1;
	?>
	<nav class="jb-path" id="jb-path" aria-label="<?php esc_attr_e( 'Breadcrumb', 'jbnewgen' ); ?>" hidden>
		<?php foreach ( $segments as $i => $segment ) : ?>
			<span class="jb-path__seg<?php echo $i === $last ? ' jb-path__seg--current' : ''; ?>"
				<?php echo $i === $last ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $segment ); ?></span>
			<?php
			// Separator after every segment but the last — except when there
			// is only one, which keeps its trailing slash so "dashboard/"
			// still reads as a path.
			if ( $i < $last || 0 === $last ) :
				?>
				<span class="jb-path__sep">/</span>
			<?php endif; ?>
		<?php endforeach; ?>
	</nav>
	<script>
	( function () {
		var path = document.getElementById( 'jb-path' );
		var wrap = document.querySelector( '.wrap' );
		if ( ! path || ! wrap ) { return; }

		// Carbon Fields prints its options-page heading as an <h2>, not an <h1>
		// — it is the only screen family that does, and the path box was
		// silently skipping all three of them because of it. Fall back to a
		// direct-child <h2> rather than any <h2>, so a section heading deeper
		// in the screen is never mistaken for the page title.
		var h1 = wrap.querySelector( 'h1' ) || wrap.querySelector( ':scope > h2' );
		if ( ! h1 ) { return; }

		// The title and the path become one block, and that block plus the
		// Add-new button become one flex row. Absolute positioning was tried
		// first and could not hold the button level with the path: the box it
		// was measured against is the whole screen, not the header, so
		// "bottom" meant the bottom of the page.
		var head  = document.createElement( 'div' );
		var stack = document.createElement( 'div' );
		head.className  = 'jb-head';
		stack.className = 'jb-head__title';

		h1.parentNode.insertBefore( head, h1 );
		stack.appendChild( h1 );
		stack.appendChild( path );
		head.appendChild( stack );

		path.hidden = false;
		document.body.classList.add( 'jb-has-path' );
	}() );
	</script>
	<?php
}, 5 );
