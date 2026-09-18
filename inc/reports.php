<?php
/**
 * Reports — the channel from the people using this CMS to the person who
 * maintains it.
 *
 * The problem this solves: JB NewGen's team will hit things that are broken,
 * confusing or missing, and with nowhere to put that, it goes into a WhatsApp
 * message, or nowhere at all. Neither reaches the developer with enough
 * context to act on, and neither leaves a record anyone can look back at.
 *
 * Two screens, one store:
 *
 *   Report   inc/reports.php   everyone. A form. One per problem.
 *   Issues   inc/issues.php    developers only. The queue, with the metadata
 *                              needed to decide what to fix first.
 *
 * Reports are a private post type. `show_ui` is false because these screens
 * are hand-written — a report is not a piece of site content and should not
 * appear anywhere near one, and the list-table chrome (bulk edit, quick edit,
 * Trash) is wrong for it.
 *
 * Nothing here is ever rendered on the public site.
 */

const JBNEWGEN_REPORT_TYPE = 'jb_report';

/* -------------------------------------------------------------------------
   Vocabulary
   ------------------------------------------------------------------------- */

/**
 * What kind of thing is being reported.
 *
 * Three, not seven. A longer list makes the reporter stop and classify instead
 * of describe, and the developer can re-file it in one click on the Issues
 * screen if it lands in the wrong bucket.
 *
 * @return array<string,array{label:string,hint:string}>
 */
function jbnewgen_report_kinds() {
	return array(
		'problem' => array(
			'label' => __( 'Something is broken', 'jbnewgen' ),
			'hint'  => __( 'It errors, it does nothing, or it does the wrong thing.', 'jbnewgen' ),
		),
		'confusing' => array(
			'label' => __( 'Something is confusing', 'jbnewgen' ),
			'hint'  => __( 'It works, but it was not clear how, or it was easy to get wrong.', 'jbnewgen' ),
		),
		'idea' => array(
			'label' => __( 'An idea or a request', 'jbnewgen' ),
			'hint'  => __( 'Something missing that would make the job easier.', 'jbnewgen' ),
		),
	);
}

/**
 * How much it is costing the reporter right now.
 *
 * Phrased as consequence rather than as a severity number. "Critical / major /
 * minor" asks a non-technical person to guess at engineering vocabulary and
 * they will guess high; "I cannot do my work" is a question about their own
 * day, which they can answer accurately.
 *
 * The weight is what the Issues screen sorts on.
 *
 * @return array<string,array{label:string,short:string,weight:int}>
 */
function jbnewgen_report_impacts() {
	return array(
		'blocked' => array(
			'label'  => __( 'I cannot do my work at all', 'jbnewgen' ),
			'short'  => __( 'Blocked', 'jbnewgen' ),
			'weight' => 3,
		),
		'slowed' => array(
			'label'  => __( 'I found a way round it, but it cost me time', 'jbnewgen' ),
			'short'  => __( 'Slowed', 'jbnewgen' ),
			'weight' => 2,
		),
		'annoying' => array(
			'label'  => __( 'Nothing is stopping me — it just should be better', 'jbnewgen' ),
			'short'  => __( 'Minor', 'jbnewgen' ),
			'weight' => 1,
		),
	);
}

/**
 * Where in the admin it happened.
 *
 * Built from the sections the sidebar already uses, so the reporter picks the
 * name they can see on screen rather than a slug. Pre-selected from the page
 * they came from, which they can correct.
 *
 * @return array<string,string>
 */
function jbnewgen_report_areas() {
	$areas = array(
		'dashboard' => __( 'Dashboard', 'jbnewgen' ),
	);

	foreach ( jbnewgen_role_content_types() as $slug => $type ) {
		$areas[ $slug ] = $type['label'];
	}

	$areas['media']     = __( 'Media', 'jbnewgen' );
	$areas['pages']     = __( 'Homepage / About / Site Settings', 'jbnewgen' );
	$areas['users']     = __( 'Users & Roles', 'jbnewgen' );
	$areas['profile']   = __( 'Profile', 'jbnewgen' );
	$areas['website']   = __( 'The public website', 'jbnewgen' );
	$areas['elsewhere'] = __( 'Somewhere else / not sure', 'jbnewgen' );

	return $areas;
}

