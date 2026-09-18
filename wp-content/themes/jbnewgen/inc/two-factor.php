<?php
/**
 * Two-factor sign-in — parity with the live Payload admin.
 *
 * `../jbnewgen/src/collections/Users.ts` carries the whole scheme:
 *
 *   auth: { maxLoginAttempts: 5, lockTime: 10 * 60 * 1000 }
 *   notifyEmail   where the code is actually sent, if not the login address
 *   otpHash       the code, hashed
 *   otpExpiresAt  when it stops working
 *   otpAttempts   how many wrong guesses so far
 *   otpLastSentAt the resend throttle
 *
 * Same six things here, as user meta, with WordPress's own login flow around
 * them. The password is checked first and is not enough on its own: a correct
 * password ends at a code screen, and only a correct code sets a cookie.
 *
 * Why in the theme and not a plugin: the plugin budget is three, and they are
 * spoken for (security, backup, cache — CONTEXT.md §4). Two-factor is one
 * screen and one email; it does not warrant one of them.
 *
 * ---------------------------------------------------------------------------
 * TURNING IT OFF
 *
 * Add to wp-config.php, above "stop editing":
 *
 *     define( 'JBNEWGEN_2FA', false );
 *
 * That is the only switch, it is deliberately not in the admin, and it exists
 * for exactly one situation: a local copy with no mail transport, where every
 * login would otherwise wait for an email that is never sent. It should never
 * be defined on the live site.
 * ---------------------------------------------------------------------------
 */

const JBNEWGEN_2FA_TTL       = 10 * MINUTE_IN_SECONDS;  // how long a code lives
const JBNEWGEN_2FA_MAX       = 5;                       // Users.ts maxLoginAttempts
const JBNEWGEN_2FA_LOCK      = 10 * MINUTE_IN_SECONDS;  // Users.ts lockTime
const JBNEWGEN_2FA_RESEND    = 60;                      // seconds between sends
const JBNEWGEN_2FA_HANDOFF   = 15 * MINUTE_IN_SECONDS;  // how long the password step stays good

/**
 * Is it on?
 */
function jbnewgen_2fa_enabled() {
	if ( defined( 'JBNEWGEN_2FA' ) && ! JBNEWGEN_2FA ) {
		return false;
	}
	return true;
}

/**
 * Where this person's code goes.
 *
 * Payload's `notifyEmail` exists because the address you sign in with is not
 * always the address you read — a shared login on a personal inbox. Same
 * field, same fallback.
 *
 * @param WP_User $user
 * @return string
 */
function jbnewgen_2fa_destination( $user ) {
	$notify = (string) get_user_meta( $user->ID, 'jb_notify_email', true );
	return is_email( $notify ) ? $notify : $user->user_email;
}

/* -------------------------------------------------------------------------
   The code
   ------------------------------------------------------------------------- */

/**
 * Make one, store its hash, and send it.
 *
 * The code is never stored in the clear. `wp_hash_password` is bcrypt — slow
 * on purpose, which for a six-digit secret with five attempts is exactly the
 * property wanted.
 *
 * @param WP_User $user
 * @return true|WP_Error
 */
function jbnewgen_2fa_issue( $user ) {
	$last = (int) get_user_meta( $user->ID, 'jb_otp_last_sent', true );
	if ( $last && ( time() - $last ) < JBNEWGEN_2FA_RESEND ) {
		return new WP_Error(
			'throttled',
			sprintf(
				/* translators: %d: seconds to wait */
				__( 'A code was just sent. Wait %d seconds before asking for another.', 'jbnewgen' ),
				JBNEWGEN_2FA_RESEND - ( time() - $last )
			)
		);
	}

	// wp_rand() is seeded from a CSPRNG where one is available; mt_rand() is
	// not, and a predictable second factor is not a second factor.
	$code = str_pad( (string) wp_rand( 0, 999999 ), 6, '0', STR_PAD_LEFT );

	update_user_meta( $user->ID, 'jb_otp_hash', wp_hash_password( $code ) );
	update_user_meta( $user->ID, 'jb_otp_expires', time() + JBNEWGEN_2FA_TTL );
	update_user_meta( $user->ID, 'jb_otp_attempts', 0 );
	update_user_meta( $user->ID, 'jb_otp_last_sent', time() );

	$to   = jbnewgen_2fa_destination( $user );
	$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

	$sent = wp_mail(
		$to,
		/* translators: %s: the code */
		sprintf( __( '%s is your sign-in code', 'jbnewgen' ), $code ),
		implode( "\n", array(
			/* translators: %s: site name */
			sprintf( __( 'Someone is signing in to %s.', 'jbnewgen' ), $site ),
			'',
			$code,
			'',
			/* translators: %d: minutes */
			sprintf( __( 'The code stops working in %d minutes.', 'jbnewgen' ), JBNEWGEN_2FA_TTL / MINUTE_IN_SECONDS ),
			__( 'If this was not you, change your password — somebody else has it.', 'jbnewgen' ),
		) )
	);

	if ( ! $sent ) {
		// The code is already stored, so a failure here is a delivery problem,
		// not a state problem. Say so plainly rather than letting somebody wait
		// for a mail that is not coming.
		return new WP_Error(
			'send',
			__( 'The code could not be emailed. Tell whoever runs this site — the mail settings are wrong.', 'jbnewgen' )
		);
	}

	return true;
}

