<?php
require __DIR__ . '/../app/public/wp-load.php';
global $wpdb;
$table = $wpdb->prefix . 'escuela_inscripciones';
$rows = $wpdb->get_results( "DESCRIBE {$table};" );
var_export( $rows );
