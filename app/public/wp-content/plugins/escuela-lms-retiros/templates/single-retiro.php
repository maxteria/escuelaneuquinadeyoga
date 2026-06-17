<?php
/**
 * Template for single retiro CPT.
 * Loaded via single_template filter from escuela-lms-retiros plugin.
 */

get_header();

while (have_posts()) : the_post();
    $meta = enyr_retiros_get_card_meta(get_the_ID());
    $settings = enyr_retiros_get_settings();
    ?>

    <div class="enyr-single-retiro">
        <?php if ($meta['thumbnail']): ?>
        <div class="enyr-single-retiro__hero">
            <img class="enyr-single-retiro__hero-img" src="<?php echo esc_url($meta['thumbnail']); ?>" alt="<?php echo esc_attr($meta['title']); ?>">
            <div class="enyr-single-retiro__hero-overlay">
                <div class="enyr-container">
                    <h1 class="enyr-single-retiro__hero-title"><?php echo esc_html($meta['title']); ?></h1>
                    <div class="enyr-retiro-card__meta">
                        <?php if ($meta['fecha_formateada']): ?>
                            <span class="enyr-retiro-card__fecha enyr-single-retiro__hero-meta"><?php echo esc_html($meta['fecha_formateada']); ?></span>
                        <?php endif; ?>
                        <span class="enyr-retiro-card__tipo enyr-single-retiro__hero-meta"><?php echo esc_html($meta['tipo_label']); ?></span>
                        <span class="enyr-retiro-card__estado <?php echo esc_attr($meta['estado_class']); ?>"><?php echo esc_html($meta['estado_label']); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="enyr-single-retiro__hero enyr-single-retiro__hero--noimg">
            <div class="enyr-container">
                <h1 class="enyr-single-retiro__hero-title"><?php echo esc_html($meta['title']); ?></h1>
                <div class="enyr-retiro-card__meta">
                    <?php if ($meta['fecha_formateada']): ?>
                        <span class="enyr-retiro-card__fecha"><?php echo esc_html($meta['fecha_formateada']); ?></span>
                    <?php endif; ?>
                    <span class="enyr-retiro-card__tipo"><?php echo esc_html($meta['tipo_label']); ?></span>
                    <span class="enyr-retiro-card__estado <?php echo esc_attr($meta['estado_class']); ?>"><?php echo esc_html($meta['estado_label']); ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="enyr-container">
            <div class="enyr-single-retiro__main">
                <a href="<?php echo esc_url(home_url('/retiros/')); ?>" class="enyr-single-retiro__back">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    Volver a retiros
                </a>

                <?php if ($meta['duracion'] || $meta['lugar']): ?>
                <div class="enyr-single-retiro__info">
                    <?php if ($meta['duracion']): ?>
                    <div class="enyr-single-retiro__info-item">
                        <svg class="enyr-single-retiro__info-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span><?php echo esc_html($meta['duracion']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($meta['lugar']): ?>
                    <div class="enyr-single-retiro__info-item">
                        <svg class="enyr-single-retiro__info-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span><?php echo esc_html($meta['lugar']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="enyr-single-retiro__content entry-content single-content">
                    <?php the_content(); ?>
                </div>

                <div class="enyr-single-retiro__cta">
                    <?php if ($meta['show_cta']): ?>
                    <a href="<?php echo esc_url($meta['cta_href']); ?>" class="enyr-btn enyr-btn--primary enyr-btn--large" target="_blank" rel="noopener noreferrer">
                        <?php echo esc_html($meta['cta_text_output']); ?>
                    </a>
                    <?php else: ?>
                    <span class="enyr-retiro-card__finished-label">Retiro finalizado</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php
endwhile;

get_footer();
