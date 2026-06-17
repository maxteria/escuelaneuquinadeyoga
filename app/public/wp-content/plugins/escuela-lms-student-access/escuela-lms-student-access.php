<?php
/**
 * Plugin Name: Escuela LMS Student Access
 * Plugin URI: https://escuelaneuquinadeyoga.local
 * Description: Control de acceso para suscriptores: redirigir wp-admin a profile, ocultar admin bar y gestionar el flujo del Aula Virtual
 * Version: 1.1.0
 * Author: Escuela Neuquina de Yoga
 * License: GPL v2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load feature modules behind the feature flag.
 *
 * The redirect handler is disabled by default (feature flag = false)
 * and can be enabled via: wp option update escuela_lms_enable_single_course_redirect 1
 */
$redirect_handler_enabled = (bool) apply_filters(
	'escuela_lms_redirect_handler_enabled',
	get_option( 'escuela_lms_enable_single_course_redirect', false )
);

if ( $redirect_handler_enabled ) {
	require_once __DIR__ . '/includes/redirects.php';
}

// CTA module is always active (shows "Volver al aula" to students).
require_once __DIR__ . '/includes/cta.php';

/**
 * Enqueue CTA styles on frontend.
 */
add_action( 'wp_enqueue_scripts', 'escuela_lms_enqueue_cta_styles' );

function escuela_lms_enqueue_cta_styles() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$user = wp_get_current_user();

	if ( ! escuela_lms_is_aula_student( $user ) ) {
		return;
	}

	wp_enqueue_style(
		'escuela-lms-cta',
		plugin_dir_url( __FILE__ ) . 'assets/css/cta.css',
		array(),
		'1.0.0'
	);
}

/**
 * Redirect guests from /profile/ to /aula/
 * Only applies to non-logged-in users
 */
add_action( 'template_redirect', 'enya_redirect_guests_from_profile' );

function enya_redirect_guests_from_profile() {
    if ( ! is_user_logged_in() ) {
        $current_path = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
        
        // If accessing /profile/ or /profile (with or without trailing slash)
        if ( preg_match('#^/profile/?#', $current_path ) ) {
            wp_redirect( home_url( '/aula/' ) );
            exit;
        }
    }
}

/**
 * Redirect subscribers from wp-admin to /profile/
 * Only applies to users without management capabilities
 */
add_action( 'admin_init', 'enya_redirect_subscribers_to_profile' );

function enya_redirect_subscribers_to_profile() {
    $user = wp_get_current_user();

    // If user is subscriber (no manage_options capability) and trying to access wp-admin
    if ( in_array( 'subscriber', (array) $user->roles ) && ! user_can( $user, 'manage_options' ) ) {
        // Allow access to profile.php only (for profile editing)
        $current_page = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';

        if ( strpos( $current_page, '/wp-admin/profile.php' ) === false ) {
            wp_redirect( home_url( '/profile/' ) );
            exit;
        }
    }
}

/**
 * Hide admin bar on frontend for subscribers
 */
add_action( 'after_setup_theme', 'enya_hide_admin_bar_for_students' );

function enya_hide_admin_bar_for_students() {
    $user = wp_get_current_user();

    // Hide admin bar for subscribers without management capabilities
    if ( in_array( 'subscriber', (array) $user->roles ) && ! user_can( $user, 'manage_options' ) ) {
        show_admin_bar( false );
    }
}

/**
 * Remove admin bar for subscribers via user meta
 * Applied on user login to ensure consistency
 */
add_action( 'wp_login', 'enya_disable_admin_bar_on_login', 10, 2 );

function enya_disable_admin_bar_on_login( $user_login, $user ) {
    if ( in_array( 'subscriber', (array) $user->roles ) && ! user_can( $user, 'manage_options' ) ) {
        update_user_meta( $user->ID, 'show_admin_bar_front', 'false' );
    }
}