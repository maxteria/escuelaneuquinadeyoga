<?php
/**
 * Uninstall handler for Escuela Instructor
 *
 * This file is executed when the plugin is uninstalled via WP-CLI or the UI.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Drop custom table
$table_name = $wpdb->prefix . 'escuela_inscripciones';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

// Remove capability
$capability = 'manage_inscripciones';
$roles = array( 'administrator', 'group_leader' );

if ( function_exists( 'get_role' ) ) {
    foreach ( $roles as $role_name ) {
        $role = get_role( $role_name );
        if ( $role && $role->has_cap( $capability ) ) {
            $role->remove_cap( $capability );
        }
    }
}

// Optionally remove the page created on activation
$slug = 'inscripcion-pendiente';
$page = get_page_by_path( $slug, OBJECT, 'page' );
if ( $page ) {
    wp_delete_post( $page->ID, true );
}