/**
 * The lifecycle a report moves through, in order.
 *
 * @return array<string,string>
 */
function jbnewgen_report_statuses() {
	return array(
		'new'      => __( 'New', 'jbnewgen' ),
		'accepted' => __( 'Accepted', 'jbnewgen' ),
		'working'  => __( 'In progress', 'jbnewgen' ),
		'fixed'    => __( 'Fixed', 'jbnewgen' ),
		'declined' => __( 'Not doing', 'jbnewgen' ),
	);
}

/** Statuses that still need somebody. */
function jbnewgen_report_open_statuses() {
	return array( 'new', 'accepted', 'working' );
}

/* -------------------------------------------------------------------------
   Storage
   ------------------------------------------------------------------------- */

add_action( 'init', function () {
	register_post_type( JBNEWGEN_REPORT_TYPE, array(
		'labels'          => array(
			'name'          => __( 'Reports', 'jbnewgen' ),
			'singular_name' => __( 'Report', 'jbnewgen' ),
		),
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_ui'             => false,
		'show_in_menu'        => false,
		'show_in_rest'        => false,
		'has_archive'         => false,
		'rewrite'             => false,
		'query_var'           => false,
		'supports'            => array( 'title', 'editor', 'author' ),
		'map_meta_cap'        => true,
		'capability_type'     => array( 'jb_report', 'jb_reports' ),
	) );
} );

/**
 * Which screen the reporter was on, as best as can be told from the referring
 * URL. A guess, offered as the pre-selected option — never stored without the
 * reporter having seen it.
 *
 * @param string $referer
 * @return string one of jbnewgen_report_areas() keys
 */
function jbnewgen_report_guess_area( $referer ) {
	$referer = (string) $referer;

	if ( '' === $referer ) {
		return 'elsewhere';
	}

	$query = (string) wp_parse_url( $referer, PHP_URL_QUERY );
	$path  = (string) wp_parse_url( $referer, PHP_URL_PATH );
	parse_str( $query, $args );

	if ( ! empty( $args['post_type'] ) && isset( jbnewgen_report_areas()[ $args['post_type'] ] ) ) {
		return $args['post_type'];
	}

	// An edit screen names the post, not the type — look the type up.
	if ( ! empty( $args['post'] ) ) {
		$type = get_post_type( (int) $args['post'] );
		if ( $type && isset( jbnewgen_report_areas()[ $type ] ) ) {
			return $type;
		}
	}

	if ( false !== strpos( $path, 'upload.php' ) || false !== strpos( $path, 'media' ) ) {
		return 'media';
	}
	if ( false !== strpos( $path, 'users.php' ) || false !== strpos( $path, 'user-' ) ) {
		return 'users';
	}
	if ( false !== strpos( $path, 'profile.php' ) ) {
		return 'profile';
	}
	if ( false !== strpos( $query, 'crb_carbon_fields_container' ) ) {
		return 'pages';
	}
	if ( false !== strpos( $path, 'index.php' ) || rtrim( $path, '/' ) === '/wp-admin' ) {
		return 'dashboard';
	}
	if ( false === strpos( $path, '/wp-admin' ) ) {
		return 'website';
	}

	return 'elsewhere';
}

/**
 * The environment attached to every report.
 *
 * This is the half of a bug report that non-technical people cannot be
 * expected to produce and that the developer cannot work without. It is
 * gathered automatically and shown to the reporter before they send — nothing
 * is collected that they cannot see on the form.
 *
 * Deliberately not collected: IP address and anything resembling one. It would
 * add nothing to a five-person internal CMS and it is personal data.
 *
 * @param array $posted the browser-side values the form carried
 * @return array<string,string>
 */
