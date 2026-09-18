<?php
/**
 * The chart block — parity with Payload's ChartBlock.
 *
 * `Insights.ts` embeds a Lexical block into the article body:
 *
 *   ChartBlock { kind: bar|line|area|pie, title, data (json), caption }
 *
 * and `src/components/insights/ChartRenderer.tsx` draws it with Recharts. This
 * is the same four fields and the same output, as a Gutenberg block.
 *
 * Two deliberate differences from the Next.js original:
 *
 *   no library    Recharts is 200KB and a build step, and this theme has
 *                 neither. The dashboard already draws its charts as hand-cut
 *                 SVG for the same reason (inc/admin-dashboard.php); this is
 *                 that decision applied again, not a new one.
 *   `data` is a   Payload has a real `json` field type. A block attribute is a
 *   string        string, and keeping it one means a half-typed array is still
 *                 there when you come back to it rather than being thrown away
 *                 as invalid on every keystroke. It is parsed at render.
 *
 * The palette, the geometry and the proportions are read from ChartRenderer.tsx
 * rather than chosen here — flame #EA832E, the five-step pie ramp, the dashed
 * horizontal-only grid, the 50/92 donut, the 4px bar radius, the 0.15 area
 * fill. An article that moves across should look identical.
 */

/** Brand orange. Same value ChartRenderer.tsx resolves --color-flame-500 to. */
const JBNEWGEN_CHART_FLAME = '#EA832E';

/** ChartRenderer.tsx PIE_COLORS, in order. */
function jbnewgen_chart_pie_colors() {
	return array( '#EA832E', '#f28f3d', '#d56f1b', '#64748b', '#94a3b8' );
}

/**
 * The four kinds, and what to call them. Labels match the Payload select.
 *
 * @return array<string,string>
 */
function jbnewgen_chart_kinds() {
	return array(
		'bar'  => __( 'Bar', 'jbnewgen' ),
		'line' => __( 'Line', 'jbnewgen' ),
		'area' => __( 'Area', 'jbnewgen' ),
		'pie'  => __( 'Pie', 'jbnewgen' ),
	);
}

/*
 * The stylesheet, in all three places it is needed.
 *
 * `add_editor_style()` is the only one of these that reaches inside the block
 * editor's canvas iframe, and it takes a path relative to the theme rather
 * than a URL. The admin enqueue covers the editing panel, which React renders
 * outside the canvas. The front-end enqueue is what will paint a published
 * article; it is registered now so the block is complete rather than waiting
 * on templates that do not exist yet.
 */
add_action( 'after_setup_theme', function () {
	add_editor_style( 'assets/block-chart.css' );
} );

add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_style(
		'jbnewgen-chart',
		get_template_directory_uri() . '/assets/block-chart.css',
		array(),
		filemtime( get_template_directory() . '/assets/block-chart.css' )
	);
} );

add_action( 'wp_enqueue_scripts', function () {
	if ( has_block( 'jbnewgen/chart' ) ) {
		wp_enqueue_style(
			'jbnewgen-chart',
			get_template_directory_uri() . '/assets/block-chart.css',
			array(),
			filemtime( get_template_directory() . '/assets/block-chart.css' )
		);
	}
} );

add_action( 'init', function () {
	$dir = get_template_directory();
	$uri = get_template_directory_uri();

	wp_register_script(
		'jbnewgen-chart-block',
		$uri . '/assets/block-chart.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ),
		filemtime( $dir . '/assets/block-chart.js' ),
		true
	);

	// The kind list is defined once, in PHP, and handed to the editor — so
	// adding a fifth kind is one edit and not two that can disagree.
	wp_localize_script( 'jbnewgen-chart-block', 'jbChartBlock', array(
		'kinds'   => jbnewgen_chart_kinds(),
		'example' => "[\n  { \"label\": \"2024\", \"value\": 120 },\n  { \"label\": \"2025\", \"value\": 260 }\n]",
	) );

	register_block_type( 'jbnewgen/chart', array(
		'api_version'     => 3,
		'title'           => __( 'Chart / Graph', 'jbnewgen' ),
		'description'     => __( 'A bar, line, area or pie chart inside the article.', 'jbnewgen' ),
		'category'        => 'media',
		'icon'            => 'chart-bar',
		'editor_script'   => 'jbnewgen-chart-block',
		'render_callback' => 'jbnewgen_render_chart_block',
		'attributes'      => array(
			'kind'    => array( 'type' => 'string', 'default' => 'bar' ),
			'title'   => array( 'type' => 'string', 'default' => '' ),
			'data'    => array( 'type' => 'string', 'default' => '' ),
			'caption' => array( 'type' => 'string', 'default' => '' ),
		),
	) );
} );

