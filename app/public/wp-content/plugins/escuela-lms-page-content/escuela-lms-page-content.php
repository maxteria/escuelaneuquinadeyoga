<?php
/**
 * Plugin Name: Escuela LMS Page Content
 * Description: Shortcodes para contenido de páginas
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return the fully-qualified uploads URL for a relative path.
 * Never hardcode an environment-specific domain.
 */
function escuela_upload_url( $path ) {
    return home_url( '/wp-content/uploads/' . ltrim( $path, '/' ) );
}

if ( ! class_exists( 'Escuela_Aula_Dashboard_Service' ) ) {
    /**
     * Provides LearnDash-backed data for the Aula dashboard.
     */
    class Escuela_Aula_Dashboard_Service {
        /**
         * Cached datasets keyed by user ID.
         *
         * @var array<int,array>
         */
        protected static $cache = array();

        /**
         * Return the Aula dashboard dataset for a user.
         *
         * @param int $user_id WordPress user ID.
         * @return array
         */
        public static function get( $user_id ) {
            $user_id = absint( $user_id );

            $default = self::empty_payload();

            if ( ! $user_id ) {
                $default['meta']['error'] = __( 'No pudimos identificar a la persona que inició sesión.', 'escuela-lms' );
                return $default;
            }

            if ( isset( self::$cache[ $user_id ] ) ) {
                return self::$cache[ $user_id ];
            }

            if ( ! function_exists( 'learndash_user_get_enrolled_courses' ) ) {
                $default['meta']['error'] = __( 'La integración con LearnDash no está disponible en este momento.', 'escuela-lms' );
                return self::$cache[ $user_id ] = $default;
            }

            try {
                $courses = learndash_user_get_enrolled_courses(
                    $user_id,
                    array(
                        'num' => -1,
                    )
                );
            } catch ( Throwable $throwable ) {
                $default['meta']['error'] = $throwable->getMessage();
                return self::$cache[ $user_id ] = $default;
            }

            if ( is_wp_error( $courses ) ) {
                $default['meta']['error'] = $courses->get_error_message();
                return self::$cache[ $user_id ] = $default;
            }

            if ( empty( $courses ) ) {
                return self::$cache[ $user_id ] = $default;
            }

            $active    = array();
            $completed = array();

            foreach ( $courses as $course_id ) {
                $course_id = absint( $course_id );

                if ( ! $course_id ) {
                    continue;
                }

                $entry = self::build_course_entry( $user_id, $course_id );

                if ( empty( $entry ) ) {
                    continue;
                }

                if ( 'completed' === $entry['status'] ) {
                    $completed[] = $entry;
                } else {
                    $active[] = $entry;
                }
            }

            usort( $active, array( __CLASS__, 'sort_by_last_activity' ) );
            usort( $completed, array( __CLASS__, 'sort_by_last_activity' ) );

            $payload = array(
                'active'    => $active,
                'completed' => $completed,
                'meta'      => array(
                    'single_active' => 1 === count( $active ),
                    'has_courses'   => ! empty( $active ) || ! empty( $completed ),
                    'error'         => null,
                ),
            );

            return self::$cache[ $user_id ] = apply_filters( 'escuela_lms_aula_dashboard_data', $payload, $user_id );
        }

        /**
         * Clear cached dataset for one or all users.
         *
         * @param int|null $user_id User ID or null for all.
         * @return void
         */
        public static function reset( $user_id = null ) {
            if ( null === $user_id ) {
                self::$cache = array();
                return;
            }

            $user_id = absint( $user_id );

            if ( $user_id && isset( self::$cache[ $user_id ] ) ) {
                unset( self::$cache[ $user_id ] );
            }
        }

        /**
         * Build a single course card dataset.
         *
         * @param int $user_id   User ID.
         * @param int $course_id Course ID.
         *
         * @return array
         */
        protected static function build_course_entry( $user_id, $course_id ) {
            $course_post = get_post( $course_id );

            if ( ! $course_post instanceof WP_Post ) {
                return array();
            }

            $progress = self::resolve_progress( $user_id, $course_id );
            $status   = self::resolve_status( $user_id, $course_id, $progress );

            $last_activity = self::resolve_last_activity( $user_id, $course_id );

            return array(
                'course_id'     => $course_id,
                'title'         => get_the_title( $course_id ),
                'resume_url'    => self::resolve_resume_url( $user_id, $course_id, $status ),
                'course_url'    => get_permalink( $course_id ),
                'progress'      => $progress,
                'status'        => $status,
                'last_activity' => $last_activity,
                'return_url'    => self::dashboard_url(),
            );
        }

        /**
         * Resolve course progress counters.
         *
         * @param int $user_id   User ID.
         * @param int $course_id Course ID.
         *
         * @return array
         */
        protected static function resolve_progress( $user_id, $course_id ) {
            $progress = array(
                'completed' => 0,
                'total'     => 0,
                'percent'   => 0,
            );

            if ( function_exists( 'learndash_course_progress' ) ) {
                $raw = learndash_course_progress(
                    array(
                        'user_id' => $user_id,
                        'course_id' => $course_id,
                        'array' => true,
                    )
                );

                if ( is_array( $raw ) ) {
                    if ( isset( $raw['completed'] ) ) {
                        $progress['completed'] = (int) $raw['completed'];
                    }

                    if ( isset( $raw['total'] ) ) {
                        $progress['total'] = (int) $raw['total'];
                    }

                    if ( isset( $raw['percentage'] ) ) {
                        $progress['percent'] = max( 0, min( 100, (int) $raw['percentage'] ) );
                    } elseif ( $progress['total'] > 0 ) {
                        $progress['percent'] = min( 100, (int) round( ( $progress['completed'] / $progress['total'] ) * 100 ) );
                    }
                }
            }

            return $progress;
        }

        /**
         * Resolve LearnDash status for the course.
         *
         * @param int   $user_id  User ID.
         * @param int   $course_id Course ID.
         * @param array $progress  Progress data.
         *
         * @return string
         */
        protected static function resolve_status( $user_id, $course_id, $progress ) {
            $status = 'not_started';

            if ( function_exists( 'learndash_user_get_course_progress' ) ) {
                $course_progress = learndash_user_get_course_progress( $user_id, $course_id );

                if ( isset( $course_progress['status'] ) ) {
                    $status = $course_progress['status'];
                }
            }

            if ( 'completed' !== $status && isset( $progress['percent'] ) && 100 === (int) $progress['percent'] ) {
                $status = 'completed';
            }

            return $status;
        }

        /**
         * Determine resume URL for the next step.
         *
         * @param int    $user_id   User ID.
         * @param int    $course_id Course ID.
         * @param string $status    Course status.
         *
         * @return string
         */
        protected static function resolve_resume_url( $user_id, $course_id, $status ) {
            $course_url = get_permalink( $course_id );

            if ( 'completed' === $status ) {
                return $course_url;
            }

            if ( function_exists( 'learndash_user_progress_get_first_incomplete_step' ) ) {
                $step_id = learndash_user_progress_get_first_incomplete_step( $user_id, $course_id );

                if ( $step_id ) {
                    $step_url = learndash_get_step_permalink( $step_id, $course_id );

                    if ( $step_url ) {
                        return $step_url;
                    }
                }
            }

            return $course_url;
        }

        /**
         * Capture the last activity timestamp for ordering.
         *
         * @param int $user_id   User ID.
         * @param int $course_id Course ID.
         *
         * @return int
         */
        protected static function resolve_last_activity( $user_id, $course_id ) {
            $last_activity = 0;

            if ( function_exists( 'learndash_get_user_activity' ) ) {
                $activity = learndash_get_user_activity(
                    array(
                        'user_id'       => $user_id,
                        'course_id'     => $course_id,
                        'post_id'       => $course_id,
                        'activity_type' => 'course',
                    )
                );

                if ( $activity instanceof LDLMS_Model_Activity ) {
                    if ( ! empty( $activity->activity_updated ) ) {
                        $last_activity = (int) $activity->activity_updated;
                    } elseif ( ! empty( $activity->activity_completed ) ) {
                        $last_activity = (int) $activity->activity_completed;
                    } elseif ( ! empty( $activity->activity_started ) ) {
                        $last_activity = (int) $activity->activity_started;
                    }
                }
            }

            if ( ! $last_activity && function_exists( 'ld_course_access_from' ) ) {
                $last_activity = (int) ld_course_access_from( $course_id, $user_id );
            }

            return $last_activity;
        }

        /**
         * Sort utility by last activity (desc).
         *
         * @param array $a First item.
         * @param array $b Second item.
         *
         * @return int
         */
        protected static function sort_by_last_activity( $a, $b ) {
            $a_time = isset( $a['last_activity'] ) ? (int) $a['last_activity'] : 0;
            $b_time = isset( $b['last_activity'] ) ? (int) $b['last_activity'] : 0;

            if ( $a_time === $b_time ) {
                return 0;
            }

            return ( $a_time < $b_time ) ? 1 : -1;
        }

        /**
         * Base payload skeleton.
         *
         * @return array
         */
        protected static function empty_payload() {
            return array(
                'active'    => array(),
                'completed' => array(),
                'meta'      => array(
                    'single_active' => false,
                    'has_courses'   => false,
                    'error'         => null,
                ),
            );
        }

        /**
         * Dashboard base URL.
         *
         * @return string
         */
        protected static function dashboard_url() {
            return escuela_lms_get_aula_dashboard_url();
        }
    }
}

