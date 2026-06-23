<?php
// Helper script used by the apply agent to inspect WP DB via bootstrap.
require __DIR__ . '/../app/public/wp-load.php';

global $wpdb;
$rows = $wpdb->get_results("SHOW TABLES LIKE '%escuela_inscripciones%'");
var_export($rows);
