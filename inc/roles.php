<?php
/**
 * Roles and capabilities.
 *
 * Five built-in roles, plus a builder so Joyjeet can create his own without
 * touching code. Every custom post type declares its own capability_type (see
 * inc/post-types.php), which is what makes "may edit Job Openings but not
 * Services" expressible at all — with the shared `post` capabilities WordPress
 * gives types by default, a role can only ever be all-content or no-content.
 *
 * Roles are stored by WordPress in the `wp_user_roles` option, so they persist
 * in the database rather than being rebuilt per request. That has one
 * consequence worth knowing: changing the arrays below does nothing until the
 * roles are re-applied. jbnewgen_roles_maybe_install() handles that by version
 * stamp, so a deploy that edits this file re-syncs on the next admin request.
 */

const JBNEWGEN_ROLES_VERSION = 9;
const JBNEWGEN_CUSTOM_ROLES_OPTION = 'jbnewgen_custom_roles';

/**
 * Developer is not a rung on the access ladder — it is a second hat.
 *
 * Everyone in this CMS has exactly one of the five roles above, which answers
 * "how much of the site may this person change". Developer answers a different
 * question — "does this person maintain the CMS itself" — and the two are
 * independent: Joyjeet may well want to be Owner and Developer at once so he
 * sees the reports as well as the content.
 *
 * WordPress supports more than one role per user (add_role/remove_role rather
 * than set_role), and stacking is exactly what that is for. So Developer is
 * held ALONGSIDE the content role, never instead of it, and it carries no
 * content capability of its own — dropping it can never take away somebody's
 * ability to do their actual job.
 */
const JBNEWGEN_DEVELOPER_ROLE = 'jb_developer';

/**
 * The content types roles are expressed over, and the plural used to build
 * their capability names.
 *
 * @return array<string,array{label:string,plural:string}>
 */
function jbnewgen_role_content_types() {
	return array(
		'pillar'      => array( 'label' => __( 'Service Categories', 'jbnewgen' ), 'plural' => 'pillars' ),
		'service'     => array( 'label' => __( 'Services', 'jbnewgen' ),           'plural' => 'services' ),
		'insight'     => array( 'label' => __( 'Insights', 'jbnewgen' ),           'plural' => 'insights' ),
		'team_member' => array( 'label' => __( 'Core Team', 'jbnewgen' ),          'plural' => 'team_members' ),
		'job'         => array( 'label' => __( 'Job Openings', 'jbnewgen' ),       'plural' => 'jobs' ),
	);
}

/**
 * The four things a role can be allowed to do to a content type. These map
 * onto WordPress's capability names, which are not symmetrical — "create"
 * needs the edit caps too, or the editor screen refuses to load.
 *
 * @return array<string,string>
 */
function jbnewgen_role_actions() {
	return array(
		'create'  => __( 'Create', 'jbnewgen' ),
		'edit'    => __( 'Edit', 'jbnewgen' ),
		'publish' => __( 'Publish', 'jbnewgen' ),
		'delete'  => __( 'Delete', 'jbnewgen' ),
	);
}

/**
 * Expand one action on one content type into the capabilities it actually
 * requires.
 *
 * @param string $action create|edit|publish|delete
 * @param string $plural e.g. "services"
 * @return string[]
 */
function jbnewgen_caps_for( $action, $plural ) {
	switch ( $action ) {
		case 'create':
			// create_* alone is not enough: WordPress checks edit_* when it
			// renders post-new.php, so a create-only role would see a menu
			// entry and then be told it may not edit this item.
			return array( 'create_' . $plural, 'edit_' . $plural );

		case 'edit':
			return array( 'edit_' . $plural, 'edit_published_' . $plural );

		case 'publish':
			return array( 'publish_' . $plural, 'edit_published_' . $plural );

		case 'delete':
			return array( 'delete_' . $plural, 'delete_published_' . $plural );
	}
	return array();
}

/**
 * Capabilities that let a role act on *other people's* entries. Held apart
 * from the per-action list because it is the difference between Author and
 * Editor, not a per-type choice.
 *
 * @param string $plural
 * @return string[]
 */
function jbnewgen_others_caps( $plural ) {
	return array(
		'edit_others_' . $plural,
		'delete_others_' . $plural,
		'read_private_' . $plural,
		'edit_private_' . $plural,
		'delete_private_' . $plural,
	);
}

/**
 * Every capability this theme's content types define, granted.
 *
 * @param bool $others include other-people's-content caps
 * @return array<string,bool>
 */
function jbnewgen_all_content_caps( $others = true ) {
	$caps = array();
	foreach ( jbnewgen_role_content_types() as $type ) {
		$plural = $type['plural'];
		foreach ( array_keys( jbnewgen_role_actions() ) as $action ) {
			foreach ( jbnewgen_caps_for( $action, $plural ) as $cap ) {
				$caps[ $cap ] = true;
			}
		}
		if ( $others ) {
			foreach ( jbnewgen_others_caps( $plural ) as $cap ) {
				$caps[ $cap ] = true;
			}
		}
	}
	return $caps;
}

/**
 * The five built-in roles, in order of increasing access.
 *
 * @return array<string,array{name:string,description:string,caps:array<string,bool>}>
 */