/**
 * Determine if the current user should see the Aula dashboard experience.
 *
 * @param null|int|WP_User $user Optional user reference.
 * @return bool
 */
function escuela_lms_is_aula_student( $user = null ) {
    if ( null === $user ) {
        $user = wp_get_current_user();
    } elseif ( is_numeric( $user ) ) {
        $user = get_user_by( 'id', absint( $user ) );
    }

    if ( ! ( $user instanceof WP_User ) ) {
        return false;
    }

    if ( ! $user->exists() ) {
        return false;
    }

    if ( user_can( $user, 'manage_options' ) || user_can( $user, 'edit_users' ) ) {
        return false;
    }

    if ( function_exists( 'learndash_is_group_leader_user' ) && learndash_is_group_leader_user( $user ) ) {
        return false;
    }

    if ( function_exists( 'learndash_is_admin_user' ) && learndash_is_admin_user( $user ) ) {
        return false;
    }

    /**
     * Filter Aula student capability check.
     */
    $is_student = apply_filters( 'escuela_lms_is_aula_student', user_can( $user, 'read' ), $user );

    return (bool) $is_student;
}

/**
 * Return the canonical Aula dashboard URL.
 *
 * @return string
 */
function escuela_lms_get_aula_dashboard_url() {
    return trailingslashit( home_url( '/aula' ) );
}

/**
 * Return Aula dashboard URL with redirect bypass query.
 *
 * @return string
 */
function escuela_lms_get_aula_dashboard_bypass_url() {
    return add_query_arg( 'aula-dashboard', '1', escuela_lms_get_aula_dashboard_url() );
}

/**
 * Determine whether the current request targets the Aula dashboard route.
 *
 * @return bool
 */
function escuela_lms_is_aula_route() {
    if ( function_exists( 'is_page' ) && ( is_page( 'aula' ) || is_page( 1652 ) ) ) {
        return true;
    }

    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';

    return (bool) preg_match( '#/aula/?#', $request_uri );
}

add_action( 'wp_enqueue_scripts', 'escuela_lms_page_content_enqueue_assets' );

/**
 * Register and enqueue plugin styles with scoped contexts.
 *
 * @return void
 */
function escuela_lms_page_content_enqueue_assets() {
    $css_base_url  = plugin_dir_url( __FILE__ ) . 'assets/css/';
    $css_base_path = plugin_dir_path( __FILE__ ) . 'assets/css/';

    $focus_path    = $css_base_path . 'learndash-focus-overrides.css';
    $focus_version = file_exists( $focus_path ) ? (string) filemtime( $focus_path ) : null;

    wp_register_style(
        'escuela-ld-focus-overrides',
        $css_base_url . 'learndash-focus-overrides.css',
        array(),
        $focus_version
    );

    wp_enqueue_style( 'escuela-ld-focus-overrides' );

    $style_map = array(
        'escuela-formaciones-landing' => 'formaciones-landing.css',
        'escuela-instructora'         => 'instructora.css',
        'escuela-retiros'             => 'retiros.css',
        'escuela-la-escuela'          => 'la-escuela.css',
        'escuela-aula-dashboard'      => 'aula.css',
    );

    foreach ( $style_map as $handle => $filename ) {
        $path    = $css_base_path . $filename;
        $version = file_exists( $path ) ? (string) filemtime( $path ) : null;

        if ( ! wp_style_is( $handle, 'registered' ) ) {
            wp_register_style(
                $handle,
                $css_base_url . $filename,
                array( 'escuela-ld-focus-overrides' ),
                $version
            );
        }
    }

    if ( is_page( 'courses' ) || is_post_type_archive( 'sfwd-courses' ) || is_page( 11 ) || is_singular( 'sfwd-courses' ) ) {
        wp_enqueue_style( 'escuela-formaciones-landing' );
    }

    if ( is_page( 1483 ) || is_page( 1492 ) || is_page( 1493 ) || is_page( 'courses' ) || is_post_type_archive( 'sfwd-courses' ) ) {
        wp_enqueue_style( 'escuela-instructora' );
    }

    if ( is_page( 'retiros' ) ) {
        wp_enqueue_style( 'escuela-retiros' );
    }

    if ( is_page( 1492 ) ) {
        wp_enqueue_style( 'escuela-la-escuela' );
    }

    if ( escuela_lms_is_aula_route() ) {
        wp_enqueue_style( 'escuela-aula-dashboard' );
    }
}

add_action( 'template_redirect', 'escuela_lms_page_content_template_redirect', 1 );

/**
 * Handle Aula redirects and virtual templates before rendering.
 *
 * @return void
 */
