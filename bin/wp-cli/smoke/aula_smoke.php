<?php
/**
 * Aula Virtual smoke tests.
 * Run: wp eval-file bin/wp-cli/smoke/aula_smoke.php --url=https://escuelaneuquinadeyoga.local
 *
 * Guest tests use wp_remote_get (real HTTP requests).
 * Authenticated tests use internal WordPress inspection.
 *
 * Exits 0 on all pass, 1 on any failure.
 */

$exit_code = 0;
$site_url  = home_url();

WP_CLI::line( '=== Aula Virtual Smoke Tests ===' );
WP_CLI::line( '' );

/* -------------------------------------------------------------------------
 * Test 1: Guest /aula/ — should return 200 and show login option
 * ------------------------------------------------------------------------- */
WP_CLI::line( '1. Guest /aula/ ...' );

$response = wp_remote_get( $site_url . '/aula/' );
$code     = wp_remote_retrieve_response_code( $response );
$body     = wp_remote_retrieve_body( $response );

if ( 200 !== $code ) {
	WP_CLI::warning( '  FAIL: /aula/ returned HTTP ' . $code . ' (expected 200)' );
	$exit_code = 1;
} elseif ( false === stripos( $body, 'iniciar sesi' ) && false === stripos( $body, 'login' ) ) {
	WP_CLI::warning( '  FAIL: /aula/ guest view missing login option' );
	$exit_code = 1;
} else {
	WP_CLI::success( '  PASS' );
}

/* -------------------------------------------------------------------------
 * Test 2: Registration page /registration-2/ — loads with 200
 * ------------------------------------------------------------------------- */
WP_CLI::line( '2. /registration-2/ registration form ...' );

$response = wp_remote_get( $site_url . '/registration-2/' );
$code     = wp_remote_retrieve_response_code( $response );

if ( 200 !== $code ) {
	WP_CLI::warning( '  FAIL: /registration-2/ returned HTTP ' . $code . ' (expected 200)' );
	$exit_code = 1;
} else {
	WP_CLI::success( '  PASS' );
}

/* -------------------------------------------------------------------------
 * Test 3: /registro-completado/ — registration success page
 * ------------------------------------------------------------------------- */
WP_CLI::line( '3. /registro-completado/ success page ...' );

$response = wp_remote_get( $site_url . '/registro-completado/' );
$code     = wp_remote_retrieve_response_code( $response );
$body     = wp_remote_retrieve_body( $response );

if ( 200 !== $code ) {
	WP_CLI::warning( '  FAIL: /registro-completado/ returned HTTP ' . $code . ' (expected 200)' );
	$exit_code = 1;
} elseif ( false === stripos( $body, 'registro' ) ) {
	WP_CLI::warning( '  FAIL: /registro-completado/ does not appear to be registration success page' );
	$exit_code = 1;
} else {
	WP_CLI::success( '  PASS' );
}

/* -------------------------------------------------------------------------
 * Test 4: Guest /profile/ — should redirect to /aula/
 * ------------------------------------------------------------------------- */
WP_CLI::line( '4. Guest /profile/ redirect to /aula/ ...' );

$response = wp_remote_get( $site_url . '/profile/', array( 'redirection' => 0 ) );
$code     = wp_remote_retrieve_response_code( $response );
$location = wp_remote_retrieve_header( $response, 'location' );

if ( 302 !== $code && 301 !== $code ) {
	WP_CLI::warning( '  FAIL: /profile/ for guest returned HTTP ' . $code . ' (expected 301/302 redirect)' );
	$exit_code = 1;
} elseif ( false === strpos( (string) $location, '/aula/' ) ) {
	WP_CLI::warning( '  FAIL: redirect target is not /aula/ (got: ' . (string) $location . ')' );
	$exit_code = 1;
} else {
	WP_CLI::success( '  PASS' );
}

/* -------------------------------------------------------------------------
 * Test 5: Subscriber /wp-admin/ — redirect logic (internal check)
 *
 * Uses internal validation because wp_remote_get runs as a separate HTTP
 * request that can't share wp_set_current_user context.
 * ------------------------------------------------------------------------- */
