<?php
/**
 * SVG uploads, sanitised.
 *
 * The live Payload admin accepts them — Media.ts declares
 * `upload: { mimeTypes: ['image/*', 'image/svg+xml'] }` — and the site needs
 * them: all three logo fields on Site Settings are described as SVG, and the
 * icon set is vector. WordPress refuses `.svg` outright, so before this file
 * the Site Settings screen offered three fields that could not be filled.
 *
 * WordPress refuses them for a real reason. An SVG is an XML document that the
 * browser executes: it can carry <script>, event-handler attributes, and
 * `javascript:` links, and it is served from the site's own origin — so an
 * uploaded SVG is a stored cross-site-scripting hole unless something strips
 * those first. "Just allow the mime type" is the advice in most snippets and
 * it is wrong.
 *
 * Two things narrow the risk here:
 *
 *   who    only `manage_options` — Owner and Administrator. The people who
 *          edit Site Settings, which is the only screen that needs an SVG.
 *          An Author uploading a photo has no use for one.
 *   what   every upload is parsed and rewritten. Anything that can execute is
 *          removed, and a file that will not parse as XML is rejected rather
 *          than stored unexamined.
 *
 * This is an allowlist, not a blocklist: the document is rebuilt from the
 * elements and attributes named below and everything else is dropped. A
 * blocklist of known-bad tags is a list somebody eventually gets past.
 */

/**
 * Who may upload one.
 */
function jbnewgen_can_upload_svg() {
	return current_user_can( 'manage_options' );
}

add_filter( 'upload_mimes', function ( $mimes ) {
	if ( jbnewgen_can_upload_svg() ) {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
	}
	return $mimes;
} );

/**
 * WordPress cross-checks the extension against what finfo says the bytes are,
 * and finfo reports an SVG as `image/svg` or `text/plain` depending on the
 * build — neither matches `image/svg+xml`, so the upload is rejected as a
 * mismatch even once the mime type is allowed. Restore the verdict for this
 * one extension only, after the extension itself has been checked.
 */
add_filter( 'wp_check_filetype_and_ext', function ( $data, $file, $filename, $mimes ) {
	if ( ! jbnewgen_can_upload_svg() ) {
		return $data;
	}

	$ext = strtolower( (string) pathinfo( $filename, PATHINFO_EXTENSION ) );
	if ( 'svg' !== $ext && 'svgz' !== $ext ) {
		return $data;
	}

	$data['ext']             = $ext;
	$data['type']            = 'image/svg+xml';
	$data['proper_filename'] = false;

	return $data;
}, 10, 4 );

/**
 * Rewrite the file before it is stored.
 *
 * Runs on `wp_handle_upload_prefilter`, which fires while the file is still in
 * PHP's temporary directory — so a file that fails to sanitise never reaches
 * wp-content/uploads at all.
 */