function jbnewgen_builtin_roles() {
	/*
	 * `edit_posts` is in the base set, and it is not what it looks like.
	 *
	 * It does not grant access to any of this site's content — every content
	 * type declares its own capability_type (inc/post-types.php), so Services
	 * are governed by `edit_services`, Insights by `edit_insights`, and so on.
	 * `edit_posts` governs WordPress's built-in blog Posts, which this site
	 * does not use and whose screens jbnewgen_block_unused_screens() closes.
	 *
	 * It has to be here because of how WordPress decides whether you may open
	 * an admin page. Every list screen is `edit.php`, whatever `post_type` is
	 * on the query string, and wp-admin/includes/menu.php runs its access check
	 * BEFORE the screen sets $parent_file — so `user_can_access_admin_page()`
	 * takes the `empty( $parent )` branch and tests
	 * `$_wp_submenu_nopriv['edit.php']['edit.php']`. That entry exists for
	 * anyone who cannot edit built-in Posts. The result: Viewer, Contributor
	 * and Author were refused every list screen in the admin — "Sorry, you are
	 * not allowed to access this page." on Services, Insights, Job Openings,
	 * all of them — while Editor and Owner worked, because they were already
	 * being handed `edit_posts` further down this function.
	 *
	 * Granting it and closing the built-in screens is the fix that keeps the
	 * per-type model intact. A Viewer holding `edit_posts` still cannot edit a
	 * single thing on this site.
	 */
	$base = array(
		'read'       => true,
		'edit_posts' => true,
	);

	/*
	 * Viewer: can open the admin and look, and nothing more.
	 *
	 * It held `read` alone, and that made it useless: every list screen in
	 * WordPress is gated on that type's `edit_posts` capability — wp-admin/edit.php
	 * calls current_user_can( $post_type_object->cap->edit_posts ) before it
	 * renders a single row — so a role with no edit capability was refused
	 * Services, Insights, Job Openings and Core Team alike. The role promised
	 * "read everything" on the Roles screen and delivered a dashboard and five
	 * error pages.
	 *
	 * WordPress has no read-only capability, so the shape of the fix is: hold
	 * the capabilities that OPEN a screen, and refuse the request that WRITES.
	 * The opening half is here; the refusing half is
	 * jbnewgen_viewer_is_read_only() below, and the two must be read together.
	 */
	$viewer = $base;
	foreach ( jbnewgen_role_content_types() as $type ) {
		$plural = $type['plural'];
		$viewer[ 'edit_' . $plural ]           = true;  // opens the list and the editor
		$viewer[ 'edit_others_' . $plural ]    = true;  // …including other people's
		$viewer[ 'edit_published_' . $plural ] = true;  // …including live ones
		$viewer[ 'read_private_' . $plural ]   = true;  // …and the unpublished ones
	}

	// Contributor: own drafts only, cannot publish and cannot delete anything
	// already live.
	$contributor = $base;
	foreach ( jbnewgen_role_content_types() as $type ) {
		$plural = $type['plural'];
		$contributor[ 'create_' . $plural ] = true;
		$contributor[ 'edit_' . $plural ]   = true;
		$contributor[ 'delete_' . $plural ] = true;
	}

	// Author: own content, may publish it, may delete their own published work.
	$author = $contributor;
	foreach ( jbnewgen_role_content_types() as $type ) {
		$plural = $type['plural'];
		$author[ 'publish_' . $plural ]           = true;
		$author[ 'edit_published_' . $plural ]    = true;
		$author[ 'delete_published_' . $plural ]  = true;
	}

	// Editor: everyone's content, plus media and terms — but no users and no
	// settings. This is the role most of the team should have.
	$editor = array_merge( $author, jbnewgen_all_content_caps( true ), array(
		'upload_files'      => true,
		'manage_categories' => true,
		'edit_posts'        => true,
		'edit_pages'        => true,
		'publish_pages'     => true,
		'edit_others_pages' => true,
	) );

	/*
	 * Owner and Administrator are one role wearing two titles.
	 *
	 * WordPress already ships a role called Administrator and the site's own
	 * account holds it, so a second role literally named "Administrator" would
	 * put two identical words in every role dropdown and solve nothing. The
	 * pair is therefore: core's `administrator`, titled Administrator, and
	 * `jb_owner`, titled Owner — with the same power.
	 *
	 * "The same power" has to be true and not approximately true, so Owner is
	 * built from core Administrator's own capability list rather than from a
	 * hand-written set that drifts every time WordPress adds a capability. The
	 * previous version listed seven caps and was missing `install_plugins`,
	 * `update_core`, `export`, `import` and a dozen more — an Owner could not
	 * install the backup plugin they are responsible for.
	 *
	 * The two are protected from each other and from everybody else further
	 * down this file: neither account can be deleted and neither can be demoted.
	 */
	$core_admin = get_role( 'administrator' );
	$owner      = array_merge(
		$editor,
		$core_admin ? array_filter( $core_admin->capabilities ) : array(),
		jbnewgen_all_content_caps( true )
	);

	return array(
		'jb_viewer' => array(
			'name'        => __( 'Viewer', 'jbnewgen' ),
			'description' => __( 'Can sign in and read everything. Cannot change anything.', 'jbnewgen' ),
			'caps'        => $viewer,
		),
		'jb_contributor' => array(
			'name'        => __( 'Contributor', 'jbnewgen' ),
			'description' => __( 'Writes and edits their own entries. Cannot publish — someone else reviews.', 'jbnewgen' ),
			'caps'        => $contributor,
		),
		'jb_author' => array(
			'name'        => __( 'Author', 'jbnewgen' ),
			'description' => __( 'Writes, edits and publishes their own entries. Cannot touch anyone else&#8217;s.', 'jbnewgen' ),
			'caps'        => $author,
		),
		'jb_editor' => array(
			'name'        => __( 'Editor', 'jbnewgen' ),
			'description' => __( 'Full control of all content and media. No access to users or settings.', 'jbnewgen' ),
			'caps'        => $editor,
		),
		'jb_owner' => array(
			'name'        => __( 'Owner', 'jbnewgen' ),
			'description' => __( 'Everything. The same access as an Administrator, under a different title — and, like an Administrator, cannot be deleted or demoted by anyone.', 'jbnewgen' ),
			'caps'        => $owner,
		),
	);
}

