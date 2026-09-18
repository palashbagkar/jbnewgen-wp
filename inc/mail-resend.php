<?php
/**
 * Routes all WordPress mail through Resend.
 *
 * Deliberately implemented in the theme rather than as an SMTP plugin: the
 * plugin budget is three (security, backup, cache — CONTEXT.md §4) and mail
 * does not warrant one of them.
 *
 * TWO TRANSPORTS, HTTPS FIRST.
 *
 * Shared hosting very often blocks outbound SMTP (port 587) to third-party
 * servers, and the failure is miserable to diagnose: PHPMailer reports a
 * connection timeout minutes later, the sign-in code never arrives, and
 * nothing distinguishes it from a bad API key. So mail goes over Resend's
 * HTTPS API first, which travels the same path as any other outbound request
 * and cannot be port-blocked. If that call fails, this returns null and
 * WordPress falls through to the SMTP configuration below — so a blocked
 * port and a broken API both still have a second chance.
 *
 * Configure by adding to wp-config.php, above "stop editing":
 *
 *   define( 'JBNEWGEN_RESEND_API_KEY', 're_xxxxxxxx' );
 *   define( 'JBNEWGEN_MAIL_FROM',      'noreply@jbnewgen.com' );
 *   define( 'JBNEWGEN_MAIL_FROM_NAME', 'JB NewGen' );
 *
 * THE SENDER MUST BE A DOMAIN VERIFIED IN RESEND. It is the apex domain,
 * noreply@jbnewgen.com — matching TWOFA_FROM in the Next.js app. The `send.`
 * subdomain carries the DKIM/return-path records, it is NOT the From address;
 * sending from send.jbnewgen.com returns 403 "not authorized to send emails
 * from ...". Verified against the live API, not assumed.
 *
 * The key is a credential: it belongs in wp-config.php (gitignored), never in
 * the theme. With no key defined this file does nothing at all and WordPress
 * falls back to PHP mail(), so a missing key degrades rather than breaks.
 */

/**
 * Is Resend configured?
 */
function jbnewgen_resend_ready() {
	return defined( 'JBNEWGEN_RESEND_API_KEY' ) && '' !== trim( (string) JBNEWGEN_RESEND_API_KEY );
}

/**
 * The verified sender, as Resend wants it: "Name <address>".
 *
 * @return string
 */
function jbnewgen_resend_from() {
	$email = defined( 'JBNEWGEN_MAIL_FROM' ) ? JBNEWGEN_MAIL_FROM : 'noreply@jbnewgen.com';
	$name  = defined( 'JBNEWGEN_MAIL_FROM_NAME' ) ? JBNEWGEN_MAIL_FROM_NAME : 'JB NewGen';
	return $name ? sprintf( '%s <%s>', $name, $email ) : $email;
}

/**
 * Send through Resend's HTTPS API before WordPress reaches for PHPMailer.
 *
 * Returning null hands the message back to WordPress unchanged, so every
 * failure path here ends in the SMTP transport rather than a lost email.
 *
 * @param null|bool $short_circuit
 * @param array     $atts to, subject, message, headers, attachments
 * @return true|null
 */
add_filter( 'pre_wp_mail', function ( $short_circuit, $atts ) {
	if ( ! jbnewgen_resend_ready() ) {
		return $short_circuit;
	}

	$to = $atts['to'];
	if ( ! is_array( $to ) ) {
		$to = preg_split( '/,\s*/', (string) $to, -1, PREG_SPLIT_NO_EMPTY );
	}
	$to = array_values( array_filter( array_map( 'trim', (array) $to ) ) );
	if ( ! $to ) {
		return $short_circuit;
	}

	// wp_mail() callers signal HTML with a Content-Type header. Anything else
	// is plain text, and sending it as HTML would collapse the line breaks in
	// the sign-in email into one unreadable paragraph.
	$headers = $atts['headers'];
	if ( ! is_array( $headers ) ) {
		$headers = preg_split( "/\r\n|\n|\r/", (string) $headers, -1, PREG_SPLIT_NO_EMPTY );
	}
	$is_html = false;
	foreach ( (array) $headers as $header ) {
		if ( stripos( (string) $header, 'content-type' ) !== false && stripos( (string) $header, 'text/html' ) !== false ) {
			$is_html = true;
			break;
		}
	}

	$body = array(
		'from'    => jbnewgen_resend_from(),
		'to'      => $to,
		'subject' => (string) $atts['subject'],
	);
	$body[ $is_html ? 'html' : 'text' ] = (string) $atts['message'];

	$response = wp_remote_post(
		'https://api.resend.com/emails',
		array(
			'timeout' => 15,
			'headers' => array(
				'Authorization' => 'Bearer ' . JBNEWGEN_RESEND_API_KEY,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( $body ),
		)
	);

	if ( is_wp_error( $response ) ) {
		error_log( 'jbnewgen mail: Resend API unreachable (' . $response->get_error_message() . ') — falling back to SMTP' );
		return $short_circuit;
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( $code >= 200 && $code < 300 ) {
		return true;
	}

	// 403 here almost always means the From address is not a domain verified
	// in Resend. Log the body: it names the domain it refused.
	error_log( 'jbnewgen mail: Resend API returned ' . $code . ' — ' . wp_remote_retrieve_body( $response ) . ' — falling back to SMTP' );
	return $short_circuit;
}, 10, 2 );

/**
 * Fallback transport: point PHPMailer at Resend's SMTP endpoint.
 */
add_action( 'phpmailer_init', function ( $phpmailer ) {
	if ( ! jbnewgen_resend_ready() ) {
		return;
	}

	$phpmailer->isSMTP();
	$phpmailer->Host       = 'smtp.resend.com';
	$phpmailer->SMTPAuth   = true;
	$phpmailer->Username   = 'resend';           // literal, per Resend's SMTP docs
	$phpmailer->Password   = JBNEWGEN_RESEND_API_KEY;
	$phpmailer->SMTPSecure = 'tls';
	$phpmailer->Port       = 587;
	$phpmailer->CharSet    = 'UTF-8';
} );

/**
 * Send from the verified Resend sender. An unverified From address is the most
 * common reason Resend accepts a message and then silently drops it.
 */
add_filter( 'wp_mail_from', function ( $email ) {
	if ( ! jbnewgen_resend_ready() ) {
		return $email;
	}
	return defined( 'JBNEWGEN_MAIL_FROM' ) ? JBNEWGEN_MAIL_FROM : 'noreply@jbnewgen.com';
} );

add_filter( 'wp_mail_from_name', function ( $name ) {
	if ( ! jbnewgen_resend_ready() ) {
		return $name;
	}
	return defined( 'JBNEWGEN_MAIL_FROM_NAME' ) ? JBNEWGEN_MAIL_FROM_NAME : 'JB NewGen';
} );

/**
 * Surface send failures in the admin instead of losing them silently — the
 * failure mode that makes "the reset code never arrived" impossible to debug.
 */
add_action( 'wp_mail_failed', function ( $error ) {
	if ( function_exists( 'error_log' ) ) {
		error_log( 'jbnewgen mail failed: ' . $error->get_error_message() );
	}
} );
