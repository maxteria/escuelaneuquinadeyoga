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

// User header component (stateful Aula header control).
require_once __DIR__ . '/includes/user-header-component.php';

/**
 * Enqueue CTA styles on frontend.
 */
add_action( 'wp_enqueue_scripts', 'escuela_lms_enqueue_cta_styles' );

/**
 * Enqueue user header component assets on frontend.
 */
add_action( 'wp_enqueue_scripts', 'escuela_lms_enqueue_user_header_assets' );

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

function escuela_lms_enqueue_user_header_assets() {
	$css_path = plugin_dir_path( __FILE__ ) . 'assets/css/user-header-component.css';
	$js_path  = plugin_dir_path( __FILE__ ) . 'assets/js/user-header-component.js';

	$css_version = file_exists( $css_path ) ? (string) filemtime( $css_path ) : '1.0.0';
	$js_version  = file_exists( $js_path ) ? (string) filemtime( $js_path ) : '1.0.0';

	wp_enqueue_style(
		'escuela-lms-user-header',
		plugin_dir_url( __FILE__ ) . 'assets/css/user-header-component.css',
		array(),
		$css_version
	);

	wp_enqueue_script(
		'escuela-lms-user-header',
		plugin_dir_url( __FILE__ ) . 'assets/js/user-header-component.js',
		array(),
		$js_version,
		true
	);
}

/**
 * Enqueue frontend auth form validation.
 */
add_action( 'wp_enqueue_scripts', 'escuela_lms_enqueue_auth_validation' );

function escuela_lms_enqueue_auth_validation() {
	if ( is_admin() ) {
		return;
	}

	$js_path = plugin_dir_path( __FILE__ ) . 'assets/js/auth-form-validation.js';
	if ( ! file_exists( $js_path ) ) {
		return;
	}

	wp_enqueue_script(
		'escuela-lms-auth-validation',
		plugin_dir_url( __FILE__ ) . 'assets/js/auth-form-validation.js',
		array(),
		(string) filemtime( $js_path ),
		true
	);

	$css_path = plugin_dir_path( __FILE__ ) . 'assets/css/auth-form-validation.css';
	if ( file_exists( $css_path ) ) {
		wp_enqueue_style(
			'escuela-lms-auth-validation',
			plugin_dir_url( __FILE__ ) . 'assets/css/auth-form-validation.css',
			array(),
			(string) filemtime( $css_path )
		);
	}
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

/**
 * Use frontend pages for auth flows instead of wp-login.php and legacy slugs.
 */
add_filter( 'lostpassword_url', 'escuela_lms_lostpassword_url', 10, 2 );
add_filter( 'register_url', 'escuela_lms_register_url' );

function escuela_lms_lostpassword_url( $lostpassword_url, $redirect ) {
    $url = home_url( '/recuperar-contrasena/' );
    if ( ! empty( $redirect ) ) {
        $url = add_query_arg( 'redirect_to', urlencode( $redirect ), $url );
    }
    return $url;
}

function escuela_lms_register_url( $register_url ) {
    return home_url( '/registro/' );
}

// Autologin after user registration (LearnDash / WP)
add_action('user_register', function($user_id) {
    if (is_admin()) return;

    $user = get_user_by('id', $user_id);
    if (!$user) return;

    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true);
});

// Override LearnDash registration redirect to Aula with autologin
add_filter('learndash_registration_redirect', function($url, $user_id) {
    if (!empty($user_id)) {
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);
    }
    return home_url('/aula/');
}, 10, 2);

// Force redirect after login (covers registration flows that log in the user)
add_filter('login_redirect', function($redirect_to, $request, $user) {
    if (is_wp_error($user) || !$user) {
        return $redirect_to;
    }

    // If coming from registration-related flows, send to Aula
    if (isset($_REQUEST['redirect_to']) && strpos($_REQUEST['redirect_to'], 'registro') !== false) {
        return home_url('/aula/');
    }

    return $redirect_to;
}, 10, 3);

/**
 * Translate LearnDash auth UI strings to Spanish on the frontend.
 */
add_filter( 'gettext', 'escuela_lms_translate_learndash_auth_strings', 10, 3 );

function escuela_lms_translate_learndash_auth_strings( $translation, $text, $domain ) {
    if ( is_admin() || $domain !== 'learndash' ) {
        return $translation;
    }

    $strings = array(
        'Forgot Password'                => '¿Olvidaste tu contraseña?',
        'Reset Password'                 => 'Restablecer contraseña',
        'Username or Email Address'      => 'Nombre de usuario o correo electrónico',
        'Username or Email Address *'    => 'Nombre de usuario o correo electrónico *',
        'Password'                       => 'Contraseña',
        'Log In'                         => 'Acceder',
        'Login'                          => 'Iniciar sesión',
        'Register'                       => 'Registrarse',
        'Remember Me'                    => 'Recuérdame',
        'Lost your password?'            => '¿Olvidaste tu contraseña?',
        'Create Account'                 => 'Crear cuenta',
        'Don\'t have an account? Create one!' => '¿No tenés una cuenta? ¡Creá una!',
        'Register your account'          => 'Registrá tu cuenta',
        'If an account with that username or email address exists, an email has been sent with password reset instructions.' => 'Si existe una cuenta con ese correo electrónico, recibirás un enlace para restablecer tu contraseña.',
    );

    if ( isset( $strings[ $text ] ) ) {
        return $strings[ $text ];
    }

    return $translation;
}

/**
 * Redirect legacy auth slugs to the current friendly URLs.
 */
add_action( 'template_redirect', 'escuela_lms_redirect_legacy_auth_slugs' );

function escuela_lms_redirect_legacy_auth_slugs() {
    if ( is_admin() ) {
        return;
    }

    $current_path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) : '';
    if ( empty( $current_path ) ) {
        return;
    }

    $legacy_slugs = array(
        '/registration-2/' => home_url( '/registro/' ),
        '/registration-2'  => home_url( '/registro/' ),
        '/reset-password/' => home_url( '/recuperar-contrasena/' ),
        '/reset-password'  => home_url( '/recuperar-contrasena/' ),
    );

    if ( isset( $legacy_slugs[ $current_path ] ) ) {
        wp_redirect( $legacy_slugs[ $current_path ], 301 );
        exit;
    }
}