/**
 * What the Developer hat carries.
 *
 * Three capabilities and nothing else. `read` so the role is valid on its own
 * for a developer who is not also an editor; the two `issues` caps gate
 * inc/issues.php. Deliberately NOT included: `jb_grant_developer`. A developer
 * who could appoint developers would make the "only an administrator decides
 * this" rule unenforceable within a day.
 *
 * @return array<string,bool>
 */
function jbnewgen_developer_caps() {
	return array(
		'read'             => true,
		'jb_view_issues'   => true,
		'jb_manage_issues' => true,
	);
}

/**
 * Everyone wearing the Developer hat.
 *
 * @return WP_User[]
 */
function jbnewgen_developers() {
	return get_users( array( 'role' => JBNEWGEN_DEVELOPER_ROLE, 'orderby' => 'display_name' ) );
}

/**
 * Is this user a developer?
 *
 * @param int|WP_User|null $user
 */
function jbnewgen_is_developer( $user = null ) {
	$user = $user ? ( $user instanceof WP_User ? $user : get_userdata( $user ) ) : wp_get_current_user();
	return $user && in_array( JBNEWGEN_DEVELOPER_ROLE, (array) $user->roles, true );
}

/**
 * Install or re-sync roles.
 *
 * Runs on admin_init behind a version stamp rather than on activation, because
 * a theme edited in place is never "activated" again — without this, changing
 * the arrays above would silently do nothing.
 */
function jbnewgen_roles_maybe_install() {
	if ( (int) get_option( 'jbnewgen_roles_version' ) === JBNEWGEN_ROLES_VERSION ) {
		return;
	}

	foreach ( jbnewgen_builtin_roles() as $slug => $role ) {
		remove_role( $slug );
		add_role( $slug, $role['name'], $role['caps'] );
	}

	// Custom roles are rebuilt from their stored definition for the same
	// reason: capability names can change when a content type is added.
	foreach ( jbnewgen_get_custom_roles() as $slug => $custom ) {
		remove_role( $slug );
		add_role( $slug, $custom['name'], jbnewgen_custom_role_caps( $custom ) );
	}

	// Developer is installed apart from the ladder because it is not part of
	// it; see JBNEWGEN_DEVELOPER_ROLE.
	remove_role( JBNEWGEN_DEVELOPER_ROLE );
	add_role( JBNEWGEN_DEVELOPER_ROLE, __( 'Developer', 'jbnewgen' ), jbnewgen_developer_caps() );

	// The administrator role predates all of this and has none of the new
	// per-type capabilities, so without this the person doing the migrating
	// loses access to every content type on the site.
	$admin = get_role( 'administrator' );
	if ( $admin ) {
		foreach ( array_keys( jbnewgen_all_content_caps( true ) ) as $cap ) {
			$admin->add_cap( $cap );
		}
		// An administrator hands the Developer hat out, and sees the queue
		// whether or not they wear it — somebody has to be able to check that
		// reports are being answered.
		$admin->add_cap( 'jb_grant_developer' );
		$admin->add_cap( 'jb_view_issues' );
		$admin->add_cap( 'jb_manage_issues' );
	}

	// Owner is this site's administrator by another name, so it appoints too.
	$owner = get_role( 'jb_owner' );
	if ( $owner ) {
		$owner->add_cap( 'jb_grant_developer' );
	}

	update_option( 'jbnewgen_roles_version', JBNEWGEN_ROLES_VERSION );
}
add_action( 'admin_init', 'jbnewgen_roles_maybe_install' );

/**
 * @return array<string,array{name:string,grants:array<string,string[]>,others:bool}>
 */
function jbnewgen_get_custom_roles() {
	$roles = get_option( JBNEWGEN_CUSTOM_ROLES_OPTION, array() );
	return is_array( $roles ) ? $roles : array();
}

/**
 * Turn a stored custom-role definition into a capability map.
 *
 * @param array $custom
 * @return array<string,bool>
 */
function jbnewgen_custom_role_caps( $custom ) {
	$caps  = array( 'read' => true );
	$types = jbnewgen_role_content_types();

	foreach ( (array) ( isset( $custom['grants'] ) ? $custom['grants'] : array() ) as $type_slug => $actions ) {
		if ( ! isset( $types[ $type_slug ] ) ) {
			continue;
		}
		$plural = $types[ $type_slug ]['plural'];

		foreach ( (array) $actions as $action ) {
			foreach ( jbnewgen_caps_for( $action, $plural ) as $cap ) {
				$caps[ $cap ] = true;
			}
		}
		if ( ! empty( $custom['others'] ) && $actions ) {
			foreach ( jbnewgen_others_caps( $plural ) as $cap ) {
				$caps[ $cap ] = true;
			}
		}
	}

	if ( ! empty( $custom['upload'] ) ) {
		$caps['upload_files'] = true;
	}

	// "May appoint developers" is a custom role's one administrative power.
	// It is the answer to a real need — the administrator should not be the
	// only person who can hand the hat over — and it is held apart from the
	// content grants above because it is the only thing on this screen that
	// changes who can change the CMS itself.
	if ( ! empty( $custom['grant_developer'] ) ) {
		$caps['jb_grant_developer'] = true;
		$caps['list_users']         = true;
	}

	return $caps;
}

/* -------------------------------------------------------------------------
   The two top roles protect each other
   ------------------------------------------------------------------------- */

/**
 * The pair that cannot be removed.
 *
 * @return string[]
 */
function jbnewgen_protected_roles() {
	return array( 'administrator', 'jb_owner' );
}

/**
 * Does this account hold one of them?
 *
 * @param int|WP_User $user
 */
