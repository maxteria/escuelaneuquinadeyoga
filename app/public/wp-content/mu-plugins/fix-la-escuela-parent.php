<?php
/**
 * Redirect old /tradicion/ URLs to /la-escuela/.
 * /instructora/ also redirects since it is now part of the combined bios page.
 */
add_action('template_redirect', function() {
    $request_path = untrailingslashit(wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));

    $redirects = [
        '/tradicion/la-escuela'  => '/la-escuela/',
        '/tradicion/instructora' => '/la-escuela/',
        '/tradicion'             => '/la-escuela/',
        '/instructora'           => '/la-escuela/',
    ];

    if (isset($redirects[$request_path])) {
        wp_redirect($redirects[$request_path], 301);
        exit;
    }
});
