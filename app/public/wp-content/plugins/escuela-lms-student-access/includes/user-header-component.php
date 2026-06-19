<?php
/**
 * User header component.
 *
 * Renders a stateful header control inside the Kadence primary menu:
 * - Logged-out users see an "Enter Aula" CTA.
 * - Logged-in users see an avatar + greeting dropdown.
 *
 * @package Escuela_LMS_Student_Access
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'theme_mod_header_desktop_items', 'escuela_lms_remove_legacy_header_button' );

/**
 * Remove the legacy "Aula Virtual" button from the Kadence desktop header.
 *
 * The user header component now provides the Aula CTA / dropdown, so the
 * theme's header button is redundant.
 *
 * @param array $items Kadence header desktop items.
 * @return array
 */
function escuela_lms_remove_legacy_header_button( $items ) {
	if ( ! is_array( $items ) || ! isset( $items['main']['main_right'] ) ) {
		return $items;
	}

	$items['main']['main_right'] = array_values(
		array_diff( $items['main']['main_right'], array( 'button' ) )
	);

	return $items;
}

add_filter( 'wp_nav_menu_items', 'escuela_lms_inject_user_header_component', 20, 2 );

/**
 * Inject the user header component into the Kadence primary menu.
 *
 * @param string   $items Menu HTML.
 * @param stdClass $args  Menu arguments.
 * @return string
 */
function escuela_lms_inject_user_header_component( $items, $args ) {
	$allowed_locations = array( 'primary', 'mobile_navigation' );

	if ( empty( $args->theme_location ) || ! in_array( $args->theme_location, $allowed_locations, true ) ) {
		return $items;
	}

	$markup = escuela_lms_render_user_header_component();

	if ( ! $markup ) {
		return $items;
	}

	return $items . $markup;
}

/**
 * Render the appropriate header component based on authentication state.
 *
 * @return string
 */
function escuela_lms_render_user_header_component() {
	if ( is_user_logged_in() ) {
		return escuela_lms_render_user_dropdown();
	}

	return escuela_lms_render_aula_cta();
}

/**
 * Render the logged-in user dropdown.
 *
 * @return string
 */
function escuela_lms_render_user_dropdown() {
	$user = wp_get_current_user();

	if ( ! $user instanceof WP_User || ! $user->ID ) {
		return '';
	}

	$greeting_name = escuela_lms_get_user_greeting_name( $user );
	$avatar        = get_avatar( $user->ID, 40, '', '', array( 'class' => 'enyf-user-nav__avatar-img' ) );

	$dropdown_id = 'enyf-user-nav-dropdown-' . uniqid();

	$links = array(
		array(
			'href'  => home_url( '/profile/' ),
			'label' => __( 'Mi perfil', 'escuela-lms' ),
			'class' => 'enyf-user-nav__link',
		),
		array(
			'href'  => escuela_lms_get_aula_dashboard_url_safe(),
			'label' => __( 'Aula Virtual', 'escuela-lms' ),
			'class' => 'enyf-user-nav__link',
		),
		array(
			'href'  => wp_logout_url( home_url( '/aula/' ) ),
			'label' => __( 'Cerrar sesión', 'escuela-lms' ),
			'class' => 'enyf-user-nav__link enyf-user-nav__link--logout',
		),
	);

	$items_markup = '';

	foreach ( $links as $link ) {
		$items_markup .= sprintf(
			'<li class="enyf-user-nav__dropdown-item" role="none"><a class="%1$s" href="%2$s" role="menuitem">%3$s</a></li>',
			esc_attr( $link['class'] ),
			esc_url( $link['href'] ),
			esc_html( $link['label'] )
		);
	}

	$trigger_label = sprintf(
		/* translators: %s: user's first name or display name */
		__( 'Hola, %s', 'escuela-lms' ),
		$greeting_name
	);

	return sprintf(
		'<li class="menu-item menu-item-type-custom menu-item-object-custom enyf-user-nav__wrapper has-dropdown">
			<button class="enyf-user-nav__trigger" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="%4$s">
				<span class="enyf-user-nav__avatar" aria-hidden="true">%1$s</span>
				<span class="enyf-user-nav__label">%2$s</span>
				<span class="enyf-user-nav__caret" aria-hidden="true"></span>
			</button>
			<ul id="%4$s" class="enyf-user-nav__dropdown" role="menu">%3$s</ul>
		</li>',
		$avatar,
		esc_html( $trigger_label ),
		$items_markup,
		esc_attr( $dropdown_id )
	);
}

/**
 * Render the public Aula CTA for logged-out users.
 *
 * @return string
 */
function escuela_lms_render_aula_cta() {
	$label = __( 'Entrar al aula', 'escuela-lms' );
	$url   = escuela_lms_get_aula_dashboard_url_safe();

	$icon = '<svg class="enyf-user-nav__cta-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>';

	return sprintf(
		'<li class="menu-item menu-item-type-custom menu-item-object-custom enyf-user-nav__cta-item"><a class="enyf-user-nav__cta" href="%1$s">%3$s<span>%2$s</span></a></li>',
		esc_url( $url ),
		esc_html( $label ),
		$icon
	);
}

/**
 * Return a safe greeting name for the user.
 *
 * @param WP_User $user Current user.
 * @return string
 */
function escuela_lms_get_user_greeting_name( WP_User $user ) {
	$first_name = get_user_meta( $user->ID, 'first_name', true );

	if ( ! empty( $first_name ) ) {
		return $first_name;
	}

	if ( ! empty( $user->display_name ) ) {
		return $user->display_name;
	}

	return $user->user_login;
}

/**
 * Safely resolve the Aula dashboard URL.
 *
 * @return string
 */
function escuela_lms_get_aula_dashboard_url_safe() {
	if ( function_exists( 'escuela_lms_get_aula_dashboard_url' ) ) {
		return escuela_lms_get_aula_dashboard_url();
	}

	return trailingslashit( home_url( '/aula' ) );
}