/**
 * Parse the `data` attribute into rows.
 *
 * Anything that is not an array of objects carrying a numeric `value` is
 * dropped rather than guessed at — a chart drawn from misread data is worse
 * than a chart that says it cannot be drawn.
 *
 * @param string $json
 * @return array<int,array{label:string,value:float}>
 */
function jbnewgen_chart_rows( $json ) {
	$decoded = json_decode( (string) $json, true );

	if ( ! is_array( $decoded ) ) {
		return array();
	}

	$rows = array();
	foreach ( $decoded as $row ) {
		if ( ! is_array( $row ) || ! isset( $row['value'] ) || ! is_numeric( $row['value'] ) ) {
			continue;
		}
		$rows[] = array(
			'label' => isset( $row['label'] ) ? (string) $row['label'] : '',
			'value' => (float) $row['value'],
		);
	}

	return $rows;
}

/**
 * Render.
 *
 * @param array $attributes
 * @return string
 */
function jbnewgen_render_chart_block( $attributes ) {
	$attributes = wp_parse_args( $attributes, array(
		'kind'    => 'bar',
		'title'   => '',
		'data'    => '',
		'caption' => '',
	) );

	$kind = isset( jbnewgen_chart_kinds()[ $attributes['kind'] ] ) ? $attributes['kind'] : 'bar';
	$rows = jbnewgen_chart_rows( $attributes['data'] );

	ob_start();
	?>
	<figure class="jb-chartblock jb-chartblock--<?php echo esc_attr( $kind ); ?>">
		<?php if ( '' !== trim( (string) $attributes['title'] ) ) : ?>
			<figcaption class="jb-chartblock__title"><?php echo esc_html( $attributes['title'] ); ?></figcaption>
		<?php endif; ?>

		<div class="jb-chartblock__plot">
			<?php
			if ( ! $rows ) {
				printf(
					'<p class="jb-chartblock__empty">%s</p>',
					esc_html__( 'No data yet. Paste an array of {"label": …, "value": …} entries.', 'jbnewgen' )
				);
			} elseif ( 'pie' === $kind ) {
				echo jbnewgen_chart_svg_pie( $rows );   // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped numbers below
			} else {
				echo jbnewgen_chart_svg_cartesian( $rows, $kind ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</div>

		<?php if ( '' !== trim( (string) $attributes['caption'] ) ) : ?>
			<figcaption class="jb-chartblock__caption"><?php echo esc_html( $attributes['caption'] ); ?></figcaption>
		<?php endif; ?>
	</figure>
	<?php
	return (string) ob_get_clean();
}

/**
 * Bar, line and area share an axis frame, so they share a function — the only
 * thing that differs is what is drawn inside the plot rectangle.
 *
 * @param array<int,array{label:string,value:float}> $rows
 * @param string                                     $kind bar|line|area
 * @return string
 */
function jbnewgen_chart_svg_cartesian( $rows, $kind ) {
	$w   = 720;
	$h   = 288;                                     // h-72, the height of the card in ChartRenderer.tsx
	$pad = array( 'l' => 44, 'r' => 14, 't' => 14, 'b' => 34 );

	$plot_w = $w - $pad['l'] - $pad['r'];
	$plot_h = $h - $pad['t'] - $pad['b'];

	$values = wp_list_pluck( $rows, 'value' );
	$max    = max( $values );
	$min    = min( 0, min( $values ) );             // a negative series still touches the baseline
	$span   = ( $max - $min ) > 0 ? ( $max - $min ) : 1;

	$y_of = static function ( $value ) use ( $pad, $plot_h, $min, $span ) {
		return round( $pad['t'] + $plot_h - ( ( $value - $min ) / $span ) * $plot_h, 2 );
	};

	$count = count( $rows );

	// Four gridlines including the baseline, rounded so the labels are whole
	// numbers rather than 3.3333.
	$ticks = array();
	for ( $i = 0; $i <= 3; $i++ ) {
		$value   = $min + ( $span * ( $i / 3 ) );
		$ticks[] = array( 'v' => $value, 'y' => $y_of( $value ) );
	}

	ob_start();
	?>
	<svg class="jb-chartblock__svg" viewBox="0 0 <?php echo esc_attr( $w . ' ' . $h ); ?>"
		role="img" preserveAspectRatio="none"
		aria-label="<?php esc_attr_e( 'Chart', 'jbnewgen' ); ?>">

		<?php foreach ( $ticks as $tick ) : ?>
			<line class="jb-chartblock__grid"
				x1="<?php echo esc_attr( $pad['l'] ); ?>" x2="<?php echo esc_attr( $w - $pad['r'] ); ?>"
				y1="<?php echo esc_attr( $tick['y'] ); ?>" y2="<?php echo esc_attr( $tick['y'] ); ?>" />
			<text class="jb-chartblock__tick" x="<?php echo esc_attr( $pad['l'] - 8 ); ?>"
				y="<?php echo esc_attr( $tick['y'] + 4 ); ?>" text-anchor="end">
				<?php echo esc_html( number_format_i18n( round( $tick['v'], 2 ) ) ); ?>
			</text>
		<?php endforeach; ?>

		<?php if ( 'bar' === $kind ) : ?>
			<?php
			// Slot width per row, with the bar taking 62% of it — the gap is
			// what makes a bar chart readable as separate quantities.
			$slot  = $plot_w / max( 1, $count );
			$bar_w = max( 2, $slot * 0.62 );
			$base  = $y_of( max( 0, $min ) );
			?>
			<?php foreach ( $rows as $i => $row ) : ?>
				<?php
				$x    = $pad['l'] + ( $i * $slot ) + ( ( $slot - $bar_w ) / 2 );
				$y    = $y_of( $row['value'] );
				$hgt  = max( 1, abs( $base - $y ) );
				$top  = min( $y, $base );
				?>
				<rect class="jb-chartblock__bar" rx="4"
					x="<?php echo esc_attr( round( $x, 2 ) ); ?>" y="<?php echo esc_attr( round( $top, 2 ) ); ?>"
					width="<?php echo esc_attr( round( $bar_w, 2 ) ); ?>" height="<?php echo esc_attr( round( $hgt, 2 ) ); ?>">
					<title><?php echo esc_html( $row['label'] . ' — ' . number_format_i18n( $row['value'] ) ); ?></title>
				</rect>
			<?php endforeach; ?>

		<?php else : ?>
			<?php
			// A single point has no line to draw between anything, so the step
			// falls back to the full width and the point sits at the left edge.
			$step   = $count > 1 ? ( $plot_w / ( $count - 1 ) ) : $plot_w;
			$points = array();
			foreach ( $rows as $i => $row ) {
				$points[] = round( $pad['l'] + ( $i * $step ), 2 ) . ',' . $y_of( $row['value'] );
			}
			$line = implode( ' ', $points );
			?>
			<?php if ( 'area' === $kind ) : ?>
				<?php
				$first = explode( ',', $points[0] )[0];
				$last  = explode( ',', $points[ count( $points ) - 1 ] )[0];
				$floor = $h - $pad['b'];
				?>
				<path class="jb-chartblock__area"
					d="<?php echo esc_attr( 'M' . $first . ',' . $floor . ' L' . implode( ' L', $points ) . ' L' . $last . ',' . $floor . ' Z' ); ?>" />
			<?php endif; ?>

			<polyline class="jb-chartblock__line" points="<?php echo esc_attr( $line ); ?>" />

			<?php foreach ( $rows as $i => $row ) : ?>
				<circle class="jb-chartblock__dot" r="3"
					cx="<?php echo esc_attr( round( $pad['l'] + ( $i * $step ), 2 ) ); ?>"
					cy="<?php echo esc_attr( $y_of( $row['value'] ) ); ?>">
					<title><?php echo esc_html( $row['label'] . ' — ' . number_format_i18n( $row['value'] ) ); ?></title>
				</circle>
			<?php endforeach; ?>
		<?php endif; ?>
	</svg>

	<div class="jb-chartblock__axis">
		<?php foreach ( $rows as $row ) : ?>
			<span class="jb-chartblock__label"><?php echo esc_html( $row['label'] ); ?></span>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * The donut. Same 50 / 92 radii and 2° gap as ChartRenderer.tsx.
 *
 * Drawn with arc paths rather than stroke-dasharray on a circle: the gap
 * between segments has to be an angle, not a length, or it changes width with
 * the size of the slice.
 *
 * @param array<int,array{label:string,value:float}> $rows
 * @return string
 */
function jbnewgen_chart_svg_pie( $rows ) {
	$total = 0;
	foreach ( $rows as $row ) {
		$total += max( 0, $row['value'] );
	}
	if ( $total <= 0 ) {
		return '<p class="jb-chartblock__empty">' . esc_html__( 'Every value is zero, so there is no pie to draw.', 'jbnewgen' ) . '</p>';
	}

	$size   = 288;
	$cx     = $size / 2;
	$cy     = $size / 2;
	$outer  = 92;
	$inner  = 50;
	$gap    = 2;       // degrees
	$colors = jbnewgen_chart_pie_colors();

	$angle  = -90;     // twelve o'clock
	$slices = array();

	foreach ( $rows as $i => $row ) {
		$value = max( 0, $row['value'] );
		if ( $value <= 0 ) {
			continue;
		}

		$sweep = ( $value / $total ) * 360;
		$start = $angle + ( $gap / 2 );
		$end   = $angle + $sweep - ( $gap / 2 );
		$angle += $sweep;

		if ( $end <= $start ) {
			continue;   // a slice thinner than the gap between slices
		}

		$large = ( $end - $start ) > 180 ? 1 : 0;

		$x1 = $cx + $outer * cos( deg2rad( $start ) );
		$y1 = $cy + $outer * sin( deg2rad( $start ) );
		$x2 = $cx + $outer * cos( deg2rad( $end ) );
		$y2 = $cy + $outer * sin( deg2rad( $end ) );
		$x3 = $cx + $inner * cos( deg2rad( $end ) );
		$y3 = $cy + $inner * sin( deg2rad( $end ) );
		$x4 = $cx + $inner * cos( deg2rad( $start ) );
		$y4 = $cy + $inner * sin( deg2rad( $start ) );

		$slices[] = array(
			'd' => sprintf(
				'M%s,%s A%s,%s 0 %d 1 %s,%s L%s,%s A%s,%s 0 %d 0 %s,%s Z',
				round( $x1, 2 ), round( $y1, 2 ), $outer, $outer, $large, round( $x2, 2 ), round( $y2, 2 ),
				round( $x3, 2 ), round( $y3, 2 ), $inner, $inner, $large, round( $x4, 2 ), round( $y4, 2 )
			),
			'fill'  => $colors[ $i % count( $colors ) ],
			'label' => $row['label'],
			'value' => $row['value'],
			'pct'   => round( ( $value / $total ) * 100 ),
		);
	}

	ob_start();
	?>
	<div class="jb-chartblock__pie">
		<svg class="jb-chartblock__svg jb-chartblock__svg--pie"
			viewBox="0 0 <?php echo esc_attr( $size . ' ' . $size ); ?>" role="img"
			aria-label="<?php esc_attr_e( 'Pie chart', 'jbnewgen' ); ?>">
			<?php foreach ( $slices as $slice ) : ?>
				<path d="<?php echo esc_attr( $slice['d'] ); ?>" fill="<?php echo esc_attr( $slice['fill'] ); ?>">
					<title><?php echo esc_html( $slice['label'] . ' — ' . number_format_i18n( $slice['value'] ) . ' (' . $slice['pct'] . '%)' ); ?></title>
				</path>
			<?php endforeach; ?>
		</svg>

		<ul class="jb-chartblock__key">
			<?php foreach ( $slices as $slice ) : ?>
				<li>
					<span class="jb-chartblock__swatch" style="background: <?php echo esc_attr( $slice['fill'] ); ?>"></span>
					<span class="jb-chartblock__key-label"><?php echo esc_html( $slice['label'] ); ?></span>
					<span class="jb-chartblock__key-value"><?php echo esc_html( number_format_i18n( $slice['value'] ) ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
	return (string) ob_get_clean();
}
