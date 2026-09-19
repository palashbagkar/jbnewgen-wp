<?php
/**
 * Shared markup for the four small nav components every non-homepage template
 * reuses: Breadcrumbs, PageHeader, NavGrid, NextStep (CONTEXT.md §6 "nav/").
 * Ported verbatim from ../jbnewgen/src/components/nav/*.tsx. Factored out here
 * -- rather than re-inlined per page like front-page.php's sections -- because
 * every page from here on (services, insights, careers, contact) uses all
 * four, so inlining would mean four copies of the same markup times a dozen
 * templates instead of one.
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param array $trail [ ['label'=>.., 'href'=>..], ... ]
 */
function jbnewgen_breadcrumbs( $trail ) {
	?>
	<nav aria-label="<?php esc_attr_e( 'Breadcrumb', 'jbnewgen' ); ?>">
		<ol class="flex flex-wrap items-center gap-1.5 text-sm">
			<?php foreach ( $trail as $jb_i => $jb_c ) : ?>
				<?php $jb_last = $jb_i === count( $trail ) - 1; ?>
				<li class="flex items-center gap-1.5">
					<?php if ( $jb_i > 0 ) : ?>
						<?php jbnewgen_icon( 'chevronDown', 14, '-rotate-90 text-ink-300' ); ?>
					<?php endif; ?>
					<?php if ( $jb_last ) : ?>
						<span class="font-medium text-ink-500" aria-current="page"><?php echo esc_html( $jb_c['label'] ); ?></span>
					<?php else : ?>
						<a href="<?php echo esc_url( jbnewgen_href( $jb_c['href'] ) ); ?>" class="text-ink-400 transition-colors hover:text-flame-600"><?php echo esc_html( $jb_c['label'] ); ?></a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	</nav>
	<?php
}

/**
 * @param array $args eyebrow, title, trail, note
 *
 * `eyebrow` is accepted and intentionally ignored: PageHeader.tsx still
 * declares the prop in its type but its function body never renders it --
 * a dead prop left over from a past refactor. Every caller in
 * ../jbnewgen/src still passes one, and the live site never shows it, so
 * this matches production rather than the stale type signature.
 */
function jbnewgen_page_header( $args ) {
	$title = isset( $args['title'] ) ? $args['title'] : '';
	$trail = isset( $args['trail'] ) ? $args['trail'] : array();
	$note  = isset( $args['note'] ) ? $args['note'] : '';
	?>
	<header class="relative overflow-hidden border-b border-ink-100 bg-ink-50/40">
		<div class="dot-grid pointer-events-none absolute inset-0 opacity-[0.3]"></div>
		<div class="pointer-events-none absolute -top-24 right-[-6rem] h-72 w-72 rounded-[7px] bg-flame-500/10 blur-3xl"></div>

		<div class="relative mx-auto max-w-7xl px-5 pb-14 pt-10 sm:px-8 sm:pb-16 sm:pt-14">
			<?php if ( $trail ) : ?>
				<div class="mb-6"><?php jbnewgen_breadcrumbs( $trail ); ?></div>
			<?php endif; ?>
			<h1 class="text-balance text-4xl font-bold tracking-tight text-ink-900 sm:text-5xl md:text-[3.4rem] md:leading-[1.05]"><?php echo esc_html( $title ); ?></h1>
			<?php if ( $note ) : ?>
				<p class="mt-5 max-w-2xl text-pretty text-base text-ink-500 sm:text-lg"><?php echo esc_html( $note ); ?></p>
			<?php endif; ?>
		</div>
	</header>
	<?php
}

/**
 * @param array $args title, caption, items [ ['label','href','desc','icon','external'] ], columns
 */
