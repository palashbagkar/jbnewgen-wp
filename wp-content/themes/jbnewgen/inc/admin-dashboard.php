<?php
/**
 * The dashboard.
 *
 * WordPress's stock widgets are all removed. None of them fit this site:
 * Quick Draft writes blog posts nobody publishes, At a Glance counts posts and
 * pages this site does not use, Site Health is a developer tool, and Activity
 * duplicates the recently-edited list below in a worse format.
 *
 * What replaces them is the two things an editor actually opens a CMS home
 * screen for: how much of each thing exists, and what was last touched.
 */

/**
 * Strip the default widgets. Runs late so anything registered by core or a
 * plugin on the normal hook is already in the array by the time we clear it.
 */
add_action( 'wp_dashboard_setup', function () {
	global $wp_meta_boxes;
	$wp_meta_boxes['dashboard'] = array();

	// The welcome panel is not a widget and survives clearing the array.
	remove_action( 'welcome_panel', 'wp_welcome_panel' );
}, 999 );

/**
 * The four numbers along the top. Only counts the user may actually see are
 * shown — a Viewer with no access to Job Openings should not be told how many
 * there are.
 *
 * @return array<int,array{label:string,count:int,url:string}>
 */
function jbnewgen_dashboard_stats() {
	$stats = array();

	foreach ( jbnewgen_role_content_types() as $slug => $type ) {
		$object = get_post_type_object( $slug );
		if ( ! $object || ! current_user_can( $object->cap->edit_posts ) ) {
			continue;
		}

		$counts = wp_count_posts( $slug );
		$total  = (int) $counts->publish + (int) $counts->draft + (int) $counts->pending;

		$stats[] = array(
			'label' => $type['label'],
			'count' => $total,
			'url'   => admin_url( 'edit.php?post_type=' . $slug ),
		);
	}

	return $stats;
}

/**
 * The last N entries modified across every content type this user can see.
 *
 * One query rather than one per type: post_type accepts an array, and sorting
 * by modified date across the union is the whole point — five separate queries
 * would have to be merged and re-sorted in PHP anyway.
 *
 * @param int $limit
 * @return WP_Post[]
 */
function jbnewgen_dashboard_recent( $limit = 8 ) {
	$types = array();
	foreach ( array_keys( jbnewgen_role_content_types() ) as $slug ) {
		$object = get_post_type_object( $slug );
		if ( $object && current_user_can( $object->cap->edit_posts ) ) {
			$types[] = $slug;
		}
	}
	if ( ! $types ) {
		return array();
	}

	return get_posts( array(
		'post_type'        => $types,
		'post_status'      => array( 'publish', 'draft', 'pending', 'private' ),
		'orderby'          => 'modified',
		'order'            => 'DESC',
		'numberposts'      => $limit,
		'suppress_filters' => false,
	) );
}

/**
 * Twelve months of entries created, per month, across every content type this
 * user can see.
 *
 * One grouped query rather than twelve range queries: the database can bucket
 * by month far more cheaply than PHP can, and the result set is at most twelve
 * rows. Months with nothing in them do not come back from SQL at all, so the
 * series is seeded with zeroes first and filled in — otherwise a quiet month
 * would silently shorten the chart instead of showing a gap.
 *
 * @return array<int,array{label:string,short:string,count:int}>
 */
