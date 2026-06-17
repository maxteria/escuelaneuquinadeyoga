<?php
/**
 * Fix: Ensure logo + site title appear in header at all breakpoints.
 *
 * Problem: Kadence header layout has no branding item configured for desktop,
 * and mobile/tablet hide the site title.
 *
 * Solution:
 *   1. Inject branding into desktop header via JS (cloned from mobile)
 *   2. Force site title visible via CSS at all breakpoints
 */
add_action('wp_footer', function() {
    ?>
    <script>
    (function() {
        var leftSection = document.querySelector('.site-header-upper-wrap .site-header-main-section-left');
        if (!leftSection || leftSection.children.length > 0) return;
        var mobileBranding = document.querySelector('.site-mobile-header-wrap .site-branding');
        if (!mobileBranding) return;
        var clone = mobileBranding.cloneNode(true);
        clone.classList.remove('mobile-site-branding');
        clone.classList.add('desktop-site-branding');
        leftSection.insertBefore(clone, leftSection.firstChild);
    })();
    (function() {
        var btn = document.querySelector('.header-button');
        if (!btn || btn.dataset.enyfIcon) return;
        btn.dataset.enyfIcon = '1';
        btn.style.display = 'inline-flex';
        btn.style.alignItems = 'center';
        btn.style.gap = '8px';
        var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('width', '16');
        svg.setAttribute('height', '16');
        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('fill', 'none');
        svg.setAttribute('stroke', 'currentColor');
        svg.setAttribute('stroke-width', '2');
        svg.setAttribute('stroke-linecap', 'round');
        svg.setAttribute('stroke-linejoin', 'round');
        svg.setAttribute('aria-hidden', 'true');
        svg.innerHTML = '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>';
        btn.insertBefore(svg, btn.firstChild);
    })();
    </script>
    <?php
});

add_action('wp_head', function() {
    ?>
    <style>
    /* Force site title visible in mobile branding */
    .mobile-site-branding .site-title {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        font-size: clamp(13px, 1.5vw, 18px);
        white-space: nowrap;
        line-height: 1.2;
    }
    /* Logo sizing for mobile */
    .mobile-site-branding .custom-logo {
        max-height: 42px;
        width: auto;
    }
    /* Desktop branding */
    .desktop-site-branding .site-title {
        display: block !important;
        font-size: clamp(16px, 1.3vw, 22px);
        white-space: nowrap;
        line-height: 1.2;
    }
    .desktop-site-branding .custom-logo {
        max-height: 48px;
        width: auto;
    }
    /* Branding layout */
    .site-branding {
        display: flex !important;
        align-items: center;
        gap: 10px;
    }
    .site-branding .brand {
        display: flex !important;
        align-items: center;
        gap: 10px;
    }
    </style>
    <?php
});

add_filter( 'wp_nav_menu_items', 'escuela_lms_mu_inject_aula_nav_items', 20, 2 );

add_action( 'learndash-focus-header-nav-after', 'escuela_lms_mu_focus_return_link', 15, 2 );

add_filter( 'learndash_focus_header_user_dropdown_items', 'escuela_lms_mu_focus_dropdown_link', 15, 3 );

/**
 * Append Aula-specific links to the Kadence primary menu for students.
 *
 * @param string   $items Menu HTML items.
 * @param stdClass $args  Menu arguments.
 *
 * @return string
 */
function escuela_lms_mu_inject_aula_nav_items( $items, $args ) {
    $allowed_locations = array( 'primary', 'mobile_navigation' );

    if ( empty( $args->theme_location ) || ! in_array( $args->theme_location, $allowed_locations, true ) ) {
        return $items;
    }

    $markup = escuela_lms_mu_render_nav_markup( true );

    if ( ! $markup ) {
        return $items;
    }

    return $items . $markup;
}

/**
 * Render Aula nav component (dropdown or CTA).
 *
 * @param bool $include_return Include "Volver al aula" control for students.
 *
 * @return string
 */
function escuela_lms_mu_render_nav_markup( $include_return = false ) {
    if ( escuela_lms_mu_is_aula_student() ) {
        return escuela_lms_mu_render_dropdown_markup( $include_return );
    }

    return escuela_lms_mu_render_public_cta();
}