/**
 * Check a typed code.
 *
 * @param WP_User $user
 * @param string  $code
 * @return true|WP_Error
 */
function jbnewgen_2fa_check( $user, $code ) {
	$locked = (int) get_user_meta( $user->ID, 'jb_otp_locked_until', true );
	if ( $locked > time() ) {
		return new WP_Error(
			'locked',
			sprintf(
				/* translators: %d: minutes remaining */
				__( 'Too many wrong codes. Try again in %d minutes.', 'jbnewgen' ),
				max( 1, (int) ceil( ( $locked - time() ) / MINUTE_IN_SECONDS ) )
			)
		);
	}

	$hash    = (string) get_user_meta( $user->ID, 'jb_otp_hash', true );
	$expires = (int) get_user_meta( $user->ID, 'jb_otp_expires', true );

	if ( ! $hash || ! $expires ) {
		return new WP_Error( 'none', __( 'There is no code waiting. Ask for a new one.', 'jbnewgen' ) );
	}

	if ( time() > $expires ) {
		jbnewgen_2fa_clear( $user->ID );
		return new WP_Error( 'expired', __( 'That code has expired. Ask for a new one.', 'jbnewgen' ) );
	}

	$code = preg_replace( '/\D/', '', (string) $code );

	if ( ! wp_check_password( $code, $hash ) ) {
		$attempts = (int) get_user_meta( $user->ID, 'jb_otp_attempts', true ) + 1;
		update_user_meta( $user->ID, 'jb_otp_attempts', $attempts );

		if ( $attempts >= JBNEWGEN_2FA_MAX ) {
			// The code goes with the lock. Otherwise waiting out the lockout
			// hands back a code that has now had five guesses spent on it.
			jbnewgen_2fa_clear( $user->ID );
			update_user_meta( $user->ID, 'jb_otp_locked_until', time() + JBNEWGEN_2FA_LOCK );

			return new WP_Error(
				'locked',
				sprintf(
					/* translators: %d: minutes */
					__( 'Too many wrong codes. Try again in %d minutes.', 'jbnewgen' ),
					JBNEWGEN_2FA_LOCK / MINUTE_IN_SECONDS
				)
			);
		}

		return new WP_Error(
			'wrong',
			sprintf(
				/* translators: %d: attempts remaining */
				_n( 'That code is wrong. %d attempt left.', 'That code is wrong. %d attempts left.', JBNEWGEN_2FA_MAX - $attempts, 'jbnewgen' ),
				JBNEWGEN_2FA_MAX - $attempts
			)
		);
	}

	// Single use. A code that still works after it has been used is a code
	// sitting in an inbox waiting to be replayed.
	jbnewgen_2fa_clear( $user->ID );
	delete_user_meta( $user->ID, 'jb_otp_locked_until' );

	return true;
}

function jbnewgen_2fa_clear( $user_id ) {
	delete_user_meta( $user_id, 'jb_otp_hash' );
	delete_user_meta( $user_id, 'jb_otp_expires' );
	delete_user_meta( $user_id, 'jb_otp_attempts' );
}

/* -------------------------------------------------------------------------
   Recovery codes
   ------------------------------------------------------------------------- */