function jbnewgen_dashboard_monthly() {
	global $wpdb;

	$types = array();
	foreach ( array_keys( jbnewgen_role_content_types() ) as $slug ) {
		$object = get_post_type_object( $slug );
		if ( $object && current_user_can( $object->cap->edit_posts ) ) {
			$types[] = $slug;
		}
	}
	if ( ! $types ) {
		return array();
	}

	// Seed twelve buckets ending with the current month.
	$series = array();
	for ( $i = 11; $i >= 0; $i-- ) {
		$stamp = strtotime( "-$i months", current_time( 'timestamp' ) );
		$series[ gmdate( 'Y-m', $stamp ) ] = array(
			'label' => date_i18n( 'F Y', $stamp ),
			'short' => date_i18n( 'M', $stamp ),
			'count' => 0,
		);
	}

	$placeholders = implode( ',', array_fill( 0, count( $types ), '%s' ) );
	$since        = gmdate( 'Y-m-d H:i:s', strtotime( '-11 months', current_time( 'timestamp' ) ) );

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- placeholders built above, values passed to prepare()
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT DATE_FORMAT(post_date, '%%Y-%%m') AS ym, COUNT(*) AS total
			 FROM {$wpdb->posts}
			 WHERE post_type IN ($placeholders)
			   AND post_status IN ('publish','draft','pending','private')
			   AND post_date >= %s
			 GROUP BY ym",
			array_merge( $types, array( $since ) )
		)
	);
	// phpcs:enable

	foreach ( (array) $rows as $row ) {
		if ( isset( $series[ $row->ym ] ) ) {
			$series[ $row->ym ]['count'] = (int) $row->total;
		}
	}

	return array_values( $series );
}

/**
 * Published / draft split per content type.
 *
 * @return array<int,array{label:string,publish:int,draft:int,total:int}>
 */
function jbnewgen_dashboard_mix() {
	$mix = array();

	foreach ( jbnewgen_role_content_types() as $slug => $type ) {
		$object = get_post_type_object( $slug );
		if ( ! $object || ! current_user_can( $object->cap->edit_posts ) ) {
			continue;
		}

		$counts  = wp_count_posts( $slug );
		$publish = (int) $counts->publish;
		$draft   = (int) $counts->draft + (int) $counts->pending;

		$mix[] = array(
			'label'   => $type['label'],
			'publish' => $publish,
			'draft'   => $draft,
			'total'   => $publish + $draft,
		);
	}

	return $mix;
}

/**
 * Render the analytics panel.
 *
 * Charts are hand-drawn inline SVG rather than a charting library. Three
 * reasons: the theme has no build step, the plugin budget is three and none of
 * it should go to a graph, and every library ships its own palette and type
 * scale that would then have to be fought back to this one. Two bar charts are
 * about eighty lines of SVG — a library is not worth 200KB and a dependency.
 *
 * These are CONTENT analytics, drawn from the posts table. There is no traffic
 * data here and none can be invented: visitor numbers need an analytics
 * provider wired up, which is a separate decision.
 */
