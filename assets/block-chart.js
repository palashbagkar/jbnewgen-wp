/**
 * Editor side of the chart block. See inc/block-chart.php for the render.
 *
 * Written against wp.element.createElement rather than JSX because this theme
 * has no build step — the same reason the charts are hand-cut SVG rather than
 * Recharts. `el` below is that function under a shorter name.
 *
 * ---------------------------------------------------------------------------
 * WHY THIS IS NOT A JSON FIELD ANY MORE
 *
 * The first version handed the writer Payload's own control: a textarea and
 * "paste an array of {label, value}". That is a developer's field. It asks a
 * marketing lead to hand-write a data structure, punishes a missing comma with
 * a blank chart, and gives no way to reorder a row or see a total.
 *
 * What replaced it is a table — a row per data point, with the number in a
 * number field and the label in a text field — plus the one shortcut that
 * matters on this project: the numbers already exist in a spreadsheet (see
 * ../jbnewgen/UPDATE/, which ships an .xlsx), so a paste from Excel or Sheets
 * fills the whole table in one action.
 *
 * The STORAGE has not changed. The attribute is still the same JSON string, so
 * an article written before this editor existed opens in it unchanged, and the
 * shape still matches Payload's `data` field for the migration.
 * ---------------------------------------------------------------------------
 */