/*
 * The half of two-factor that decides whether it is usable.
 *
 * Every second factor eventually fails for somebody who has done nothing
 * wrong: the mailbox is down, the address was a leaver's, the domain moved.
 * Without a way back in, the honest options at that point are "an
 * administrator resets it" — which is a phone call and a person who might be
 * asleep — or "turn it off", which is what actually happens.
 *
 * Ten codes, each usable once, shown exactly once. Stored the same way the
 * OTP is: hashed, never in the clear, so a database dump is not a set of keys.
 */

const JBNEWGEN_2FA_RECOVERY_COUNT = 10;

/**
 * Generate a fresh set, store the hashes, return the plain codes.
 *
 * The alphabet leaves out the characters that get misread off a printout or
 * out of a password manager — 0/O, 1/l/I, 5/S, 2/Z — because these are typed
 * by hand at the worst possible moment.
 *
 * @param int $user_id
 * @return string[]
 */
function jbnewgen_2fa_make_recovery_codes( $user_id ) {
	$alphabet = 'abcdefghjkmnpqrtuvwxy346789';
	$codes    = array();
	$hashes   = array();

	for ( $i = 0; $i < JBNEWGEN_2FA_RECOVERY_COUNT; $i++ ) {
		$code = '';
		for ( $c = 0; $c < 10; $c++ ) {
			if ( 5 === $c ) {
				$code .= '-';
			}
			$code .= $alphabet[ wp_rand( 0, strlen( $alphabet ) - 1 ) ];
		}
		$codes[]  = $code;
		$hashes[] = wp_hash_password( $code );
	}

	update_user_meta( $user_id, 'jb_recovery_codes', $hashes );
	update_user_meta( $user_id, 'jb_recovery_made', time() );

	return $codes;
}

/**
 * How many are left.
 *
 * @param int $user_id
 */
function jbnewgen_2fa_recovery_left( $user_id ) {
	$hashes = get_user_meta( $user_id, 'jb_recovery_codes', true );
	return is_array( $hashes ) ? count( $hashes ) : 0;
}

/**
 * Spend one, if it matches.
 *
 * Every stored hash is checked rather than stopping at the first miss — there
 * is no index to look a code up by, and bailing early would leak which
 * position matched through timing.
 *
 * @param int    $user_id
 * @param string $code
 * @return bool
 */
function jbnewgen_2fa_spend_recovery_code( $user_id, $code ) {
	$hashes = get_user_meta( $user_id, 'jb_recovery_codes', true );
	if ( ! is_array( $hashes ) || ! $hashes ) {
		return false;
	}

	$code  = strtolower( trim( preg_replace( '/[^A-Za-z0-9\-]/', '', (string) $code ) ) );
	$found = -1;

	foreach ( $hashes as $i => $hash ) {
		if ( wp_check_password( $code, $hash ) ) {
			$found = $i;
		}
	}

	if ( $found < 0 ) {
		return false;
	}

	unset( $hashes[ $found ] );
	update_user_meta( $user_id, 'jb_recovery_codes', array_values( $hashes ) );

	return true;
}

/**
 * Does this look like a recovery code rather than an emailed one?
 *
 * The OTP is six digits; a recovery code is ten letters and digits with a
 * hyphen in the middle. Telling them apart on shape means one field on the
 * sign-in screen instead of two, and nobody has to decide which box to use.
 *
 * @param string $input
 */
function jbnewgen_2fa_looks_like_recovery( $input ) {
	return (bool) preg_match( '/[a-z\-]/i', (string) $input );
}

/* -------------------------------------------------------------------------
   The handoff between the two steps
   ------------------------------------------------------------------------- */

/**
 * A one-shot token that says "this browser got the password right".
 *
 * Held in a transient rather than in the URL alone: the URL is the thing that
 * ends up in history, in a shoulder-glance and in a pasted bug report, and on
 * its own it must not be enough to reach the second step. The transient
 * carries the user id; the URL carries only the key.
 *
 * @param int $user_id
 * @return string
 */
function jbnewgen_2fa_handoff( $user_id ) {
	$key = wp_generate_password( 32, false, false );
	set_transient( 'jb_2fa_' . $key, (int) $user_id, JBNEWGEN_2FA_HANDOFF );
	return $key;
}

/**
 * @param string $key
 * @return WP_User|null
 */
