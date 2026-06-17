<?php
/**
 * "Volver al aula" CTA injection.
 *
 * Injects a persistent "Volver al aula" link into course pages
 * (standard and Focus Mode) via LearnDash hooks and content filters.
 *
 * @package Escuela_LMS_Student_Access
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the "Volver al aula" CTA HTML.
 *
 * @return string The CTA markup.
 */
function escuela_lms_render_return_cta() {
	$return_url = home_url( '/aula/' );

	return sprintf(
		'<a href="%s" class="escuela-lms-return-cta">%s</a>',
		esc_url( $return_url ),
		esc_html__( 'Volver al aula', 'escuela-lms' )
	);
}

/**
 * Inject CTA into standard course pages via the_content filter.
 *
 * Skips injection in Focus Mode to avoid duplication (Focus Mode
 * has its own hook-based CTA via learndash-focus-header-nav-after).
 *
 * @param string $content The post content.
 * @return string Modified content with CTA prepended.
 */
function escuela_lms_inject_return_cta_content( $content ) {
	if ( ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	if ( 'sfwd-courses' !== get_post_type() ) {
		return $content;
	}

	// Skip if Focus Mode is active (duplicate CTA in header already).
	if ( function_exists( 'learndash_get_theme' ) && 'ld30' === learndash_get_theme() ) {
		$focus_mode_enabled = \LearnDash_Settings_Section::get_section_setting(
			'LearnDash_Settings_Theme_LD30',
			'focus_mode_enabled'
		);
		if ( 'yes' === $focus_mode_enabled ) {
			return $content;
		}
	}

	$user = wp_get_current_user();

	if ( ! $user instanceof WP_User || ! $user->ID ) {
		return $content;
	}

	if ( ! escuela_lms_is_aula_student( $user ) ) {
		return $content;
	}

	$cta = sprintf(
		'<div class="escuela-lms-return-cta-wrapper">%s</div>',
		escuela_lms_render_return_cta()
	);

	return $cta . $content;
}
add_filter( 'the_content', 'escuela_lms_inject_return_cta_content', 5 );

/**
 * Inject CTA into Focus Mode navigation area (after the logo area).
 *
 * Uses the learndash-focus-header-nav-after hook which fires in the masthead,
 * after the navigation links. This places the CTA prominently without breaking
 * existing LearnDash chrome.
 *
 * @param int $course_id The current course ID.
 * @param int $user_id   The current user ID.
 * @return void
 */
function escuela_lms_inject_return_cta_focus_mode( $course_id, $user_id ) {
	$user = get_userdata( $user_id );

	if ( ! $user ) {
		return;
	}

	if ( ! escuela_lms_is_aula_student( $user ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<nav class="escuela-lms-focus-return">' . escuela_lms_render_return_cta() . '</nav>';
}
add_action( 'learndash-focus-header-nav-after', 'escuela_lms_inject_return_cta_focus_mode', 10, 2 );
