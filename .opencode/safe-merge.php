<?php
require_once __DIR__ . '/app/public/wp-load.php';

$opts = get_option('theme_mods_kadence', array());

if (!is_array($opts)) {
    $opts = array();
}

$opts['header_button_label'] = 'Entrar a la Escuela';
$opts['header_button_url'] = '/profile/';
$opts['header_button_background'] = array('color' => '#6B7F59', 'hover' => '#8B9F72');

update_option('theme_mods_kadence', $opts);

echo "header_button_label: " . get_theme_mod('header_button_label') . "\n";
echo "header_button_url: " . get_theme_mod('header_button_url') . "\n";