<?php
/**
 * Plugin Name: Escuela LMS Logo Reveal
 * Description: Shortcode [eny_logo_reveal] para el logo animado por capas en el hero.
 */

add_shortcode('eny_logo_reveal', function() {
    $yantra_id   = 1630;
    $mandala_id  = 1627;
    $ring_id     = 1625;

    $yantra_url  = wp_get_attachment_url($yantra_id);
    $mandala_url = wp_get_attachment_url($mandala_id);
    $ring_url    = wp_get_attachment_url($ring_id);

    return '<div class="eny-logo-reveal" aria-hidden="true">
    <img class="eny-logo-reveal__layer eny-logo-reveal__layer--yantra" src="' . esc_url($yantra_url) . '" alt="">
    <img class="eny-logo-reveal__layer eny-logo-reveal__layer--mandala" src="' . esc_url($mandala_url) . '" alt="">
    <img class="eny-logo-reveal__layer eny-logo-reveal__layer--ring" src="' . esc_url($ring_url) . '" alt="">
</div>';
});

add_action('wp_enqueue_scripts', function() {
    if (!is_singular() && !is_front_page()) return;
    $post = get_queried_object();
    $has_shortcode = $post && (
        has_shortcode($post->post_content ?? '', 'eny_logo_reveal') ||
        has_shortcode($post->post_content ?? '', 'inicio_landing')
    );
    if (!$has_shortcode) return;

    $plugin_url = plugin_dir_url(__FILE__);

    wp_enqueue_style(
        'escuela-logo-reveal',
        $plugin_url . 'assets/css/logo-reveal.css',
        [],
        '1.0.0'
    );

    wp_enqueue_script(
        'escuela-logo-reveal',
        $plugin_url . 'assets/js/logo-reveal.js',
        [],
        '1.0.0',
        true
    );
});