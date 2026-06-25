<?php
/**
 * Kadence Child functions
 * Enqueue parent + child styles and load minimal assets for the courses landing.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts', 'kadence_child_enqueue_styles', 20 );

function kadence_child_enqueue_styles() {
    $child_handle = 'kadence-child-style';
    $child_path   = get_stylesheet_directory() . '/style.css';
    $child_ver    = file_exists( $child_path ) ? filemtime( $child_path ) : null;
    wp_enqueue_style( $child_handle, get_stylesheet_uri(), array( 'kadence-style' ), $child_ver );
}
