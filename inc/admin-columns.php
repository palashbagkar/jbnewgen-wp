<?php
/**
 * List-screen columns and default sort.
 *
 * Payload declares both per collection and the live admin obeys them:
 *
 *   service-pillars  defaultColumns short, title, slug, order   defaultSort order
 *   services         defaultColumns title, pillar, order        defaultSort order
 *   jobs             defaultColumns title, locations, type,
 *                                   active, order              defaultSort order
 *   team-members     defaultColumns name, role, order           defaultSort order
 *   insights         defaultColumns title, category, date, _status
 *
 * WordPress ships Title and Date and nothing else, so every one of these
 * screens was a column of names with no way to see which pillar a service
 * belongs to, whether a role is still listed, or what order any of it comes
 * out in — all of which are answerable in the live admin at a glance. And the
 * four types Payload sorts by `order` were coming back newest-first, so the
 * running order of the site was invisible on the screen that sets it.
 *
 * `Date` is dropped from the four ordered types. It is WordPress's column, not
 * one Payload shows, and on content that is a fixed ordered list — four
 * pillars, five roles — the day somebody typed it is not a fact anyone needs.
 * Insights keep it: there it is the publication date and it is on Payload's
 * own list.
 */

/**
 * The column set for each type, in order. Keys are either a WordPress column
 * name to keep, or one of ours to render.
 *
 * @return array<string,array<string,string>>
 */
function jbnewgen_admin_columns() {
	return array(
		'pillar' => array(
			'cb'         => '',
			'title'      => __( 'Short name', 'jbnewgen' ),
			'jb_full'    => __( 'Full title', 'jbnewgen' ),
			'jb_slug'    => __( 'Slug', 'jbnewgen' ),
			'jb_order'   => __( 'Order', 'jbnewgen' ),
		),
		'service' => array(
			'cb'       => '',
			'title'    => __( 'Title', 'jbnewgen' ),
			'jb_pillar' => __( 'Service category', 'jbnewgen' ),
			'jb_order' => __( 'Order', 'jbnewgen' ),
		),
		'job' => array(
			'cb'           => '',
			'title'        => __( 'Role title', 'jbnewgen' ),
			'jb_locations' => __( 'Locations', 'jbnewgen' ),
			'jb_worktype'  => __( 'Work type', 'jbnewgen' ),
			'jb_active'    => __( 'Listed', 'jbnewgen' ),
			'jb_order'     => __( 'Order', 'jbnewgen' ),
		),
		'team_member' => array(
			'cb'       => '',
			'title'    => __( 'Full name', 'jbnewgen' ),
			'jb_role'  => __( 'Title', 'jbnewgen' ),
			'jb_order' => __( 'Order', 'jbnewgen' ),
		),
		'insight' => array(
			'cb'          => '',
			'title'       => __( 'Title', 'jbnewgen' ),
			'jb_category' => __( 'Category', 'jbnewgen' ),
			'jb_status'   => __( 'Status', 'jbnewgen' ),
			'date'        => __( 'Date', 'jbnewgen' ),
		),
	);
}

/** The types Payload sorts by `order`. */
function jbnewgen_ordered_types() {
	return array( 'pillar', 'service', 'job', 'team_member' );
}

/*
 * Register the columns.
 *
 * Built by walking our list rather than by unsetting core's, so the ORDER of
 * the columns is ours too — array key order is what WP_List_Table renders.
 * `cb` is carried over from core rather than invented: it holds the row's
 * checkbox markup, and the data bar's bulk actions read from it.
 */
/*
 * The type slugs are written out rather than read from jbnewgen_admin_columns():
 * that function calls __(), and this loop runs when the file is required —
 * before `init`. Touching a translation that early makes WordPress load the
 * text domain just in time and log a _doing_it_wrong notice for it. The slugs
 * are the one part of the table that needs no translating.
 */
foreach ( array( 'pillar', 'service', 'job', 'team_member', 'insight' ) as $jb_type ) {
	add_filter( "manage_edit-{$jb_type}_columns", function ( $columns ) use ( $jb_type ) {
		$wanted = jbnewgen_admin_columns()[ $jb_type ];
		$out    = array();

		foreach ( $wanted as $key => $label ) {
			if ( isset( $columns[ $key ] ) && '' === $label ) {
				$out[ $key ] = $columns[ $key ];   // cb — keep core's markup
			} else {
				$out[ $key ] = $label;
			}
		}

		return $out;
	} );

	add_filter( "manage_edit-{$jb_type}_sortable_columns", function ( $columns ) {
		// menu_order is a real column on wp_posts, so the sort is done by the
		// database rather than in PHP after the fact.
		$columns['jb_order'] = 'menu_order';
		return $columns;
	} );
}

