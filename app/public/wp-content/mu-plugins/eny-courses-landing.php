<?php
/**
 * Mu-plugin: ENY Courses Landing Override
 *
 * Provides a repo-controlled replacement for the [formaciones_landing] shortcode.
 * - Registers override on init (priority 20) so it replaces plugin registration.
 * - Caches rendered template output in a transient keyed by template mtime.
 * - Flushes cache on course lifecycle events.
 * - Exposes eny_courses_landing_flush_cache() and a WP-CLI helper.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'eny_courses_landing_register_override', 20 );

function eny_courses_landing_register_override() {
    // Feature flag: allow rollback by setting this option to false (string '0' or boolean false)
    $override = get_option( 'eny_override_courses_landing', '1' );

    if ( $override === '0' || false === $override ) {
        // If we stored a previous handler and can re-register it, try to restore.
        $prev = get_option( 'eny_courses_landing_prev_handler', false );
        if ( $prev && is_callable( $prev ) ) {
            // Replace our handler with the previous callable.
            remove_shortcode( 'formaciones_landing' );
            add_shortcode( 'formaciones_landing', $prev );
        }

        return;
    }

    // Capture any existing handler so rollback can restore it at runtime when feasible.
    global $shortcode_tags;
    if ( isset( $shortcode_tags['formaciones_landing'] ) ) {
        $existing = $shortcode_tags['formaciones_landing'];
        // Only persist serializable handlers; closures are stored as a marker.
        if ( is_string( $existing ) || is_array( $existing ) ) {
            update_option( 'eny_courses_landing_prev_handler', $existing );
        } else {
            update_option( 'eny_courses_landing_prev_handler', 'closure' );
        }
    }

    add_shortcode( 'formaciones_landing', 'eny_formaciones_landing_shortcode' );
}

/**
 * Shortcode handler: queries published sfwd-courses and renders the theme partial.
 * Caches rendered HTML in a transient keyed by the template mtime.
 *
 * @return string
 */
function eny_formaciones_landing_shortcode( $atts = array(), $content = null ) {
    $template_rel = 'template-parts/courses/landing.php';
    $template_path = locate_template( $template_rel );
    $template_mtime = ( $template_path && file_exists( $template_path ) ) ? (int) filemtime( $template_path ) : 0;

    $cache_key = 'eny_courses_landing_grid_v' . $template_mtime;
    $cached = get_transient( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    // Query published LearnDash courses.
    $args = array(
        'post_type'      => 'sfwd-courses',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    $query = new WP_Query( $args );
    $courses = array();

    if ( $query->have_posts() ) {
        foreach ( $query->posts as $post ) {
            $thumb = get_the_post_thumbnail_url( $post->ID, 'large' );
            $alt   = '';
            if ( has_post_thumbnail( $post->ID ) ) {
                $alt = get_post_meta( get_post_thumbnail_id( $post->ID ), '_wp_attachment_image_alt', true );
            }

            $courses[] = array(
                'ID'        => $post->ID,
                'title'     => get_the_title( $post->ID ),
                'excerpt'   => $post->post_excerpt ? $post->post_excerpt : wp_trim_words( wp_strip_all_tags( $post->post_content ), 24, '...' ),
                'permalink' => get_permalink( $post->ID ),
                'thumbnail' => $thumb,
                'alt'       => $alt ? $alt : sprintf( /* translators: course title */ __( 'Course: %s', 'escuela-lms' ), get_the_title( $post->ID ) ),
            );
        }
        wp_reset_postdata();
    }

    // Render template with scoped variables.
    ob_start();
    $context = array( 'courses' => $courses );

    if ( $template_path && file_exists( $template_path ) ) {
        // Make $context available to the template.
        // Extracting into local scope keeps the template looking for $courses.
        extract( $context ); // phpcs:ignore WordPress.PHP.DontExtract
        include $template_path;
    } else {
        // Template missing: fallback to a safe empty state.
        echo '<div class="enyf-empty-state">' . esc_html__( 'No hay formaciones disponibles.', 'escuela-lms' ) . '</div>';
    }

    $output = ob_get_clean();

    // Cache for 300 seconds.
    set_transient( $cache_key, $output, 300 );

    return $output;
}

/**
 * Flush any transients created by this mu-plugin.
 * Uses direct option lookup to remove transient entries by prefix.
 */
function eny_courses_landing_flush_cache() {
    global $wpdb;

    $like = $wpdb->esc_like( '_transient_eny_courses_landing_grid_v' ) . '%';
    $rows = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s", $like ) );

    if ( ! empty( $rows ) ) {
        foreach ( $rows as $option_name ) {
            // option_name includes the _transient_ prefix; derive the transient key.
            $transient_key = str_replace( '_transient_', '', $option_name );
            delete_transient( $transient_key );
        }
    }
}

// Invalidate on course lifecycle events.
add_action( 'save_post_sfwd-courses', 'eny_courses_landing_flush_cache', 10, 3 );
add_action( 'transition_post_status', function( $new_status, $old_status, $post ) {
    if ( isset( $post->post_type ) && 'sfwd-courses' === $post->post_type && $new_status !== $old_status ) {
        eny_courses_landing_flush_cache();
    }
}, 10, 3 );
add_action( 'deleted_post', function( $post_id ) {
    $post = get_post( $post_id );
    if ( $post && 'sfwd-courses' === $post->post_type ) {
        eny_courses_landing_flush_cache();
    }
} );
add_action( 'trash_post', function( $post_id ) {
    $post = get_post( $post_id );
    if ( $post && 'sfwd-courses' === $post->post_type ) {
        eny_courses_landing_flush_cache();
    }
} );

// WP-CLI helper: `wp eny courses-landing flush`
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    WP_CLI::add_command( 'eny courses-landing', function( $args ) {
        if ( isset( $args[0] ) && 'flush' === $args[0] ) {
            eny_courses_landing_flush_cache();
            WP_CLI::success( 'Flushed courses landing cache' );
            return;
        }

        WP_CLI::error( 'Usage: wp eny courses-landing flush' );
    } );
}
