<?php
/**
 * Issues — the developer's side of inc/reports.php.
 *
 * Only holders of `jb_view_issues` ever see this screen or its entry in the
 * navigation. That capability comes from the Developer role, which an
 * administrator grants on the Roles screen; see inc/roles.php.
 *
 * The screen answers one question — what should I fix first — and it answers
 * it without asking the developer to read every report. Three things do that:
 *
 *   the summary   four counts, so the size of the queue is known before any
 *                 of it is read
 *   the order     impact first, then age. A blocker from this morning
 *                 outranks a nice-to-have from March, and nothing has to be
 *                 dragged into position for that to be true
 *   the clusters  how many other open reports name the same area. Three
 *                 reports about Insights is one problem with Insights, and
 *                 that is invisible when they are read one at a time
 *
 * Everything else is deliberately absent. There is no assignee (there is one
 * developer), no sprint, no estimate and no comment thread — a note back to
 * the reporter is the whole conversation this needs.
 */

/* -------------------------------------------------------------------------
   Ordering
   ------------------------------------------------------------------------- */

/**
 * Sort reports the way the queue should be worked.
 *
 * Impact is the primary key because it is the only field that says what the
 * report is costing somebody. Age breaks ties oldest-first *within* an impact
 * band, so nothing rots quietly at the bottom of the blockers — the usual
 * failure of a newest-first list.
 *
 * @param array<int,array<string,mixed>> $reports
 * @return array<int,array<string,mixed>>
 */
function jbnewgen_issues_sort( $reports ) {
	usort( $reports, static function ( $a, $b ) {
		if ( $a['impact_weight'] !== $b['impact_weight'] ) {
			return $b['impact_weight'] <=> $a['impact_weight'];
		}
		return $a['created'] <=> $b['created'];
	} );

	return $reports;
}

/**
 * How many open reports name each area.
 *
 * @param array<int,array<string,mixed>> $reports
 * @return array<string,int>
 */
function jbnewgen_issues_clusters( $reports ) {
	$open  = jbnewgen_report_open_statuses();
	$count = array();

	foreach ( $reports as $report ) {
		if ( ! in_array( $report['status'], $open, true ) ) {
			continue;
		}
		$area = $report['area'];
		$count[ $area ] = isset( $count[ $area ] ) ? $count[ $area ] + 1 : 1;
	}

	return $count;
}

/* -------------------------------------------------------------------------
   Acting on one
   ------------------------------------------------------------------------- */

/**
 * Change a status, add a note, or both.
 *
 * @return string|WP_Error|null
 */
function jbnewgen_issues_handle_post() {
	if ( ! isset( $_POST['jbnewgen_issues_nonce'] ) ) {
		return null;
	}
	if ( ! wp_verify_nonce( sanitize_key( $_POST['jbnewgen_issues_nonce'] ), 'jbnewgen_issues' ) ) {
		return new WP_Error( 'nonce', __( 'That form expired. Try again.', 'jbnewgen' ) );
	}
	if ( ! current_user_can( 'jb_manage_issues' ) ) {
		return new WP_Error( 'cap', __( 'You are not allowed to change reports.', 'jbnewgen' ) );
	}

	$id     = isset( $_POST['report'] ) ? (int) $_POST['report'] : 0;
	$report = jbnewgen_report_get( $id );
	if ( ! $report ) {
		return new WP_Error( 'missing', __( 'No such report.', 'jbnewgen' ) );
	}

	$changed = array();

	$status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : '';
	if ( $status && isset( jbnewgen_report_statuses()[ $status ] ) && $status !== $report['status'] ) {
		update_post_meta( $id, '_jb_status', $status );
		$changed[] = jbnewgen_report_statuses()[ $status ];
	}

	$note = isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '';
	if ( '' !== trim( $note ) ) {
		// Appended rather than replaced: the note is the only record of why a
		// decision was taken, and overwriting it loses that on the second edit.
		// (array) '' is array( '' ), so casting an unwritten meta key seeds the
		// list with an empty string — which the renderer then indexes into as
		// if it were a note. Same trap as in jbnewgen_report_get().
		$notes   = get_post_meta( $id, '_jb_notes', true );
		$notes   = is_array( $notes ) ? $notes : array();
		$notes[] = array(
			'text' => $note,
			'by'   => get_current_user_id(),
			'at'   => time(),
		);
		update_post_meta( $id, '_jb_notes', $notes );
		$changed[] = __( 'note added', 'jbnewgen' );
	}

	if ( ! $changed ) {
		return null;
	}

	// Touch the row so "last activity" means something.
	wp_update_post( array( 'ID' => $id, 'post_modified' => current_time( 'mysql' ) ) );

	/*
	 * Post / Redirect / Get, same as the Report form. Without it, refreshing
	 * after adding a note added the note again — and a note is append-only, so
	 * every refresh left another copy in the thread.
	 *
	 * What changed is carried as a list of already-translated fragments rather
	 * than as a sentence, so the URL stays short and the message is rebuilt on
	 * the other side.
	 */
	wp_safe_redirect( add_query_arg(
		array(
			'page'    => 'jbnewgen-issues',
			'report'  => $id,
			'updated' => rawurlencode( implode( '|', $changed ) ),
		),
		admin_url( 'admin.php' )
	) );
	exit;
}

