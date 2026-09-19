<?php
/**
 * Form handling for /quote and the homepage newsletter signup.
 *
 * Neither form has a JS-backed fetch endpoint here (no build step, no client
 * framework) — both are plain POSTs, handled server-side, then redirected
 * (POST/redirect/GET) back to a clean URL with a query flag the template
 * reads to show a success or error state. Mail goes through wp_mail(), which
 * inc/mail-resend.php already routes through Resend.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Quote form -> admin-post.php?action=jbnewgen_quote. Registered for both
 * logged-in and anonymous visitors since every real visitor is anonymous.
 */
function jbnewgen_handle_quote_submit() {
	if ( ! isset( $_POST['jbnewgen_quote_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jbnewgen_quote_nonce'] ) ), 'jbnewgen_quote' ) ) {
		wp_safe_redirect( home_url( '/quote/?quote_error=1' ) );
		exit;
	}

	// Honeypot: a real visitor never fills the visually hidden "company" field.
	if ( ! empty( $_POST['company'] ) ) {
		wp_safe_redirect( home_url( '/quote/?sent=1' ) );
		exit;
	}

	$fields = array(
		'first_name'       => isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '',
		'last_name'        => isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '',
		'your_email'       => isset( $_POST['your_email'] ) ? sanitize_email( wp_unslash( $_POST['your_email'] ) ) : '',
		'your_phone'       => isset( $_POST['your_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['your_phone'] ) ) : '',
		'your_web_address' => isset( $_POST['your_web_address'] ) ? sanitize_text_field( wp_unslash( $_POST['your_web_address'] ) ) : '',
		'your_service'     => isset( $_POST['your_service'] ) ? sanitize_text_field( wp_unslash( $_POST['your_service'] ) ) : '',
		'your_message'     => isset( $_POST['your_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['your_message'] ) ) : '',
	);

	foreach ( $fields as $value ) {
		if ( '' === trim( (string) $value ) ) {
			wp_safe_redirect( home_url( '/quote/?quote_error=fields' ) );
			exit;
		}
	}
	if ( ! is_email( $fields['your_email'] ) ) {
		wp_safe_redirect( home_url( '/quote/?quote_error=fields' ) );
		exit;
	}

	$site    = jbnewgen_site_info();
	$subject = sprintf( '[Quote request] %s %s - %s', $fields['first_name'], $fields['last_name'], $fields['your_service'] );
	$body    = "New quote request from jbnewgen.com/quote\n\n"
		. "Name: {$fields['first_name']} {$fields['last_name']}\n"
		. "Email: {$fields['your_email']}\n"
		. "Phone: {$fields['your_phone']}\n"
		. "Website: {$fields['your_web_address']}\n"
		. "Service: {$fields['your_service']}\n\n"
		. "Message:\n{$fields['your_message']}\n";

	$sent = wp_mail( $site['emails']['sales'], $subject, $body, array( 'Reply-To: ' . $fields['your_email'] ) );

	wp_safe_redirect( home_url( $sent ? '/quote/?sent=1' : '/quote/?quote_error=1' ) );
	exit;
}
add_action( 'admin_post_jbnewgen_quote', 'jbnewgen_handle_quote_submit' );
add_action( 'admin_post_nopriv_jbnewgen_quote', 'jbnewgen_handle_quote_submit' );

/**
 * Newsletter form on the homepage posts to itself (no action attribute), so
 * it is caught here on template_redirect rather than through admin-post.php.
 */
add_action( 'template_redirect', function () {
	if ( 'POST' !== $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['jb_newsletter_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jb_newsletter_nonce'] ) ), 'jb_newsletter' ) ) {
		wp_safe_redirect( home_url( '/?newsletter_error=1' ) );
		exit;
	}

	$email = isset( $_POST['jb_email'] ) ? sanitize_email( wp_unslash( $_POST['jb_email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		wp_safe_redirect( home_url( '/?newsletter_error=1' ) );
		exit;
	}

	$site = jbnewgen_site_info();
	wp_mail(
		$site['emails']['sales'],
		'[Newsletter signup] ' . $email,
		"New newsletter signup from jbnewgen.com:\n\n{$email}\n"
	);

	wp_safe_redirect( home_url( '/?subscribed=1' ) );
	exit;
} );