function escuela_lms_page_content_template_redirect() {
    $request = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';

    if ( is_page( 'retiros' ) ) {
        global $post;
        $post->post_content = do_shortcode( '[retiros_landing]' );
    }

    if ( false !== strpos( $request, 'registration-success-2' ) ) {
        // Redirect directly to Aula after registration (autologin handled via hooks)
        wp_safe_redirect( home_url( '/aula/' ) );
        exit;
    }

    if ( is_post_type_archive( 'sfwd-courses' ) ) {
        global $wp_query;

        $virtual_post = new WP_Post(
            (object) array(
                'ID'                    => 0,
                'post_author'           => 1,
                'post_date'             => current_time( 'mysql' ),
                'post_date_gmt'         => current_time( 'mysql', true ),
                'post_content'          => do_shortcode( '[formaciones_landing]' ),
                'post_title'            => '',
                'post_excerpt'          => '',
                'post_status'           => 'publish',
                'comment_status'        => 'closed',
                'ping_status'           => 'closed',
                'post_password'         => '',
                'to_ping'               => '',
                'pinged'                => '',
                'post_modified'         => current_time( 'mysql' ),
                'post_modified_gmt'     => current_time( 'mysql', true ),
                'post_content_filtered' => '',
                'post_parent'           => 0,
                'guid'                  => '',
                'menu_order'            => 0,
                'post_type'             => 'page',
                'post_mime_type'        => '',
                'comment_count'         => 0,
            )
        );

        $virtual_post->filter      = 'raw';
        $wp_query->posts           = array( $virtual_post );
        $wp_query->post_count      = 1;
        $wp_query->queried_object  = $virtual_post;
        $wp_query->queried_object_id = 0;
        $wp_query->is_archive      = false;
        $wp_query->is_page         = true;
    }
}

add_filter('body_class', function($classes) {
    if (is_post_type_archive('sfwd-courses')) {
        $classes[] = 'enyf-courses';
        $classes[] = 'enyf-landing';
    }
    if (is_singular('sfwd-courses')) {
        $classes[] = 'enyf-courses';
    }
    return $classes;
});

add_shortcode('la_escuela_registration_success', function() {
    return '<div style="background:#faf7f4;padding:80px 20px;text-align:center;min-height:60vh;display:flex;flex-direction:column;justify-content:center;align-items:center;">
        <h1 style="font-family:Gilda Display;font-size:48px;color:#3D3229;margin-bottom:30px;">Registro completado</h1>
        <p style="font-family:Raleway;font-size:20px;color:#5C4F43;max-width:600px;margin:0 auto 24px;line-height:1.8;">Tu cuenta se activó correctamente. Ya podés comenzar tu recorrido en La Escuela.</p>
        <p style="font-family:Raleway;font-size:18px;color:#5C4F43;max-width:600px;margin:0 auto 16px;line-height:1.7;">Seguimos así:</p>
        <ol style="font-family:Raleway;font-size:18px;color:#5C4F43;max-width:600px;margin:0 auto 36px;padding-left:22px;text-align:left;line-height:1.7;">
            <li>Ingresá a tu Aula Virtual con el botón de abajo.</li>
            <li>En tu perfil, elegí <strong>Formación en Meditación</strong>.</li>
            <li>Completá la primera lección para iniciar el recorrido.</li>
        </ol>
        <div style="display:flex;gap:20px;flex-wrap:wrap;justify-content:center;">
            <a href="/aula/" style="background:#6B7F59;color:#fff;padding:18px 40px;text-decoration:none;border-radius:4px;font-family:Cabin;font-weight:500;display:inline-block;"><svg class="enyf-icon-enter" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg> Aula Virtual</a>
            <a href="/courses/" style="background:transparent;color:#6B7F59;padding:18px 40px;text-decoration:none;border:2px solid #6B7F59;border-radius:4px;font-family:Cabin;font-weight:500;display:inline-block;">Ver formaciones</a>
        </div>
    </div>';
});

add_shortcode( 'la_escuela_hub', 'escuela_lms_render_aula_shortcode' );

/**
 * Render Aula dashboard shortcode with role-aware content.
 *
 * @return string
 */
function escuela_lms_render_aula_shortcode() {
    wp_enqueue_style( 'escuela-aula-dashboard' );

    if ( ! is_user_logged_in() ) {
        return escuela_lms_render_aula_guest_view();
    }

    $user = wp_get_current_user();

    if ( ! escuela_lms_is_aula_student( $user ) ) {
        return escuela_lms_render_aula_restricted_card( $user );
    }

    $aula_data = Escuela_Aula_Dashboard_Service::get( $user->ID );
    $template  = plugin_dir_path( __FILE__ ) . 'templates/aula-dashboard.php';

    if ( ! file_exists( $template ) ) {
        return escuela_lms_render_aula_restricted_card( $user );
    }

    $aula_user = $user;

    ob_start();
    /** @var WP_User $aula_user */
    /** @var array    $aula_data */
    include $template;

    return ob_get_clean();
}

/**
 * Guest state for Aula landing.
 *
 * @return string
 */