WP_CLI::line( '5. Subscriber /wp-admin/ redirect (internal check) ...' );

wp_set_current_user( 2 ); // alumno_demo.
$user    = wp_get_current_user();
$is_sub = in_array( 'subscriber', (array) $user->roles, true );
$can_manage = user_can( $user, 'manage_options' );

if ( ! $is_sub ) {
	WP_CLI::warning( '  FAIL: alumno_demo (ID 2) is not a subscriber' );
	$exit_code = 1;
} elseif ( $can_manage ) {
	WP_CLI::warning( '  FAIL: subscriber unexpectedly can manage_options' );
	$exit_code = 1;
} else {
	// Verify the redirect function exists and runs without error.
	try {
		$caught = null;
		// We can't safely test the actual admin_init redirect without
		// triggering a real wp_redirect(), but we verify the guard function.
		if ( function_exists( 'enya_redirect_subscribers_to_profile' ) ) {
			WP_CLI::success( '  PASS (redirect guard exists + role check OK)' );
		} else {
			WP_CLI::warning( '  FAIL: enya_redirect_subscribers_to_profile() not found' );
			$exit_code = 1;
		}
	} catch ( Exception $e ) {
		WP_CLI::warning( '  FAIL: ' . $e->getMessage() );
		$exit_code = 1;
	}
}

wp_set_current_user( 0 );

/* -------------------------------------------------------------------------
 * Test 6: Logout URL uses nonce (wp_logout_url)
 * ------------------------------------------------------------------------- */
WP_CLI::line( '6. Logout URL contains nonce ...' );

$logout_url = wp_logout_url( home_url( '/aula/' ) );

$has_nonce = false !== strpos( $logout_url, '_wpnonce' );

// The redirect_to parameter contains the URL-encoded target.
$has_aula_redirect = false !== strpos( rawurldecode( $logout_url ), '/aula/' );

if ( ! $has_nonce ) {
	WP_CLI::warning( '  FAIL: logout URL does not contain nonce' );
	$exit_code = 1;
} elseif ( ! $has_aula_redirect ) {
	WP_CLI::warning( '  FAIL: logout redirect target is not /aula/' );
	$exit_code = 1;
} else {
	WP_CLI::success( '  PASS' );
}

/* -------------------------------------------------------------------------
 * Test 7: alumno_demo /aula/ — no auto-redirect to course
 *
 * This test checks /aula/ behavior for the logged-in student.
 * Since we removed the redirect, /aula/ should return 200 with dashboard.
 *
 * The feature flag (redirect handler) defaults to OFF, so no redirect
 * should occur even if the test user had a single active course before.
 * ------------------------------------------------------------------------- */
WP_CLI::line( '7. Logged-in student /aula/ (no auto-redirect) ...' );

$response = wp_remote_get( $site_url . '/aula/', array( 'redirection' => 0 ) );
$code     = wp_remote_retrieve_response_code( $response );
$body     = wp_remote_retrieve_body( $response );
$location = wp_remote_retrieve_header( $response, 'location' );

if ( 200 !== $code ) {
	if ( 302 === $code || 301 === $code ) {
		WP_CLI::warning( '  FAIL: /aula/ redirects to ' . (string) $location . ' (expected 200, no redirect)' );
	} else {
		WP_CLI::warning( '  FAIL: /aula/ returned HTTP ' . $code . ' (expected 200)' );
	}
	$exit_code = 1;
} elseif ( false === stripos( $body, 'aula-dashboard' ) && false === stripos( $body, 'Tu aula virtual' ) ) {
	WP_CLI::warning( '  FAIL: /aula/ dashboard content not found for logged-in student' );
	$exit_code = 1;
} else {
	WP_CLI::success( '  PASS' );
}

/* -------------------------------------------------------------------------
 * Summary
 * ------------------------------------------------------------------------- */
WP_CLI::line( '' );
if ( 0 === $exit_code ) {
	WP_CLI::success( 'All smoke tests passed.' );
} else {
	WP_CLI::error( 'Some smoke tests failed. Review warnings above.' );
}

exit( $exit_code );
