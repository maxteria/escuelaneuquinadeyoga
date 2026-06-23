<?php
/**
 * Hooks and public-facing controllers for Escuela Instructor
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Escuela_Instructor_Hooks' ) ) {
    class Escuela_Instructor_Hooks {
        public static function init() {
            add_action( 'init', array( __CLASS__, 'handle_inscribirse' ) );
            add_filter( 'the_content', array( __CLASS__, 'append_cta' ) );
            // Render payment instructions on the pending-inscription page
            add_filter( 'the_content', array( __CLASS__, 'render_pending_page' ) );
            add_shortcode( 'escuela_inscribirme', array( __CLASS__, 'shortcode_inscribirme' ) );
            add_shortcode( 'escuela_inscripcion_pendiente', array( __CLASS__, 'shortcode_pendiente' ) );
        }

        /**
         * Handle ?inscribirse={course_id}&_escuela_nonce=... requests
         */
        public static function handle_inscribirse() {
            if ( empty( $_GET['inscribirse'] ) ) {
                return;
            }

            $course_id = intval( $_GET['inscribirse'] );

            // Require a nonce param
            if ( empty( $_GET['_escuela_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_escuela_nonce'] ) ), 'escuela_inscribirse_' . $course_id ) ) {
                // Redirect back with error
                $back = wp_get_referer() ? wp_get_referer() : get_permalink( $course_id );
                $back = add_query_arg( 'inscripcion_error', 'invalid_nonce', $back );
                wp_safe_redirect( esc_url_raw( $back ) );
                exit;
            }

            // Require logged-in user
            if ( ! is_user_logged_in() ) {
                // Redirect to login and then back to course
                $login = wp_login_url( get_permalink( $course_id ) );
                wp_safe_redirect( $login );
                exit;
            }

            $user_id = get_current_user_id();

            $res = Escuela_Instructor_Service::inscribir_usuario( $user_id, $course_id );

            if ( is_wp_error( $res ) ) {
                $code = $res->get_error_code();
                $back = wp_get_referer() ? wp_get_referer() : get_permalink( $course_id );
                $back = add_query_arg( 'inscripcion_error', $code, $back );
                wp_safe_redirect( esc_url_raw( $back ) );
                exit;
            }

            // Success: redirect to pending-inscription page
            $pending = home_url( '/inscripcion-pendiente/' );
            wp_safe_redirect( $pending );
            exit;
        }

        /**
         * Append CTA to course content
         *
         * @param string $content
         * @return string
         */
        public static function append_cta( $content ) {
            if ( ! is_singular() ) {
                return $content;
            }

            global $post;

            if ( ! $post || 'sfwd-courses' !== get_post_type( $post ) ) {
                return $content;
            }

            $cta = self::render_cta( $post->ID );

            return $content . $cta;
        }

        /**
         * Shortcode handler [escuela_inscribirme course_id="123"]
         */
        public static function shortcode_inscribirme( $atts ) {
            $atts = shortcode_atts( array( 'course_id' => 0 ), $atts, 'escuela_inscribirme' );

            $course_id = intval( $atts['course_id'] );

            if ( 0 === $course_id ) {
                return ''; // nothing to render
            }

            return self::render_cta( $course_id );
        }

        /**
         * Shortcode handler to render payment instructions on pending page
         */
        public static function shortcode_pendiente( $atts ) {
            $course_id = 0;

            if ( is_user_logged_in() ) {
                $user_id = get_current_user_id();
                $last = Escuela_Instructor_DB::get_last_for_user( $user_id );
                if ( $last ) {
                    $course_id = intval( $last['course_id'] );
                }
            }

            if ( 0 === $course_id ) {
                return '<p>No encontramos una inscripción pendiente asociada a tu usuario. Si ya realizaste el pago, contactá al instructor.</p>';
            }

            ob_start();
            self::render_payment_instructions( $course_id );
            return ob_get_clean();
        }

        /**
         * Render CTA markup depending on user state
         *
         * @param int $course_id
         * @return string
         */
        public static function render_cta( $course_id ) {
            $course_id = intval( $course_id );

            $markup = '<div class="escuela-inscripcion-cta">';

            if ( ! is_user_logged_in() ) {
                $login = esc_url( wp_login_url( get_permalink( $course_id ) ) );
                $markup .= '<p>Por favor <a href="' . $login . '">iniciá sesión</a> para inscribirte.</p>';
                $markup .= '</div>';
                return $markup;
            }

            $user_id = get_current_user_id();

            $insc = Escuela_Instructor_Service::get_inscripcion( $user_id, $course_id );

            if ( $insc && ! empty( $insc['status'] ) ) {
                $status = sanitize_text_field( $insc['status'] );

                if ( 'pending' === $status ) {
                    $markup .= '<p>Tu inscripción está pendiente. Te aparecerán las instrucciones de pago en la página de inscripción pendiente.</p>';
                } else {
                    $markup .= '<p>Estás inscripto en este curso.</p>';
                }

                $markup .= '</div>';
                return $markup;
            }

            // No inscription — render button with nonce
            $nonce = wp_create_nonce( 'escuela_inscribirse_' . $course_id );

            $link = esc_url( add_query_arg( array( 'inscribirse' => $course_id, '_escuela_nonce' => $nonce ), get_permalink( $course_id ) ) );

            $markup .= '<p><a class="button" href="' . $link . '">Inscribirme</a></p>';
            $markup .= '</div>';

            return $markup;
        }

        /**
         * Render payment instructions template for a course
         *
         * @param int $course_id
         */
        public static function render_payment_instructions( $course_id ) {
            $course_id = intval( $course_id );

            $tpl = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/payment-instructions.php';
            if ( file_exists( $tpl ) ) {
                // make $course_id available in template
                include $tpl;
            }
        }

        /**
         * If on the pending inscription page, render payment instructions for
         * the current user's pending inscriptions.
         *
         * @param string $content
         * @return string
         */
        public static function render_pending_page( $content ) {
            if ( ! is_page() ) {
                return $content;
            }

            global $post;

            if ( ! $post || 'inscripcion-pendiente' !== $post->post_name ) {
                return $content;
            }

            if ( ! is_user_logged_in() ) {
                return $content;
            }

            $user_id = get_current_user_id();

            $rows = Escuela_Instructor_DB::list_by_user( $user_id, 'pending' );

            if ( empty( $rows ) ) {
                // No pending inscriptions — return original content
                return $content;
            }

            $out = $content;

            foreach ( $rows as $row ) {
                $course_id = intval( $row['course_id'] );
                // Render the template for each pending inscription
                ob_start();
                self::render_payment_instructions( $course_id );
                $out .= ob_get_clean();
            }

            return $out;
        }
    }
}