( function ( blocks, element, blockEditor, components, serverSideRender, i18n ) {
	'use strict';

	var el       = element.createElement;
	var Fragment = element.Fragment;
	var useState = element.useState;
	var __       = i18n.__;
	var config   = window.jbChartBlock || { kinds: {}, example: '' };

	/* ---------------------------------------------------------------- data */

	/**
	 * The attribute is a JSON string; the editor works in rows. These two are
	 * the only places that conversion happens.
	 */
	function toRows( raw ) {
		if ( ! raw || ! raw.trim() ) { return []; }
		var parsed;
		try { parsed = JSON.parse( raw ); } catch ( e ) { return []; }
		if ( ! Array.isArray( parsed ) ) { return []; }

		return parsed.map( function ( row ) {
			if ( ! row || typeof row !== 'object' ) { return { label: '', value: '' }; }
			return {
				label: row.label === undefined || row.label === null ? '' : String( row.label ),
				value: row.value === undefined || row.value === null ? '' : String( row.value )
			};
		} );
	}

	/**
	 * Rows out, JSON in. A row with a blank value is kept in the editor — you
	 * are allowed to be halfway through typing — but written as null so the
	 * renderer skips it rather than plotting a zero that was never entered.
	 */
	function toJson( rows ) {
		return JSON.stringify( rows.map( function ( r ) {
			var n = parseFloat( r.value );
			return { label: r.label, value: isNaN( n ) ? null : n };
		} ), null, 2 );
	}

	/**
	 * A paste out of a spreadsheet.
	 *
	 * Excel and Sheets both put tab-separated text on the clipboard; a CSV
	 * export uses commas. Both are handled, and a header row is dropped when
	 * its second cell is not a number — which is what a header looks like.
	 */
	function parsePaste( text ) {
		var lines = String( text ).replace( /\r/g, '' ).split( '\n' ).filter( function ( l ) {
			return l.trim() !== '';
		} );
		if ( ! lines.length ) { return null; }

		var rows = lines.map( function ( line ) {
			var cells = line.indexOf( '\t' ) > -1 ? line.split( '\t' ) : line.split( ',' );
			return {
				label: ( cells[0] || '' ).trim().replace( /^"|"$/g, '' ),
				// Strip the things spreadsheets add and numbers do not have:
				// thousands separators, currency symbols, trailing percent.
				value: ( cells[1] || '' ).trim().replace( /^"|"$/g, '' ).replace( /[^0-9.\-]/g, '' )
			};
		} );

		if ( rows.length > 1 && isNaN( parseFloat( rows[0].value ) ) ) { rows.shift(); }

		return rows.length ? rows : null;
	}

	/* --------------------------------------------------------------- pieces */

	/** A small glyph per chart kind, so the picker shows shapes not words. */
	function kindGlyph( kind ) {
		var common = { width: 22, height: 16, viewBox: '0 0 22 16', 'aria-hidden': 'true', focusable: 'false' };

		if ( kind === 'line' || kind === 'area' ) {
			return el( 'svg', common,
				kind === 'area'
					? el( 'path', { d: 'M1 15 L1 10 L7 6 L13 9 L21 2 L21 15 Z', fill: 'currentColor', opacity: 0.25, stroke: 'none' } )
					: null,
				el( 'polyline', { points: '1,10 7,6 13,9 21,2', fill: 'none', stroke: 'currentColor', strokeWidth: 1.6, strokeLinejoin: 'round', strokeLinecap: 'round' } )
			);
		}

		if ( kind === 'pie' ) {
			return el( 'svg', common,
				el( 'circle', { cx: 11, cy: 8, r: 6.4, fill: 'none', stroke: 'currentColor', strokeWidth: 1.6 } ),
				el( 'path', { d: 'M11 8 L11 1.6 A6.4 6.4 0 0 1 17 10 Z', fill: 'currentColor', stroke: 'none' } )
			);
		}

		return el( 'svg', common,
			el( 'rect', { x: 1.5, y: 9,   width: 4, height: 6,  fill: 'currentColor' } ),
			el( 'rect', { x: 8.5, y: 5,   width: 4, height: 10, fill: 'currentColor' } ),
			el( 'rect', { x: 15.5, y: 1.5, width: 4, height: 13.5, fill: 'currentColor' } )
		);
	}

	/**
	 * What the table has to say about itself: how many points are plottable,
	 * the total, and the range. Shown under the table because a chart is a
	 * claim about numbers and the numbers should be checkable without
	 * squinting at the picture.
	 */
	function summarise( rows ) {
		var nums = rows.map( function ( r ) { return parseFloat( r.value ); } )
			.filter( function ( n ) { return ! isNaN( n ); } );

		if ( ! nums.length ) { return null; }

		var total = nums.reduce( function ( a, b ) { return a + b; }, 0 );
		return {
			count: nums.length,
			skipped: rows.length - nums.length,
			total: total,
			min: Math.min.apply( null, nums ),
			max: Math.max.apply( null, nums )
		};
	}

	function num( n ) {
		return Math.round( n * 100 ) / 100;
	}

	/* ----------------------------------------------------------------- edit */

	blocks.registerBlockType( 'jbnewgen/chart', {
		edit: function ( props ) {
			var a    = props.attributes;
			var rows = toRows( a.data );

			var pasteState = useState( '' );
			var pasteNote  = pasteState[0];
			var setPasteNote = pasteState[1];

			function setRows( next ) {
				props.setAttributes( { data: toJson( next ) } );
			}

			function setCell( i, key, value ) {
				var next = rows.slice();
				next[ i ] = { label: next[ i ].label, value: next[ i ].value };
				next[ i ][ key ] = value;
				setRows( next );
			}

			function addRow() {
				setRows( rows.concat( [ { label: '', value: '' } ] ) );
			}

			function removeRow( i ) {
				setRows( rows.filter( function ( _, j ) { return j !== i; } ) );
			}

			function move( i, by ) {
				var j = i + by;
				if ( j < 0 || j >= rows.length ) { return; }
				var next = rows.slice();
				var tmp  = next[ i ];
				next[ i ] = next[ j ];
				next[ j ] = tmp;
				setRows( next );
			}

			function sortBy( key, dir ) {
				var next = rows.slice().sort( function ( x, y ) {
					if ( key === 'value' ) {
						var a1 = parseFloat( x.value ); var b1 = parseFloat( y.value );
						if ( isNaN( a1 ) ) { return 1; }
						if ( isNaN( b1 ) ) { return -1; }
						return dir * ( a1 - b1 );
					}
					return dir * String( x.label ).localeCompare( String( y.label ) );
				} );
				setRows( next );
			}

			function onPaste( event ) {
				var text = ( event.clipboardData || window.clipboardData ).getData( 'text' );
				if ( ! text || text.indexOf( '\n' ) === -1 && text.indexOf( '\t' ) === -1 ) {
					return;   // a single cell — let the field handle it normally
				}

				var parsed = parsePaste( text );
				if ( ! parsed ) { return; }

				event.preventDefault();
				setRows( parsed );
				setPasteNote(
					parsed.length + __( ' rows pasted, replacing what was here.', 'jbnewgen' )
				);
				window.setTimeout( function () { setPasteNote( '' ); }, 6000 );
			}

			var stats = summarise( rows );

			/* --- the type picker --------------------------------------- */
			var picker = el( 'div', { className: 'jb-chartedit__kinds', role: 'radiogroup', 'aria-label': __( 'Chart type', 'jbnewgen' ) },
				Object.keys( config.kinds ).map( function ( kind ) {
					return el( 'button', {
						key: kind,
						type: 'button',
						role: 'radio',
						'aria-checked': a.kind === kind ? 'true' : 'false',
						className: 'jb-chartedit__kind' + ( a.kind === kind ? ' is-current' : '' ),
						onClick: function () { props.setAttributes( { kind: kind } ); }
					},
						kindGlyph( kind ),
						el( 'span', null, config.kinds[ kind ] )
					);
				} )
			);

			/* --- the table ---------------------------------------------- */
			var table = el( 'table', { className: 'jb-chartedit__table' },
				el( 'thead', null,
					el( 'tr', null,
						el( 'th', { className: 'jb-chartedit__n' }, '#' ),
						el( 'th', null,
							__( 'Label', 'jbnewgen' ),
							el( 'span', { className: 'jb-chartedit__sorts' },
								el( 'button', { type: 'button', onClick: function () { sortBy( 'label', 1 ); }, title: __( 'Sort A–Z', 'jbnewgen' ) }, '↑' ),
								el( 'button', { type: 'button', onClick: function () { sortBy( 'label', -1 ); }, title: __( 'Sort Z–A', 'jbnewgen' ) }, '↓' )
							)
						),
						el( 'th', { className: 'jb-chartedit__v' },
							__( 'Value', 'jbnewgen' ),
							el( 'span', { className: 'jb-chartedit__sorts' },
								el( 'button', { type: 'button', onClick: function () { sortBy( 'value', 1 ); }, title: __( 'Smallest first', 'jbnewgen' ) }, '↑' ),
								el( 'button', { type: 'button', onClick: function () { sortBy( 'value', -1 ); }, title: __( 'Largest first', 'jbnewgen' ) }, '↓' )
							)
						),
						el( 'th', { className: 'jb-chartedit__acts' }, el( 'span', { className: 'screen-reader-text' }, __( 'Row actions', 'jbnewgen' ) ) )
					)
				),
				el( 'tbody', null,
					rows.length
						? rows.map( function ( row, i ) {
							var bad = row.value !== '' && isNaN( parseFloat( row.value ) );
							return el( 'tr', { key: i, className: bad ? 'is-bad' : '' },
								el( 'td', { className: 'jb-chartedit__n' }, String( i + 1 ) ),
								el( 'td', null,
									el( 'input', {
										type: 'text',
										value: row.label,
										placeholder: __( 'e.g. 2025', 'jbnewgen' ),
										onChange: function ( e ) { setCell( i, 'label', e.target.value ); },
										onPaste: onPaste,
										'aria-label': __( 'Label for row ', 'jbnewgen' ) + ( i + 1 )
									} )
								),
								el( 'td', { className: 'jb-chartedit__v' },
									el( 'input', {
										type: 'text',
										inputMode: 'decimal',
										value: row.value,
										placeholder: '0',
										onChange: function ( e ) { setCell( i, 'value', e.target.value ); },
										onPaste: onPaste,
										'aria-label': __( 'Value for row ', 'jbnewgen' ) + ( i + 1 )
									} )
								),
								el( 'td', { className: 'jb-chartedit__acts' },
									el( 'button', { type: 'button', onClick: function () { move( i, -1 ); }, disabled: i === 0, title: __( 'Move up', 'jbnewgen' ) }, '↑' ),
									el( 'button', { type: 'button', onClick: function () { move( i, 1 ); }, disabled: i === rows.length - 1, title: __( 'Move down', 'jbnewgen' ) }, '↓' ),
									el( 'button', { type: 'button', className: 'is-remove', onClick: function () { removeRow( i ); }, title: __( 'Remove this row', 'jbnewgen' ) }, '×' )
								)
							);
						} )
						: el( 'tr', { className: 'jb-chartedit__blank' },
							el( 'td', { colSpan: 4 },
								__( 'No rows yet. Add one, or paste two columns straight from a spreadsheet.', 'jbnewgen' )
							)
						)
				)
			);

			/* --- the editor panel --------------------------------------- */
			var editor = el( 'div', { className: 'jb-chartedit' },

				el( 'div', { className: 'jb-chartedit__section' },
					el( 'span', { className: 'jb-chartedit__legend' }, __( 'Type', 'jbnewgen' ) ),
					picker
				),

				el( 'div', { className: 'jb-chartedit__section jb-chartedit__section--split' },
					el( 'label', null,
						el( 'span', { className: 'jb-chartedit__legend' }, __( 'Title', 'jbnewgen' ) ),
						el( 'input', {
							type: 'text',
							value: a.title,
							placeholder: __( 'Sits above the chart', 'jbnewgen' ),
							onChange: function ( e ) { props.setAttributes( { title: e.target.value } ); }
						} )
					),
					el( 'label', null,
						el( 'span', { className: 'jb-chartedit__legend' }, __( 'Caption', 'jbnewgen' ) ),
						el( 'input', {
							type: 'text',
							value: a.caption,
							placeholder: __( 'Small print underneath — a source, or what the numbers mean', 'jbnewgen' ),
							onChange: function ( e ) { props.setAttributes( { caption: e.target.value } ); }
						} )
					)
				),

				el( 'div', { className: 'jb-chartedit__section' },
					el( 'span', { className: 'jb-chartedit__legend' }, __( 'Data', 'jbnewgen' ) ),
					el( 'p', { className: 'jb-chartedit__hint' },
						__( 'One row per point. You can paste two columns — label, then number — straight out of Excel or Sheets; a header row is dropped automatically.', 'jbnewgen' )
					),
					table,

					el( 'div', { className: 'jb-chartedit__foot' },
						el( 'button', { type: 'button', className: 'jb-chartedit__add', onClick: addRow },
							'+ ', __( 'Add row', 'jbnewgen' )
						),
						stats
							? el( 'span', { className: 'jb-chartedit__stats' },
								stats.count + __( ' points', 'jbnewgen' ),
								el( 'span', { className: 'jb-chartedit__dot' }, '·' ),
								__( 'total ', 'jbnewgen' ) + num( stats.total ),
								el( 'span', { className: 'jb-chartedit__dot' }, '·' ),
								__( 'range ', 'jbnewgen' ) + num( stats.min ) + '–' + num( stats.max ),
								stats.skipped
									? el( 'span', { className: 'jb-chartedit__warn' },
										el( 'span', { className: 'jb-chartedit__dot' }, '·' ),
										stats.skipped + __( ' row(s) have no number and will be skipped', 'jbnewgen' )
									)
									: null
							)
							: el( 'span', { className: 'jb-chartedit__stats' }, __( 'Nothing to plot yet.', 'jbnewgen' ) )
					),

					pasteNote ? el( 'p', { className: 'jb-chartedit__paste' }, pasteNote ) : null
				)
			);

			return el( Fragment, null,

				el( blockEditor.BlockControls, { group: 'block' },
					el( components.ToolbarGroup, null,
						Object.keys( config.kinds ).map( function ( kind ) {
							return el( components.ToolbarButton, {
								key: kind,
								label: config.kinds[ kind ],
								isActive: a.kind === kind,
								icon: kindGlyph( kind ),
								onClick: function () { props.setAttributes( { kind: kind } ); }
							} );
						} )
					)
				),

				el( blockEditor.InspectorControls, null,
					el( components.PanelBody, { title: __( 'Chart', 'jbnewgen' ), initialOpen: true },
						el( 'p', { className: 'jb-chartedit__aside' },
							__( 'The type, the wording and the numbers are all edited on the chart itself — select it and the table appears underneath.', 'jbnewgen' )
						),
						el( components.TextareaControl, {
							label: __( 'Raw data (JSON)', 'jbnewgen' ),
							help: __( 'What is actually stored. Only worth opening if something needs pasting in wholesale.', 'jbnewgen' ),
							value: a.data,
							rows: 8,
							onChange: function ( value ) { props.setAttributes( { data: value } ); },
							__nextHasNoMarginBottom: true
						} )
					)
				),

				el( 'div', blockEditor.useBlockProps ? blockEditor.useBlockProps() : {},
					el( 'div', { className: 'jb-chartedit__preview' },
						el( serverSideRender, {
							block: 'jbnewgen/chart',
							attributes: a,
							skipBlockSupportAttributes: true
						} )
					),
					props.isSelected ? editor : null
				)
			);
		},

		// Server-rendered: nothing is written into post_content but the
		// comment delimiter and the attributes.
		save: function () {
			return null;
		}
	} );
}(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.serverSideRender,
	window.wp.i18n
) );