/**
 * Render Aula dropdown markup for student roles.
 *
 * @param bool $include_return Include "Volver al aula" action.
 *
 * @return string
 */
function escuela_lms_mu_render_dropdown_markup( $include_return = false ) {
    $profile_url = home_url( '/profile/' );
    $logout_url  = wp_logout_url( home_url( '/' ) );

    $links = array();

    if ( $include_return ) {
        $links[] = array(
            'href'  => escuela_lms_mu_get_aula_return_url(),
            'label' => __( 'Volver al aula', 'escuela-lms' ),
            'class' => 'escuela-aula-nav__link escuela-aula-nav__link--return',
        );
    }

    $links[] = array(
        'href'  => $profile_url,
        'label' => __( 'Mi perfil', 'escuela-lms' ),
        'class' => 'escuela-aula-nav__link',
    );

    $links[] = array(
        'href'  => $logout_url,
        'label' => __( 'Salir del aula', 'escuela-lms' ),
        'class' => 'escuela-aula-nav__link escuela-aula-nav__link--logout',
    );

    $items_markup = '';

    foreach ( $links as $link ) {
        $items_markup .= sprintf(
            '<li class="escuela-aula-nav__dropdown-item" role="none"><a class="%1$s" href="%2$s" role="menuitem">%3$s</a></li>',
            esc_attr( $link['class'] ),
            esc_url( $link['href'] ),
            esc_html( $link['label'] )
        );
    }

    $trigger_label = __( 'Mi Aula', 'escuela-lms' );

    return sprintf(
        '<li class="menu-item menu-item-type-custom menu-item-object-custom escuela-aula-nav__wrapper has-dropdown"><button class="escuela-aula-nav__trigger" type="button" aria-haspopup="true" aria-expanded="false">%1$s<span class="escuela-aula-nav__caret" aria-hidden="true"></span></button><ul class="escuela-aula-nav__dropdown" role="menu">%2$s</ul></li>',
        esc_html( $trigger_label ),
        $items_markup
    );
}

/**
 * Render Aula CTA button for public users.
 *
 * @return string
 */
function escuela_lms_mu_render_public_cta() {
    $label = __( 'Aula Virtual', 'escuela-lms' );
    $url   = home_url( '/aula/' );

    return sprintf(
        '<li class="menu-item menu-item-type-custom menu-item-object-custom escuela-aula-nav__cta-item"><a class="escuela-aula-nav__cta" href="%1$s">%2$s</a></li>',
        esc_url( $url ),
        esc_html( $label )
    );
}

/**
 * Output a focus-mode return to Aula button.
 *
 * @param int $course_id Course ID.
 * @param int $user_id   User ID.
 *
 * @return void
 */
function escuela_lms_mu_focus_return_link( $course_id, $user_id ) {
    if ( ! escuela_lms_mu_should_render_focus_links( $user_id ) ) {
        return;
    }

    $return_url = escuela_lms_mu_get_aula_return_url();

    echo '<a class="escuela-focus-return" href="' . esc_url( $return_url ) . '">' . esc_html__( 'Volver al aula', 'escuela-lms' ) . '</a>';
}

/**
 * Prepend Aula link to the focus-mode user dropdown.
 *
 * @param array $items     Existing menu items.
 * @param int   $course_id Course ID.
 * @param int   $user_id   User ID.
 *
 * @return array
 */
function escuela_lms_mu_focus_dropdown_link( $items, $course_id, $user_id ) {
    if ( ! escuela_lms_mu_should_render_focus_links( $user_id ) ) {
        return $items;
    }

    if ( isset( $items['escuela-aula-return'] ) ) {
        return $items;
    }

    $items = (array) $items;

    $items = array_merge(
        array(
            'escuela-aula-return' => array(
                'url'     => escuela_lms_mu_get_aula_return_url(),
                'label'   => __( 'Volver al aula', 'escuela-lms' ),
                'classes' => 'ld-focus-menu-link ld-focus-menu-escuela-aula',
                'target'  => '',
                'xfn'     => '',
                'attr_title' => '',
            ),
        ),
        $items
    );

    return $items;
}

/**
 * Determine whether the current visitor should see Aula-specific controls.
 *
 * @param int|null $user_id User ID context.
 *
 * @return bool
 */