function jbnewgen_report_environment( $posted = array() ) {
	$user  = wp_get_current_user();
	$theme = wp_get_theme();

	$env = array(
		'page'     => isset( $posted['page_url'] ) ? esc_url_raw( $posted['page_url'] ) : '',
		'screen'   => isset( $posted['screen'] ) ? sanitize_text_field( $posted['screen'] ) : '',
		'viewport' => isset( $posted['viewport'] ) ? sanitize_text_field( $posted['viewport'] ) : '',
		'browser'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
		'roles'    => implode( ', ', (array) $user->roles ),
		'locale'   => get_user_locale( $user->ID ),
		'wp'       => get_bloginfo( 'version' ),
		'theme'    => $theme ? $theme->get( 'Version' ) : '',
		'php'      => PHP_VERSION,
	);

	return array_filter( $env, static function ( $value ) {
		return '' !== $value;
	} );
}

/**
 * Human labels for the environment keys, so the Issues screen and the
 * disclosure on the form read the same.
 *
 * @return array<string,string>
 */
function jbnewgen_report_environment_labels() {
	return array(
		'page'     => __( 'Page', 'jbnewgen' ),
		'screen'   => __( 'Screen', 'jbnewgen' ),
		'viewport' => __( 'Window size', 'jbnewgen' ),
		'browser'  => __( 'Browser', 'jbnewgen' ),
		'roles'    => __( 'Their roles', 'jbnewgen' ),
		'locale'   => __( 'Language', 'jbnewgen' ),
		'wp'       => __( 'WordPress', 'jbnewgen' ),
		'theme'    => __( 'Theme', 'jbnewgen' ),
		'php'      => __( 'PHP', 'jbnewgen' ),
	);
}

/* -------------------------------------------------------------------------
   Reading reports back
   ------------------------------------------------------------------------- */

/**
 * One report, flattened into the shape both screens want.
 *
 * @param WP_Post|int $post
 * @return array<string,mixed>|null
 */
function jbnewgen_report_get( $post ) {
	$post = get_post( $post );
	if ( ! $post || JBNEWGEN_REPORT_TYPE !== $post->post_type ) {
		return null;
	}

	$kinds   = jbnewgen_report_kinds();
	$impacts = jbnewgen_report_impacts();
	$areas   = jbnewgen_report_areas();

	$kind   = (string) get_post_meta( $post->ID, '_jb_kind', true );
	$impact = (string) get_post_meta( $post->ID, '_jb_impact', true );
	$area   = (string) get_post_meta( $post->ID, '_jb_area', true );
	$status = (string) get_post_meta( $post->ID, '_jb_status', true );

	if ( ! isset( jbnewgen_report_statuses()[ $status ] ) ) {
		$status = 'new';
	}

	return array(
		'id'        => $post->ID,
		'subject'   => $post->post_title,
		'details'   => $post->post_content,
		'steps'     => (string) get_post_meta( $post->ID, '_jb_steps', true ),
		'expected'  => (string) get_post_meta( $post->ID, '_jb_expected', true ),
		'kind'      => $kind,
		'kind_label'   => isset( $kinds[ $kind ] ) ? $kinds[ $kind ]['label'] : $kind,
		'impact'       => $impact,
		'impact_label' => isset( $impacts[ $impact ] ) ? $impacts[ $impact ]['short'] : $impact,
		'impact_weight' => isset( $impacts[ $impact ] ) ? $impacts[ $impact ]['weight'] : 0,
		'area'        => $area,
		'area_label'  => isset( $areas[ $area ] ) ? $areas[ $area ] : $area,
		'status'      => $status,
		'status_label' => jbnewgen_report_statuses()[ $status ],
		'author'      => (int) $post->post_author,
		'author_name' => get_the_author_meta( 'display_name', $post->post_author ),
		'created'     => get_post_time( 'U', true, $post ),
		'updated'     => get_post_modified_time( 'U', true, $post ),
		// get_post_meta() answers '' for a key that was never written, and
		// (array) '' is array( '' ) — one empty string, not an empty array. So
		// a report with no notes looked like a report with one, and the
		// renderer indexed into a string. Cast only what is already an array.
		'environment' => is_array( $env = get_post_meta( $post->ID, '_jb_env', true ) ) ? $env : array(),
		// array_filter( …, 'is_array' ) as well as the is_array() guard: rows
		// written before that guard existed carry a leading empty string, and
		// this screen has to keep rendering for them.
		'notes'       => array_values( array_filter(
			is_array( $notes = get_post_meta( $post->ID, '_jb_notes', true ) ) ? $notes : array(),
			'is_array'
		) ),
	);
}