function jbnewgen_is_protected_user( $user ) {
	$user = $user instanceof WP_User ? $user : get_userdata( (int) $user );
	if ( ! $user || ! $user->exists() ) {
		return false;
	}
	return (bool) array_intersect( (array) $user->roles, jbnewgen_protected_roles() );
}

/**
 * Neither one can be deleted or demoted — by the other, by themselves, or by
 * anybody a custom role hands `promote_users` to.
 *
 * The rule is symmetrical on purpose. An Administrator who can remove the
 * Owner, or an Owner who can remove the Administrator, is not two equal
 * account holders — it is a race, and whoever clicks first owns the site. It
 * also removes the single most damaging accident available on the Users
 * screen: deleting the only other person who could have let you back in.
 *
 *   delete_user    refused outright. The Delete row action and the bulk
 *                  option are both drawn from this capability, so they
 *                  disappear rather than failing when pressed.
 *   promote_user   refused, which removes the role dropdown from their edit
 *                  screen and blocks the bulk "Change role to…".
 *
 * `edit_user` is deliberately NOT refused: they still need to change their own
 * name, email and password, and an Administrator still needs to be able to
 * reset an Owner's password if they are locked out.
 */
add_filter( 'map_meta_cap', function ( $caps, $cap, $user_id, $args ) {
	if ( ! in_array( $cap, array( 'delete_user', 'promote_user', 'remove_user' ), true ) ) {
		return $caps;
	}

	$target = isset( $args[0] ) ? (int) $args[0] : 0;
	if ( $target && jbnewgen_is_protected_user( $target ) ) {
		return array( 'do_not_allow' );
	}

	return $caps;
}, 10, 4 );

/**
 * The backstop.
 *
 * The capability check above closes the screens, but a role can also be
 * changed from code, from WP-CLI, or from a request that never consults
 * `promote_user`. This runs after the change and puts it back — so the rule
 * holds regardless of the route taken to break it.
 */
add_action( 'set_user_role', function ( $user_id, $role, $old_roles ) {
	$was = array_intersect( (array) $old_roles, jbnewgen_protected_roles() );
	if ( ! $was ) {
		return;
	}
	if ( in_array( $role, jbnewgen_protected_roles(), true ) ) {
		return;   // Administrator <-> Owner is a rename, not a demotion
	}

	$user = get_userdata( $user_id );
	if ( $user ) {
		foreach ( $was as $protected ) {
			$user->add_role( $protected );
		}
	}
}, 10, 3 );

/**
 * Say why the controls are missing, rather than leaving a row that is quietly
 * different from every other row.
 */
add_filter( 'user_row_actions', function ( $actions, $user ) {
	if ( jbnewgen_is_protected_user( $user ) ) {
		$actions['jb_protected'] = '<span class="jb-protected">' . esc_html__( 'Cannot be removed', 'jbnewgen' ) . '</span>';
	}
	return $actions;
}, 10, 2 );

/* -------------------------------------------------------------------------
   Read-only, enforced
   ------------------------------------------------------------------------- */

/**
 * Is this user a Viewer and only a Viewer?
 *
 * Roles stack in this admin (see JBNEWGEN_DEVELOPER_ROLE), so "holds
 * jb_viewer" is not the same as "is read-only" — somebody could hold Viewer
 * and Editor at once, and the more permissive role must win. Read-only means
 * Viewer is the only role carrying content capabilities.
 */
function jbnewgen_viewer_is_read_only( $user = null ) {
	$user = $user ? ( $user instanceof WP_User ? $user : get_userdata( $user ) ) : wp_get_current_user();
	if ( ! $user || ! $user->exists() ) {
		return false;
	}

	$roles = (array) $user->roles;
	if ( ! in_array( 'jb_viewer', $roles, true ) ) {
		return false;
	}

	// Everything except the two hats that carry no content access of their own.
	$content_roles = array_diff( $roles, array( 'jb_viewer', JBNEWGEN_DEVELOPER_ROLE ) );

	return ! $content_roles;
}

/**
 * Refuse every write a Viewer could otherwise make.
 *
 * Two rules, and the split between them is the whole design:
 *
 *   always     create, publish and delete. A Viewer must never see an "Add
 *              Insight" button or a Trash link, and these caps are what render
 *              them, so denying them removes the controls rather than letting
 *              somebody press one and be told no.
 *
 *   on write   `edit_post`. This one cannot be denied outright — WordPress
 *              checks it to OPEN post.php as well as to save from it, and a
 *              Viewer who cannot open an entry cannot read it. So it is
 *              allowed on GET and refused on anything else, which covers the
 *              classic editor's POST to post.php, the block editor's REST
 *              write, and autosave over admin-ajax in one rule.
 *
 * `do_not_allow` rather than an empty array: an empty array means "no
 * capability required", which grants rather than denies.
 */
add_filter( 'map_meta_cap', function ( $caps, $cap, $user_id, $args ) {
	if ( ! jbnewgen_viewer_is_read_only( $user_id ) ) {
		return $caps;
	}

	foreach ( jbnewgen_role_content_types() as $type ) {
		$plural = $type['plural'];
		$blocked = array(
			'create_' . $plural,
			'publish_' . $plural,
			'delete_' . $plural,
			'delete_published_' . $plural,
			'delete_others_' . $plural,
			'delete_private_' . $plural,
		);
		/*
		 * `edit_published_*` and `edit_private_*` are deliberately NOT here.
		 * They read as write capabilities and they are not: map_meta_cap
		 * requires them to resolve `edit_post` for a published or private
		 * entry, so denying them shut the Viewer out of opening any live
		 * content — which is the only thing the role exists to do. The write
		 * is stopped by the request-method rule below instead.
		 */
		if ( in_array( $cap, $blocked, true ) ) {
			return array( 'do_not_allow' );
		}
	}

	if ( in_array( $cap, array( 'delete_post', 'delete_page', 'publish_post' ), true ) ) {
		return array( 'do_not_allow' );
	}

	if ( 'edit_post' === $cap || 'edit_page' === $cap ) {
		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';
		if ( 'GET' !== $method && 'HEAD' !== $method ) {
			return array( 'do_not_allow' );
		}
	}

	return $caps;
}, 10, 4 );

