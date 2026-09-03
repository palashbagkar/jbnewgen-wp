<?php
/**
 * Replaces the default WordPress dashboard with a grid of the things this
 * site actually contains, grouped the same way the sidebar is.
 */

/**
 * Dashboard contents. Each card is [ label, list URL, add-new URL|null ].
 * Options pages have no "add new" — they are single screens.
 */
function jbnewgen_dashboard_sections() {
	return array(
		__( 'Services', 'jbnewgen' ) => array(
			array(
				'label' => __( 'Service Categories', 'jbnewgen' ),
				'url'   => admin_url( 'edit.php?post_type=pillar' ),
				'add'   => admin_url( 'post-new.php?post_type=pillar' ),
			),
			array(
				'label' => __( 'Services', 'jbnewgen' ),
				'url'   => admin_url( 'edit.php?post_type=service' ),
				'add'   => admin_url( 'post-new.php?post_type=service' ),
			),
		),
		__( 'Careers', 'jbnewgen' ) => array(
			array(
				'label' => __( 'Job Openings', 'jbnewgen' ),
				'url'   => admin_url( 'edit.php?post_type=job' ),
				'add'   => admin_url( 'post-new.php?post_type=job' ),
			),
			array(
				'label' => __( 'Core Team', 'jbnewgen' ),
				'url'   => admin_url( 'edit.php?post_type=team_member' ),
				'add'   => admin_url( 'post-new.php?post_type=team_member' ),
			),
		),
		__( 'Insights', 'jbnewgen' ) => array(
			array(
				'label' => __( 'Insights', 'jbnewgen' ),
				'url'   => admin_url( 'edit.php?post_type=insight' ),
				'add'   => admin_url( 'post-new.php?post_type=insight' ),
			),
			array(
				'label' => __( 'Insight Categories', 'jbnewgen' ),
				'url'   => admin_url( 'edit-tags.php?taxonomy=insight_category&post_type=insight' ),
				'add'   => null,
			),
		),
		__( 'Site', 'jbnewgen' ) => array(
			array(
				'label' => __( 'Media', 'jbnewgen' ),
				'url'   => admin_url( 'upload.php' ),
				'add'   => admin_url( 'media-new.php' ),
			),
			array(
				'label' => __( 'Users', 'jbnewgen' ),
				'url'   => admin_url( 'users.php' ),
				'add'   => admin_url( 'user-new.php' ),
			),
			array(
				'label' => __( 'Homepage', 'jbnewgen' ),
				'url'   => admin_url( 'admin.php?page=crb_carbon_fields_container_homepage.php' ),
				'add'   => null,
			),
			array(
				'label' => __( 'About Pages', 'jbnewgen' ),
				'url'   => admin_url( 'admin.php?page=crb_carbon_fields_container_about.php' ),
				'add'   => null,
			),
			array(
				'label' => __( 'Site Settings', 'jbnewgen' ),
				'url'   => admin_url( 'admin.php?page=crb_carbon_fields_container_site_settings.php' ),
				'add'   => null,
			),
		),
	);
}

/**
 * Swap every core dashboard widget for the custom grid.
 */
add_action( 'wp_dashboard_setup', function () {
	global $wp_meta_boxes;
	$wp_meta_boxes['dashboard'] = array();

	add_meta_box(
		'jbnewgen_dashboard',
		__( 'Overview', 'jbnewgen' ),
		'jbnewgen_render_dashboard',
		'dashboard',
		'normal',
		'high'
	);
}, 999 );

/**
 * The dashboard is one static box, so WordPress's postbox script buys us
 * nothing — and it makes the box collapsible and drag-sortable, which on a
 * touch screen lets a stray tap-drag make the whole thing vanish.
 */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'index.php' === $hook ) {
		wp_dequeue_script( 'postbox' );
	}
} );

function jbnewgen_render_dashboard() {
	foreach ( jbnewgen_dashboard_sections() as $heading => $cards ) {
		echo '<h2 class="jb-dash__heading">' . esc_html( $heading ) . '</h2>';
		echo '<div class="jb-dash__grid">';

		foreach ( $cards as $card ) {
			echo '<div class="jb-dash__card">';
			printf(
				'<a class="jb-dash__card-link" href="%s">%s</a>',
				esc_url( $card['url'] ),
				esc_html( $card['label'] )
			);

			if ( ! empty( $card['add'] ) ) {
				printf(
					'<a class="jb-dash__add" href="%s" aria-label="%s">+</a>',
					esc_url( $card['add'] ),
					/* translators: %s: item name */
					esc_attr( sprintf( __( 'Add new %s', 'jbnewgen' ), $card['label'] ) )
				);
			}

			echo '</div>';
		}

		echo '</div>';
	}
}
