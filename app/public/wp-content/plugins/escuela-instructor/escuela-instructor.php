<?php
/**
 * Plugin Name: Escuela Instructor
 * Plugin URI:  https://example.com/
 * Description: Manage instructor inscriptions (foundation).
 * Version:     0.1.0
 * Author:      Escuela
 * Text Domain: escuela-instructor
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Escuela_Instructor' ) ) {
    class Escuela_Instructor {
        const CAPABILITY = 'manage_inscripciones';

        /**
         * Activation entrypoint
         */
        public static function activate() {
            self::create_table();
            self::assign_capabilities();
            self::ensure_pending_page();
        }

        /**
         * Create the custom table used by the plugin
         */
        public static function create_table() {
            global $wpdb;

            $table_name = $wpdb->prefix . 'escuela_inscripciones';
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE {$table_name} (
                id BIGINT NOT NULL AUTO_INCREMENT,
                user_id BIGINT NOT NULL,
                course_id BIGINT NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY user_course (user_id, course_id),
                PRIMARY KEY  (id)
            ) {$charset_collate};";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta( $sql );
        }

        /**
         * Assign capability to roles
         */
        public static function assign_capabilities() {
            $roles = array( 'administrator', 'group_leader' );

            foreach ( $roles as $role_name ) {
                $role = get_role( $role_name );
                if ( $role && ! $role->has_cap( self::CAPABILITY ) ) {
                    $role->add_cap( self::CAPABILITY );
                }
            }
        }

        /**
         * Ensure the pending-inscription page exists
         */
        public static function ensure_pending_page() {
            $slug = 'inscripcion-pendiente';

            // get_page_by_path respects hierarchical pages; limit to pages
            $page = get_page_by_path( $slug, OBJECT, 'page' );

            if ( ! $page ) {
                // create a minimal page
                $page_data = array(
                    'post_title'   => 'Inscripción pendiente',
                    'post_name'    => $slug,
                    'post_content' => 'Página para inscripciones pendientes.',
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                );

                wp_insert_post( $page_data );
            }
        }
    }
}

register_activation_hook( __FILE__, array( 'Escuela_Instructor', 'activate' ) );

// Load DB helper class
if ( file_exists( plugin_dir_path( __FILE__ ) . 'includes/class-db.php' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-db.php';
}

// Load service and hooks
if ( file_exists( plugin_dir_path( __FILE__ ) . 'includes/class-service.php' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-service.php';
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'includes/class-hooks.php' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-hooks.php';
    // Initialize public hooks
    Escuela_Instructor_Hooks::init();
}