/**
 * Query reports.
 *
 * @param array $args author, status[], kind, impact, area, limit
 * @return array<int,array<string,mixed>>
 */
function jbnewgen_report_query( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'author' => 0,
		'status' => array(),
		'kind'   => '',
		'impact' => '',
		'area'   => '',
		'limit'  => 200,
	) );

	$meta = array();

	if ( $args['status'] ) {
		$meta[] = array(
			'key'     => '_jb_status',
			'value'   => (array) $args['status'],
			'compare' => 'IN',
		);
	}
	foreach ( array( 'kind' => '_jb_kind', 'impact' => '_jb_impact', 'area' => '_jb_area' ) as $arg => $key ) {
		if ( $args[ $arg ] ) {
			$meta[] = array( 'key' => $key, 'value' => $args[ $arg ] );
		}
	}

	$query = array(
		'post_type'        => JBNEWGEN_REPORT_TYPE,
		'post_status'      => 'private',
		'numberposts'      => (int) $args['limit'],
		'orderby'          => 'date',
		'order'            => 'DESC',
		'suppress_filters' => false,
	);

	if ( $args['author'] ) {
		$query['author'] = (int) $args['author'];
	}
	if ( $meta ) {
		$meta['relation']   = 'AND';
		$query['meta_query'] = $meta; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	}

	$out = array();
	foreach ( get_posts( $query ) as $post ) {
		$report = jbnewgen_report_get( $post );
		if ( $report ) {
			$out[] = $report;
		}
	}

	return $out;
}

/**
 * How many open reports there are, for the badge on the rail.
 *
 * @return int
 */
function jbnewgen_report_open_count() {
	static $count = null;

	if ( null === $count ) {
		$count = count( jbnewgen_report_query( array(
			'status' => jbnewgen_report_open_statuses(),
			'limit'  => 500,
		) ) );
	}

	return $count;
}

/* -------------------------------------------------------------------------
   Submitting
   ------------------------------------------------------------------------- */

/**
 * Handle the form.
 *
 * Returns a WP_Error on a rejected submission, or null when nothing was
 * submitted. A success never returns at all — it redirects (see below). The
 * form re-renders with whatever was typed on a rejection: a validation failure
 * that empties the textarea is a way of losing somebody's bug report.
 *
 * @return WP_Error|null
 */