/**
 * Say so on screen, rather than leaving a Save button that silently fails.
 *
 * The capabilities above already remove Publish, Trash and Add New. Update on
 * an existing entry is drawn from `edit_post`, which is allowed while the
 * screen is being read, so that one button survives and has to be taken out
 * here.
 */
add_action( 'admin_body_class', function ( $classes ) {
	return jbnewgen_viewer_is_read_only() ? $classes . ' jb-readonly' : $classes;
} );

add_action( 'all_admin_notices', function () {
	$screen = get_current_screen();
	if ( ! jbnewgen_viewer_is_read_only() || ! $screen || ! in_array( $screen->base, array( 'post', 'edit' ), true ) ) {
		return;
	}
	printf(
		'<p class="jb-readonly-note">%s</p>',
		esc_html__( 'You have read-only access. Everything here is visible and nothing you do can change it.', 'jbnewgen' )
	);
} );

// Autosave would fire a write on a timer and log a failure every 60 seconds.
add_action( 'admin_enqueue_scripts', function () {
	if ( jbnewgen_viewer_is_read_only() ) {
		wp_dequeue_script( 'autosave' );
	}
}, 20 );

/**
 * Developer never appears in a role dropdown.
 *
 * The Users screen changes a role with WP_User::set_role(), which REPLACES
 * every role the person holds. Left in the list, "Change role to → Developer"
 * would quietly strip an editor of all their content access and hand them a
 * hat instead — and it would let anyone with `promote_users` appoint a
 * developer, which is the one thing this is supposed to reserve for an
 * administrator.
 *
 * Removing it from get_editable_roles() closes both: the entry disappears from
 * the bulk dropdown and from the user-edit screen. The Roles screen adds and
 * removes it with add_role()/remove_role(), which do not consult this list, so
 * the intended route is unaffected.
 *
 * The role is still shown in the Users table's Role column — seeing who wears
 * the hat is not the same as being able to hand it out.
 */
add_filter( 'editable_roles', function ( $roles ) {
	unset( $roles[ JBNEWGEN_DEVELOPER_ROLE ] );

	/*
	 * WordPress's own five roles go with it, and for a plainer reason: they
	 * are duplicates that do not work.
	 *
	 * The Roles screen documents five roles. The Users screen was offering
	 * eleven — those five, plus Subscriber, Contributor, Author, Editor and
	 * Administrator — with three of the names appearing twice. And core's
	 * Editor is not this site's Editor: every content type here declares its
	 * own capability_type (see inc/post-types.php), so core's Editor holds
	 * `edit_posts` and not one of `edit_services`, `edit_insights`,
	 * `edit_jobs`… Assigning it hands somebody a role that looks right in the
	 * column and lets them edit nothing.
	 *
	 * Administrator stays. It is the only account that can install a plugin or
	 * recover the site, and it is not a duplicate of anything.
	 */
	foreach ( array( 'subscriber', 'contributor', 'author', 'editor' ) as $stock ) {
		unset( $roles[ $stock ] );
	}

	return $roles;
} );

/* -------------------------------------------------------------------------
   The builder screen
   ------------------------------------------------------------------------- */

/*
 * Registered as a top-level page, not as a child of Users.
 *
 * It was a submenu of users.php, and inc/admin-style.php then promoted a copy
 * of it into the Users & Roles section and dropped the original from the
 * submenu. That broke the screen outright: user_can_access_admin_page() in
 * wp-admin/includes/plugin.php resolves a `?page=` request by walking
 * $submenu[$parent] for a matching slug and returning false when it finds
 * none, so removing the submenu entry removed the only record of what
 * capability the page needs — and every visit, administrator included, was
 * refused. A top-level registration is its own record and needs no promotion.
 */
add_action( 'admin_menu', function () {
	add_menu_page(
		__( 'Roles', 'jbnewgen' ),
		__( 'Roles', 'jbnewgen' ),
		'promote_users',
		'jbnewgen-roles',
		'jbnewgen_roles_screen',
		'',
		71
	);
}, 20 );

/**
 * Create / update / delete custom roles.
 */
