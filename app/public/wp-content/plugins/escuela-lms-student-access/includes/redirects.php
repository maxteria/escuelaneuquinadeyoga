<?php
/**
 * Aula redirect handler.
 *
 * Centralizes redirect decisioning for the Aula Virtual flow.
 * Controlled by feature flag: escuela_lms_enable_single_course_redirect (default: false).
 *
 * @package Escuela_LMS_Student_Access
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the feature flag status.
 *
 * @return bool True if the redirect handler is enabled.
 */
function escuela_lms_redirect_handler_enabled() {
	return (bool) apply_filters(
		'escuela_lms_redirect_handler_enabled',
		get_option( 'escuela_lms_enable_single_course_redirect', false )
	);
}

/**
 * Filter to customize the redirect allowlist.
 *
 * @param string[] $allowed List of allowed URL paths.
 * @return string[]
 */
function escuela_lms_redirect_allowlist( $allowed = array() ) {
	$defaults = array(
		home_url( '/aula/' ),
		home_url( '/profile/' ),
		home_url( '/courses/' ),
		home_url( '/registro-completado/' ),
	);

	return apply_filters( 'escuela_lms_redirect_allowlist', array_unique( array_merge( $defaults, $allowed ) ) );
}

/**
 * Log a redirect decision.
 *
 * @param string $reason  Why the redirect happened.
 * @param string $target  Where the user was sent.
 * @param int    $user_id The user ID (or 0 for guests).
 * @return void
 */
function escuela_lms_log_redirect( $reason, $target, $user_id = 0 ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		// Translators: 1: reason, 2: target URL, 3: user ID.
		$message = sprintf(
			'enya-redirect [%1$s] → %2$s (user: %3$d)',
			$reason,
			esc_url( $target ),
			absint( $user_id )
		);
		error_log( $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}
}

/**
 * Determine whether a redirect should happen for this user/course.
 *
 * @param WP_User $user The current user.
 * @return string|false The URL to redirect to, or false.
 */
function escuela_lms_should_redirect( $user ) {
	if ( ! $user instanceof WP_User || ! $user->ID ) {
		return false;
	}

	if ( ! escuela_lms_is_aula_student( $user ) ) {
		return false;
	}

	if ( ! function_exists( 'learndash_user_get_enrolled_courses' ) ) {
		return false;
	}

	$courses = learndash_user_get_enrolled_courses( $user->ID, array( 'num' => -1 ) );

	if ( empty( $courses ) || is_wp_error( $courses ) ) {
		return false;
	}

	if ( 1 !== count( $courses ) ) {
		return false;
	}

	$course_id = absint( reset( $courses ) );

	if ( ! $course_id ) {
		return false;
	}

	$target = get_permalink( $course_id );

	if ( ! $target ) {
		return false;
	}

	// Only redirect if the course is in progress (not completed).
	$progress = learndash_course_progress(
		array(
			'user_id'   => $user->ID,
			'course_id' => $course_id,
			'array'     => true,
		)
	);

	if ( isset( $progress['percentage'] ) && 100 === (int) $progress['percentage'] ) {
		return false;
	}

	return $target;
}

/**
 * Handle the aula redirect.
 * Hooked into template_redirect at low priority (20) so it runs after the basic page routing.
 *
 * @return void
 */
function escuela_lms_handle_aula_redirect() {
	if ( ! escuela_lms_redirect_handler_enabled() ) {
		return;
	}

	// Only act on /aula/ route.
	if ( ! escuela_lms_is_aula_route() ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		return;
	}

	$user = wp_get_current_user();

	if ( ! escuela_lms_is_aula_student( $user ) ) {
		return;
	}

	// Allow dashboard bypass via query param.
	if ( isset( $_GET['aula-dashboard'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$target = escuela_lms_should_redirect( $user );

	if ( ! $target ) {
		return;
	}

	$validated = wp_validate_redirect( $target, false );

	if ( false === $validated ) {
		escuela_lms_log_redirect( 'invalid_redirect_target', $target, $user->ID );
		return;
	}

	escuela_lms_log_redirect( 'single_active_course', $validated, $user->ID );
	wp_safe_redirect( $validated );
	exit;
}
add_action( 'template_redirect', 'escuela_lms_handle_aula_redirect', 20 );