function jbnewgen_dashboard_analytics() {
	$monthly = jbnewgen_dashboard_monthly();
	$mix     = jbnewgen_dashboard_mix();

	if ( ! $monthly && ! $mix ) {
		return;
	}

	$peak = 0;
	foreach ( $monthly as $month ) {
		$peak = max( $peak, $month['count'] );
	}
	$scale = $peak > 0 ? $peak : 1;

	$mix_peak = 0;
	foreach ( $mix as $row ) {
		$mix_peak = max( $mix_peak, $row['total'] );
	}
	$mix_scale = $mix_peak > 0 ? $mix_peak : 1;
	?>
	<div class="jb-dash__charts">

		<section class="jb-dash__panel jb-chart jb-chart--line">
			<h2 class="jb-dash__title"><?php esc_html_e( 'Library growth', 'jbnewgen' ); ?></h2>

			<div class="jb-chart__body">
				<?php
				$year_total = 0;
				foreach ( $monthly as $month ) {
					$year_total += $month['count'];
				}
				?>
				<span class="jb-chart__total">
					<?php
					/* translators: %s: number of entries added in the last year */
					printf( esc_html__( '%s added in 12 months', 'jbnewgen' ), esc_html( number_format_i18n( $year_total ) ) );
					?>
				</span>
			<?php if ( ! $monthly ) : ?>
				<p class="jb-dash__empty"><?php esc_html_e( 'Nothing to chart yet.', 'jbnewgen' ); ?></p>
			<?php else : ?>
				<?php
				/*
				 * Two series over the same twelve months:
				 *   total  the running size of the library — the line that
				 *          answers "is the site growing, and how fast"
				 *   added  what landed in each individual month
				 *
				 * Cumulative is the useful one day to day: a flat stretch says
				 * nothing has been published in months, and a steepening slope
				 * says the opposite, neither of which a per-month bar makes
				 * obvious at a glance.
				 *
				 * Drawn as SVG in a 0 0 720 200 viewBox with
				 * preserveAspectRatio="none" on the fills, so the chart scales
				 * to the panel without recomputing anything.
				 */
				$w   = 720;
				$h   = 200;
				$pad = array( 'l' => 34, 'r' => 12, 't' => 14, 'b' => 26 );

				// The library already held entries before this window opened;
				// counting only these twelve months would draw a curve that
				// starts at zero and understates the total.
				$library_now = 0;
				foreach ( jbnewgen_dashboard_mix() as $row ) {
					$library_now += $row['total'];
				}
				$in_window = 0;
				foreach ( $monthly as $month ) {
					$in_window += $month['count'];
				}
				$running = max( 0, $library_now - $in_window );

				$totals = array();
				foreach ( $monthly as $month ) {
					$running += $month['count'];
					$totals[] = $running;
				}

				$y_max  = max( 1, max( $totals ) );
				$step   = ( $w - $pad['l'] - $pad['r'] ) / max( 1, count( $monthly ) - 1 );
				$plot_h = $h - $pad['t'] - $pad['b'];

				$points = array();
				foreach ( $totals as $i => $value ) {
					$x = $pad['l'] + ( $i * $step );
					$y = $pad['t'] + $plot_h - ( ( $value / $y_max ) * $plot_h );
					$points[] = array( 'x' => round( $x, 2 ), 'y' => round( $y, 2 ), 'v' => $value );
				}

				$line = array();
				foreach ( $points as $pt ) {
					$line[] = $pt['x'] . ',' . $pt['y'];
				}
				$line_d = implode( ' ', $line );
				$area_d = 'M' . $points[0]['x'] . ',' . ( $h - $pad['b'] ) . ' L' . implode( ' L', $line )
					. ' L' . end( $points )['x'] . ',' . ( $h - $pad['b'] ) . ' Z';

				// Four gridlines, rounded so the labels are whole numbers.
				$ticks = array();
				for ( $i = 0; $i <= 3; $i++ ) {
					$value   = (int) round( $y_max * ( $i / 3 ) );
					$ticks[] = array(
						'v' => $value,
						'y' => round( $pad['t'] + $plot_h - ( ( $value / $y_max ) * $plot_h ), 2 ),
					);
				}
				?>
				<div class="jb-line">
					<svg viewBox="0 0 <?php echo esc_attr( $w . ' ' . $h ); ?>" class="jb-line__svg"
						role="img" preserveAspectRatio="none"
						aria-label="<?php esc_attr_e( 'Total entries in the library over the last twelve months', 'jbnewgen' ); ?>">

						<defs>
							<linearGradient id="jbLineFill" x1="0" y1="0" x2="0" y2="1">
								<stop offset="0%" stop-color="currentColor" stop-opacity=".22" />
								<stop offset="100%" stop-color="currentColor" stop-opacity="0" />
							</linearGradient>
						</defs>

						<?php foreach ( $ticks as $tick ) : ?>
							<line class="jb-line__grid" x1="<?php echo esc_attr( $pad['l'] ); ?>"
								x2="<?php echo esc_attr( $w - $pad['r'] ); ?>"
								y1="<?php echo esc_attr( $tick['y'] ); ?>"
								y2="<?php echo esc_attr( $tick['y'] ); ?>" />
							<text class="jb-line__tick" x="<?php echo esc_attr( $pad['l'] - 8 ); ?>"
								y="<?php echo esc_attr( $tick['y'] + 3 ); ?>" text-anchor="end">
								<?php echo esc_html( number_format_i18n( $tick['v'] ) ); ?>
							</text>
						<?php endforeach; ?>

						<path class="jb-line__area" d="<?php echo esc_attr( $area_d ); ?>" fill="url(#jbLineFill)" />
						<polyline class="jb-line__path" points="<?php echo esc_attr( $line_d ); ?>" />

						<?php foreach ( $points as $i => $pt ) : ?>
							<g class="jb-line__pt">
								<line class="jb-line__rule" x1="<?php echo esc_attr( $pt['x'] ); ?>"
									x2="<?php echo esc_attr( $pt['x'] ); ?>"
									y1="<?php echo esc_attr( $pad['t'] ); ?>"
									y2="<?php echo esc_attr( $h - $pad['b'] ); ?>" />
								<circle class="jb-line__dot" cx="<?php echo esc_attr( $pt['x'] ); ?>"
									cy="<?php echo esc_attr( $pt['y'] ); ?>" r="3.5" />
								<?php // a wide invisible target, so the dot does not have to be hit exactly ?>
								<rect class="jb-line__hit" x="<?php echo esc_attr( $pt['x'] - ( $step / 2 ) ); ?>"
									y="0" width="<?php echo esc_attr( $step ); ?>" height="<?php echo esc_attr( $h ); ?>">
									<title><?php
										printf(
											/* translators: 1: month, 2: library total, 3: added that month */
											esc_html__( '%1$s — %2$s total, %3$s added', 'jbnewgen' ),
											esc_html( $monthly[ $i ]['label'] ),
											esc_html( number_format_i18n( $pt['v'] ) ),
											esc_html( number_format_i18n( $monthly[ $i ]['count'] ) )
										);
									?></title>
								</rect>
							</g>
						<?php endforeach; ?>
					</svg>

					<div class="jb-line__axis">
						<?php foreach ( $monthly as $month ) : ?>
							<span class="jb-line__month"><?php echo esc_html( $month['short'] ); ?></span>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
			</div>
		</section>

		<?php if ( $mix ) : ?>
		<section class="jb-dash__panel jb-chart">
			<h2 class="jb-dash__title"><?php esc_html_e( 'Content mix', 'jbnewgen' ); ?></h2>

			<div class="jb-chart__body">
				<ul class="jb-mix">
					<?php foreach ( $mix as $row ) : ?>
						<?php
						$pub_pct   = (int) round( ( $row['publish'] / $mix_scale ) * 100 );
						$draft_pct = (int) round( ( $row['draft'] / $mix_scale ) * 100 );
						?>
						<li class="jb-mix__row">
							<span class="jb-mix__label"><?php echo esc_html( $row['label'] ); ?></span>
							<span class="jb-mix__track">
								<span class="jb-mix__pub" style="width: <?php echo esc_attr( $pub_pct ); ?>%;"></span>
								<span class="jb-mix__draft" style="width: <?php echo esc_attr( $draft_pct ); ?>%;"></span>
							</span>
							<span class="jb-mix__num">
								<?php echo esc_html( number_format_i18n( $row['total'] ) ); ?>
								<?php if ( $row['draft'] > 0 ) : ?>
									<em class="jb-mix__drafts">
										<?php
										/* translators: %s: number of drafts */
										printf( esc_html__( '%s draft', 'jbnewgen' ), esc_html( number_format_i18n( $row['draft'] ) ) );
										?>
									</em>
								<?php endif; ?>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>

				<p class="jb-chart__key">
					<span class="jb-key jb-key--pub"></span> <?php esc_html_e( 'Published', 'jbnewgen' ); ?>
					<span class="jb-key jb-key--draft"></span> <?php esc_html_e( 'Draft', 'jbnewgen' ); ?>
				</p>
			</div>
		</section>
		<?php endif; ?>

	</div>
	<?php
}

