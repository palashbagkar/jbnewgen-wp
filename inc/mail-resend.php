<?php
/**
 * Routes all WordPress mail through Resend over SMTP.
 *
 * Deliberately implemented in the theme rather than as an SMTP plugin: the
 * plugin budget is three (security, backup, cache — CONTEXT.md §4) and mail
 * does not warrant one of them.
 *
 * Resend is already set up for this domain on the `send.` subdomain, so no DNS
 * change is required — and none should be made (CONTEXT.md §8: the A record is
 * the only thing that ever moves, or company email dies).
 *
 * Configure by adding to wp-config.php, above "stop editing":
 *
 *   define( 'JBNEWGEN_RESEND_API_KEY', 're_xxxxxxxx' );
 *   define( 'JBNEWGEN_MAIL_FROM',      'noreply@send.jbnewgen.com' );
 *   define( 'JBNEWGEN_MAIL_FROM_NAME', 'JB NewGen' );
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
 * Point PHPMailer at Resend's SMTP endpoint.
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
	return defined( 'JBNEWGEN_MAIL_FROM' ) ? JBNEWGEN_MAIL_FROM : $email;
} );

add_filter( 'wp_mail_from_name', function ( $name ) {
	if ( ! jbnewgen_resend_ready() ) {
		return $name;
	}
	return defined( 'JBNEWGEN_MAIL_FROM_NAME' ) ? JBNEWGEN_MAIL_FROM_NAME : $name;
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
