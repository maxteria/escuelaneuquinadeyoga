<?php
/**
 * Extract Kadence option values from the SQL dump and output them as
 * PHP commands suitable for wp option update via SSH.
 * 
 * Usage: php import-kadence.php
 */
require __DIR__ . '/app/public/wp-load.php';

$option_names = [
    'kadence_blocks_schema_version',
    'kadence_global_palette', 
    'kadenceblocks_data_settings',
    'kadence_blocks_config_blocks',
    'theme_mods_kadence',
];

global $wpdb;
$prefix = $wpdb->prefix;

foreach ($option_names as $opt_name) {
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT option_value, autoload FROM {$prefix}options WHERE option_name = %s",
        $opt_name
    ));
    if (!$row) {
        echo "// Option not found: {$opt_name}" . PHP_EOL;
        continue;
    }
    // Encode the value in base64 to avoid shell escaping issues
    $encoded = base64_encode($row->option_value);
    $autoload = $row->autoload === 'yes' ? 'yes' : 'no';
    echo "{$opt_name}|{$autoload}|{$encoded}" . PHP_EOL;
}