function jbnewgen_nav_grid( $args ) {
	$title   = isset( $args['title'] ) ? $args['title'] : '';
	$caption = isset( $args['caption'] ) ? $args['caption'] : '';
	$items   = isset( $args['items'] ) ? $args['items'] : array();
	$columns = isset( $args['columns'] ) ? (int) $args['columns'] : 3;
	$cols    = array(
		1 => 'sm:grid-cols-1',
		2 => 'sm:grid-cols-2',
		3 => 'sm:grid-cols-2 lg:grid-cols-3',
		4 => 'sm:grid-cols-2 lg:grid-cols-4',
	);
	?>
	<section class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-16">
		<?php if ( $title || $caption ) : ?>
			<div class="mb-8 flex flex-wrap items-end justify-between gap-3">
				<div>
					<?php if ( $title ) : ?><h2 class="text-xl font-bold tracking-tight text-ink-900 sm:text-2xl"><?php echo esc_html( $title ); ?></h2><?php endif; ?>
					<?php if ( $caption ) : ?><p class="mt-1 text-sm text-ink-500"><?php echo esc_html( $caption ); ?></p><?php endif; ?>
				</div>
				<span class="font-mono text-xs uppercase tracking-widest text-ink-400"><?php echo (int) count( $items ); ?> <?php echo 1 === count( $items ) ? esc_html__( 'link', 'jbnewgen' ) : esc_html__( 'links', 'jbnewgen' ); ?></span>
			</div>
		<?php endif; ?>

		<div class="grid grid-cols-1 gap-4 <?php echo esc_attr( isset( $cols[ $columns ] ) ? $cols[ $columns ] : $cols[3] ); ?>">
			<?php foreach ( $items as $jb_item ) : ?>
				<?php
				$jb_external = ! empty( $jb_item['external'] );
				$jb_icon     = ! empty( $jb_item['icon'] ) ? $jb_item['icon'] : 'arrowUpRight';
				?>
				<a href="<?php echo esc_url( $jb_external ? $jb_item['href'] : jbnewgen_href( $jb_item['href'] ) ); ?>" <?php echo $jb_external ? 'target="_blank" rel="noreferrer"' : ''; ?> class="group relative flex h-full items-start gap-4 overflow-hidden rounded-2xl border border-ink-100 bg-white p-5 transition-all duration-300 hover:-translate-y-1 hover:border-transparent hover:shadow-[0_24px_50px_-28px_rgba(8,21,39,0.45)]">
					<span class="pointer-events-none absolute inset-x-0 top-0 h-0.5 origin-left scale-x-0 bg-flame-500 transition-transform duration-300 group-hover:scale-x-100"></span>
					<span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-flame-500/10 text-flame-600 ring-1 ring-inset ring-flame-500/15"><?php jbnewgen_icon( $jb_icon, 20 ); ?></span>
					<span class="min-w-0 flex-1">
						<span class="flex items-center gap-1.5 font-semibold text-ink-900">
							<?php echo esc_html( $jb_item['label'] ); ?>
							<?php jbnewgen_icon( $jb_external ? 'arrowUpRight' : 'arrowRight', 15, 'text-flame-500 opacity-0 transition-all duration-200 group-hover:translate-x-0.5 group-hover:opacity-100' ); ?>
						</span>
						<?php if ( ! empty( $jb_item['desc'] ) ) : ?><span class="mt-1 block text-sm text-ink-500"><?php echo esc_html( $jb_item['desc'] ); ?></span><?php endif; ?>
						<span class="mt-2 block truncate font-mono text-[0.7rem] text-ink-300"><?php echo esc_html( $jb_item['href'] ); ?></span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
}

/**
 * @param string $heading
 * @param array  $actions [ ['label','href','icon','variant'] ]
 */
function jbnewgen_next_step( $heading = null, $actions = null ) {
	if ( null === $heading ) {
		$heading = __( 'No page is a dead end.', 'jbnewgen' );
	}
	if ( null === $actions ) {
		$actions = array(
			array( 'label' => __( 'Get a quote', 'jbnewgen' ), 'href' => '/quote', 'variant' => 'primary' ),
			array( 'label' => __( 'Contact us', 'jbnewgen' ), 'href' => '/contact', 'variant' => 'light', 'icon' => 'arrowUpRight' ),
		);
	}
	?>
	<section class="px-5 pb-20 pt-4 sm:px-8">
		<div class="relative mx-auto flex max-w-7xl flex-col items-start gap-6 overflow-hidden rounded-3xl bg-ink-950 px-6 py-10 sm:flex-row sm:items-center sm:justify-between sm:px-12 sm:py-12" data-reveal>
			<div class="mesh pointer-events-none absolute inset-0 opacity-60"></div>
			<div class="pointer-events-none absolute -right-10 -top-10 h-48 w-48 rounded-[7px] bg-flame-500/20 blur-3xl"></div>
			<div class="relative">
				<p class="font-mono text-xs uppercase tracking-widest text-flame-400"><?php esc_html_e( 'Conversion rail', 'jbnewgen' ); ?></p>
				<h2 class="mt-2 text-2xl font-bold text-white sm:text-3xl"><?php echo esc_html( $heading ); ?></h2>
			</div>
			<div class="relative flex flex-wrap gap-3">
				<?php foreach ( $actions as $jb_a ) : ?>
					<?php
					jbnewgen_button(
						$jb_a['label'],
						jbnewgen_href( $jb_a['href'] ),
						array(
							'variant' => isset( $jb_a['variant'] ) ? $jb_a['variant'] : 'primary',
							'icon'    => isset( $jb_a['icon'] ) ? $jb_a['icon'] : '',
						)
					);
					?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}