/**
 * Render the screen. Replaces the whole widget area rather than adding a
 * widget to it, so nothing else can reorder or collapse it.
 *
 * Printed into the footer and then moved, rather than hooked to
 * admin_notices: that hook fires BEFORE the <h1>, which put the panels above
 * the page title. There is no core hook between the heading and the screen
 * content, so relocation is the only way to sit under it without replacing
 * core's heading markup.
 */
add_action( 'admin_footer', function () {
	$screen = get_current_screen();
	if ( ! $screen || 'dashboard' !== $screen->id ) {
		return;
	}

	$stats  = jbnewgen_dashboard_stats();
	$recent = jbnewgen_dashboard_recent();
	?>
	<div class="jb-dash" id="jb-dash" hidden>

		<?php if ( $stats ) : ?>
			<div class="jb-dash__stats">
				<?php foreach ( $stats as $stat ) : ?>
					<a class="jb-stat" href="<?php echo esc_url( $stat['url'] ); ?>">
						<span class="jb-stat__num"><?php echo esc_html( number_format_i18n( $stat['count'] ) ); ?></span>
						<span class="jb-stat__label"><?php echo esc_html( $stat['label'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<section class="jb-dash__panel">
			<h2 class="jb-dash__title"><?php esc_html_e( 'Recently edited', 'jbnewgen' ); ?></h2>

			<?php if ( ! $recent ) : ?>
				<p class="jb-dash__empty"><?php esc_html_e( 'Nothing has been edited yet.', 'jbnewgen' ); ?></p>
			<?php else : ?>
				<ul class="jb-recent">
					<?php foreach ( $recent as $post ) : ?>
						<?php
						$object = get_post_type_object( $post->post_type );
						$author = get_the_author_meta( 'display_name', $post->post_author );
						$edit   = get_edit_post_link( $post->ID );
						?>
						<li class="jb-recent__row">
							<a class="jb-recent__title" href="<?php echo esc_url( $edit ? $edit : '#' ); ?>">
								<?php echo esc_html( $post->post_title ? $post->post_title : __( '(no title)', 'jbnewgen' ) ); ?>
							</a>
							<span class="jb-recent__type"><?php echo esc_html( $object ? $object->labels->singular_name : $post->post_type ); ?></span>
							<?php if ( 'publish' !== $post->post_status ) : ?>
								<span class="jb-recent__status"><?php echo esc_html( $post->post_status ); ?></span>
							<?php endif; ?>
							<span class="jb-recent__who"><?php echo esc_html( $author ); ?></span>
							<span class="jb-recent__when">
								<?php
								/* translators: %s: human readable time difference, e.g. "2 hours" */
								printf(
									esc_html__( '%s ago', 'jbnewgen' ),
									esc_html( human_time_diff( get_post_modified_time( 'U', true, $post ), time() ) )
								);
								?>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</section>

		<?php jbnewgen_dashboard_analytics(); ?>
	</div>
	<script>
	( function () {
		var dash = document.getElementById( 'jb-dash' );
		var wrap = document.querySelector( '.wrap' );
		if ( ! dash || ! wrap ) { return; }

		// After the whole header block, not after the path box.
		//
		// The path now lives INSIDE .jb-head (the flex row holding the title
		// and the Add-new button), so inserting after it put the entire
		// dashboard inside that row — the panels became a flex item and the
		// Add-new button wrapped to a line of its own at the very bottom of
		// the page. Anchoring on .jb-head keeps the header a header.
		var after = document.querySelector( '.jb-head' ) ||
			document.getElementById( 'jb-path' ) ||
			wrap.querySelector( 'h1' );
		if ( ! after ) { return; }

		after.parentNode.insertBefore( dash, after.nextSibling );
		dash.hidden = false;
	}() );
	</script>
	<?php
}, 20 );
