<?php
/**
 * Plugin Name: Escuela LMS Retiros
 * Description: Gestión de retiros como entidad independiente. CPT + metaboxes + shortcodes + settings.
 * Version: 1.1.0
 */

defined('ABSPATH') || exit;

define('ENYR_RETIROS_VERSION', '1.1.0');
define('ENYR_RETIROS_DIR', plugin_dir_path(__FILE__));
define('ENYR_RETIROS_URL', plugin_dir_url(__FILE__));

register_activation_hook(__FILE__, function () {
    enyr_retiros_register_cpt();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});

add_action('init', 'enyr_retiros_register_cpt');
function enyr_retiros_register_cpt()
{
    $labels = [
        'name'               => 'Retiros',
        'singular_name'      => 'Retiro',
        'add_new'            => 'Agregar retiro',
        'add_new_item'       => 'Agregar nuevo retiro',
        'edit_item'          => 'Editar retiro',
        'new_item'           => 'Nuevo retiro',
        'view_item'          => 'Ver retiro',
        'search_items'       => 'Buscar retiros',
        'not_found'          => 'No se encontraron retiros',
        'not_found_in_trash' => 'No hay retiros en la papelera',
        'all_items'          => 'Todos los retiros',
        'menu_name'          => 'Retiros',
    ];

    $args = [
        'labels'       => $labels,
        'public'       => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-palmtree',
        'menu_position' => 25,
        'supports'     => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions'],
        'rewrite'      => ['slug' => 'retiros', 'with_front' => false],
        'has_archive'  => false,
        'publicly_queryable' => true,
        'show_in_rest' => true,
    ];

    register_post_type('retiro', $args);
}

add_action('add_meta_boxes', 'enyr_retiros_metabox');
function enyr_retiros_metabox()
{
    add_meta_box(
        'enyr_retiro_datos',
        'Datos del retiro',
        'enyr_retiros_metabox_cb',
        'retiro',
        'normal',
        'high'
    );
}

