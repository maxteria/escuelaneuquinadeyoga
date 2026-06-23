<?php
/**
 * Database helper for Escuela Instructor inscriptions
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Escuela_Instructor_DB' ) ) {
    class Escuela_Instructor_DB {
        /**
         * Return full table name with prefix
         *
         * @return string
         */
        protected static function table_name() {
            global $wpdb;
            return $wpdb->prefix . 'escuela_inscripciones';
        }

        /**
         * Insert a new inscription record
         *
         * @param int    $user_id
         * @param int    $course_id
         * @param string $status
         * @return int|false Insert ID on success, false on failure
         */
        public static function insert( $user_id, $course_id, $status = 'pending' ) {
            global $wpdb;

            $table = self::table_name();

            $data = array(
                'user_id'   => intval( $user_id ),
                'course_id' => intval( $course_id ),
                'status'    => sanitize_text_field( $status ),
            );

            $format = array( '%d', '%d', '%s' );

            $res = $wpdb->insert( $table, $data, $format );

            if ( false === $res ) {
                return false;
            }

            return $wpdb->insert_id;
        }

        /**
         * Get a record by user_id and course_id
         *
         * @param int $user_id
         * @param int $course_id
         * @return array|object|null
         */
        public static function get_by_user_course( $user_id, $course_id ) {
            global $wpdb;
            $table = self::table_name();

            $sql = $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d AND course_id = %d LIMIT 1", $user_id, $course_id );
            return $wpdb->get_row( $sql, ARRAY_A );
        }

        /**
         * Update the status of a record by ID
         *
         * @param int    $id
         * @param string $status
         * @return int|false Number of rows affected or false on failure
         */
        public static function update_status( $id, $status ) {
            global $wpdb;
            $table = self::table_name();

            $data = array( 'status' => sanitize_text_field( $status ) );
            $where = array( 'id' => intval( $id ) );
            $formats = array( '%s' );
            $where_formats = array( '%d' );

            $res = $wpdb->update( $table, $data, $where, $formats, $where_formats );

            if ( false === $res ) {
                return false;
            }

            return $wpdb->rows_affected;
        }

        /**
         * List records filtered by status
         *
         * @param string $status
         * @return array
         */
        public static function list_by_status( $status ) {
            global $wpdb;
            $table = self::table_name();

            $sql = $wpdb->prepare( "SELECT * FROM {$table} WHERE status = %s", $status );
            return $wpdb->get_results( $sql, ARRAY_A );
        }

        /**
         * Check if an inscription exists for user+course
         *
         * @param int $user_id
         * @param int $course_id
         * @return bool
         */
        public static function exists( $user_id, $course_id ) {
            global $wpdb;
            $table = self::table_name();

            $sql = $wpdb->prepare( "SELECT COUNT(1) FROM {$table} WHERE user_id = %d AND course_id = %d", $user_id, $course_id );
            $count = $wpdb->get_var( $sql );

            return (bool) $count;
        }
    }
}
