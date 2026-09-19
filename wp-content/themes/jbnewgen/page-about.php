<?php
/**
 * Template Name: About (redirect)
 *
 * Ported from ../jbnewgen/src/app/(frontend)/about/page.tsx, which is a bare
 * redirect() to /about/company -- there is no standalone /about landing page
 * on the live site, just the dropdown's three children.
 */

defined( 'ABSPATH' ) || exit;

wp_safe_redirect( home_url( '/about/company' ), 301 );
exit;