function escuela_lms_mu_should_render_focus_links( $user_id = null ) {
    if ( empty( $user_id ) ) {
        $user_id = get_current_user_id();
    }

    if ( ! $user_id ) {
        return false;
    }

    return escuela_lms_mu_is_aula_student( $user_id );
}

/**
 * Identify if the current page context is the Aula dashboard or related views.
 *
 * @param string $scope Context scope (aula|course|any).
 *
 * @return bool
 */
function escuela_lms_mu_is_aula_context( $scope = 'any' ) {
    if ( ! is_user_logged_in() ) {
        return false;
    }

    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
    $is_aula_page = function_exists( 'is_page' ) && ( is_page( 'aula' ) || is_page( 1652 ) );
    $is_aula_slug = (bool) preg_match( '#/aula/?#', $request_uri );
    $is_course     = escuela_lms_mu_is_course_context();

    if ( 'aula' === $scope ) {
        return $is_aula_page || $is_aula_slug;
    }

    if ( 'course' === $scope ) {
        return $is_course;
    }

    return $is_aula_page || $is_aula_slug || $is_course;
}

/**
 * Helper to detect Aula students without relying on plugin load order.
 *
 * @param null|int|WP_User $user User reference.
 *
 * @return bool
 */
function escuela_lms_mu_is_aula_student( $user = null ) {
    if ( function_exists( 'escuela_lms_is_aula_student' ) ) {
        return escuela_lms_is_aula_student( $user );
    }

    if ( null === $user ) {
        $user = wp_get_current_user();
    } elseif ( is_numeric( $user ) ) {
        $user = get_user_by( 'id', absint( $user ) );
    }

    if ( ! ( $user instanceof WP_User ) || ! $user->exists() ) {
        return false;
    }

    if ( user_can( $user, 'manage_options' ) || user_can( $user, 'edit_users' ) ) {
        return false;
    }

    if ( function_exists( 'learndash_is_group_leader_user' ) && learndash_is_group_leader_user( $user ) ) {
        return false;
    }

    return user_can( $user, 'read' );
}

/**
 * Aula dashboard return URL used for focus-mode escape hatches.
 *
 * @return string
 */
function escuela_lms_mu_get_aula_return_url() {
    return add_query_arg( 'aula-dashboard', '1', trailingslashit( home_url( '/aula' ) ) );
}

/**
 * Determine whether current request is a LearnDash course page.
 *
 * @return bool
 */
function escuela_lms_mu_is_course_context() {
    return function_exists( 'is_singular' ) && is_singular( 'sfwd-courses' );
}

if ( ! function_exists( 'escuela_lms_mu_dropdown_script' ) ) {
    add_action( 'wp_footer', 'escuela_lms_mu_dropdown_script', 25 );

    /**
     * Inline toggle script for Aula header dropdown.
     */
    function escuela_lms_mu_dropdown_script() {
        ?>
        <script>
        (function () {
            const wrappers = Array.from(document.querySelectorAll('.escuela-aula-nav__wrapper.has-dropdown'));
            if (!wrappers.length) {
                return;
            }

            const closeAll = () => {
                wrappers.forEach((wrapper) => {
                    wrapper.classList.remove('is-open');
                    const trigger = wrapper.querySelector('.escuela-aula-nav__trigger');
                    if (trigger) {
                        trigger.setAttribute('aria-expanded', 'false');
                    }
                });
            };

            wrappers.forEach((wrapper) => {
                const trigger = wrapper.querySelector('.escuela-aula-nav__trigger');
                if (!trigger) {
                    return;
                }

                trigger.addEventListener('click', function (event) {
                    event.preventDefault();
                    const isOpen = wrapper.classList.contains('is-open');
                    closeAll();
                    if (!isOpen) {
                        wrapper.classList.add('is-open');
                        trigger.setAttribute('aria-expanded', 'true');
                    }
                });

                trigger.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        closeAll();
                        trigger.focus();
                    }
                });
            });

            document.addEventListener('click', function (event) {
                if (event.target.closest('.escuela-aula-nav__wrapper')) {
                    return;
                }
                closeAll();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeAll();
                }
            });
        })();
        </script>
        <?php
    }
}