add_filter( 'wp_handle_upload_prefilter', function ( $file ) {
	if ( empty( $file['name'] ) ) {
		return $file;
	}

	$ext = strtolower( (string) pathinfo( $file['name'], PATHINFO_EXTENSION ) );
	if ( 'svg' !== $ext && 'svgz' !== $ext ) {
		return $file;
	}

	if ( ! jbnewgen_can_upload_svg() ) {
		$file['error'] = __( 'Only an administrator can upload SVG files.', 'jbnewgen' );
		return $file;
	}

	$clean = jbnewgen_sanitize_svg( (string) file_get_contents( $file['tmp_name'] ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	if ( null === $clean ) {
		$file['error'] = __( 'That SVG could not be read. Re-export it from your design tool and try again.', 'jbnewgen' );
		return $file;
	}

	file_put_contents( $file['tmp_name'], $clean ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

	return $file;
} );

/**
 * Elements an SVG may contain. Shapes, structure, paint and text — nothing
 * that loads, scripts or embeds.
 *
 * Absent on purpose: script, foreignObject (arbitrary HTML), image and use
 * (both can reference a remote document), animate/set (can rewrite an
 * attribute after load), style (CSS can carry url() and @import).
 *
 * @return string[]
 */
function jbnewgen_svg_allowed_elements() {
	return array(
		'svg', 'g', 'defs', 'symbol', 'title', 'desc', 'metadata',
		'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon',
		'text', 'tspan',
		'linearGradient', 'radialGradient', 'stop',
		'clipPath', 'mask', 'pattern', 'filter',
		'feGaussianBlur', 'feOffset', 'feBlend', 'feColorMatrix', 'feMerge', 'feMergeNode',
	);
}

/**
 * Attributes those elements may carry.
 *
 * Matched case-insensitively, and anything beginning `on` is refused
 * regardless — that is the whole event-handler surface in one rule.
 *
 * @return string[]
 */
function jbnewgen_svg_allowed_attributes() {
	return array(
		'id', 'class', 'viewbox', 'xmlns', 'xmlns:xlink', 'version',
		'width', 'height', 'x', 'y', 'x1', 'y1', 'x2', 'y2', 'cx', 'cy', 'r', 'rx', 'ry',
		'd', 'points', 'transform', 'preserveaspectratio',
		'fill', 'fill-rule', 'fill-opacity', 'stroke', 'stroke-width', 'stroke-linecap',
		'stroke-linejoin', 'stroke-dasharray', 'stroke-dashoffset', 'stroke-opacity',
		'opacity', 'color', 'stop-color', 'stop-opacity', 'offset',
		'font-family', 'font-size', 'font-weight', 'font-style', 'text-anchor', 'letter-spacing',
		'clip-path', 'clip-rule', 'mask', 'filter', 'gradientunits', 'gradienttransform',
		'patternunits', 'maskunits', 'maskcontentunits', 'clippathunits', 'filterunits',
		'stddeviation', 'in', 'in2', 'result', 'mode', 'type', 'values', 'dx', 'dy',
		'vector-effect', 'shape-rendering', 'aria-hidden', 'role', 'focusable',
	);
}

/**
 * Rebuild an SVG from what is allowed.
 *
 * @param string $svg raw file contents
 * @return string|null cleaned markup, or null if it is not parseable XML
 */
function jbnewgen_sanitize_svg( $svg ) {
	if ( '' === trim( $svg ) ) {
		return null;
	}

	/*
	 * A DOCTYPE can pull in an external entity before a single node is read —
	 * the parse itself is the attack. Strip it whole.
	 *
	 * `<!DOCTYPE[^>]*>` is the pattern every snippet uses and it is wrong: an
	 * internal subset carries its own `>` characters, so the match ends inside
	 * the brackets and leaves a stray `]>` behind. libxml then fails on line 2
	 * with "Start tag expected" and the file is rejected as malformed — which
	 * looks like the sanitiser working and is really the sanitiser never
	 * running. The optional `\[...\]` group is what consumes the subset.
	 */
	$svg = preg_replace( '/<!DOCTYPE[^>\[]*(\[[^\]]*\])?\s*>/is', '', $svg );
	$svg = preg_replace( '/<\?php.*?\?>/is', '', (string) $svg );

	$previous = libxml_use_internal_errors( true );

	$dom                     = new DOMDocument();
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput       = false;

	$loaded = $dom->loadXML( (string) $svg, LIBXML_NONET | LIBXML_NOENT );

	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	if ( ! $loaded || ! $dom->documentElement || 'svg' !== strtolower( $dom->documentElement->nodeName ) ) {
		return null;
	}

	$elements   = array_map( 'strtolower', jbnewgen_svg_allowed_elements() );
	$attributes = jbnewgen_svg_allowed_attributes();

	// Collected first, removed after: removing while iterating a live
	// DOMNodeList skips siblings, which is how a sanitiser quietly leaves half
	// the bad nodes in place.
	$drop = array();

	$walk = static function ( DOMNode $node ) use ( &$walk, &$drop, $elements, $attributes ) {
		foreach ( iterator_to_array( $node->childNodes ) as $child ) {
			if ( XML_ELEMENT_NODE !== $child->nodeType ) {
				if ( XML_COMMENT_NODE === $child->nodeType || XML_PI_NODE === $child->nodeType ) {
					$drop[] = $child;
				}
				continue;
			}

			if ( ! in_array( strtolower( $child->nodeName ), $elements, true ) ) {
				$drop[] = $child;
				continue;
			}

			foreach ( iterator_to_array( $child->attributes ) as $attr ) {
				$name = strtolower( $attr->nodeName );

				if ( 0 === strpos( $name, 'on' ) || ! in_array( $name, $attributes, true ) ) {
					$child->removeAttribute( $attr->nodeName );
					continue;
				}

				// url(...) inside a paint or filter value can point anywhere;
				// only a same-document reference (#id) is kept.
				if ( preg_match( '/url\(\s*[\'"]?\s*(?!#)/i', (string) $attr->nodeValue ) ) {
					$child->removeAttribute( $attr->nodeName );
				}
			}

			$walk( $child );
		}
	};

	/*
	 * The root <svg> element's own attributes, then its subtree.
	 *
	 * The walker below descends into childNodes, so the root itself is the one
	 * element it never inspects — and `onload` on the root is the single most
	 * common way a hostile SVG runs code. Cleaning it here rather than
	 * restructuring the walk keeps the recursion doing one thing.
	 */
	foreach ( iterator_to_array( $dom->documentElement->attributes ) as $attr ) {
		$name = strtolower( $attr->nodeName );
		if ( 0 === strpos( $name, 'on' ) || ! in_array( $name, $attributes, true ) ) {
			$dom->documentElement->removeAttribute( $attr->nodeName );
		}
	}

	$walk( $dom->documentElement );

	foreach ( $drop as $node ) {
		if ( $node->parentNode ) {
			$node->parentNode->removeChild( $node );
		}
	}

	$out = $dom->saveXML( $dom->documentElement );

	return is_string( $out ) ? '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $out : null;
}

/**
 * An SVG has no pixel dimensions in the database, so the media library sizes
 * its thumbnail at 0×0 and the tile renders empty. Report the viewBox instead.
 */
add_filter( 'wp_generate_attachment_metadata', function ( $metadata, $attachment_id ) {
	if ( 'image/svg+xml' !== get_post_mime_type( $attachment_id ) ) {
		return $metadata;
	}

	$file = get_attached_file( $attachment_id );
	if ( ! $file || ! file_exists( $file ) ) {
		return $metadata;
	}

	$previous = libxml_use_internal_errors( true );
	$xml      = simplexml_load_file( $file, 'SimpleXMLElement', LIBXML_NONET );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	if ( ! $xml ) {
		return $metadata;
	}

	$attr = $xml->attributes();
	$w    = isset( $attr->width ) ? (float) $attr->width : 0;
	$h    = isset( $attr->height ) ? (float) $attr->height : 0;

	if ( ( ! $w || ! $h ) && isset( $attr->viewBox ) ) {
		$box = preg_split( '/[\s,]+/', trim( (string) $attr->viewBox ) );
		if ( $box && 4 === count( $box ) ) {
			$w = (float) $box[2];
			$h = (float) $box[3];
		}
	}

	if ( $w && $h ) {
		$metadata['width']  = (int) round( $w );
		$metadata['height'] = (int) round( $h );
	}

	return $metadata;
}, 10, 2 );