function jbnewgen_2fa_pending( $key ) {
	$key = preg_replace( '/[^A-Za-z0-9]/', '', (string) $key );
	if ( ! $key ) {
		return null;
	}

	$user_id = get_transient( 'jb_2fa_' . $key );
	if ( ! $user_id ) {
		return null;
	}

	$user = get_userdata( (int) $user_id );
	return $user && $user->exists() ? $user : null;
}

/* -------------------------------------------------------------------------
   Step one: the password stops being enough
   ------------------------------------------------------------------------- */

/**
 * Runs after WordPress has checked the password and before it sets a cookie.
 *
 * Priority 40 puts it after `wp_authenticate_username_password` (20) and after
 * `wp_authenticate_email_password` (20), so `$user` here is either a WP_User
 * whose password was correct or an error somebody else already raised.
 *
 * Redirect-and-exit from inside a filter is unusual but it is the only place
 * that fits: wp-login.php calls wp_signon() and then sets the auth cookie
 * itself, so returning a user here means being logged in. Leaving before that
 * happens is the point.
 */
add_filter( 'authenticate', function ( $user, $username, $password ) {
	if ( ! jbnewgen_2fa_enabled() ) {
		return $user;
	}
	if ( is_wp_error( $user ) || ! ( $user instanceof WP_User ) ) {
		return $user;
	}
	if ( empty( $password ) ) {
		return $user;   // an interim/cookie check, not a sign-in
	}

	// The code screen posts back through wp-login.php too; without this the
	// filter would send it round the loop again.
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! empty( $_REQUEST['jb_2fa'] ) ) {
		return $user;
	}

	$locked = (int) get_user_meta( $user->ID, 'jb_otp_locked_until', true );
	if ( $locked > time() ) {
		return new WP_Error(
			'jb_2fa_locked',
			sprintf(
				/* translators: %d: minutes */
				__( '<strong>Locked.</strong> Too many wrong codes. Try again in %d minutes.', 'jbnewgen' ),
				max( 1, (int) ceil( ( $locked - time() ) / MINUTE_IN_SECONDS ) )
			)
		);
	}

	$issued = jbnewgen_2fa_issue( $user );
	$key    = jbnewgen_2fa_handoff( $user->ID );

	$url = add_query_arg(
		array_filter( array(
			'action'      => 'jb_2fa',
			'jb_key'      => $key,
			'redirect_to' => isset( $_REQUEST['redirect_to'] ) ? rawurlencode( esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
			'jb_error'    => is_wp_error( $issued ) ? $issued->get_error_code() : '',
		) ),
		wp_login_url()
	);

	wp_safe_redirect( $url );
	exit;
}, 40, 3 );

/* -------------------------------------------------------------------------
   Step two: the code screen
   ------------------------------------------------------------------------- */

