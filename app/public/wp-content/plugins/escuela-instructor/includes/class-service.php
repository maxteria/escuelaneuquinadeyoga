<?php
/**
 * Service layer for Escuela Instructor
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Escuela_Instructor_Service' ) ) {
    class Escuela_Instructor_Service {
        /**
         * Create a pending inscription for a user and course.
         *
         * @param int $user_id
         * @param int $course_id
         * @return int|WP_Error Insert ID on success, WP_Error on failure
         */
        public static function inscribir_usuario( $user_id, $course_id ) {
            $user_id   = intval( $user_id );
            $course_id = intval( $course_id );

            if ( 0 === $user_id || 0 === $course_id ) {
                return new WP_Error( 'invalid_input', 'Usuario o curso inválido.' );
            }

            // Validate course exists and is a LearnDash course (sfwd-courses)
            $course = get_post( $course_id );
            if ( ! $course || 'sfwd-courses' !== $course->post_type ) {
                return new WP_Error( 'invalid_course', 'Curso inválido.' );
            }

            // Check duplicate
            if ( Escuela_Instructor_DB::exists( $user_id, $course_id ) ) {
                return new WP_Error( 'duplicate_enrollment', 'Ya existe una inscripción para este usuario y curso.' );
            }

            $insert_id = Escuela_Instructor_DB::insert( $user_id, $course_id, 'pending' );

            if ( false === $insert_id ) {
                return new WP_Error( 'db_error', 'No se pudo crear la inscripción.' );
            }

            return intval( $insert_id );
        }

        /**
         * Approve an inscription by ID. Updates status to 'active' and
         * grants course access via LearnDash when available.
         *
         * @param int $inscripcion_id
         * @param int $admin_user_id
         * @return array|WP_Error ['id'=>int,'status'=>string] on success
         */
        public static function aprobar_inscripcion( $inscripcion_id, $admin_user_id = 0 ) {
            global $wpdb;

            $inscripcion_id = intval( $inscripcion_id );
            $admin_user_id  = intval( $admin_user_id );

            if ( 0 === $inscripcion_id ) {
                return new WP_Error( 'invalid_input', 'ID de inscripción inválido.' );
            }

            // If admin user not provided, use current user
            if ( 0 === $admin_user_id ) {
                $admin_user_id = get_current_user_id();
            }

            // Permission check if Escuela_Instructor class is available
            if ( class_exists( 'Escuela_Instructor' ) && ! user_can( $admin_user_id, Escuela_Instructor::CAPABILITY ) ) {
                return new WP_Error( 'permission_denied', 'No tenés permisos para aprobar inscripciones.' );
            }

            $table = $wpdb->prefix . 'escuela_inscripciones';
            $row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $inscripcion_id ), ARRAY_A );

            if ( empty( $row ) ) {
                return new WP_Error( 'not_found', 'Inscripción no encontrada.' );
            }

            $updated = Escuela_Instructor_DB::update_status( $inscripcion_id, 'active' );

            if ( false === $updated ) {
                return new WP_Error( 'db_error', 'No se pudo actualizar el estado.' );
            }

            // Grant course access via LearnDash if available
            if ( function_exists( 'ld_update_course_access' ) ) {
                try {
                    ld_update_course_access( intval( $row['user_id'] ), intval( $row['course_id'] ), false );
                } catch ( Exception $e ) {
                    // Silently continue — course access is best-effort
                }
            }

            // Auto-add to default LD group 'todos-los-cursos' so instructors
            // can see the student in Reports / ProPanel
            $group = get_page_by_path( 'todos-los-cursos', OBJECT, 'groups' );
            if ( $group ) {
                $members = get_post_meta( $group->ID, 'group_users', true );
                if ( ! is_array( $members ) ) {
                    $members = array();
                }
                $user_id = intval( $row['user_id'] );
                if ( ! in_array( $user_id, $members, true ) ) {
                    $members[] = $user_id;
                    update_post_meta( $group->ID, 'group_users', $members );

                    // Also associate the course with the group if not already
                    $courses = get_post_meta( $group->ID, 'group_courses', true );
                    if ( ! is_array( $courses ) ) {
                        $courses = array();
                    }
                    $course_id = intval( $row['course_id'] );
                    if ( ! in_array( $course_id, $courses, true ) ) {
                        $courses[] = $course_id;
                        update_post_meta( $group->ID, 'group_courses', $courses );
                    }
                }
            }

            return array( 'id' => $inscripcion_id, 'status' => 'active' );
        }

        /**
         * Helper to fetch an inscription by user + course
         *
         * @param int $user_id
         * @param int $course_id
         * @return array|null
         */
        public static function get_inscripcion( $user_id, $course_id ) {
            $user_id   = intval( $user_id );
            $course_id = intval( $course_id );

            if ( 0 === $user_id || 0 === $course_id ) {
                return null;
            }

            return Escuela_Instructor_DB::get_by_user_course( $user_id, $course_id );
        }
    }
}
