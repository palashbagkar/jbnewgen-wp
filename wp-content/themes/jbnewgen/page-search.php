<?php
/**
 * Template Name: Search
 *
 * Ported from ../jbnewgen/src/app/(frontend)/search/page.tsx and
 * `allPages`/`searchPages` in ../jbnewgen/src/lib/content.ts: a fuzzy
 * substring match over a static sitemap, not WordPress's native `s=` search
 * (there is no full-text index of body copy worth searching here yet -- this
 * matches the live site's actual behaviour).
 */

defined( 'ABSPATH' ) || exit;

get_header();

/**
 * @return array [ ['href','title','group'] ]
 */
function jbnewgen_all_pages() {
	$list = array(
		array( 'href' => '/', 'title' => __( 'Home', 'jbnewgen' ), 'group' => 'Main' ),
		array( 'href' => '/about/company', 'title' => __( 'The Company', 'jbnewgen' ), 'group' => 'About' ),
		array( 'href' => '/about/ceo', 'title' => __( 'Our CEO - Joyjeet Bose', 'jbnewgen' ), 'group' => 'About' ),
		array( 'href' => '/about/team', 'title' => __( 'Core Team', 'jbnewgen' ), 'group' => 'About' ),
		array( 'href' => '/services', 'title' => __( 'Services - Overview', 'jbnewgen' ), 'group' => 'Services' ),
		array( 'href' => '/careers', 'title' => __( 'Careers', 'jbnewgen' ), 'group' => 'Main' ),
		array( 'href' => '/contact', 'title' => __( 'Contact', 'jbnewgen' ), 'group' => 'Main' ),
		array( 'href' => '/quote', 'title' => __( 'Get a Quote', 'jbnewgen' ), 'group' => 'Main' ),
		array( 'href' => '/insights', 'title' => __( 'Insights - Overview', 'jbnewgen' ), 'group' => 'Insights' ),
	);

	foreach ( jbnewgen_nav_pillars() as $jb_p ) {
		$list[] = array( 'href' => $jb_p['url'], 'title' => $jb_p['short'], 'group' => 'Services' );
		foreach ( $jb_p['services'] as $jb_s ) {
			$list[] = array( 'href' => $jb_s['url'], 'title' => $jb_s['title'], 'group' => 'Services · ' . $jb_p['short'] );
		}
	}

	$terms = get_terms( array( 'taxonomy' => 'insight_category', 'hide_empty' => false ) );
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $jb_t ) {
			/* translators: %s: category name */
			$list[] = array( 'href' => '/insights/category/' . $jb_t->slug, 'title' => sprintf( __( '%s (category)', 'jbnewgen' ), $jb_t->name ), 'group' => 'Insights' );
		}
	}

	$posts = get_posts( array( 'post_type' => 'insight', 'post_status' => 'publish', 'numberposts' => -1, 'suppress_filters' => false ) );
	foreach ( $posts as $jb_post ) {
		$list[] = array( 'href' => '/insights/' . $jb_post->post_name, 'title' => get_the_title( $jb_post ), 'group' => 'Insights · Articles' );
	}

	return $list;
}

/**
 * @param string $query
 * @return array
 */
function jbnewgen_search_pages( $query ) {
	$q = trim( strtolower( $query ) );
	if ( '' === $q ) {
		return array();
	}
	$terms   = preg_split( '/\s+/', $q );
	$scored  = array();
	foreach ( jbnewgen_all_pages() as $jb_page ) {
		$hay   = strtolower( $jb_page['title'] . ' ' . $jb_page['group'] . ' ' . $jb_page['href'] );
		$score = 0;
		foreach ( $terms as $jb_t ) {
			if ( '' !== $jb_t && false !== strpos( $hay, $jb_t ) ) {
				++$score;
			}
		}
		if ( $score > 0 ) {
			$scored[] = array( 'page' => $jb_page, 'score' => $score );
		}
	}
	usort( $scored, function ( $a, $b ) {
		return $b['score'] <=> $a['score'];
	} );
	return array_map( function ( $r ) {
		return $r['page'];
	}, $scored );
}

$jb_query   = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
$jb_query   = trim( $jb_query );
$jb_results = $jb_query ? jbnewgen_search_pages( $jb_query ) : array();