/**
 * Fill the cells.
 */
add_action( 'manage_posts_custom_column', function ( $column, $post_id ) {
	switch ( $column ) {
		case 'jb_order':
			echo esc_html( (string) (int) get_post_field( 'menu_order', $post_id ) );
			break;

		case 'jb_slug':
			$slug = get_post_field( 'post_name', $post_id );
			echo $slug ? '<code>' . esc_html( $slug ) . '</code>' : '<span class="jb-col-empty">&mdash;</span>';
			break;

		case 'jb_full':
			jbnewgen_column_meta( $post_id, 'full_title' );
			break;

		case 'jb_locations':
			jbnewgen_column_meta( $post_id, 'locations' );
			break;

		case 'jb_worktype':
			jbnewgen_column_meta( $post_id, 'work_type' );
			break;

		case 'jb_role':
			jbnewgen_column_meta( $post_id, 'role' );
			break;

		case 'jb_active':
			// Carbon Fields stores an unticked checkbox as '' and a ticked one
			// as 'yes'; a job saved before the field existed has no row at all,
			// and the field defaults to ticked, so absent reads as listed.
			$raw    = get_post_meta( $post_id, '_active', true );
			$listed = ( '' === $raw ) ? true : (bool) $raw;
			printf(
				'<span class="jb-chip%s">%s</span>',
				$listed ? '' : ' jb-chip--off',
				esc_html( $listed ? __( 'Listed', 'jbnewgen' ) : __( 'Hidden', 'jbnewgen' ) )
			);
			break;

		case 'jb_pillar':
			// The association field stores "post|pillar|<id>" strings.
			$value = get_post_meta( $post_id, '_pillar', true );
			$name  = '';
			if ( is_array( $value ) && $value ) {
				$first = reset( $value );
				$id    = is_array( $first ) && isset( $first['id'] ) ? (int) $first['id'] : 0;
				$name  = $id ? get_the_title( $id ) : '';
			}
			if ( $name ) {
				echo esc_html( $name );
			} else {
				echo '<span class="jb-col-empty">&mdash;</span>';
			}
			break;

		case 'jb_category':
			$terms = get_the_terms( $post_id, 'insight_category' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				echo esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) );
			} else {
				echo '<span class="jb-col-empty">&mdash;</span>';
			}
			break;

		case 'jb_status':
			$status = get_post_status( $post_id );
			$object = get_post_status_object( $status );
			printf(
				'<span class="jb-chip%s">%s</span>',
				'publish' === $status ? '' : ' jb-chip--draft',
				esc_html( $object ? $object->label : $status )
			);
			break;
	}
}, 10, 2 );

/**
 * Print one Carbon Fields text value, truncated.
 *
 * Carbon Fields prefixes its post meta keys with an underscore. Read directly
 * rather than through carbon_get_post_meta() because this runs once per row
 * per column and the helper loads the whole container definition to do it.
 *
 * @param int    $post_id
 * @param string $key   the field name without Carbon's underscore
 * @param int    $chars
 */
function jbnewgen_column_meta( $post_id, $key, $chars = 60 ) {
	$value = (string) get_post_meta( $post_id, '_' . $key, true );

	if ( '' === trim( $value ) ) {
		echo '<span class="jb-col-empty">&mdash;</span>';
		return;
	}

	echo esc_html( wp_html_excerpt( $value, $chars, '…' ) );
}

/**
 * Sort the ordered types by `order`, the way Payload's defaultSort does.
 *
 * Only when nothing else is asked for: clicking a column header sets `orderby`
 * and that must win, or the header would be a control that does nothing.
 */
add_action( 'pre_get_posts', function ( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit' !== $screen->base ) {
		return;
	}

	if ( ! in_array( $screen->post_type, jbnewgen_ordered_types(), true ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! empty( $_GET['orderby'] ) ) {
		return;
	}

	$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
} );