function jbnewgen_roles_handle_post() {
	if ( ! isset( $_POST['jbnewgen_roles_nonce'] ) ) {
		return null;
	}
	if ( ! wp_verify_nonce( sanitize_key( $_POST['jbnewgen_roles_nonce'] ), 'jbnewgen_roles' ) ) {
		return new WP_Error( 'nonce', __( 'That form expired. Try again.', 'jbnewgen' ) );
	}
	if ( ! current_user_can( 'promote_users' ) ) {
		return new WP_Error( 'cap', __( 'You are not allowed to manage roles.', 'jbnewgen' ) );
	}

	$custom = jbnewgen_get_custom_roles();

	// --- the Developer hat ----------------------------------------------
	if ( isset( $_POST['developer_user'] ) ) {
		if ( ! current_user_can( 'jb_grant_developer' ) ) {
			return new WP_Error( 'cap', __( 'Only an administrator can appoint developers.', 'jbnewgen' ) );
		}

		$user_id = (int) $_POST['developer_user'];
		$user    = get_userdata( $user_id );
		if ( ! $user ) {
			return new WP_Error( 'missing', __( 'No such person.', 'jbnewgen' ) );
		}

		// add_role/remove_role, never set_role: the hat sits on top of
		// whatever content role they already hold, and taking it off must
		// leave that role exactly where it was.
		if ( ! empty( $_POST['developer_off'] ) ) {
			$user->remove_role( JBNEWGEN_DEVELOPER_ROLE );
			/* translators: %s: person's name */
			return sprintf( __( '%s is no longer a developer. Their other access is unchanged.', 'jbnewgen' ), $user->display_name );
		}

		$user->add_role( JBNEWGEN_DEVELOPER_ROLE );
		/* translators: %s: person's name */
		return sprintf( __( '%s is now a developer and can see Issues.', 'jbnewgen' ), $user->display_name );
	}

	// --- delete ---------------------------------------------------------
	if ( ! empty( $_POST['delete_role'] ) ) {
		$slug = sanitize_key( wp_unslash( $_POST['delete_role'] ) );
		if ( isset( $custom[ $slug ] ) ) {
			// Anyone still holding the role would be left with no role at all,
			// which locks them out of the admin entirely — move them down to
			// Viewer rather than stranding them.
			foreach ( get_users( array( 'role' => $slug ) ) as $user ) {
				$user->remove_role( $slug );
				$user->add_role( 'jb_viewer' );
			}
			unset( $custom[ $slug ] );
			update_option( JBNEWGEN_CUSTOM_ROLES_OPTION, $custom );
			remove_role( $slug );
			return __( 'Role deleted. Anyone who held it is now a Viewer.', 'jbnewgen' );
		}
		return new WP_Error( 'missing', __( 'No such role.', 'jbnewgen' ) );
	}

	// --- create / update ------------------------------------------------
	$name = isset( $_POST['role_name'] ) ? sanitize_text_field( wp_unslash( $_POST['role_name'] ) ) : '';
	if ( '' === trim( $name ) ) {
		return new WP_Error( 'name', __( 'Give the role a name.', 'jbnewgen' ) );
	}

	$slug = isset( $_POST['role_slug'] ) && $_POST['role_slug']
		? sanitize_key( wp_unslash( $_POST['role_slug'] ) )
		: 'jb_' . sanitize_key( $name );

	if ( isset( jbnewgen_builtin_roles()[ $slug ] ) ) {
		return new WP_Error( 'builtin', __( 'That name collides with a built-in role. Pick another.', 'jbnewgen' ) );
	}

	$valid_actions = array_keys( jbnewgen_role_actions() );
	$grants        = array();

	foreach ( jbnewgen_role_content_types() as $type_slug => $type ) {
		$posted = isset( $_POST['grants'][ $type_slug ] ) ? (array) $_POST['grants'][ $type_slug ] : array();
		$clean  = array_values( array_intersect( array_map( 'sanitize_key', $posted ), $valid_actions ) );
		if ( $clean ) {
			$grants[ $type_slug ] = $clean;
		}
	}

	$custom[ $slug ] = array(
		'name'            => $name,
		'grants'          => $grants,
		'others'          => ! empty( $_POST['others'] ),
		'upload'          => ! empty( $_POST['upload'] ),
		'grant_developer' => ! empty( $_POST['grant_developer'] ),
	);
	update_option( JBNEWGEN_CUSTOM_ROLES_OPTION, $custom );

	// Re-add rather than edit: capability sets shrink as well as grow, and
	// add_role() replaces wholesale where add_cap() only ever accumulates.
	remove_role( $slug );
	add_role( $slug, $name, jbnewgen_custom_role_caps( $custom[ $slug ] ) );

	return __( 'Role saved.', 'jbnewgen' );
}