function jbnewgen_report_handle_post() {
	if ( ! isset( $_POST['jbnewgen_report_nonce'] ) ) {
		return null;
	}
	if ( ! wp_verify_nonce( sanitize_key( $_POST['jbnewgen_report_nonce'] ), 'jbnewgen_report' ) ) {
		return new WP_Error( 'nonce', __( 'That form expired before it was sent. Everything you typed is still here — send it again.', 'jbnewgen' ) );
	}
	if ( ! is_user_logged_in() ) {
		return new WP_Error( 'auth', __( 'You need to be signed in to send a report.', 'jbnewgen' ) );
	}

	$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
	$details = isset( $_POST['details'] ) ? sanitize_textarea_field( wp_unslash( $_POST['details'] ) ) : '';

	if ( '' === trim( $subject ) ) {
		return new WP_Error( 'subject', __( 'Give it a one-line summary so it can be told apart from the others.', 'jbnewgen' ) );
	}
	if ( '' === trim( $details ) ) {
		return new WP_Error( 'details', __( 'Describe what happened — even one sentence is enough to work from.', 'jbnewgen' ) );
	}

	$kind   = isset( $_POST['kind'] ) ? sanitize_key( wp_unslash( $_POST['kind'] ) ) : '';
	$impact = isset( $_POST['impact'] ) ? sanitize_key( wp_unslash( $_POST['impact'] ) ) : '';
	$area   = isset( $_POST['area'] ) ? sanitize_key( wp_unslash( $_POST['area'] ) ) : '';

	if ( ! isset( jbnewgen_report_kinds()[ $kind ] ) ) {
		$kind = 'problem';
	}
	if ( ! isset( jbnewgen_report_impacts()[ $impact ] ) ) {
		$impact = 'slowed';
	}
	if ( ! isset( jbnewgen_report_areas()[ $area ] ) ) {
		$area = 'elsewhere';
	}

	// Private, not draft: a draft is something half-written that its author
	// means to come back to. A sent report is finished, and `private` keeps it
	// out of every public query without pretending it is unfinished.
	$id = wp_insert_post( array(
		'post_type'    => JBNEWGEN_REPORT_TYPE,
		'post_status'  => 'private',
		'post_title'   => $subject,
		'post_content' => $details,
		'post_author'  => get_current_user_id(),
	), true );

	if ( is_wp_error( $id ) ) {
		return $id;
	}

	update_post_meta( $id, '_jb_kind', $kind );
	update_post_meta( $id, '_jb_impact', $impact );
	update_post_meta( $id, '_jb_area', $area );
	update_post_meta( $id, '_jb_status', 'new' );
	update_post_meta( $id, '_jb_env', jbnewgen_report_environment( wp_unslash( $_POST ) ) );

	if ( ! empty( $_POST['steps'] ) ) {
		update_post_meta( $id, '_jb_steps', sanitize_textarea_field( wp_unslash( $_POST['steps'] ) ) );
	}
	if ( ! empty( $_POST['expected'] ) ) {
		update_post_meta( $id, '_jb_expected', sanitize_textarea_field( wp_unslash( $_POST['expected'] ) ) );
	}

	jbnewgen_report_notify( $id );

	/*
	 * Post / Redirect / Get.
	 *
	 * Rendering the confirmation straight off the POST left the browser
	 * holding a form submission: reloading the page re-sent it, so a refresh
	 * filed the same report a second time, and the form came back still
	 * carrying everything that had just been sent. Redirecting to a plain GET
	 * ends the submission — refresh reloads the confirmation, and the form
	 * underneath is empty because there is no POST left to repopulate it from.
	 */
	wp_safe_redirect( add_query_arg(
		array( 'page' => 'jbnewgen-report', 'sent' => (int) $id ),
		admin_url( 'admin.php' )
	) );
	exit;
}

/**
 * Tell the developers.
 *
 * Mail goes to everyone holding the Developer role. With nobody holding it the
 * mail would go nowhere and the report would sit unseen, so it falls back to
 * the site admin address — a report that reaches the wrong person is
 * recoverable, one that reaches nobody is not.
 *
 * A failure to send is not a failure to report: the row is already saved and
 * the Issues screen is the system of record. Mail is the nudge, not the
 * channel.
 *
 * @param int $id
 */
function jbnewgen_report_notify( $id ) {
	$report = jbnewgen_report_get( $id );
	if ( ! $report ) {
		return;
	}

	$to = array();
	foreach ( jbnewgen_developers() as $developer ) {
		if ( is_email( $developer->user_email ) ) {
			$to[] = $developer->user_email;
		}
	}
	if ( ! $to ) {
		$to[] = get_option( 'admin_email' );
	}

	$lines = array(
		sprintf( /* translators: 1: reporter, 2: area */ __( '%1$s reported something in %2$s.', 'jbnewgen' ), $report['author_name'], $report['area_label'] ),
		'',
		$report['subject'],
		'',
		$report['details'],
		'',
		sprintf( __( 'Kind: %s', 'jbnewgen' ), $report['kind_label'] ),
		sprintf( __( 'Impact: %s', 'jbnewgen' ), $report['impact_label'] ),
		'',
		admin_url( 'admin.php?page=jbnewgen-issues&report=' . $report['id'] ),
	);

	wp_mail(
		array_unique( $to ),
		sprintf(
			/* translators: 1: impact, 2: subject */
			__( '[%1$s] %2$s', 'jbnewgen' ),
			$report['impact_label'],
			$report['subject']
		),
		implode( "\n", $lines )
	);
}