function enyr_retiros_metabox_cb($post)
{
    wp_nonce_field('enyr_retiro_meta', 'enyr_retiro_nonce');

    $fecha   = get_post_meta($post->ID, '_enyr_retiro_fecha', true);
    $tipo    = get_post_meta($post->ID, '_enyr_retiro_tipo', true);
    $estado  = get_post_meta($post->ID, '_enyr_retiro_estado', true);
    $duracion = get_post_meta($post->ID, '_enyr_retiro_duracion', true);
    $lugar   = get_post_meta($post->ID, '_enyr_retiro_lugar', true);
    $cta_text = get_post_meta($post->ID, '_enyr_retiro_cta_text', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="enyr_retiro_fecha">Fecha del retiro</label></th>
            <td><input type="date" id="enyr_retiro_fecha" name="enyr_retiro_fecha" value="<?php echo esc_attr($fecha); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="enyr_retiro_tipo">Tipo de retiro</label></th>
            <td>
                <select id="enyr_retiro_tipo" name="enyr_retiro_tipo">
                    <option value="profundizacion" <?php selected($tipo, 'profundizacion'); ?>>Profundización</option>
                    <option value="principiantes" <?php selected($tipo, 'principiantes'); ?>>Principiantes</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="enyr_retiro_estado">Estado</label></th>
            <td>
                <select id="enyr_retiro_estado" name="enyr_retiro_estado">
                    <option value="proximo" <?php selected($estado, 'proximo'); ?>>Próximo</option>
                    <option value="abierto" <?php selected($estado, 'abierto'); ?>>Abierto</option>
                    <option value="completo" <?php selected($estado, 'completo'); ?>>Completo</option>
                    <option value="finalizado" <?php selected($estado, 'finalizado'); ?>>Finalizado</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="enyr_retiro_duracion">Duración</label></th>
            <td><input type="text" id="enyr_retiro_duracion" name="enyr_retiro_duracion" value="<?php echo esc_attr($duracion); ?>" class="regular-text" placeholder="ej: 3 días, 2 noches"></td>
        </tr>
        <tr>
            <th><label for="enyr_retiro_lugar">Lugar</label></th>
            <td><input type="text" id="enyr_retiro_lugar" name="enyr_retiro_lugar" value="<?php echo esc_attr($lugar); ?>" class="regular-text" placeholder="ej: Cerro de los Siete Colores"></td>
        </tr>
        <tr>
            <th><label for="enyr_retiro_cta_text">Texto CTA WhatsApp</label></th>
            <td>
                <input type="text" id="enyr_retiro_cta_text" name="enyr_retiro_cta_text" value="<?php echo esc_attr($cta_text); ?>" class="regular-text" placeholder="ej: Quiero información sobre este retiro">
                <p class="description">Texto que se envía al hacer clic en "Consultar por WhatsApp".</p>
            </td>
        </tr>
    </table>
    <?php
}

add_action('save_post', 'enyr_retiros_save_meta');
function enyr_retiros_save_meta($post_id)
{
    if (!isset($_POST['enyr_retiro_nonce']) || !wp_verify_nonce($_POST['enyr_retiro_nonce'], 'enyr_retiro_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = [
        'enyr_retiro_fecha'    => '_enyr_retiro_fecha',
        'enyr_retiro_tipo'     => '_enyr_retiro_tipo',
        'enyr_retiro_estado'   => '_enyr_retiro_estado',
        'enyr_retiro_duracion'  => '_enyr_retiro_duracion',
        'enyr_retiro_lugar'    => '_enyr_retiro_lugar',
        'enyr_retiro_cta_text'  => '_enyr_retiro_cta_text',
    ];

    foreach ($fields as $name => $meta_key) {
        if (isset($_POST[$name])) {
            update_post_meta($post_id, $meta_key, sanitize_text_field(wp_unslash($_POST[$name])));
        }
    }
}

/* =============================================
   SETTINGS API — Pantalla Retiros → Ajustes
   ============================================= */

add_action('admin_menu', 'enyr_retiros_add_settings_page');
function enyr_retiros_add_settings_page()
{
    add_submenu_page(
        'edit.php?post_type=retiro',
        'Ajustes de Retiros',
        'Ajustes',
        'manage_options',
        'enyr-retiros-settings',
        'enyr_retiros_settings_page_html'
    );
}

add_action('admin_init', 'enyr_retiros_settings_init');
function enyr_retiros_settings_init()
{
    register_setting('enyr_retiros_settings', 'enyr_retiros_settings', 'enyr_retiros_sanitize_settings');

    add_settings_section(
        'enyr_retiros_main',
        'Comportamiento del listado',
        '__return_false',
        'enyr-retiros-settings'
    );

    add_settings_field(
        'enyr_expired_behavior',
        'Retiros vencidos',
        'enyr_retiros_field_expired_behavior',
        'enyr-retiros-settings',
        'enyr_retiros_main'
    );

    add_settings_field(
        'enyr_expired_show_cta',
        'CTA en vencidos',
        'enyr_retiros_field_expired_show_cta',
        'enyr-retiros-settings',
        'enyr_retiros_main'
    );

    add_settings_field(
        'enyr_expired_cta_text',
        'Texto CTA alternativo',
        'enyr_retiros_field_expired_cta_text',
        'enyr-retiros-settings',
        'enyr_retiros_main'
    );

    add_settings_field(
        'enyr_expired_cta_url',
        'URL CTA alternativo',
        'enyr_retiros_field_expired_cta_url',
        'enyr-retiros-settings',
        'enyr_retiros_main'
    );

    add_settings_field(
        'enyr_auto_update_status',
        'Actualización automática',
        'enyr_retiros_field_auto_update',
        'enyr-retiros-settings',
        'enyr_retiros_main'
    );

    add_settings_field(
        'enyr_hide_full_retiros',
        'Ocultar completos',
        'enyr_retiros_field_hide_full',
        'enyr-retiros-settings',
        'enyr_retiros_main'
    );
}

function enyr_retiros_sanitize_settings($input)
{
    $output = [];

    $valid_behavior = ['show_finished', 'hide', 'archive_section'];
    $output['expired_behavior'] = in_array($input['expired_behavior'] ?? '', $valid_behavior, true)
        ? $input['expired_behavior']
        : 'archive_section';

    $output['expired_show_cta'] = !empty($input['expired_show_cta']) ? 1 : 0;
    $output['auto_update_status'] = !empty($input['auto_update_status']) ? 1 : 0;
    $output['hide_full_retiros'] = !empty($input['hide_full_retiros']) ? 1 : 0;

    $output['expired_cta_text'] = sanitize_text_field($input['expired_cta_text'] ?? 'Consultar próximos retiros');
    $output['expired_cta_url'] = esc_url_raw($input['expired_cta_url'] ?? 'https://wa.me/5492942337884');

    return $output;
}

function enyr_retiros_get_settings()
{
    $defaults = [
        'expired_behavior'   => 'archive_section',
        'expired_show_cta'   => 0,
        'expired_cta_text'   => 'Consultar próximos retiros',
        'expired_cta_url'    => 'https://wa.me/5492942337884',
        'auto_update_status' => 0,
        'hide_full_retiros'  => 0,
    ];
    $settings = get_option('enyr_retiros_settings', []);
    return wp_parse_args($settings, $defaults);
}

/* ---- Field callbacks ---- */

function enyr_retiros_field_expired_behavior()
{
    $settings = enyr_retiros_get_settings();
    $current = $settings['expired_behavior'];
    $options = [
        'show_finished'   => 'Mostrar en listado como "Finalizado"',
        'hide'            => 'Ocultar del listado principal',
        'archive_section' => 'Mostrar en sección separada "Retiros anteriores"',
    ];
    foreach ($options as $value => $label) {
        printf(
            '<label><input type="radio" name="enyr_retiros_settings[expired_behavior]" value="%s" %s> %s</label><br>',
            esc_attr($value),
            checked($current, $value, false),
            esc_html($label)
        );
    }
    echo '<p class="description">Define qué ocurre con retiros cuya fecha ya pasó.</p>';
}

function enyr_retiros_field_expired_show_cta()
{
    $settings = enyr_retiros_get_settings();
    printf(
        '<label><input type="checkbox" name="enyr_retiros_settings[expired_show_cta]" value="1" %s> Mostrar CTA en retiros finalizados</label>',
        checked($settings['expired_show_cta'], 1, false)
    );
    echo '<p class="description">Si está desactivado, se oculta el botón WhatsApp y se muestra "Retiro finalizado".</p>';
}

function enyr_retiros_field_expired_cta_text()
{
    $settings = enyr_retiros_get_settings();
    printf(
        '<input type="text" name="enyr_retiros_settings[expired_cta_text]" value="%s" class="regular-text" placeholder="Consultar próximos retiros">',
        esc_attr($settings['expired_cta_text'])
    );
    echo '<p class="description">Texto del botón CTA para retiros vencidos (si está activo).</p>';
}

function enyr_retiros_field_expired_cta_url()
{
    $settings = enyr_retiros_get_settings();
    printf(
        '<input type="url" name="enyr_retiros_settings[expired_cta_url]" value="%s" class="regular-text" placeholder="https://wa.me/5492942337884">',
        esc_attr($settings['expired_cta_url'])
    );
    echo '<p class="description">URL del CTA alternativo (WhatsApp u otra).</p>';
}

function enyr_retiros_field_auto_update()
{
    $settings = enyr_retiros_get_settings();
    printf(
        '<label><input type="checkbox" name="enyr_retiros_settings[auto_update_status]" value="1" %s> Actualizar estado a <strong>Finalizado</strong> automáticamente cuando la fecha ya pasó</label>',
        checked($settings['auto_update_status'], 1, false)
    );
    echo '<p class="description"><strong>Importante:</strong> Por ahora solo es visual. No se modifica postmeta automáticamente sin aprobación explícita.</p>';
}

function enyr_retiros_field_hide_full()
{
    $settings = enyr_retiros_get_settings();
    printf(
        '<label><input type="checkbox" name="enyr_retiros_settings[hide_full_retiros]" value="1" %s> Ocultar retiros marcados como <strong>Completo</strong></label>',
        checked($settings['hide_full_retiros'], 1, false)
    );
}

function enyr_retiros_settings_page_html()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>Ajustes de Retiros</h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('enyr_retiros_settings');
            do_settings_sections('enyr-retiros-settings');
            submit_button('Guardar ajustes');
            ?>
        </form>
    </div>
    <?php
}

/* =============================================
   HELPERS
   ============================================= */

function enyr_is_retiro_expired($post_id)
{
    $fecha = get_post_meta($post_id, '_enyr_retiro_fecha', true);
    if (empty($fecha)) {
        return false;
    }
    return $fecha < current_time('Y-m-d');
}

function enyr_get_retiro_display_status($post_id)
{
    if (enyr_is_retiro_expired($post_id)) {
        return 'finalizado_auto';
    }
    return get_post_meta($post_id, '_enyr_retiro_estado', true);
}

function enyr_should_show_retiro($post_id, $context = 'main')
{
    $settings = enyr_retiros_get_settings();
    $is_expired = enyr_is_retiro_expired($post_id);
    $estado = get_post_meta($post_id, '_enyr_retiro_estado', true);

    if (!empty($settings['hide_full_retiros']) && $estado === 'completo') {
        return false;
    }

    if (!$is_expired) {
        return true;
    }

    if ($context === 'main') {
        if ($settings['expired_behavior'] === 'hide') {
            return false;
        }
        return true;
    }

    return true;
}

function enyr_retiros_get_card_meta($post_id)
{
    $fecha      = get_post_meta($post_id, '_enyr_retiro_fecha', true);
    $tipo       = get_post_meta($post_id, '_enyr_retiro_tipo', true);
    $estado     = get_post_meta($post_id, '_enyr_retiro_estado', true);
    $duracion   = get_post_meta($post_id, '_enyr_retiro_duracion', true);
    $lugar      = get_post_meta($post_id, '_enyr_retiro_lugar', true);
    $cta_text   = get_post_meta($post_id, '_enyr_retiro_cta_text', true);
    $thumbnail  = get_the_post_thumbnail_url($post_id, 'large');
    $title      = get_the_title($post_id);
    $excerpt    = get_the_excerpt($post_id);

    $tipo_label = $tipo === 'profundizacion' ? 'Profundización' : 'Principiantes';

    $display_status = enyr_get_retiro_display_status($post_id);
    $is_expired = enyr_is_retiro_expired($post_id);

    $estado_class = '';
    $estado_label = '';
    switch ($display_status) {
        case 'finalizado_auto':
            $estado_class = 'enyr-estado--finalizado';
            $estado_label = 'Finalizado';
            break;
        case 'finalizado':
            $estado_class = 'enyr-estado--finalizado';
            $estado_label = 'Finalizado';
            break;
        case 'proximo':
            $estado_class = 'enyr-estado--proximo';
            $estado_label = 'Próximo';
            break;
        case 'abierto':
            $estado_class = 'enyr-estado--abierto';
            $estado_label = 'Abierto';
            break;
        case 'completo':
            $estado_class = 'enyr-estado--completo';
            $estado_label = 'Completo';
            break;
    }

    $fecha_formateada = '';
    if (!empty($fecha)) {
        $timestamp = strtotime($fecha);
        if ($timestamp) {
            $fecha_formateada = date_i18n('j F Y', $timestamp);
        }
    }

    $show_cta = true;
    $cta_href = 'https://wa.me/5492942337884?text=';
    $cta_text_output = 'Consultar por WhatsApp';

    if ($is_expired) {
        $s = enyr_retiros_get_settings();
        if (empty($s['expired_show_cta'])) {
            $show_cta = false;
        } else {
            $cta_text_output = !empty($s['expired_cta_text']) ? esc_html($s['expired_cta_text']) : 'Consultar próximos retiros';
            $cta_href = !empty($s['expired_cta_url']) ? esc_url($s['expired_cta_url']) : 'https://wa.me/5492942337884';
        }
    } else {
        if (empty($cta_text)) {
            $cta_href .= urlencode('Hola, quiero consultar por el retiro: ' . $title);
        } else {
            $cta_href .= urlencode($cta_text);
        }
    }

    $permalink = get_permalink($post_id);

    return compact(
        'post_id', 'fecha', 'tipo', 'estado', 'duracion', 'lugar',
        'cta_text', 'thumbnail', 'title', 'excerpt', 'tipo_label',
        'estado_class', 'estado_label', 'fecha_formateada',
        'is_expired', 'show_cta', 'cta_href', 'cta_text_output',
        'permalink'
    );
}

function enyr_retiros_render_card($m)
{
    extract($m);
    ?>
    <article class="enyr-retiro-card<?php echo $is_expired ? ' enyr-retiro-card--expired' : ''; ?>">
        <?php if ($thumbnail): ?>
            <a href="<?php echo esc_url($permalink); ?>" class="enyr-retiro-card__image-wrap" tabindex="-1" aria-hidden="true">
                <img class="enyr-retiro-card__image" src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
            </a>
        <?php endif; ?>
        <div class="enyr-retiro-card__body">
            <div class="enyr-retiro-card__meta">
                <?php if ($fecha_formateada): ?>
                    <span class="enyr-retiro-card__fecha"><?php echo esc_html($fecha_formateada); ?></span>
                <?php endif; ?>
                <span class="enyr-retiro-card__tipo"><?php echo esc_html($tipo_label); ?></span>
                <span class="enyr-retiro-card__estado <?php echo esc_attr($estado_class); ?>"><?php echo esc_html($estado_label); ?></span>
            </div>
            <h3 class="enyr-retiro-card__title">
                <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
            </h3>
            <?php if ($excerpt): ?>
                <p class="enyr-retiro-card__excerpt"><?php echo esc_html($excerpt); ?></p>
            <?php endif; ?>
            <?php if ($duracion || $lugar): ?>
                <div class="enyr-retiro-card__details">
                    <?php if ($duracion): ?>
                        <span class="enyr-retiro-card__detail"><?php echo esc_html($duracion); ?></span>
                    <?php endif; ?>
                    <?php if ($lugar): ?>
                        <span class="enyr-retiro-card__detail"><?php echo esc_html($lugar); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="enyr-retiro-card__actions">
                <a href="<?php echo esc_url($permalink); ?>" class="enyr-retiro-card__detail-link">
                    Ver detalle
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
                <?php if ($show_cta): ?>
                    <a href="<?php echo esc_url($cta_href); ?>"
                       class="enyr-btn enyr-btn--primary"
                       target="_blank"
                       rel="noopener noreferrer">
                        <?php echo esc_html($cta_text_output); ?>
                    </a>
                <?php else: ?>
                    <span class="enyr-retiro-card__finished-label">Retiro finalizado</span>
                <?php endif; ?>
            </div>
        </div>
    </article>
    <?php
}

/* =============================================
   SHORTCODE: [retiros_listado]
   ============================================= */

add_shortcode('retiros_listado', 'enyr_retiros_listado_sc');
function enyr_retiros_listado_sc($atts)
{
    $atts = shortcode_atts([
        'limit' => -1,
        'tipo'  => '',
    ], $atts);

    $meta_query = [];
    if (!empty($atts['tipo'])) {
        $meta_query[] = [
            'key'   => '_enyr_retiro_tipo',
            'value' => $atts['tipo'],
        ];
    }

    $query = new WP_Query([
        'post_type'      => 'retiro',
        'posts_per_page' => $atts['limit'],
        'meta_key'       => '_enyr_retiro_fecha',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => $meta_query,
    ]);

    ob_start();

    if (!$query->have_posts()) {
        ?>
        <div class="enyr-retiros-empty">
            <p class="enyr-retiros-empty__text">Próximamente publicaremos nuevas fechas de retiros.</p>
            <a href="https://wa.me/5492942337884?text=Hola%2C%20quiero%20consultar%20por%20los%20pr%C3%B3ximos%20retiros."
               class="enyr-btn enyr-btn--primary enyr-btn--large"
               target="_blank"
               rel="noopener noreferrer">
                Consultar por WhatsApp
            </a>
        </div>
        <?php
        return ob_get_clean();
    }

    $settings = enyr_retiros_get_settings();
    $behavior = $settings['expired_behavior'];

    $upcoming = [];
    $past = [];

    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();

        if (!enyr_should_show_retiro($post_id, 'main')) {
            continue;
        }

        $meta = enyr_retiros_get_card_meta($post_id);

        if ($meta['is_expired']) {
            $past[] = $meta;
        } else {
            $upcoming[] = $meta;
        }
    }

    if (empty($upcoming) && empty($past)) {
        ?>
        <div class="enyr-retiros-empty">
            <p class="enyr-retiros-empty__text">Próximamente publicaremos nuevas fechas de retiros.</p>
            <a href="https://wa.me/5492942337884?text=Hola%2C%20quiero%20consultar%20por%20los%20pr%C3%B3ximos%20retiros."
               class="enyr-btn enyr-btn--primary enyr-btn--large"
               target="_blank"
               rel="noopener noreferrer">
                Consultar por WhatsApp
            </a>
        </div>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }

    if ($behavior === 'archive_section') {
        if (!empty($upcoming)) {
            echo '<section class="enyr-retiros-listado"><div class="enyr-container">';
            echo '<div class="enyr-retiros-listado__heading"><h2 class="enyr-retiros-listado__title">Próximos retiros</h2></div>';
            echo '<div class="enyr-retiros__grid">';
            foreach ($upcoming as $meta) {
                enyr_retiros_render_card($meta);
            }
            echo '</div></div></section>';
        }
        if (!empty($past)) {
            echo '<section class="enyr-retiros-listado enyr-retiros-listado--past"><div class="enyr-container">';
            echo '<div class="enyr-retiros-listado__heading"><h2 class="enyr-retiros-listado__title">Retiros anteriores</h2></div>';
            echo '<div class="enyr-retiros__grid">';
            $past_desc = array_reverse($past);
            foreach ($past_desc as $meta) {
                enyr_retiros_render_card($meta);
            }
            echo '</div></div></section>';
        }
    } else {
        echo '<section class="enyr-retiros-listado"><div class="enyr-container">';
        echo '<div class="enyr-retiros-listado__heading"><h2 class="enyr-retiros-listado__title">Próximos retiros</h2></div>';
        echo '<div class="enyr-retiros__grid">';
        foreach ($upcoming as $meta) {
            enyr_retiros_render_card($meta);
        }
        foreach ($past as $meta) {
            enyr_retiros_render_card($meta);
        }
        echo '</div></div></section>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}

add_shortcode('retiro_destacado', 'enyr_retiro_destacado_sc');
function enyr_retiro_destacado_sc($atts)
{
    $atts = shortcode_atts([
        'id' => 0,
    ], $atts);

    if (empty($atts['id'])) {
        return '';
    }

    $post = get_post((int) $atts['id']);
    if (!$post || $post->post_type !== 'retiro' || $post->post_status !== 'publish') {
        return '';
    }

    setup_postdata($post);
    $post_id = $post->ID;
    $fecha   = get_post_meta($post_id, '_enyr_retiro_fecha', true);
    $tipo    = get_post_meta($post_id, '_enyr_retiro_tipo', true);
    $estado  = get_post_meta($post_id, '_enyr_retiro_estado', true);
    $duracion = get_post_meta($post_id, '_enyr_retiro_duracion', true);
    $lugar   = get_post_meta($post_id, '_enyr_retiro_lugar', true);
    $thumbnail = get_the_post_thumbnail_url($post_id, 'large');
    $title   = get_the_title();
    $excerpt = get_the_excerpt();

    $tipo_label = $tipo === 'profundizacion' ? 'Profundización' : 'Principiantes';

    $estado_label = '';
    switch ($estado) {
        case 'proximo': $estado_label = 'Próximo'; break;
        case 'abierto': $estado_label = 'Abierto'; break;
        case 'completo': $estado_label = 'Completo'; break;
        case 'finalizado': $estado_label = 'Finalizado'; break;
    }

    $cta_text = 'Hola%2C%20quiero%20consultar%20por%20el%20retiro%3A%20' . urlencode($title);

    $fecha_formateada = '';
    if (!empty($fecha)) {
        $timestamp = strtotime($fecha);
        if ($timestamp) {
            $fecha_formateada = date_i18n('j F Y', $timestamp);
        }
    }

    ob_start();
    ?>
    <section class="enyr-retiro-destacado">
        <div class="enyr-retiro-destacado__inner">
            <?php if ($thumbnail): ?>
                <div class="enyr-retiro-destacado__image-wrap">
                    <img class="enyr-retiro-destacado__image" src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
                </div>
            <?php endif; ?>
            <div class="enyr-retiro-destacado__body">
                <div class="enyr-retiro-card__meta">
                    <?php if ($fecha_formateada): ?>
                        <span class="enyr-retiro-card__fecha"><?php echo esc_html($fecha_formateada); ?></span>
                    <?php endif; ?>
                    <span class="enyr-retiro-card__tipo"><?php echo esc_html($tipo_label); ?></span>
                    <?php if ($estado_label): ?>
                        <span class="enyr-retiro-card__estado"><?php echo esc_html($estado_label); ?></span>
                    <?php endif; ?>
                </div>
                <h3 class="enyr-retiro-destacado__title"><?php echo esc_html($title); ?></h3>
                <?php if ($excerpt): ?>
                    <p class="enyr-retiro-destacado__excerpt"><?php echo esc_html($excerpt); ?></p>
                <?php endif; ?>
                <?php if ($duracion || $lugar): ?>
                    <div class="enyr-retiro-card__details">
                        <?php if ($duracion): ?><span class="enyr-retiro-card__detail"><?php echo esc_html($duracion); ?></span><?php endif; ?>
                        <?php if ($lugar): ?><span class="enyr-retiro-card__detail"><?php echo esc_html($lugar); ?></span><?php endif; ?>
                    </div>
                <?php endif; ?>
                <a href="https://wa.me/5492942337884?text=<?php echo $cta_text; ?>"
                   class="enyr-btn enyr-btn--primary enyr-btn--large"
                   target="_blank"
                   rel="noopener noreferrer">
                    Consultar por WhatsApp
                </a>
            </div>
        </div>
    </section>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}

add_action('wp_enqueue_scripts', 'enyr_retiros_enqueue');
function enyr_retiros_enqueue()
{
    if (is_page('retiros') || is_singular('retiro')) {
        wp_enqueue_style(
            'enyr-retiros-cpt',
            ENYR_RETIROS_URL . 'assets/css/retiros-cpt.css',
            ['escuela-ld-focus-overrides'],
            ENYR_RETIROS_VERSION
        );
    }
}

/* =============================================
   ADMIN COLUMNS
   ============================================= */

add_filter('manage_retiro_posts_columns', 'enyr_retiros_admin_columns');
function enyr_retiros_admin_columns($columns)
{
    $new = [];
    foreach ($columns as $key => $label) {
        if ($key === 'title') {
            $new[$key] = $label;
            $new['enyr_fecha'] = 'Fecha';
            $new['enyr_tipo'] = 'Tipo';
            $new['enyr_estado'] = 'Estado';
            $new['enyr_estado_visual'] = 'Estado visual';
        } else {
            $new[$key] = $label;
        }
    }
    return $new;
}

add_action('manage_retiro_posts_custom_column', 'enyr_retiros_admin_column_data', 10, 2);
function enyr_retiros_admin_column_data($column, $post_id)
{
    switch ($column) {
        case 'enyr_fecha':
            $fecha = get_post_meta($post_id, '_enyr_retiro_fecha', true);
            echo esc_html($fecha ?: '—');
            break;
        case 'enyr_tipo':
            $tipo = get_post_meta($post_id, '_enyr_retiro_tipo', true);
            echo $tipo === 'profundizacion' ? 'Profundización' : ($tipo === 'principiantes' ? 'Principiantes' : '—');
            break;
        case 'enyr_estado':
            $estado = get_post_meta($post_id, '_enyr_retiro_estado', true);
            $labels = ['proximo' => 'Próximo', 'abierto' => 'Abierto', 'completo' => 'Completo', 'finalizado' => 'Finalizado'];
            echo isset($labels[$estado]) ? esc_html($labels[$estado]) : '—';
            break;
        case 'enyr_estado_visual':
            $display = enyr_get_retiro_display_status($post_id);
            if ($display === 'finalizado_auto') {
                echo '<span style="color:#999;">Finalizado automático</span>';
            } else {
                $labels = ['proximo' => 'Próximo', 'abierto' => 'Abierto', 'completo' => 'Completo', 'finalizado' => 'Finalizado'];
                echo isset($labels[$display]) ? esc_html($labels[$display]) : '—';
            }
            break;
    }
}

add_filter('the_content', 'enyr_retiros_append_listado');
function enyr_retiros_append_listado($content)
{
    if (!is_page('retiros') || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    $listado = do_shortcode('[retiros_listado]');
    return $content . $listado;
}

/* =============================================
   SINGLE RETIRO — Custom template
   ============================================= */

add_filter('single_template', 'enyr_retiros_single_template');
function enyr_retiros_single_template($template)
{
    global $post;
    if ($post && $post->post_type === 'retiro') {
        $plugin_template = ENYR_RETIROS_DIR . 'templates/single-retiro.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}