function jbnewgen_roles_screen() {
	$notice = jbnewgen_roles_handle_post();
	$types   = jbnewgen_role_content_types();
	$actions = jbnewgen_role_actions();
	$custom  = jbnewgen_get_custom_roles();

	$editing = isset( $_GET['role'] ) ? sanitize_key( wp_unslash( $_GET['role'] ) ) : '';
	$current = isset( $custom[ $editing ] )
		? wp_parse_args( $custom[ $editing ], array( 'name' => '', 'grants' => array(), 'others' => false, 'upload' => false, 'grant_developer' => false ) )
		: array( 'name' => '', 'grants' => array(), 'others' => false, 'upload' => false, 'grant_developer' => false );

	// How many people hold each role, so deleting one is an informed choice.
	$counts = count_users();
	?>
	<div class="wrap jb-roles">
		<h1><?php esc_html_e( 'Roles', 'jbnewgen' ); ?></h1>

		<?php if ( is_wp_error( $notice ) ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $notice->get_error_message() ); ?></p></div>
		<?php elseif ( $notice ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>

		<div class="jb-panel">
		<h2 class="jb-panel__title"><?php esc_html_e( 'Built-in roles', 'jbnewgen' ); ?></h2>
		<p class="description jb-panel__note"><?php esc_html_e( 'Ordered by how much access each one has. Assign them on the Users screen.', 'jbnewgen' ); ?></p>

		<table class="widefat striped jb-roles__table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Role', 'jbnewgen' ); ?></th>
					<th><?php esc_html_e( 'What it can do', 'jbnewgen' ); ?></th>
					<th><?php esc_html_e( 'People', 'jbnewgen' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			/*
			 * Administrator is WordPress's own role, not one of ours, so it is
			 * not in jbnewgen_builtin_roles() — that array is what gets
			 * installed, and re-installing core's role would be a way to break
			 * the site. It belongs at the top of this table all the same:
			 * leaving it off made the screen claim there are five roles when
			 * the account reading it is holding a sixth.
			 */
			$rows = array(
				'administrator' => array(
					'name'        => __( 'Administrator', 'jbnewgen' ),
					'description' => __( 'Everything. The same access as an Owner, under a different title — and, like an Owner, cannot be deleted or demoted by anyone.', 'jbnewgen' ),
				),
			);
			foreach ( array_reverse( jbnewgen_builtin_roles(), true ) as $slug => $role ) {
				$rows[ $slug ] = $role;
			}
			?>
			<?php foreach ( $rows as $slug => $role ) : ?>
				<tr<?php echo in_array( $slug, jbnewgen_protected_roles(), true ) ? ' class="jb-roles__row--locked"' : ''; ?>>
					<td>
						<strong><?php echo esc_html( $role['name'] ); ?></strong>
						<?php if ( in_array( $slug, jbnewgen_protected_roles(), true ) ) : ?>
							<span class="jb-chip jb-chip--locked"><?php esc_html_e( 'Protected', 'jbnewgen' ); ?></span>
						<?php endif; ?>
					</td>
					<td><?php echo wp_kses_post( $role['description'] ); ?></td>
					<td><?php echo isset( $counts['avail_roles'][ $slug ] ) ? (int) $counts['avail_roles'][ $slug ] : 0; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $custom ) : ?>
			<h3 class="jb-panel__sub"><?php esc_html_e( 'Custom roles', 'jbnewgen' ); ?></h3>
			<table class="widefat striped jb-roles__table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Role', 'jbnewgen' ); ?></th>
						<th><?php esc_html_e( 'Grants', 'jbnewgen' ); ?></th>
						<th><?php esc_html_e( 'People', 'jbnewgen' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $custom as $slug => $role ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $role['name'] ); ?></strong></td>
						<td>
							<?php
							$bits = array();
							foreach ( (array) $role['grants'] as $type_slug => $granted ) {
								if ( isset( $types[ $type_slug ] ) && $granted ) {
									$bits[] = $types[ $type_slug ]['label'] . ' (' . implode( ', ', $granted ) . ')';
								}
							}
							echo $bits ? esc_html( implode( ' · ', $bits ) ) : '<em>' . esc_html__( 'nothing yet', 'jbnewgen' ) . '</em>';
							?>
						</td>
						<td><?php echo isset( $counts['avail_roles'][ $slug ] ) ? (int) $counts['avail_roles'][ $slug ] : 0; ?></td>
						<td>
							<a href="<?php echo esc_url( add_query_arg( 'role', $slug ) ); ?>"><?php esc_html_e( 'Edit', 'jbnewgen' ); ?></a>
							<form method="post" style="display:inline">
								<?php wp_nonce_field( 'jbnewgen_roles', 'jbnewgen_roles_nonce' ); ?>
								<input type="hidden" name="delete_role" value="<?php echo esc_attr( $slug ); ?>" />
								<button type="submit" class="button-link jb-roles__delete"
									onclick="return confirm('<?php echo esc_js( __( 'Delete this role? Anyone holding it becomes a Viewer.', 'jbnewgen' ) ); ?>');">
									<?php esc_html_e( 'Delete', 'jbnewgen' ); ?>
								</button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		</div><?php // .jb-panel — the roles that exist ?>

		<div class="jb-panel">
		<h2 class="jb-panel__title"><?php echo $editing ? esc_html__( 'Edit custom role', 'jbnewgen' ) : esc_html__( 'Create a custom role', 'jbnewgen' ); ?></h2>

		<form method="post" class="jb-roles__form">
			<?php wp_nonce_field( 'jbnewgen_roles', 'jbnewgen_roles_nonce' ); ?>
			<input type="hidden" name="role_slug" value="<?php echo esc_attr( $editing ); ?>" />

			<p>
				<label for="jb-role-name"><strong><?php esc_html_e( 'Role name', 'jbnewgen' ); ?></strong></label><br />
				<input type="text" id="jb-role-name" name="role_name" class="regular-text"
					value="<?php echo esc_attr( $current['name'] ); ?>"
					placeholder="<?php esc_attr_e( 'e.g. Recruiter', 'jbnewgen' ); ?>" required />
			</p>

			<table class="widefat striped jb-roles__matrix">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Content type', 'jbnewgen' ); ?></th>
						<?php foreach ( $actions as $label ) : ?>
							<th><?php echo esc_html( $label ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $types as $type_slug => $type ) : ?>
					<?php $granted = isset( $current['grants'][ $type_slug ] ) ? (array) $current['grants'][ $type_slug ] : array(); ?>
					<tr>
						<td><strong><?php echo esc_html( $type['label'] ); ?></strong></td>
						<?php foreach ( $actions as $action => $label ) : ?>
							<td>
								<label class="screen-reader-text" for="jb-<?php echo esc_attr( $type_slug . '-' . $action ); ?>">
									<?php echo esc_html( $label . ' ' . $type['label'] ); ?>
								</label>
								<input type="checkbox"
									id="jb-<?php echo esc_attr( $type_slug . '-' . $action ); ?>"
									name="grants[<?php echo esc_attr( $type_slug ); ?>][]"
									value="<?php echo esc_attr( $action ); ?>"
									<?php checked( in_array( $action, $granted, true ) ); ?> />
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<p>
				<label>
					<input type="checkbox" name="others" value="1" <?php checked( ! empty( $current['others'] ) ); ?> />
					<?php esc_html_e( 'May also act on entries created by other people', 'jbnewgen' ); ?>
				</label><br />
				<span class="description"><?php esc_html_e( 'Leave unticked and the role only ever sees and edits its own work.', 'jbnewgen' ); ?></span>
			</p>

			<p>
				<label>
					<input type="checkbox" name="upload" value="1" <?php checked( ! empty( $current['upload'] ) ); ?> />
					<?php esc_html_e( 'May upload images and files', 'jbnewgen' ); ?>
				</label>
			</p>

			<?php
			// Offered only to someone who holds the power themselves. A role
			// builder that can mint a role more powerful than its author is a
			// privilege-escalation route, not a feature.
			if ( current_user_can( 'jb_grant_developer' ) ) :
				?>
				<p>
					<label>
						<input type="checkbox" name="grant_developer" value="1" <?php checked( ! empty( $current['grant_developer'] ) ); ?> />
						<?php esc_html_e( 'May appoint and dismiss developers', 'jbnewgen' ); ?>
					</label><br />
					<span class="description"><?php esc_html_e( 'Lets this role hand out the Developer hat on this screen. It does not make the role a developer, and it grants no access to content it does not already have.', 'jbnewgen' ); ?></span>
				</p>
			<?php endif; ?>

			<p>
				<button type="submit" class="button button-primary">
					<?php echo $editing ? esc_html__( 'Save role', 'jbnewgen' ) : esc_html__( 'Create role', 'jbnewgen' ); ?>
				</button>
				<?php if ( $editing ) : ?>
					<a class="button" href="<?php echo esc_url( remove_query_arg( 'role' ) ); ?>"><?php esc_html_e( 'Cancel', 'jbnewgen' ); ?></a>
				<?php endif; ?>
			</p>
		</form>
		</div><?php // .jb-panel — the builder ?>

		<?php jbnewgen_roles_developer_panel(); ?>
	</div>
	<?php
}

/**
 * Who wears the Developer hat.
 *
 * A third panel rather than a column on the Users screen, because it is not
 * the same question. Users answers "who can sign in and what may they change";
 * this answers "who maintains the CMS", and the two lists are different
 * lengths and change at different times.
 *
 * Rendered only for someone who may actually change it. Showing a read-only
 * list of developers to everyone else would be a list of people to go and
 * bother directly, which is the thing the Report screen exists to replace.
 */
function jbnewgen_roles_developer_panel() {
	if ( ! current_user_can( 'jb_grant_developer' ) ) {
		return;
	}

	$developers = jbnewgen_developers();
	$dev_ids    = wp_list_pluck( $developers, 'ID' );

	// Everyone who could be one. Capped because this screen is a list, not a
	// search — a CMS with more people than this needs a different control.
	$candidates = get_users( array(
		'orderby' => 'display_name',
		'number'  => 100,
		'exclude' => $dev_ids ? $dev_ids : array( 0 ),
	) );
	?>
	<div class="jb-panel">
		<h2 class="jb-panel__title"><?php esc_html_e( 'Developers', 'jbnewgen' ); ?></h2>
		<p class="description jb-panel__note">
			<?php esc_html_e( 'A developer sees Issues — every report sent from the Report screen, with the technical detail attached. It sits on top of whatever role they already have, so somebody can be an Owner and a developer at once, and taking it away never touches their content access.', 'jbnewgen' ); ?>
		</p>

		<?php if ( ! $developers ) : ?>
			<p class="jb-empty">
				<?php esc_html_e( 'Nobody yet. Until somebody wears this hat, reports are emailed to the site admin address instead.', 'jbnewgen' ); ?>
			</p>
		<?php else : ?>
			<table class="widefat striped jb-roles__table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Person', 'jbnewgen' ); ?></th>
						<th><?php esc_html_e( 'Their other roles', 'jbnewgen' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $developers as $developer ) : ?>
						<?php
						$other = array();
						foreach ( (array) $developer->roles as $role_slug ) {
							if ( JBNEWGEN_DEVELOPER_ROLE === $role_slug ) {
								continue;
							}
							$role_object = get_role( $role_slug );
							$other[]     = $role_object ? translate_user_role( wp_roles()->roles[ $role_slug ]['name'] ) : $role_slug;
						}
						?>
						<tr>
							<td>
								<strong><?php echo esc_html( $developer->display_name ); ?></strong><br />
								<span class="description"><?php echo esc_html( $developer->user_email ); ?></span>
							</td>
							<td>
								<?php echo $other ? esc_html( implode( ', ', $other ) ) : '<em>' . esc_html__( 'none', 'jbnewgen' ) . '</em>'; ?>
							</td>
							<td>
								<form method="post" style="display:inline">
									<?php wp_nonce_field( 'jbnewgen_roles', 'jbnewgen_roles_nonce' ); ?>
									<input type="hidden" name="developer_user" value="<?php echo esc_attr( (string) $developer->ID ); ?>" />
									<input type="hidden" name="developer_off" value="1" />
									<button type="submit" class="button-link jb-roles__delete"
										onclick="return confirm('<?php echo esc_js( __( 'Take the Developer hat off this person? Their other access is unchanged.', 'jbnewgen' ) ); ?>');">
										<?php esc_html_e( 'Remove', 'jbnewgen' ); ?>
									</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<?php if ( $candidates ) : ?>
			<form method="post" class="jb-roles__adddev">
				<?php wp_nonce_field( 'jbnewgen_roles', 'jbnewgen_roles_nonce' ); ?>
				<label class="jb-field__label" for="jb-developer-user"><?php esc_html_e( 'Make somebody a developer', 'jbnewgen' ); ?></label>
				<span class="jb-roles__adddev-row">
					<select id="jb-developer-user" name="developer_user">
						<?php foreach ( $candidates as $candidate ) : ?>
							<option value="<?php echo esc_attr( (string) $candidate->ID ); ?>">
								<?php echo esc_html( $candidate->display_name . ' — ' . $candidate->user_email ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<button type="submit" class="button"><?php esc_html_e( 'Add', 'jbnewgen' ); ?></button>
				</span>
			</form>
		<?php endif; ?>
	</div>
	<?php
}