jbnewgen_page_header( array(
	'eyebrow' => __( 'Search', 'jbnewgen' ),
	/* translators: %s: the visitor's search query */
	'title'   => $jb_query ? sprintf( __( 'Results for “%s”', 'jbnewgen' ), $jb_query ) : __( 'Search', 'jbnewgen' ),
	'trail'   => array(
		array( 'label' => __( 'Home', 'jbnewgen' ), 'href' => '/' ),
		array( 'label' => __( 'Search', 'jbnewgen' ), 'href' => '/search' ),
	),
	'note'    => $jb_query
		/* translators: %d: number of matching pages */
		? sprintf( _n( '%d page matched.', '%d pages matched.', count( $jb_results ), 'jbnewgen' ), count( $jb_results ) )
		: __( 'Search every page in the site - services, team, insights and more.', 'jbnewgen' ),
) );
?>

<div class="mx-auto max-w-7xl px-5 sm:px-8">
	<form action="<?php echo esc_url( home_url( '/search' ) ); ?>" role="search" class="-mt-4 flex w-full max-w-xl items-center gap-2 rounded-[7px] border border-ink-200 bg-white px-4 py-2 shadow-sm">
		<?php jbnewgen_icon( 'search', 20, 'shrink-0 text-ink-400' ); ?>
		<input type="search" name="q" value="<?php echo esc_attr( $jb_query ); ?>" autofocus placeholder="<?php echo esc_attr__( 'Try “ceo”, “whatsapp”, “strategy”…', 'jbnewgen' ); ?>" class="h-9 w-full bg-transparent text-ink-900 outline-none placeholder:text-ink-400">
		<button type="submit" class="shrink-0 rounded-[7px] bg-flame-500 px-5 py-2 text-sm font-semibold text-white transition-colors hover:bg-flame-600"><?php esc_html_e( 'Search', 'jbnewgen' ); ?></button>
	</form>
</div>

<?php if ( $jb_query && $jb_results ) : ?>
	<?php
	jbnewgen_nav_grid( array(
		'title'   => __( 'Matching pages', 'jbnewgen' ),
		'columns' => 2,
		'items'   => array_map( function ( $r ) {
			return array( 'label' => $r['title'], 'href' => $r['href'], 'desc' => $r['group'], 'icon' => 'arrowUpRight' );
		}, $jb_results ),
	) );
	?>
<?php elseif ( $jb_query ) : ?>
	<?php
	jbnewgen_nav_grid( array(
		'title'   => __( 'No matches - try these', 'jbnewgen' ),
		/* translators: %s: the visitor's search query */
		'caption' => sprintf( __( 'Nothing matched “%s”', 'jbnewgen' ), $jb_query ),
		'columns' => 3,
		'items'   => array(
			array( 'label' => __( 'Services', 'jbnewgen' ), 'href' => '/services', 'desc' => __( 'All 4 pillars', 'jbnewgen' ), 'icon' => 'compass' ),
			array( 'label' => __( 'About', 'jbnewgen' ), 'href' => '/about', 'desc' => __( 'Company, CEO & team', 'jbnewgen' ), 'icon' => 'users' ),
			array( 'label' => __( 'Insights', 'jbnewgen' ), 'href' => '/insights', 'desc' => __( 'Articles', 'jbnewgen' ), 'icon' => 'bulb' ),
		),
	) );
	?>
<?php else : ?>
	<?php
	jbnewgen_nav_grid( array(
		'title'   => __( 'Popular destinations', 'jbnewgen' ),
		'columns' => 3,
		'items'   => array(
			array( 'label' => __( 'Services', 'jbnewgen' ), 'href' => '/services', 'desc' => __( '4 pillars', 'jbnewgen' ), 'icon' => 'compass' ),
			array( 'label' => __( 'Our CEO', 'jbnewgen' ), 'href' => '/about/ceo', 'desc' => 'Joyjeet Bose', 'icon' => 'users' ),
			array( 'label' => __( 'Customer Communication', 'jbnewgen' ), 'href' => '/services/cpaas-omnichannel', 'desc' => 'WhatsApp, SMS, voice', 'icon' => 'chat' ),
			array( 'label' => __( 'Insights', 'jbnewgen' ), 'href' => '/insights', 'desc' => __( 'Articles & categories', 'jbnewgen' ), 'icon' => 'bulb' ),
			array( 'label' => __( 'Careers', 'jbnewgen' ), 'href' => '/careers', 'desc' => __( 'Open roles', 'jbnewgen' ), 'icon' => 'briefcase' ),
			array( 'label' => __( 'Get a quote', 'jbnewgen' ), 'href' => '/quote', 'desc' => __( 'Start a project', 'jbnewgen' ), 'icon' => 'spark' ),
		),
	) );
	?>
<?php endif; ?>

<?php jbnewgen_next_step(); ?>

<?php get_footer(); ?>