add_action( 'login_form_jb_2fa', function () {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- the handoff key is the token
	$key  = isset( $_REQUEST['jb_key'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['jb_key'] ) ) : '';
	$user = jbnewgen_2fa_pending( $key );
	// phpcs:enable

	if ( ! $user ) {
		wp_safe_redirect( wp_login_url() );
		exit;
	}

	$redirect_to = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : admin_url(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$error       = null;
	$notice      = null;

	// --- a code was typed -------------------------------------------------
	if ( isset( $_POST['jb_2fa'] ) ) {
		if ( ! isset( $_POST['jb_2fa_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['jb_2fa_nonce'] ), 'jb_2fa_' . $key ) ) {
			$error = new WP_Error( 'nonce', __( 'That page expired. Sign in again.', 'jbnewgen' ) );
		} else {
			$typed = isset( $_POST['jb_code'] ) ? wp_unslash( $_POST['jb_code'] ) : '';

			/*
			 * One field, two kinds of code. A recovery code has letters and a
			 * hyphen in it and an emailed one does not, so which was typed can
			 * be told from its shape — nobody has to pick the right box while
			 * locked out of their own inbox.
			 *
			 * A spent recovery code skips the attempt counter entirely: it is
			 * already single-use, and letting a mistyped one burn through the
			 * five tries would turn the way back in into another lockout.
			 */
			if ( jbnewgen_2fa_looks_like_recovery( $typed ) ) {
				if ( jbnewgen_2fa_spend_recovery_code( $user->ID, $typed ) ) {
					$result = true;
					jbnewgen_2fa_clear( $user->ID );
					delete_user_meta( $user->ID, 'jb_otp_locked_until' );
				} else {
					$result = new WP_Error( 'recovery', __( 'That recovery code did not match. Each one works once only.', 'jbnewgen' ) );
				}
			} else {
				$result = jbnewgen_2fa_check( $user, $typed );
			}

			if ( is_wp_error( $result ) ) {
				$error = $result;
				if ( 'locked' === $result->get_error_code() ) {
					delete_transient( 'jb_2fa_' . $key );
				}
			} else {
				// The handoff is spent the moment it works.
				delete_transient( 'jb_2fa_' . $key );

				wp_set_current_user( $user->ID );
				wp_set_auth_cookie( $user->ID, ! empty( $_POST['jb_remember'] ) );
				do_action( 'wp_login', $user->user_login, $user );

				wp_safe_redirect( $redirect_to ? $redirect_to : admin_url() );
				exit;
			}
		}
	}

	// --- another code was asked for ---------------------------------------
	if ( isset( $_POST['jb_resend'] ) ) {
		if ( ! isset( $_POST['jb_2fa_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['jb_2fa_nonce'] ), 'jb_2fa_' . $key ) ) {
			$error = new WP_Error( 'nonce', __( 'That page expired. Sign in again.', 'jbnewgen' ) );
		} else {
			$issued = jbnewgen_2fa_issue( $user );
			if ( is_wp_error( $issued ) ) {
				$error = $issued;
			} else {
				$notice = __( 'A new code is on its way.', 'jbnewgen' );
			}
		}
	}

	// --- arriving from step one with a send failure -----------------------
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $error && ! empty( $_GET['jb_error'] ) ) {
		$error = new WP_Error( 'send', __( 'The code could not be emailed. Tell whoever runs this site — the mail settings are wrong.', 'jbnewgen' ) );
	}

	jbnewgen_2fa_screen( $user, $key, $redirect_to, $error, $notice );
	exit;
} );

/**
 * The screen itself. Built on login_header()/login_footer() so it inherits the
 * login page's own chrome and this theme's stylesheet with it.
 *
 * @param WP_User       $user
 * @param string        $key
 * @param string        $redirect_to
 * @param WP_Error|null $error
 * @param string|null   $notice
 */
function jbnewgen_2fa_screen( $user, $key, $redirect_to, $error, $notice ) {
	// The address is shown partly masked. Somebody who has stolen a password
	// should not be handed the inbox to go after next, and the owner only
	// needs enough to recognise which of their addresses it went to.
	$to     = jbnewgen_2fa_destination( $user );
	$parts  = explode( '@', $to );
	$masked = $to;
	if ( 2 === count( $parts ) ) {
		$name   = $parts[0];
		$masked = mb_substr( $name, 0, 1 ) . str_repeat( '•', max( 1, mb_strlen( $name ) - 1 ) ) . '@' . $parts[1];
	}

	login_header( __( 'Check your email', 'jbnewgen' ), '', $error instanceof WP_Error ? $error : null );
	?>
	<form name="jb2fa" id="jb-2fa" class="jb-2fa" method="post"
		action="<?php echo esc_url( add_query_arg( array( 'action' => 'jb_2fa', 'jb_key' => $key ), wp_login_url() ) ); ?>">

		<?php wp_nonce_field( 'jb_2fa_' . $key, 'jb_2fa_nonce' ); ?>
		<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />

		<p class="jb-2fa__lead">
			<?php
			printf(
				/* translators: %s: partly masked email address */
				esc_html__( 'We sent a six-digit code to %s.', 'jbnewgen' ),
				'<strong>' . esc_html( $masked ) . '</strong>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
			?>
		</p>

		<?php if ( $notice ) : ?>
			<p class="jb-2fa__notice"><?php echo esc_html( $notice ); ?></p>
		<?php endif; ?>

		<p>
			<label for="jb_code"><?php esc_html_e( 'Code', 'jbnewgen' ); ?></label>
			<?php
			// maxlength fits a recovery code as well as a six-digit one, and
			// `inputmode` is text rather than numeric for the same reason —
			// a numeric keypad on a phone cannot type a recovery code.
			?>
			<input type="text" name="jb_code" id="jb_code" class="input jb-2fa__code"
				value="" size="12" maxlength="12"
				autocomplete="one-time-code"
				autocapitalize="off" autocorrect="off" spellcheck="false"
				autofocus required />
		</p>

		<p class="forgetmenot">
			<input name="jb_remember" type="checkbox" id="jb_remember" value="1" />
			<label for="jb_remember"><?php esc_html_e( 'Remember me', 'jbnewgen' ); ?></label>
		</p>

		<p class="submit">
			<input type="submit" name="jb_2fa" id="wp-submit" class="button button-primary button-large"
				value="<?php esc_attr_e( 'Sign in', 'jbnewgen' ); ?>" />
		</p>

		<p class="jb-2fa__resend">
			<button type="submit" name="jb_resend" value="1" class="button-link">
				<?php esc_html_e( 'Send another code', 'jbnewgen' ); ?>
			</button>
			<span class="jb-2fa__sep">&middot;</span>
			<a href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Start again', 'jbnewgen' ); ?></a>
		</p>

		<?php if ( jbnewgen_2fa_recovery_left( $user->ID ) ) : ?>
			<p class="jb-2fa__recovery">
				<?php esc_html_e( 'No access to that inbox? A recovery code works here too.', 'jbnewgen' ); ?>
			</p>
		<?php endif; ?>
	</form>
	<?php
	login_footer();
}

/* -------------------------------------------------------------------------
   The profile field
   ------------------------------------------------------------------------- */

/**
 * `notifyEmail` from Users.ts, on the profile screen.
 *
 * Shown to everyone about themselves, and to an administrator about anyone —
 * matching who may edit the rest of that screen. It is not a security control
 * on its own: somebody who can edit your profile can already reset your
 * password.
 */
add_action( 'show_user_profile', 'jbnewgen_2fa_profile_field' );
add_action( 'edit_user_profile', 'jbnewgen_2fa_profile_field' );

function jbnewgen_2fa_profile_field( $user ) {
	$self  = get_current_user_id() === (int) $user->ID;
	$left  = jbnewgen_2fa_recovery_left( $user->ID );
	$fresh = $self ? get_transient( 'jb_2fa_shown_' . $user->ID ) : false;

	if ( $fresh ) {
		// Shown once and then gone — a page that can be refreshed back to a
		// list of working codes is a list of working codes in the browser
		// history.
		delete_transient( 'jb_2fa_shown_' . $user->ID );
	}
	?>
	<h2><?php esc_html_e( 'Signing in', 'jbnewgen' ); ?></h2>
	<table class="form-table" role="presentation">

		<tr>
			<th><?php esc_html_e( 'Two-factor', 'jbnewgen' ); ?></th>
			<td>
				<?php if ( jbnewgen_2fa_enabled() ) : ?>
					<p class="jb-2fa-status is-on"><?php esc_html_e( 'On for everybody.', 'jbnewgen' ); ?></p>
					<p class="description">
						<?php esc_html_e( 'Signing in takes your password and then a six-digit code emailed to you. There is no way to switch it off for one account.', 'jbnewgen' ); ?>
					</p>
				<?php else : ?>
					<p class="jb-2fa-status is-off"><?php esc_html_e( 'Off on this installation.', 'jbnewgen' ); ?></p>
					<p class="description">
						<?php
						printf(
							/* translators: %s: the constant name */
							esc_html__( 'Something has defined %s in wp-config.php. That is intended for a local copy with no mail transport and must not be present on the live site.', 'jbnewgen' ),
							'<code>JBNEWGEN_2FA</code>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						);
						?>
					</p>
				<?php endif; ?>
			</td>
		</tr>

		<tr>
			<th><label for="jb_notify_email"><?php esc_html_e( 'Where codes go', 'jbnewgen' ); ?></label></th>
			<td>
				<input type="email" name="jb_notify_email" id="jb_notify_email" class="regular-text"
					value="<?php echo esc_attr( (string) get_user_meta( $user->ID, 'jb_notify_email', true ) ); ?>"
					placeholder="<?php echo esc_attr( $user->user_email ); ?>" />
				<p class="description">
					<?php esc_html_e( 'Leave blank and the code goes to the email address above. Set it if you sign in with one address and read another.', 'jbnewgen' ); ?>
				</p>
			</td>
		</tr>

		<tr>
			<th><?php esc_html_e( 'Recovery codes', 'jbnewgen' ); ?></th>
			<td>
				<?php if ( $fresh && is_array( $fresh ) ) : ?>
					<div class="jb-recovery">
						<p class="jb-recovery__lead">
							<strong><?php esc_html_e( 'Save these now. They will not be shown again.', 'jbnewgen' ); ?></strong>
						</p>
						<ul class="jb-recovery__codes">
							<?php foreach ( $fresh as $code ) : ?>
								<li><?php echo esc_html( $code ); ?></li>
							<?php endforeach; ?>
						</ul>
						<p class="description">
							<?php esc_html_e( 'Each one signs you in once, in place of an emailed code. Keep them somewhere that is not your email — an inbox you cannot reach is the reason you would need them.', 'jbnewgen' ); ?>
						</p>
					</div>

				<?php elseif ( $left ) : ?>
					<p class="jb-2fa-status<?php echo $left <= 2 ? ' is-low' : ''; ?>">
						<?php
						printf(
							/* translators: %s: number of codes left */
							esc_html( _n( '%s code left.', '%s codes left.', $left, 'jbnewgen' ) ),
							esc_html( number_format_i18n( $left ) )
						);
						?>
					</p>
					<?php if ( $left <= 2 ) : ?>
						<p class="description"><?php esc_html_e( 'Running low. Generate a new set before the last one is spent.', 'jbnewgen' ); ?></p>
					<?php endif; ?>

				<?php else : ?>
					<p class="jb-2fa-status is-low"><?php esc_html_e( 'None yet.', 'jbnewgen' ); ?></p>
					<p class="description">
						<?php esc_html_e( 'Without these, an inbox you cannot reach means an account you cannot reach. Generating a set takes one click.', 'jbnewgen' ); ?>
					</p>
				<?php endif; ?>

				<?php if ( $self ) : ?>
					<p>
						<label class="jb-recovery__go">
							<input type="checkbox" name="jb_2fa_regen" value="1" />
							<?php
							echo $left
								? esc_html__( 'Generate a new set when I save — the old codes stop working', 'jbnewgen' )
								: esc_html__( 'Generate a set of recovery codes when I save', 'jbnewgen' );
							?>
						</label>
					</p>
				<?php else : ?>
					<p class="description">
						<?php echo wp_kses_post( __( 'Only this person can generate their own. An administrator who could mint somebody else&#8217;s recovery codes would be an administrator who can sign in as them.', 'jbnewgen' ) ); ?>
					</p>
				<?php endif; ?>
			</td>
		</tr>
	</table>
	<?php
}

add_action( 'personal_options_update', 'jbnewgen_2fa_profile_save' );
add_action( 'edit_user_profile_update', 'jbnewgen_2fa_profile_save' );

function jbnewgen_2fa_profile_save( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}
	if ( ! isset( $_POST['jb_notify_email'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	$email = sanitize_email( wp_unslash( $_POST['jb_notify_email'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( '' === $email ) {
		delete_user_meta( $user_id, 'jb_notify_email' );
	} elseif ( is_email( $email ) ) {
		update_user_meta( $user_id, 'jb_notify_email', $email );
	}
}

/**
 * Generating a set, on save.
 *
 * The plain codes go into a 60-second transient rather than being printed
 * here: WordPress redirects after a profile save, so there is no response left
 * to print into, and a transient that expires means a set left on screen does
 * not survive somebody walking away from the desk.
 *
 * Self only. An administrator who could generate another person's recovery
 * codes could sign in as them, which would make the second factor decorative.
 */
add_action( 'personal_options_update', function ( $user_id ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( empty( $_POST['jb_2fa_regen'] ) || get_current_user_id() !== (int) $user_id ) {
		return;
	}

	$codes = jbnewgen_2fa_make_recovery_codes( $user_id );
	set_transient( 'jb_2fa_shown_' . $user_id, $codes, MINUTE_IN_SECONDS );
} );

/**
 * A rejected code must not leave the account locked out of a password reset as
 * well — and a password change is exactly what somebody should do if a code
 * arrives they did not ask for. Clearing on reset keeps the two independent.
 */
add_action( 'after_password_reset', function ( $user ) {
	jbnewgen_2fa_clear( $user->ID );
	delete_user_meta( $user->ID, 'jb_otp_locked_until' );
} );