function escuela_lms_render_aula_guest_view() {
    $img_id  = 1551;
    $img_url = wp_get_attachment_image_url( $img_id, 'large' );

    if ( ! $img_url ) {
        $img_url = plugin_dir_url( __FILE__ ) . 'assets/images/aula-default.jpg';
    }

    $login_form = do_shortcode( '[learndash_login login_label="Iniciar sesión o registrarse"]' );

    ob_start();
    ?>
    <section class="enya-aula-hero">
        <div class="enya-aula-hero__inner">
            <div class="enya-aula-hero__content">
                <span class="enya-aula-hero__eyebrow">AULA VIRTUAL</span>
                <h1 class="enya-aula-hero__title">Entrá a tu espacio de aprendizaje</h1>
                <p class="enya-aula-hero__text">Accedé a tus cursos, materiales, videos y recorridos de práctica desde un solo lugar.</p>
                <div class="enya-aula-hero__cta" data-aula-login><?php echo $login_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                <p class="enya-aula-hero__footnote">Si ya tenés cuenta, ingresá con tus datos. Si es tu primera vez, podés crearla desde el mismo acceso.</p>
            </div>
            <div class="enya-aula-hero__visual" aria-hidden="true">
                <div class="enya-aula-hero__image-wrapper">
                    <img src="<?php echo esc_url( $img_url ); ?>" alt="" loading="lazy" />
                </div>
            </div>
        </div>
    </section>
    <script>
        // LearnDash moves the login modal wrapper to <body>. Bring it back inline
        // so the form is visible without an extra click.
        (function () {
            var cta = document.querySelector('[data-aula-login]');
            if (!cta) {
                return;
            }

            function moveWrapper() {
                var modal = document.getElementById('ld-login-modal');
                if (!modal) {
                    return false;
                }
                var wrapper = modal.closest('.learndash-wrapper-login-modal');
                if (wrapper && !cta.contains(wrapper)) {
                    cta.appendChild(wrapper);
                    return true;
                }
                return false;
            }

            if (moveWrapper()) {
                return;
            }

            var observer = new MutationObserver(function () {
                if (moveWrapper()) {
                    observer.disconnect();
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });

            // Fallback: try once more after LearnDash scripts run.
            window.addEventListener('load', function () {
                moveWrapper();
            });
        })();
    </script>
    <?php
    return ob_get_clean();
}

/**
 * Render the restricted Aula card for non-student authenticated viewers.
 *
 * @param WP_User $user Current user.
 * @return string
 */
function escuela_lms_render_aula_restricted_card( WP_User $user ) {
    $initial = function_exists( 'mb_substr' ) ? mb_strtoupper( mb_substr( $user->display_name, 0, 1 ) ) : strtoupper( substr( $user->display_name, 0, 1 ) );

    ob_start();
    ?>
    <section class="aula-gateway">
        <div class="aula-gateway__card" role="note">
            <div class="aula-gateway__avatar" aria-hidden="true"><?php echo esc_html( $initial ); ?></div>
            <h1 class="aula-gateway__title">Ya estás dentro</h1>
            <p class="aula-gateway__text">Con tu rol podés seguir gestionando contenidos o revisar las formaciones públicas. Para ver el aula como estudiante, ingresá con una cuenta de alumna/o.</p>
            <div class="aula-gateway__actions">
                <a class="aula-gateway__btn aula-gateway__btn--primary" href="<?php echo esc_url( home_url( '/profile/' ) ); ?>">Ir a mi perfil</a>
                <a class="aula-gateway__btn aula-gateway__btn--ghost" href="<?php echo esc_url( home_url( '/courses/' ) ); ?>">Ver formaciones</a>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

add_shortcode('inicio_landing', function() {
    $img_url = esc_url(home_url('/wp-content/uploads/2026/05/escuela-neuquiena-de-yoga-32.png'));
    return '
<section class="enya-home">
    <div class="enya-fullbleed-wrapper">
        <div class="enya-home__hero">
            <div class="enya-home__hero-bg" aria-hidden="true"></div>
            <div class="enya-home__hero-content">
                <h1 class="enya-home__hero-title">Escuela Neuquina De Yoga</h1>
                <p class="enya-home__hero-subtitle">Formacion, practica y tradicion para quienes buscan un recorrido profundo, claro y acompañado.</p>
                <div class="enya-home__hero-ctas">
                    <a href="/courses/" class="enya-btn enya-btn--primary">Ver formaciones</a>
                    <a href="/aula/" class="enya-btn enya-btn--outline-dark"><svg class="enyf-icon-enter" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg> Aula Virtual</a>
                </div>
            </div>
        </div>
    </div>

    <div class="enya-container">
        <div class="enya-home__intro">
            <h2 class="enya-home__intro-title">Un espacio para estudiar, practicar e integrar</h2>
            <p class="enya-home__intro-text">Cada formacion combina clases, materiales de estudio, practica guiada y recursos para avanzar paso a paso.</p>
        </div>

        <div class="enya-home__cards">
            <div class="enya-home__card">
                <div class="enya-home__card-icon" aria-hidden="true">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 class="enya-home__card-title">Formacion integral</h3>
                <p class="enya-home__card-text">Recorridos pensados para profundizar de manera progresiva.</p>
            </div>
            <div class="enya-home__card">
                <div class="enya-home__card-icon" aria-hidden="true">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.651zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="enya-home__card-title">Practica guiada</h3>
                <p class="enya-home__card-text">Videos, ejercicios y propuestas para sostener la experiencia.</p>
            </div>
            <div class="enya-home__card">
                <div class="enya-home__card-icon" aria-hidden="true">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="enya-home__card-title">Material de estudio</h3>
                <p class="enya-home__card-text">PDFs, recursos descargables y evaluaciones para acompañar el aprendizaje.</p>
            </div>
        </div>

        <div class="enya-home__featured">
            <div class="enya-home__featured-content">
                <div class="enya-home__featured-label">Formacion destacada</div>
                <h2 class="enya-home__featured-title">Formacion en Meditacion</h2>
                <p class="enya-home__featured-text">Un recorrido inicial por fundamentos, posturas, respiracion y tecnicas de meditacion yoguica.</p>
                <a href="/courses/formacion-en-meditacion/" class="enya-btn enya-btn--primary">Ver formacion</a>
            </div>
            <div class="enya-home__featured-image">
                <img src="' . $img_url . '" alt="Formacion en Meditacion">
            </div>
        </div>

        <div class="enya-home__cta">
            <h2 class="enya-home__cta-title">Comenzá tu recorrido</h2>
            <p class="enya-home__cta-text">Conocé las formaciones disponibles o ingresá a La Escuela si ya tenés tu cuenta.</p>
            <div class="enya-home__cta-buttons">
                <a href="/courses/" class="enya-btn enya-btn--primary">Ver formaciones</a>
                <a href="/aula/" class="enya-btn enya-btn--outline-dark"><svg class="enyf-icon-enter" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg> Aula Virtual</a>
            </div>
        </div>
    </div>
</section>
    ';
});

add_shortcode('inicio_features', function() {
    return '<div style="background:#fff;padding:80px 20px;">
        <div style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(3,1fr);gap:40px;">
            <div><h3 style="font-family:Gilda Display;font-size:24px;color:#3D3229;">Formaciones</h3><p style="font-family:Raleway;font-size:16px;color:#5C4F43;line-height:1.6;">Programas estructurados de formacion en yoga.</p></div>
            <div><h3 style="font-family:Gilda Display;font-size:24px;color:#3D3229;">Talleres</h3><p style="font-family:Raleway;font-size:16px;color:#5C4F43;line-height:1.6;">Profundizacion en areas especificas.</p></div>
            <div><h3 style="font-family:Gilda Display;font-size:24px;color:#3D3229;">Comunidad</h3><p style="font-family:Raleway;font-size:16px;color:#5C4F43;line-height:1.6;">Un espacio de practica compartida.</p></div>
        </div>
    </div>';
});

add_shortcode('tradicion_hero', function() {
    return '<div style="background:#faf7f4;padding:100px 20px;text-align:center;">
        <h1 style="font-family:Gilda Display;font-size:48px;color:#3D3229;margin-bottom:20px;">Una tradicion de estudio</h1>
        <p style="font-family:Raleway;font-size:20px;color:#5C4F43;max-width:700px;margin:0 auto 40px;line-height:1.8;">La Escuela Neuquina de Yoga nace de la conviccion de que el yoga es una practica de estudio, no solo de postura.</p>
        <a href="/instructora/" style="background:#6B7F59;color:#fff;padding:18px 40px;text-decoration:none;border-radius:4px;font-family:Cabin;font-weight:500;display:inline-block;">Conocer a la instructora</a>
    </div>';
});

add_shortcode('tradicion_features', function() {
    return '<div style="background:#fff;padding:80px 20px;">
        <div style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(3,1fr);gap:40px;">
            <div><h3 style="font-family:Gilda Display;font-size:24px;color:#3D3229;">Practica</h3><p style="font-family:Raleway;font-size:16px;color:#5C4F43;line-height:1.6;">El cuerpo como puerta de entrada.</p></div>
            <div><h3 style="font-family:Gilda Display;font-size:24px;color:#3D3229;">Estudio</h3><p style="font-family:Raleway;font-size:16px;color:#5C4F43;line-height:1.6;">La teoria sustenta la practica.</p></div>
            <div><h3 style="font-family:Gilda Display;font-size:24px;color:#3D3229;">Experiencia</h3><p style="font-family:Raleway;font-size:16px;color:#5C4F43;line-height:1.6;">La transformacion con practica sostenida.</p></div>
        </div>
    </div>';
});

add_shortcode('instructora_bio', function() {
    $img_url = escuela_upload_url('2026/05/escuela-neuquiena-de-yoga-32.png');
    return '
<section class="enyi-instructora">
    <div class="enyi-instructora__image-wrap">
        <img class="enyi-instructora__photo"
             src="' . $img_url . '"
             alt="Andrea — guia y fundadora de la Escuela Neuquina de Yoga">
    </div>
    <div class="enyi-instructora__content">
        <div class="enyi-instructora__eyebrow">Tradicion y acompanamiento</div>
        <h1 class="enyi-instructora__title">Andrea, guia de este recorrido</h1>
        <p class="enyi-instructora__text">Andrea acompaña procesos de estudio y práctica desde una mirada sensible, profunda y cercana. Su forma de enseñar integra tradición, presencia y una atención real a cada etapa del aprendizaje.</p>
        <p class="enyi-instructora__text">En la Escuela Neuquina de Yoga, la formación no se vive solo como contenido: se vive como experiencia, práctica y transformación. Cada propuesta busca ofrecer un camino claro, humano y sostenido en el tiempo.</p>
        <p class="enyi-instructora__text">Si sentís el llamado a profundizar, podés comenzar por una formación completa o acercarte a talleres y recorridos específicos, según tu momento y tu búsqueda.</p>
        <ul class="enyi-instructora__highlights">
            <li>
                <span class="enyi-instructora__highlight-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>
                </span>
                Formacion con base teorica y practica
            </li>
            <li>
                <span class="enyi-instructora__highlight-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                </span>
                Acompanamiento paso a paso
            </li>
            <li>
                <span class="enyi-instructora__highlight-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 016.5 17H20M4 19.5A2.5 2.5 0 014 17V5a2 2 0 012-2h14a2 2 0 012 2v14l-5-2.5L4 19.5z"/></svg>
                </span>
                Recursos de estudio, clases y materiales
            </li>
        </ul>
        <div class="enyi-instructora__ctas">
            <a href="/courses/" class="enyi-btn enyi-btn--primary">Ver formaciones</a>
            <a href="https://wa.me/5492942337884?text=Hola%2C%20quiero%20consultar" class="enyi-btn enyi-btn--outline-dark" target="_blank" rel="noopener">Consultar por WhatsApp</a>
        </div>
    </div>
</section>
    ';
});

add_shortcode('escuela_info', function() {
    return '<div style="background:#faf7f4;padding:80px 20px;text-align:center;">
        <h1 style="font-family:Gilda Display;font-size:48px;color:#3D3229;margin-bottom:20px;">La Escuela</h1>
        <p style="font-family:Raleway;font-size:20px;color:#5C4F43;max-width:700px;margin:0 auto 40px;line-height:1.8;">Fundada en Neuquen, la Escuela Neuquina de Yoga ofrece formacion presencial en un ambiente de estudio serio.</p>
        <a href="https://wa.me/5492942337884?text=Hola%2C%20quiero%20consultar" style="background:#6B7F59;color:#fff;padding:18px 40px;text-decoration:none;border-radius:4px;font-family:Cabin;font-weight:500;display:inline-block;">Consultar</a>
    </div>
    <div style="background:#fff;padding:60px 20px;text-align:center;">
        <p style="font-family:Raleway;font-size:18px;color:#5C4F43;">Neuquen, Argentina</p>
    </div>';
});

add_shortcode('la_escuela_landing', function() {
    $img_url = escuela_upload_url('2026/05/escuela-neuquiena-de-yoga-10-1024x768.png');
    return '<section class="enye-hero"><div class="enye-hero__inner"><h1 class="enye-hero__title">La Escuela</h1><p class="enye-hero__subtitle">Un espacio de practica, formacion y encuentro enraizado en el yoga, la meditacion y la transmision viva de la ensenanza.</p></div></section><section class="enye-section enye-section--image"><div class="enye-container enye-grid"><div class="enye-grid__image"><img class="enye-grid__photo" src="' . $img_url . '" alt="La Escuela Neuquina de Yoga — espacio de estudio y practica"></div><div class="enye-grid__content"><div class="enye-grid__eyebrow">La Escuela</div><h2 class="enye-grid__title">Un espacio de estudio, practica y profundidad</h2><p class="enye-grid__text">La Escuela Neuquina de Yoga nace como un espacio para acompanar procesos reales de aprendizaje, practica e integracion. No se trata solo de tomar clases, sino de construir un recorrido con sentido, presencia y continuidad.</p><p class="enye-grid__text">Cada formacion combina contenidos teoricos, practica guiada, materiales de estudio y propuestas de integracion para que el aprendizaje pueda vivirse de manera clara, humana y sostenida.</p></div></div></section><section class="enye-section"><div class="enye-container enye-container--narrow"><div class="enye-grid__eyebrow">Una practica con raiz</div><h2 class="enye-grid__title">Tradicion y ensenanza viva</h2><p class="enye-grid__text">Desde la tradicion del yoga, la escuela propone un camino cercano y profundo: estudiar, practicar, observar y transformar la experiencia cotidiana desde la presencia. No es una moda ni un consumo: es un proceso que se sostiene con dedicacion, guia y comunidad.</p><p class="enye-grid__text">La practica no se limita al mat. Se extiende a la forma de respirar, de habitar el cuerpo, de relacionarse con uno mismo y con los demas. Cada formacion, taller o encuentro esta pensado para acompanar ese proceso con sentido, profundidad y continuidad.</p></div></div></section>';
});

add_shortcode('tradicion_la_escuela', function() {
    $img_url = escuela_upload_url('2026/05/escuela-neuquiena-de-yoga-10-1024x768.png');
    return '<section class="enyi-instructora enyi-instructora--reverse"><div class="enyi-instructora__image-wrap"><img class="enyi-instructora__photo" src="' . $img_url . '" alt="La Escuela Neuquina de Yoga — espacio de estudio y practica"></div><div class="enyi-instructora__content"><div class="enyi-instructora__eyebrow">La Escuela</div><h1 class="enyi-instructora__title">Un espacio de estudio, practica y profundidad</h1><p class="enyi-instructora__text">La Escuela Neuquina de Yoga nace como un espacio para acompanar procesos reales de aprendizaje, practica e integracion. No se trata solo de tomar clases, sino de construir un recorrido con sentido, presencia y continuidad.</p><p class="enyi-instructora__text">Cada formacion combina contenidos teoricos, practica guiada, materiales de estudio y propuestas de integracion para que el aprendizaje pueda vivirse de manera clara, humana y sostenida.</p><p class="enyi-instructora__text">Desde la tradicion del yoga, la escuela propone un camino cercano y profundo: estudiar, practicar, observar y transformar la experiencia cotidiana desde la presencia.</p><ul class="enyi-instructora__highlights"><li><span class="enyi-instructora__highlight-icon" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></span> Formacion progresiva y acompanada</li><li><span class="enyi-instructora__highlight-icon" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 016.5 17H20M4 19.5A2.5 2.5 0 014 17V5a2 2 0 012-2h14a2 2 0 012 2v14l-5-2.5L4 19.5z"/></svg></span> Materiales de estudio y recursos descargables</li><li><span class="enyi-instructora__highlight-icon" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg></span> Acompanamiento personalizado</li></ul><div class="enyi-instructora__ctas"><a href="/courses/" class="enyi-btn enyi-btn--primary">Ver formaciones</a><a href="https://wa.me/5492942337884?text=Hola%2C%20quiero%20consultar" class="enyi-btn enyi-btn--outline-dark" target="_blank" rel="noopener">Consultar por WhatsApp</a></div></div></section>';
});

add_shortcode('formaciones_landing', function() {
    $html = '
<section class="enyf-section enyf-intro enyf-intro--first">
    <div class="enyf-container">
        <h2 class="enyf-intro__title">Una escuela para estudiar, practicar e integrar</h2>
        <p class="enyf-intro__text">La Escuela Neuquina de Yoga ofrece recorridos de formacion seria y sostenida. Cada propuesta combina formacion teorica, practica guiada, material de estudio descargable y seguimiento del avance. No es solo contenido: es un camino estructurado pensado para quien quiere aprender de verdad.</p>
    </div>
</section>

<section class="enyf-section enyf-proposal-types">
    <div class="enyf-container">
        <div class="enyf-proposal-types__grid">
            <div class="enyf-proposal-type">
                <div class="enyf-proposal-type__icon">🎓</div>
                <h3 class="enyf-proposal-type__title">Formaciones</h3>
                <p class="enyf-proposal-type__text">Programas extensos que abarcan un tema en profundidad. Lecciones estructuradas, material descargable, evaluaciones y certificado.</p>
            </div>
            <div class="enyf-proposal-type">
                <div class="enyf-proposal-type__icon">📘</div>
                <h3 class="enyf-proposal-type__title">Cursos</h3>
                <p class="enyf-proposal-type__text">Recorridos tematicos focalizados. Ideales para profundizar en un aspecto especifico del yoga o la meditacion con un enfoque practico.</p>
            </div>
            <div class="enyf-proposal-type">
                <div class="enyf-proposal-type__icon">✨</div>
                <h3 class="enyf-proposal-type__title">Talleres</h3>
                <p class="enyf-proposal-type__text">Encuentros puntuales de profundizacion. Exploraciones intensas sobre una tecnica o practica particular, con seguimiento guiado.</p>
            </div>
        </div>
    </div>
</section>

<section class="enyf-section enyf-catalog">
    <div class="enyf-container">
        <div class="enyf-catalog__header">
            <h2 class="enyf-catalog__title">Explora nuestra oferta academica</h2>
            <p class="enyf-catalog__subtitle">Formaciones completas, cursos tematicos y talleres de practica</p>
        </div>
        <div class="enyf-cards-grid">
            <div class="enyf-card">
                <div class="enyf-card__image" style="background-image:url(' . escuela_upload_url('/2026/05/formacion-en-meditacion-01-768x432.png') . ');">
                    <div class="enyf-card__badges">
                        <span class="enyf-card__badge enyf-card__badge--type enyf-card__badge--type--formacion">Formacion</span>
                        <span class="enyf-card__badge enyf-card__badge--status enyf-card__badge--status--disponible">Disponible</span>
                    </div>
                </div>
                <div class="enyf-card__body">
                    <h3 class="enyf-card__title">Formacion en Meditacion</h3>
                    <p class="enyf-card__text">Recorrido de dos modulos por los fundamentos y tecnicas de la meditacion yoguica: historia, filosofia, respiracion y concentracion.</p>
                    <div class="enyf-card__meta">
                        <span class="enyf-card__meta-item">📚 12 lecciones</span>
                        <span class="enyf-card__meta-item">📄 PDF</span>
                        <span class="enyf-card__meta-item">🎥 Video</span>
                    </div>
                    <a href="/courses/formacion-en-meditacion/" class="enyf-card__cta enyf-card__cta--primary">Ver formacion →</a>
                </div>
            </div>

            <div class="enyf-card enyf-card--mockup">
                <div class="enyf-card__image" style="background-image:url(' . escuela_upload_url('/2026/05/ChatGPT-Image-15-may-2026-04_40_35-1.png') . ');">
                    <div class="enyf-card__badges">
                        <span class="enyf-card__badge enyf-card__badge--type enyf-card__badge--type--formacion">Formacion</span>
                        <span class="enyf-card__badge enyf-card__badge--status enyf-card__badge--status--proximamente">Proximamente</span>
                    </div>
                </div>
                <div class="enyf-card__body">
                    <h3 class="enyf-card__title">Formacion Integral en Yoga</h3>
                    <p class="enyf-card__text">Programa completo que abarca los pilares del yoga: asanas, pranayama, meditacion, filosofia y anatomia sutil. Un recorrido profundo de 6 meses.</p>
                    <div class="enyf-card__meta">
                        <span class="enyf-card__meta-item">📚 +40 lecciones</span>
                        <span class="enyf-card__meta-item">🎥 Video</span>
                        <span class="enyf-card__meta-item">📄 PDF</span>
                    </div>
                    <span class="enyf-card__cta enyf-card__cta--disabled">Proximamente</span>
                </div>
            </div>

            <div class="enyf-card enyf-card--mockup">
                <div class="enyf-card__image" style="background-image:url(' . escuela_upload_url('/2026/05/ChatGPT-Image-15-may-2026-04_40_37-4.png') . ');">
                    <div class="enyf-card__badges">
                        <span class="enyf-card__badge enyf-card__badge--type enyf-card__badge--type--formacion">Formacion</span>
                        <span class="enyf-card__badge enyf-card__badge--status enyf-card__badge--status--proximamente">Proximamente</span>
                    </div>
                </div>
                <div class="enyf-card__body">
                    <h3 class="enyf-card__title">Tradicion y Filosofia</h3>
                    <p class="enyf-card__text">Estudio riguroso de los Yoga Sutras de Patanjali, los Upanishads y las principales corrientes del yoga clasico. Para quienes buscan entender antes de practicar.</p>
                    <div class="enyf-card__meta">
                        <span class="enyf-card__meta-item">📚 10 lecciones</span>
                        <span class="enyf-card__meta-item">📄 Textos guia</span>
                        <span class="enyf-card__meta-item">🧘 Practica</span>
                    </div>
                    <span class="enyf-card__cta enyf-card__cta--disabled">Proximamente</span>
                </div>
            </div>

            <div class="enyf-card enyf-card--mockup">
                <div class="enyf-card__image" style="background-image:url(' . escuela_upload_url('/2026/05/ChatGPT-Image-15-may-2026-04_40_37-6.png') . ');">
                    <div class="enyf-card__badges">
                        <span class="enyf-card__badge enyf-card__badge--type enyf-card__badge--type--curso">Curso</span>
                        <span class="enyf-card__badge enyf-card__badge--status enyf-card__badge--status--popular">Popular</span>
                    </div>
                </div>
                <div class="enyf-card__body">
                    <h3 class="enyf-card__title">Curso de Pranayama</h3>
                    <p class="enyf-card__text">El arte de la respiracion yoguica. Ujjayi, Nadi Shodhana, Kapalabhati y mas. Practicas guiadas y progresion gradual para todos los niveles.</p>
                    <div class="enyf-card__meta">
                        <span class="enyf-card__meta-item">📚 8 lecciones</span>
                        <span class="enyf-card__meta-item">🎥 Practica guiada</span>
                        <span class="enyf-card__meta-item">📄 Guia PDF</span>
                    </div>
                    <span class="enyf-card__cta enyf-card__cta--disabled">Proximamente</span>
                </div>
            </div>

            <div class="enyf-card enyf-card--mockup">
                <div class="enyf-card__image" style="background-image:url(' . escuela_upload_url('/2026/05/ChatGPT-Image-15-may-2026-04_40_37-7.png') . ');">
                    <div class="enyf-card__badges">
                        <span class="enyf-card__badge enyf-card__badge--type enyf-card__badge--type--taller">Taller</span>
                        <span class="enyf-card__badge enyf-card__badge--status enyf-card__badge--status--nuevo">Nuevo</span>
                    </div>
                </div>
                <div class="enyf-card__body">
                    <h3 class="enyf-card__title">Taller de Introduccion a la Meditacion</h3>
                    <p class="enyf-card__text">Un encuentro para entender que es la meditacion, por que funciona y como empezar. Incluye practica de observacion de la respiracion.</p>
                    <div class="enyf-card__meta">
                        <span class="enyf-card__meta-item">📚 1 sesion</span>
                        <span class="enyf-card__meta-item">🎥 Video clase</span>
                        <span class="enyf-card__meta-item">📄 Material</span>
                    </div>
                    <span class="enyf-card__cta enyf-card__cta--disabled">Proximamente</span>
                </div>
            </div>

            <div class="enyf-card enyf-card--mockup">
                <div class="enyf-card__image" style="background-image:url(' . escuela_upload_url('/2026/05/ChatGPT-Image-15-may-2026-04_40_37-8.png') . ');">
                    <div class="enyf-card__badges">
                        <span class="enyf-card__badge enyf-card__badge--type enyf-card__badge--type--curso">Curso</span>
                    </div>
                </div>
                <div class="enyf-card__body">
                    <h3 class="enyf-card__title">Curso de Filosofia del Yoga</h3>
                    <p class="enyf-card__text">Un estudio riguroso de los Yoga Sutras de Patanjali, los Upanishads y las principales corrientes del yoga clasico.</p>
                    <div class="enyf-card__meta">
                        <span class="enyf-card__meta-item">📚 10 lecciones</span>
                        <span class="enyf-card__meta-item">📄 Textos guia</span>
                        <span class="enyf-card__meta-item">🧘 Practica</span>
                    </div>
                    <span class="enyf-card__cta enyf-card__cta--disabled">Proximamente</span>
                </div>
            </div>

            <div class="enyf-card enyf-card--mockup">
                <div class="enyf-card__image" style="background-image:url(' . escuela_upload_url('/2026/05/ChatGPT-Image-15-may-2026-04_40_37-9.png') . ');">
                    <div class="enyf-card__badges">
                        <span class="enyf-card__badge enyf-card__badge--type enyf-card__badge--type--taller">Taller</span>
                    </div>
                </div>
                <div class="enyf-card__body">
                    <h3 class="enyf-card__title">Taller de Sonido, Mantra y Nada Yoga</h3>
                    <p class="enyf-card__text">Exploracion del yoga del sonido: uso de mantras, cantos tradicionales, practicas de Nada Yoga y conexion con la vibracion como herramienta de meditacion.</p>
                    <div class="enyf-card__meta">
                        <span class="enyf-card__meta-item">📚 3 sesiones</span>
                        <span class="enyf-card__meta-item">🎵 Audio practicas</span>
                        <span class="enyf-card__meta-item">📄 Partituras</span>
                    </div>
                    <span class="enyf-card__cta enyf-card__cta--disabled">Proximamente</span>
                </div>
            </div>

            <div class="enyf-card enyf-card--mockup">
                <div class="enyf-card__image" style="background-image:url(' . escuela_upload_url('/2026/05/ChatGPT-Image-15-may-2026-04_40_37-10.png') . ');">
                    <div class="enyf-card__badges">
                        <span class="enyf-card__badge enyf-card__badge--type enyf-card__badge--type--curso">Curso</span>
                        <span class="enyf-card__badge enyf-card__badge--status enyf-card__badge--status--proximamente">Proximamente</span>
                    </div>
                </div>
                <div class="enyf-card__body">
                    <h3 class="enyf-card__title">Curso de Anatomia Aplicada al Yoga</h3>
                    <p class="enyf-card__text">El cuerpo humano en el yoga: biomecanica de las posturas, seguridad en la practica, respiracion y sistemas corporales.</p>
                    <div class="enyf-card__meta">
                        <span class="enyf-card__meta-item">📚 12 lecciones</span>
                        <span class="enyf-card__meta-item">📄 Atlas corporal</span>
                        <span class="enyf-card__meta-item">🎥 Video</span>
                    </div>
                    <span class="enyf-card__cta enyf-card__cta--disabled">Proximamente</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="enyf-section enyf-featured">
    <div class="enyf-container">
        <div class="enyf-featured__grid">
            <div class="enyf-featured__content">
                <div class="enyf-featured__eyebrow">Formacion destacada</div>
                <div class="enyf-featured__badge">
                    <span>🎓</span> 2 modulos · 12 lecciones · Certificado
                </div>
                <h2 class="enyf-featured__title">Formacion en Meditacion</h2>
                <p class="enyf-featured__text">Un recorrido de dos modulos por los fundamentos y las tecnicas de la meditacion yoguica. Desde la historia y la filosofia hasta las practicas de concentracion, respiracion y absorcion. Incluye material de estudio descargable en PDF y evaluaciones de cada modulo.</p>
                <div class="enyf-featured__details">
                    <div class="enyf-featured__detail">📚 12 lecciones</div>
                    <div class="enyf-featured__detail">📄 12 PDFs</div>
                    <div class="enyf-featured__detail">🎥 Videos</div>
                    <div class="enyf-featured__detail">✅ 2 cuestionarios</div>
                </div>
                <a href="/courses/formacion-en-meditacion/" class="enyf-featured__cta">
                    Comenzar formacion
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="enyf-featured__video">
                <img src="' . escuela_upload_url('/2026/05/formacion-en-meditacion-01-1024x576.png') . '" alt="Formacion en Meditacion">
                <div class="enyf-featured__video-overlay">
                    <a href="/courses/formacion-en-meditacion/" class="enyf-featured__play-btn" aria-label="Ver formacion"></a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="enyf-section enyf-methodology">
    <div class="enyf-container">
        <div class="enyf-methodology__header">
            <h2 class="enyf-methodology__title">Como funciona el recorrido</h2>
        </div>
        <div class="enyf-steps">
            <div class="enyf-step">
                <div class="enyf-step__number">01</div>
                <h3 class="enyf-step__title">Estudia</h3>
                <p class="enyf-step__text">Lecciones con contenido teorico, videos explicativos y material de estudio descargable.</p>
            </div>
            <div class="enyf-step">
                <div class="enyf-step__number">02</div>
                <h3 class="enyf-step__title">Practica</h3>
                <p class="enyf-step__text">Incorpora lo aprendido con ejercicios guiados, visualizaciones y respiraciones adaptadas.</p>
            </div>
            <div class="enyf-step">
                <div class="enyf-step__number">03</div>
                <h3 class="enyf-step__title">Integra</h3>
                <p class="enyf-step__text">Resuelve cuestionarios que verifican la comprension y consolidan el aprendizaje.</p>
            </div>
            <div class="enyf-step">
                <div class="enyf-step__number">04</div>
                <h3 class="enyf-step__title">Avanza</h3>
                <p class="enyf-step__text">Completa la formacion y recibe tu certificado de participacion.</p>
            </div>
        </div>
    </div>
</section>

<section class="enyf-section enyf-cta">
    <div class="enyf-container">
        <h2 class="enyf-cta__title">Comenzá tu recorrido</h2>
        <p class="enyf-cta__text">Accede a formaciones estructuradas, material de estudio descargable y un camino claro de aprendizaje. Tu lugar en la escuela ya esta esperando.</p>
        <div class="enyf-cta__buttons">
            <a href="/aula/" class="enyf-cta__btn enyf-cta__btn--white"><svg class="enyf-icon-enter" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg> Aula Virtual</a>
            <a href="https://wa.me/5492942337884?text=Hola%2C%20quiero%20consultar%20por%20las%20formaciones" class="enyf-cta__btn enyf-cta__btn--outline-white" target="_blank" rel="noopener">Consultar por WhatsApp</a>
        </div>
    </div>
</section>
    ';
    return $html;
});

add_shortcode('retiros_landing', function() {
    return '<section class="enyr-hero"><div class="enyr-hero__inner"><h1 class="enyr-hero__title">Retiros</h1><p class="enyr-hero__subtitle">Encuentros presenciales para volver al cuerpo, a la practica y al silencio, en dialogo con la naturaleza y la montana.</p></div></section><section class="enyr-intro"><div class="enyr-container"><p class="enyr-intro__text">Los retiros son espacios independientes de las formaciones. No reemplazan el proceso de estudio, sino que abren un tiempo distinto: una pausa para practicar, respirar, observar y compartir una experiencia cuidada.</p></div></section><div class="enyr-retiros-separator"><div class="enyr-container"><div class="enyr-retiros-separator__line"></div></div></div>';
});

add_filter('the_content', function($content) {
    if (!is_singular('sfwd-courses') || (int) get_the_ID() !== 1514) {
        return $content;
    }
    $url = home_url('/courses/');
    $backlink = '<div class="enyf-back-to-courses"><a href="' . esc_url($url) . '" class="enyf-back-link">&larr; Ver todas las formaciones</a></div>';
    return $backlink . $content;
});

add_filter('gettext_with_context', function($translated, $text, $context, $domain) {
    if ($domain !== 'learndash') return $translated;
    $map = [
        'Enroll in this %s'                                                                              => 'Inscribirse en este %s',
        'Already have an account?'                                                                       => "¿Ya tenés cuenta?",
        'placeholder: Message above registration form if user logged out.'                               => '', // no-op, just matching context
        'Are you a new user?'                                                                            => "¿Eres nuevo por aquí?",
        'placeholder: Message above login form if user can register.'                                    => '', // no-op, just matching context
    ];
    // For esc_html_x / _x: match by text+context
    if ($context === 'placeholder: Message above registration form if user logged out.' && $text === 'Already have an account?') {
        return "¿Ya tenés cuenta?";
    }
    if ($context === 'placeholder: Message above login form if user can register.' && $text === 'Are you a new user?') {
        return "¿Eres nuevo por aquí?";
    }
    if ($context === 'placeholder: Course' && $text === '%s Complete') {
        return '%s completado';
    }
    if (isset($map[$text])) {
        return $map[$text];
    }
    return $translated;
}, 20, 4);

add_filter('gettext', function($translated, $text, $domain) {
    if ($domain !== 'learndash') return $translated;
    $map = [
        'Log In'                  => "Iniciar sesión",
        'Log In to Enroll'        => "Iniciar sesión para inscribirse",
        'or'                      => 'o',
        'Includes'                => 'Incluye',
        'Registration'            => 'Registro',
        'Username'                => 'Usuario',
        'First Name'              => 'Nombre',
        'Last Name'               => 'Apellido',
        'Password'                => 'Contraseña',
        'Register'                => 'Registrarse',
        'Email'                   => 'Correo electrónico',
        'Confirm Password'        => 'Confirmar contraseña',
        'Logged in as %1$s'       => 'Logueado como %1$s',
        'A medium-strength password is needed to register. Tip: Try at least 12 characters long containing letters, numbers, and special characters.' => 'Se necesita una contraseña de seguridad media para registrarse. Consejo: Intenté al menos 12 caracteres que incluyan letras, números y caracteres especiales.',
        'This field is required.'                  => 'Este campo es obligatorio.',
        'Make sure this matches your password.'    => 'Asegurate de que coincida con tu contraseña.',
        'Course Content'           => 'Contenido del curso',
        'Next Lesson'              => 'Siguiente lección',
        'Next lesson'              => 'Siguiente lección',
        'Previous Lesson'          => 'Lección anterior',
        'Back to Course'           => 'Volver al curso',
        'Mark Complete'            => 'Marcar como completado',
        'Mark as Complete'         => 'Marcar como completado',
        'Lesson marked complete.'  => 'Lección completada.',
        'Lección marked complete.' => 'Lección completada.',
        'Lesson Marked Complete'   => 'Lección completada',
        'Lección Marked Complete'  => 'Lección completada',
        'Lesson completed'         => 'Lección completada',
        'Lesson Complete!'         => '¡Lección completada!',
        'Topic completed'          => 'Tema completado',
        'Congratulations'          => 'Felicitaciones',
        'You have completed this course!' => '¡Completaste este curso!',
        'Course Complete'          => 'Curso completado',
        'COURSE COMPLETE'          => 'CURSO COMPLETADO',
        'Complete'                 => 'Completado',
        '%s marked complete.'      => '%s completado.',
        'Last activity:'           => 'Última actividad:',
        'Last activity: %s'        => 'Última actividad: %s',
        '%s Complete'              => '%s completado',
        'Course Progress'          => 'Progreso del curso',
        'Finish the required activity on this page to continue.' => 'Completá la actividad requerida en esta página para continuar.',
        'Previous'                 => 'Anterior',
        'Next'                     => 'Siguiente',
        'Finish'                   => 'Finalizar',
        'Profile'                   => 'Perfil',
        'Dashboard'                 => 'Panel',
        'Your Stats'                => 'Tus estadísticas',
        'Courses'                   => 'Cursos',
        'Certificates'              => 'Certificados',
        'Points'                    => 'Puntos',
        'Not Started'               => 'Sin iniciar',
        'Not started'               => 'Sin iniciar',
        'In Progress'               => 'En progreso',
        'Show Courses Search Field' => 'Mostrar buscador de cursos',
        'Hide Courses Search Field' => 'Ocultar buscador de cursos',
        'Expand All Courses'        => 'Expandir todos los cursos',
        'Collapse All Courses'      => 'Contraer todos los cursos',
    ];

    if (isset($map[$text])) {
        return $map[$text];
    }
    return $translated;
}, 20, 3);

add_filter('option_learndash_settings_registration_fields', function($value) {
    if (!is_array($value)) return $value;
    $labels = [
        'username_label'   => 'Usuario',
        'email_label'      => 'Correo electrónico',
        'first_name_label' => 'Nombre',
        'last_name_label'  => 'Apellido',
        'password_label'   => 'Contraseña',
    ];
    foreach ($labels as $key => $label) {
        if (isset($value[$key])) {
            $value[$key] = $label;
        }
    }
    return $value;
}, 20);