/* -------------------------------------------------------------------------
   The screen
   ------------------------------------------------------------------------- */

add_action( 'admin_menu', function () {
	add_menu_page(
		__( 'Issues', 'jbnewgen' ),
		__( 'Issues', 'jbnewgen' ),
		'jb_view_issues',
		'jbnewgen-issues',
		'jbnewgen_issues_screen',
		'',
		82
	);
}, 20 );

/**
 * @param WP_Error|null $set
 * @return WP_Error|null
 */
function jbnewgen_issues_notice( $set = false ) {
	static $notice = null;
	if ( false !== $set ) {
		$notice = $set;
	}
	return $notice;
}

// Before the header is printed — see the note on the same hook in
// inc/reports.php for why a screen callback is too late to redirect from.
add_action( 'load-toplevel_page_jbnewgen-issues', function () {
	jbnewgen_issues_notice( jbnewgen_issues_handle_post() );
} );

function jbnewgen_issues_screen() {
	if ( ! current_user_can( 'jb_view_issues' ) ) {
		wp_die( esc_html__( 'This screen is for developers.', 'jbnewgen' ) );
	}

	$notice = jbnewgen_issues_notice();

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $notice && ! empty( $_GET['updated'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$changed = array_filter( array_map( 'sanitize_text_field', explode( '|', wp_unslash( $_GET['updated'] ) ) ) );
		if ( $changed ) {
			$notice = sprintf(
				/* translators: 1: report id, 2: comma-separated list of what changed */
				__( 'Report #%1$s updated — %2$s.', 'jbnewgen' ),
				isset( $_GET['report'] ) ? (int) $_GET['report'] : 0, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				implode( ', ', $changed )
			);
		}
	}

	$all      = jbnewgen_report_query( array( 'limit' => 500 ) );
	$clusters = jbnewgen_issues_clusters( $all );
	$statuses = jbnewgen_report_statuses();
	$open     = jbnewgen_report_open_statuses();

	$viewing = isset( $_GET['report'] ) ? (int) $_GET['report'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $viewing ) {
		jbnewgen_issues_detail( $viewing, $notice );
		return;
	}

	// --- filters ---------------------------------------------------------
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only view filters
	$f_status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : 'open';
	$f_area   = isset( $_GET['area'] ) ? sanitize_key( wp_unslash( $_GET['area'] ) ) : '';
	$f_kind   = isset( $_GET['kind'] ) ? sanitize_key( wp_unslash( $_GET['kind'] ) ) : '';
	// phpcs:enable

	$shown = array_filter( $all, static function ( $report ) use ( $f_status, $f_area, $f_kind, $open ) {
		if ( 'open' === $f_status && ! in_array( $report['status'], $open, true ) ) {
			return false;
		}
		if ( 'open' !== $f_status && 'all' !== $f_status && $report['status'] !== $f_status ) {
			return false;
		}
		if ( $f_area && $report['area'] !== $f_area ) {
			return false;
		}
		if ( $f_kind && $report['kind'] !== $f_kind ) {
			return false;
		}
		return true;
	} );
	$shown = jbnewgen_issues_sort( array_values( $shown ) );

	// --- the four numbers -------------------------------------------------
	$week     = time() - WEEK_IN_SECONDS;
	$n_open   = 0;
	$n_block  = 0;
	$n_week   = 0;
	$n_fixed  = 0;
	$oldest   = 0;

	foreach ( $all as $report ) {
		$is_open = in_array( $report['status'], $open, true );
		if ( $is_open ) {
			$n_open++;
			if ( 'blocked' === $report['impact'] ) {
				$n_block++;
			}
			if ( ! $oldest || $report['created'] < $oldest ) {
				$oldest = $report['created'];
			}
		}
		if ( $report['created'] >= $week ) {
			$n_week++;
		}
		if ( 'fixed' === $report['status'] ) {
			$n_fixed++;
		}
	}

	$base = admin_url( 'admin.php?page=jbnewgen-issues' );
	?>
	<div class="wrap jb-issues">
		<h1><?php esc_html_e( 'Issues', 'jbnewgen' ); ?></h1>

		<?php if ( is_wp_error( $notice ) ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $notice->get_error_message() ); ?></p></div>
		<?php elseif ( $notice ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>

		<div class="jb-dash__stats jb-issues__stats">
			<a class="jb-stat" href="<?php echo esc_url( add_query_arg( 'status', 'open', $base ) ); ?>">
				<span class="jb-stat__num"><?php echo esc_html( number_format_i18n( $n_open ) ); ?></span>
				<span class="jb-stat__label"><?php esc_html_e( 'Open', 'jbnewgen' ); ?></span>
			</a>
			<a class="jb-stat<?php echo $n_block ? ' jb-stat--loud' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'status', 'open', $base ) ); ?>">
				<span class="jb-stat__num"><?php echo esc_html( number_format_i18n( $n_block ) ); ?></span>
				<span class="jb-stat__label"><?php esc_html_e( 'Blocking someone', 'jbnewgen' ); ?></span>
			</a>
			<span class="jb-stat">
				<span class="jb-stat__num"><?php echo esc_html( number_format_i18n( $n_week ) ); ?></span>
				<span class="jb-stat__label"><?php esc_html_e( 'Came in this week', 'jbnewgen' ); ?></span>
			</span>
			<span class="jb-stat">
				<span class="jb-stat__num">
					<?php echo $oldest ? esc_html( human_time_diff( $oldest, time() ) ) : '&mdash;'; ?>
				</span>
				<span class="jb-stat__label"><?php esc_html_e( 'Oldest one still open', 'jbnewgen' ); ?></span>
			</span>
		</div>

		<?php if ( $clusters && max( $clusters ) > 1 ) : ?>
			<div class="jb-issues__clusters">
				<span class="jb-issues__clusters-label"><?php esc_html_e( 'Open reports cluster in', 'jbnewgen' ); ?></span>
				<?php
				arsort( $clusters );
				$areas = jbnewgen_report_areas();
				$shown_clusters = 0;
				foreach ( $clusters as $area => $count ) :
					if ( $count < 2 || $shown_clusters >= 4 ) {
						continue;
					}
					$shown_clusters++;
					?>
					<a class="jb-chip jb-chip--cluster" href="<?php echo esc_url( add_query_arg( array( 'status' => 'open', 'area' => $area ), $base ) ); ?>">
						<?php echo esc_html( isset( $areas[ $area ] ) ? $areas[ $area ] : $area ); ?>
						<span class="jb-chip__count"><?php echo esc_html( (string) $count ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<form method="get" class="jb-panel jb-issues__filters">
			<input type="hidden" name="page" value="jbnewgen-issues" />

			<label class="jb-issues__filter">
				<span><?php esc_html_e( 'Status', 'jbnewgen' ); ?></span>
				<select name="status">
					<option value="open" <?php selected( $f_status, 'open' ); ?>><?php esc_html_e( 'Still open', 'jbnewgen' ); ?></option>
					<option value="all" <?php selected( $f_status, 'all' ); ?>><?php esc_html_e( 'Everything', 'jbnewgen' ); ?></option>
					<?php foreach ( $statuses as $slug => $label ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $f_status, $slug ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>

			<label class="jb-issues__filter">
				<span><?php esc_html_e( 'Area', 'jbnewgen' ); ?></span>
				<select name="area">
					<option value=""><?php esc_html_e( 'Anywhere', 'jbnewgen' ); ?></option>
					<?php foreach ( jbnewgen_report_areas() as $slug => $label ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $f_area, $slug ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>

			<label class="jb-issues__filter">
				<span><?php esc_html_e( 'Kind', 'jbnewgen' ); ?></span>
				<select name="kind">
					<option value=""><?php esc_html_e( 'Any', 'jbnewgen' ); ?></option>
					<?php foreach ( jbnewgen_report_kinds() as $slug => $kind ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $f_kind, $slug ); ?>>
							<?php echo esc_html( $kind['label'] ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>

			<button type="submit" class="button"><?php esc_html_e( 'Apply', 'jbnewgen' ); ?></button>
			<a class="jb-issues__reset" href="<?php echo esc_url( $base ); ?>"><?php esc_html_e( 'Reset', 'jbnewgen' ); ?></a>
		</form>

		<div class="jb-panel jb-issues__listwrap">
			<h2 class="jb-panel__title">
				<?php
				printf(
					/* translators: %s: number of reports listed */
					esc_html( _n( '%s report, worst first', '%s reports, worst first', count( $shown ), 'jbnewgen' ) ),
					esc_html( number_format_i18n( count( $shown ) ) )
				);
				?>
			</h2>

			<?php if ( ! $shown ) : ?>
				<p class="jb-empty"><?php esc_html_e( 'Nothing here. Either the queue is clear or the filters are too narrow.', 'jbnewgen' ); ?></p>
			<?php else : ?>
				<ul class="jb-issues__list">
					<?php foreach ( $shown as $report ) : ?>
						<li class="jb-issues__row jb-issues__row--<?php echo esc_attr( $report['impact'] ); ?>">
							<a class="jb-issues__subject" href="<?php echo esc_url( add_query_arg( 'report', $report['id'], $base ) ); ?>">
								<span class="jb-issues__id">#<?php echo esc_html( (string) $report['id'] ); ?></span>
								<?php echo esc_html( $report['subject'] ); ?>
							</a>
							<span class="jb-chip jb-chip--impact jb-chip--<?php echo esc_attr( $report['impact'] ); ?>">
								<?php echo esc_html( $report['impact_label'] ); ?>
							</span>
							<span class="jb-issues__area"><?php echo esc_html( $report['area_label'] ); ?></span>
							<span class="jb-issues__who"><?php echo esc_html( $report['author_name'] ); ?></span>
							<span class="jb-issues__when">
								<?php
								printf(
									/* translators: %s: human readable time difference */
									esc_html__( '%s ago', 'jbnewgen' ),
									esc_html( human_time_diff( $report['created'], time() ) )
								);
								?>
							</span>
							<span class="jb-chip jb-chip--<?php echo esc_attr( $report['status'] ); ?>">
								<?php echo esc_html( $report['status_label'] ); ?>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * One report, in full.
 *
 * @param int                  $id
 * @param string|WP_Error|null $notice
 */
function jbnewgen_issues_detail( $id, $notice ) {
	$report = jbnewgen_report_get( $id );
	$base   = admin_url( 'admin.php?page=jbnewgen-issues' );

	if ( ! $report ) {
		?>
		<div class="wrap jb-issues">
			<h1><?php esc_html_e( 'Issues', 'jbnewgen' ); ?></h1>
			<p class="jb-empty"><?php esc_html_e( 'That report no longer exists.', 'jbnewgen' ); ?></p>
			<p><a class="button" href="<?php echo esc_url( $base ); ?>"><?php esc_html_e( 'Back to the queue', 'jbnewgen' ); ?></a></p>
		</div>
		<?php
		return;
	}

	$labels   = jbnewgen_report_environment_labels();
	$statuses = jbnewgen_report_statuses();

	// Everything else this person has sent, so a pattern is visible.
	$theirs = jbnewgen_report_query( array( 'author' => $report['author'], 'limit' => 50 ) );
	$same   = jbnewgen_report_query( array( 'area' => $report['area'], 'status' => jbnewgen_report_open_statuses(), 'limit' => 50 ) );
	?>
	<div class="wrap jb-issues jb-issues--one">
		<h1><?php echo esc_html( sprintf( '#%d %s', $report['id'], $report['subject'] ) ); ?></h1>

		<?php if ( is_wp_error( $notice ) ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $notice->get_error_message() ); ?></p></div>
		<?php elseif ( $notice ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>

		<p class="jb-issues__back">
			<a href="<?php echo esc_url( $base ); ?>">&larr; <?php esc_html_e( 'Back to the queue', 'jbnewgen' ); ?></a>
		</p>

		<div class="jb-issues__cols">

			<div class="jb-issues__main">
				<div class="jb-panel">
					<h2 class="jb-panel__title"><?php esc_html_e( 'What they said', 'jbnewgen' ); ?></h2>

					<p class="jb-issues__chips">
						<span class="jb-chip jb-chip--impact jb-chip--<?php echo esc_attr( $report['impact'] ); ?>"><?php echo esc_html( $report['impact_label'] ); ?></span>
						<span class="jb-chip"><?php echo esc_html( $report['kind_label'] ); ?></span>
						<span class="jb-chip"><?php echo esc_html( $report['area_label'] ); ?></span>
						<span class="jb-chip jb-chip--<?php echo esc_attr( $report['status'] ); ?>"><?php echo esc_html( $report['status_label'] ); ?></span>
					</p>

					<div class="jb-issues__body"><?php echo wpautop( esc_html( $report['details'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>

					<?php if ( $report['steps'] ) : ?>
						<h3 class="jb-panel__sub"><?php esc_html_e( 'How to make it happen again', 'jbnewgen' ); ?></h3>
						<div class="jb-issues__body jb-issues__steps"><?php echo wpautop( esc_html( $report['steps'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<?php endif; ?>

					<?php if ( $report['expected'] ) : ?>
						<h3 class="jb-panel__sub"><?php esc_html_e( 'What they expected instead', 'jbnewgen' ); ?></h3>
						<div class="jb-issues__body"><?php echo wpautop( esc_html( $report['expected'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<?php endif; ?>
				</div>

				<div class="jb-panel">
					<h2 class="jb-panel__title"><?php esc_html_e( 'Notes', 'jbnewgen' ); ?></h2>

					<?php if ( ! $report['notes'] ) : ?>
						<p class="jb-empty"><?php esc_html_e( 'Nothing written yet. A note here is what the reporter sees on their own Report screen.', 'jbnewgen' ); ?></p>
					<?php else : ?>
						<ul class="jb-issues__notes">
							<?php foreach ( $report['notes'] as $note ) : ?>
								<li>
									<span class="jb-issues__note-meta">
										<?php echo esc_html( get_the_author_meta( 'display_name', $note['by'] ) ); ?>
										&middot;
										<?php
										printf(
											/* translators: %s: human readable time difference */
											esc_html__( '%s ago', 'jbnewgen' ),
											esc_html( human_time_diff( (int) $note['at'], time() ) )
										);
										?>
									</span>
									<span class="jb-issues__note-text"><?php echo esc_html( $note['text'] ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if ( current_user_can( 'jb_manage_issues' ) ) : ?>
						<form method="post" class="jb-issues__form">
							<?php wp_nonce_field( 'jbnewgen_issues', 'jbnewgen_issues_nonce' ); ?>
							<input type="hidden" name="report" value="<?php echo esc_attr( (string) $report['id'] ); ?>" />

							<div class="jb-field">
								<label class="jb-field__label" for="jb-issue-note"><?php esc_html_e( 'Add a note', 'jbnewgen' ); ?></label>
								<textarea id="jb-issue-note" name="note" rows="3"
									placeholder="<?php esc_attr_e( 'What you found, or what you decided. The reporter sees the latest one.', 'jbnewgen' ); ?>"></textarea>
							</div>

							<div class="jb-field jb-field--inline">
								<label class="jb-field__label" for="jb-issue-status"><?php esc_html_e( 'Status', 'jbnewgen' ); ?></label>
								<select id="jb-issue-status" name="status">
									<?php foreach ( $statuses as $slug => $label ) : ?>
										<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $report['status'], $slug ); ?>>
											<?php echo esc_html( $label ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<button type="submit" class="button button-primary"><?php esc_html_e( 'Save', 'jbnewgen' ); ?></button>
							</div>
						</form>
					<?php endif; ?>
				</div>
			</div>

			<div class="jb-issues__side">
				<div class="jb-panel">
					<h2 class="jb-panel__title"><?php esc_html_e( 'Who and when', 'jbnewgen' ); ?></h2>
					<dl class="jb-meta">
						<div class="jb-meta__row">
							<dt><?php esc_html_e( 'Reported by', 'jbnewgen' ); ?></dt>
							<dd><?php echo esc_html( $report['author_name'] ); ?></dd>
						</div>
						<div class="jb-meta__row">
							<dt><?php esc_html_e( 'Sent', 'jbnewgen' ); ?></dt>
							<dd>
								<?php echo esc_html( date_i18n( 'j M Y, H:i', $report['created'] + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) ); ?>
							</dd>
						</div>
						<div class="jb-meta__row">
							<dt><?php esc_html_e( 'Last touched', 'jbnewgen' ); ?></dt>
							<dd>
								<?php
								printf(
									/* translators: %s: human readable time difference */
									esc_html__( '%s ago', 'jbnewgen' ),
									esc_html( human_time_diff( $report['updated'], time() ) )
								);
								?>
							</dd>
						</div>
						<div class="jb-meta__row">
							<dt><?php esc_html_e( 'They have sent', 'jbnewgen' ); ?></dt>
							<dd>
								<?php
								printf(
									/* translators: %s: number of reports */
									esc_html( _n( '%s report in total', '%s reports in total', count( $theirs ), 'jbnewgen' ) ),
									esc_html( number_format_i18n( count( $theirs ) ) )
								);
								?>
							</dd>
						</div>
						<?php if ( count( $same ) > 1 ) : ?>
							<div class="jb-meta__row">
								<dt><?php esc_html_e( 'Same area', 'jbnewgen' ); ?></dt>
								<dd>
									<a href="<?php echo esc_url( add_query_arg( array( 'status' => 'open', 'area' => $report['area'] ), $base ) ); ?>">
										<?php
										printf(
											/* translators: %s: number of other open reports */
											esc_html( _n( '%s other open report', '%s other open reports', count( $same ) - 1, 'jbnewgen' ) ),
											esc_html( number_format_i18n( count( $same ) - 1 ) )
										);
										?>
									</a>
								</dd>
							</div>
						<?php endif; ?>
					</dl>
				</div>

				<div class="jb-panel">
					<h2 class="jb-panel__title"><?php esc_html_e( 'Where it happened', 'jbnewgen' ); ?></h2>
					<?php if ( ! $report['environment'] ) : ?>
						<p class="jb-empty"><?php esc_html_e( 'Nothing was captured with this one.', 'jbnewgen' ); ?></p>
					<?php else : ?>
						<dl class="jb-meta jb-meta--tech">
							<?php foreach ( $report['environment'] as $key => $value ) : ?>
								<div class="jb-meta__row">
									<dt><?php echo esc_html( isset( $labels[ $key ] ) ? $labels[ $key ] : $key ); ?></dt>
									<dd>
										<?php if ( 'page' === $key ) : ?>
											<a href="<?php echo esc_url( $value ); ?>"><?php echo esc_html( $value ); ?></a>
										<?php else : ?>
											<?php echo esc_html( $value ); ?>
										<?php endif; ?>
									</dd>
								</div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>
				</div>
			</div>

		</div>
	</div>
	<?php
}