/* -------------------------------------------------------------------------
   The screen
   ------------------------------------------------------------------------- */

add_action( 'admin_menu', function () {
	add_menu_page(
		__( 'Report', 'jbnewgen' ),
		__( 'Report', 'jbnewgen' ),
		'read',
		'jbnewgen-report',
		'jbnewgen_report_screen',
		'',
		81
	);
}, 20 );

/**
 * Whatever the last submission had to say, held between the handler and the
 * renderer.
 *
 * @param WP_Error|null $set
 * @return WP_Error|null
 */
function jbnewgen_report_notice( $set = false ) {
	static $notice = null;
	if ( false !== $set ) {
		$notice = $set;
	}
	return $notice;
}

/*
 * The form is handled on `load-`, not inside the screen callback.
 *
 * A screen callback runs from admin.php long after the admin header has been
 * printed, so the wp_safe_redirect() that ends a successful submission was
 * being called with the response already on its way — "Cannot modify header
 * information", no redirect, and the browser left holding the POST it was
 * supposed to be freed from. `load-{$hook}` fires before a byte is sent.
 */
add_action( 'load-toplevel_page_jbnewgen-report', function () {
	jbnewgen_report_notice( jbnewgen_report_handle_post() );
} );

function jbnewgen_report_screen() {
	$result = jbnewgen_report_notice();

	$kinds    = jbnewgen_report_kinds();
	$impacts  = jbnewgen_report_impacts();
	$areas    = jbnewgen_report_areas();
	$labels   = jbnewgen_report_environment_labels();
	$error = is_wp_error( $result ) ? $result : null;

	// The confirmation is read back off the redirect, never off the POST.
	// Checked against the current user so a guessed id cannot surface somebody
	// else's report on this screen.
	$sent = null;
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! empty( $_GET['sent'] ) ) {
		$candidate = jbnewgen_report_get( (int) $_GET['sent'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $candidate && (int) $candidate['author'] === get_current_user_id() ) {
			$sent = $candidate;
		}
	}

	// A rejected submission keeps everything that was typed. Nothing here is
	// output raw — every one of these is escaped at the point of use.
	$old = static function ( $key, $fallback = '' ) use ( $error ) {
		if ( ! $error || ! isset( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return $fallback;
		}
		return wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	};

	$referer  = wp_get_referer();
	$guess    = jbnewgen_report_guess_area( $referer );
	$mine     = jbnewgen_report_query( array( 'author' => get_current_user_id(), 'limit' => 20 ) );
	$env      = jbnewgen_report_environment();
	?>
	<div class="wrap jb-report">
		<h1><?php esc_html_e( 'Report', 'jbnewgen' ); ?></h1>

		<?php if ( $sent ) : ?>
			<div class="jb-panel jb-report__sent">
				<h2 class="jb-panel__title"><?php esc_html_e( 'Sent', 'jbnewgen' ); ?></h2>
				<p class="jb-report__sent-line">
					<?php
					printf(
						/* translators: %s: the report's reference number */
						esc_html__( 'Your report is number %s. The developer has been emailed and it is now in their queue.', 'jbnewgen' ),
						'<strong>#' . esc_html( (string) $sent['id'] ) . '</strong>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					);
					?>
				</p>
				<p class="description">
					<?php esc_html_e( 'You can follow it below. Nobody will reply by email — the status on this page is the answer.', 'jbnewgen' ); ?>
				</p>
			</div>
		<?php endif; ?>

		<?php if ( $error ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $error->get_error_message() ); ?></p></div>
		<?php endif; ?>

		<div class="jb-report__cols">

			<form method="post" class="jb-panel jb-report__form">
				<?php wp_nonce_field( 'jbnewgen_report', 'jbnewgen_report_nonce' ); ?>

				<h2 class="jb-panel__title"><?php esc_html_e( 'Tell the developer', 'jbnewgen' ); ?></h2>
				<p class="description jb-panel__note">
					<?php esc_html_e( 'Anything at all — broken, confusing, or missing. There is no wrong report, and nobody is scored on how many they send.', 'jbnewgen' ); ?>
				</p>

				<fieldset class="jb-field">
					<legend class="jb-field__label"><?php esc_html_e( 'What kind of thing is it?', 'jbnewgen' ); ?></legend>
					<div class="jb-choices">
						<?php $chosen_kind = $old( 'kind', 'problem' ); ?>
						<?php foreach ( $kinds as $slug => $kind ) : ?>
							<label class="jb-choice">
								<input type="radio" name="kind" value="<?php echo esc_attr( $slug ); ?>"
									<?php checked( $chosen_kind, $slug ); ?> />
								<span class="jb-choice__body">
									<span class="jb-choice__label"><?php echo esc_html( $kind['label'] ); ?></span>
									<span class="jb-choice__hint"><?php echo esc_html( $kind['hint'] ); ?></span>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
				</fieldset>

				<div class="jb-field">
					<label class="jb-field__label" for="jb-report-area"><?php esc_html_e( 'Where were you?', 'jbnewgen' ); ?></label>
					<select id="jb-report-area" name="area">
						<?php foreach ( $areas as $slug => $label ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>"
								<?php selected( $old( 'area', $guess ), $slug ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="jb-field__hint"><?php esc_html_e( 'Pre-filled from the page you came from. Change it if that is wrong.', 'jbnewgen' ); ?></p>
				</div>

				<div class="jb-field">
					<label class="jb-field__label" for="jb-report-subject"><?php esc_html_e( 'In one line', 'jbnewgen' ); ?></label>
					<input type="text" id="jb-report-subject" name="subject" class="regular-text"
						value="<?php echo esc_attr( $old( 'subject' ) ); ?>"
						placeholder="<?php esc_attr_e( 'e.g. The Save button does nothing on Job Openings', 'jbnewgen' ); ?>"
						maxlength="140" required />
				</div>

				<div class="jb-field">
					<label class="jb-field__label" for="jb-report-details"><?php esc_html_e( 'What happened?', 'jbnewgen' ); ?></label>
					<textarea id="jb-report-details" name="details" rows="6" required
						placeholder="<?php esc_attr_e( 'In your own words. Do not worry about being technical.', 'jbnewgen' ); ?>"><?php echo esc_textarea( $old( 'details' ) ); ?></textarea>
				</div>

				<details class="jb-more">
					<summary><?php esc_html_e( 'Add more, if you have it', 'jbnewgen' ); ?></summary>

					<div class="jb-field">
						<label class="jb-field__label" for="jb-report-steps"><?php esc_html_e( 'How can they make it happen again?', 'jbnewgen' ); ?></label>
						<textarea id="jb-report-steps" name="steps" rows="4"
							placeholder="<?php esc_attr_e( "1. Open Services\n2. Click Add Service\n3. …", 'jbnewgen' ); ?>"><?php echo esc_textarea( $old( 'steps' ) ); ?></textarea>
						<p class="jb-field__hint"><?php esc_html_e( 'The single most useful thing you can add. A problem that can be repeated is usually fixed the same day.', 'jbnewgen' ); ?></p>
					</div>

					<div class="jb-field">
						<label class="jb-field__label" for="jb-report-expected"><?php esc_html_e( 'What did you expect instead?', 'jbnewgen' ); ?></label>
						<textarea id="jb-report-expected" name="expected" rows="3"><?php echo esc_textarea( $old( 'expected' ) ); ?></textarea>
					</div>
				</details>

				<fieldset class="jb-field">
					<legend class="jb-field__label"><?php esc_html_e( 'How much is it costing you?', 'jbnewgen' ); ?></legend>
					<div class="jb-choices jb-choices--tight">
						<?php $chosen_impact = $old( 'impact', 'slowed' ); ?>
						<?php foreach ( $impacts as $slug => $impact ) : ?>
							<label class="jb-choice">
								<input type="radio" name="impact" value="<?php echo esc_attr( $slug ); ?>"
									<?php checked( $chosen_impact, $slug ); ?> />
								<span class="jb-choice__body">
									<span class="jb-choice__label"><?php echo esc_html( $impact['label'] ); ?></span>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
					<p class="jb-field__hint"><?php esc_html_e( 'This is what orders the queue. Answer it honestly rather than politely — under-reporting a blocker is how it stays broken.', 'jbnewgen' ); ?></p>
				</fieldset>

				<details class="jb-more jb-report__env">
					<summary><?php esc_html_e( 'What gets sent with this', 'jbnewgen' ); ?></summary>
					<p class="jb-field__hint">
						<?php esc_html_e( 'Attached automatically so the developer does not have to ask. No IP address is recorded.', 'jbnewgen' ); ?>
					</p>
					<dl class="jb-meta">
						<?php foreach ( $env as $key => $value ) : ?>
							<div class="jb-meta__row">
								<dt><?php echo esc_html( isset( $labels[ $key ] ) ? $labels[ $key ] : $key ); ?></dt>
								<dd><?php echo esc_html( $value ); ?></dd>
							</div>
						<?php endforeach; ?>
					</dl>
				</details>

				<?php
				// Two things only the browser knows. Hidden rather than asked
				// for: nobody can answer "what is your viewport" and nobody
				// should have to.
				?>
				<input type="hidden" name="page_url" id="jb-report-url" value="<?php echo esc_url( $referer ); ?>" />
				<input type="hidden" name="viewport" id="jb-report-viewport" value="" />
				<input type="hidden" name="screen" value="<?php echo esc_attr( $guess ); ?>" />

				<p class="jb-report__actions">
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Send report', 'jbnewgen' ); ?></button>
				</p>
			</form>

			<div class="jb-panel jb-report__mine">
				<h2 class="jb-panel__title"><?php esc_html_e( 'What you have sent', 'jbnewgen' ); ?></h2>

				<?php if ( ! $mine ) : ?>
					<p class="jb-empty"><?php esc_html_e( 'Nothing yet. Anything you send will be listed here with what has been done about it.', 'jbnewgen' ); ?></p>
				<?php else : ?>
					<ul class="jb-report__list">
						<?php foreach ( $mine as $report ) : ?>
							<li class="jb-report__item">
								<span class="jb-report__item-head">
									<span class="jb-report__id">#<?php echo esc_html( (string) $report['id'] ); ?></span>
									<span class="jb-chip jb-chip--<?php echo esc_attr( $report['status'] ); ?>">
										<?php echo esc_html( $report['status_label'] ); ?>
									</span>
								</span>
								<span class="jb-report__subject"><?php echo esc_html( $report['subject'] ); ?></span>
								<span class="jb-report__when">
									<?php
									printf(
										/* translators: %s: human readable time difference */
										esc_html__( '%s ago', 'jbnewgen' ),
										esc_html( human_time_diff( $report['created'], time() ) )
									);
									?>
								</span>
								<?php if ( $report['notes'] ) : ?>
									<?php $last = end( $report['notes'] ); ?>
									<span class="jb-report__reply">
										<?php echo esc_html( $last['text'] ); ?>
									</span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

		</div>
	</div>
	<script>
	( function () {
		var viewport = document.getElementById( 'jb-report-viewport' );
		if ( viewport ) {
			viewport.value = window.innerWidth + '×' + window.innerHeight;
		}

		// The referrer is the page they were on when they clicked Report; if
		// they arrived here some other way there is nothing to record and the
		// field stays empty rather than claiming this screen.
		var url = document.getElementById( 'jb-report-url' );
		if ( url && ! url.value ) {
			url.value = document.referrer || '';
		}
	}() );
	</script>
	<?php
}
