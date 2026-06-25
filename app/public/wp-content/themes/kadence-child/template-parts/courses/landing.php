<?php
/**
 * Template partial: courses landing
 * Expects $courses = array of arrays with keys: ID, title, excerpt, permalink, thumbnail, alt
 */

if ( ! isset( $courses ) ) {
    $courses = array();
}

// Hero: prefer editable /courses page content (title + excerpt fallback).
$hero_title = '';
$hero_paragraph_text = '';
$courses_page = get_page_by_path( 'courses' );
if ( $courses_page instanceof WP_Post ) {
    $hero_title = get_the_title( $courses_page );
    $rendered   = apply_filters( 'the_content', $courses_page->post_content );

    if ( preg_match( '/<p\b[^>]*>(.*?)<\/p>/is', $rendered, $match ) ) {
        $hero_paragraph_text = wp_strip_all_tags( $match[0] );
    }
}

if ( ! $hero_title ) {
    $hero_title = __( 'Una escuela para estudiar, practicar e integrar', 'escuela-lms' );
}

if ( empty( $hero_paragraph_text ) ) {
    $hero_paragraph_text = __( 'La Escuela Neuquina de Yoga ofrece recorridos de formacion seria y sostenida. Cada propuesta combina formacion teorica, practica guiada, material de estudio descargable y seguimiento del avance. No es solo contenido: es un camino estructurado pensado para quien quiere aprender de verdad.', 'escuela-lms' );
}

// Placeholder resolution: child -> parent -> plugin upload helper
function eny_courses_placeholder_url() {
    $child = get_stylesheet_directory() . '/assets/images/course-placeholder-768x432.png';
    if ( file_exists( $child ) ) {
        return get_stylesheet_directory_uri() . '/assets/images/course-placeholder-768x432.png';
    }

    $parent = get_template_directory() . '/assets/images/course-placeholder-768x432.png';
    if ( file_exists( $parent ) ) {
        return get_template_directory_uri() . '/assets/images/course-placeholder-768x432.png';
    }

    if ( function_exists( 'escuela_upload_url' ) ) {
        return escuela_upload_url( '/2026/05/placeholder-course-768x432.png' );
    }

    return '';
}

$placeholder = eny_courses_placeholder_url();

?>

<section class="enyf-section enyf-intro enyf-intro--first">
    <div class="enyf-container">
        <h2 class="enyf-intro__title"><?php echo esc_html( $hero_title ); ?></h2>
        <p class="enyf-intro__text"><?php echo esc_html( $hero_paragraph_text ); ?></p>
    </div>
</section>

<section class="enyf-section enyf-proposal-types">
    <div class="enyf-container">
        <div class="enyf-proposal-types__grid">
            <div class="enyf-proposal-type">
                <div class="enyf-proposal-type__icon">🎓</div>
                <h3 class="enyf-proposal-type__title">Formaciones</h3>
                <p class="enyf-proposal-type__text">Programas extensos que abarcan un tema en profundidad. Lecciones estructuradas, material descargable, evaluaciones y certificado.</p>
            </div>
            <div class="enyf-proposal-type">
                <div class="enyf-proposal-type__icon">📘</div>
                <h3 class="enyf-proposal-type__title">Cursos</h3>
                <p class="enyf-proposal-type__text">Recorridos tematicos focalizados. Ideales para profundizar en un aspecto especifico del yoga o la meditacion con un enfoque practico.</p>
            </div>
            <div class="enyf-proposal-type">
                <div class="enyf-proposal-type__icon">✨</div>
                <h3 class="enyf-proposal-type__title">Talleres</h3>
                <p class="enyf-proposal-type__text">Encuentros puntuales de profundizacion. Exploraciones intensas sobre una tecnica o practica particular, con seguimiento guiado.</p>
            </div>
        </div>
    </div>
</section>

<section class="enyf-section enyf-catalog">
    <div class="enyf-container">
        <div class="enyf-catalog__header">
            <h2 class="enyf-catalog__title"><?php esc_html_e( 'Explora nuestra oferta academica', 'escuela-lms' ); ?></h2>
            <p class="enyf-catalog__subtitle"><?php esc_html_e( 'Formaciones completas, cursos tematicos y talleres de practica', 'escuela-lms' ); ?></p>
        </div>

        <div class="enyf-cards-grid">
            <?php if ( ! empty( $courses ) ) : ?>
                <?php foreach ( $courses as $c ) :
                    $image = $c['thumbnail'] ? $c['thumbnail'] : $placeholder;
                    $alt   = ! empty( $c['alt'] ) ? $c['alt'] : sprintf( __( 'Course: %s', 'escuela-lms' ), $c['title'] );
                ?>
                <div class="enyf-card">
                    <div class="enyf-card__image" style="background-image:url('<?php echo esc_url( $image ); ?>');" role="img" aria-label="<?php echo esc_attr( $alt ); ?>">
                        <div class="enyf-card__badges"></div>
                    </div>
                    <div class="enyf-card__body">
                        <h3 class="enyf-card__title"><a href="<?php echo esc_url( $c['permalink'] ); ?>"><?php echo esc_html( $c['title'] ); ?></a></h3>
                        <p class="enyf-card__text"><?php echo esc_html( wp_trim_words( $c['excerpt'], 24, '...' ) ); ?></p>
                        <div class="enyf-card__meta"></div>
                        <a href="<?php echo esc_url( $c['permalink'] ); ?>" class="enyf-card__cta enyf-card__cta--primary"><?php esc_html_e( 'Ver formacion →', 'escuela-lms' ); ?></a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="enyf-empty-state">
                    <?php esc_html_e( 'No hay formaciones publicadas todavía. Volvé pronto — estamos preparando nuevos recorridos.', 'escuela-lms' ); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="enyf-section enyf-methodology">
    <div class="enyf-container">
        <div class="enyf-methodology__header">
            <h2 class="enyf-methodology__title"><?php esc_html_e( 'Como funciona el recorrido', 'escuela-lms' ); ?></h2>
        </div>
        <div class="enyf-steps">
            <div class="enyf-step">
                <div class="enyf-step__number">01</div>
                <h3 class="enyf-step__title">Estudia</h3>
                <p class="enyf-step__text">Lecciones con contenido teorico, videos explicativos y material de estudio descargable.</p>
            </div>
            <div class="enyf-step">
                <div class="enyf-step__number">02</div>
                <h3 class="enyf-step__title">Practica</h3>
                <p class="enyf-step__text">Incorpora lo aprendido con ejercicios guiados, visualizaciones y respiraciones adaptadas.</p>
            </div>
            <div class="enyf-step">
                <div class="enyf-step__number">03</div>
                <h3 class="enyf-step__title">Integra</h3>
                <p class="enyf-step__text">Resuelve cuestionarios que verifican la comprension y consolidan el aprendizaje.</p>
            </div>
            <div class="enyf-step">
                <div class="enyf-step__number">04</div>
                <h3 class="enyf-step__title">Avanza</h3>
                <p class="enyf-step__text">Completa la formacion y recibe tu certificado de participacion.</p>
            </div>
        </div>
    </div>
</section>

<section class="enyf-section enyf-cta">
    <div class="enyf-container">
        <h2 class="enyf-cta__title"><?php esc_html_e( 'Comenzá tu recorrido', 'escuela-lms' ); ?></h2>
        <p class="enyf-cta__text"><?php esc_html_e( 'Accede a formaciones estructuradas, material de estudio descargable y un camino claro de aprendizaje. Tu lugar en la escuela ya esta esperando.', 'escuela-lms' ); ?></p>
        <div class="enyf-cta__buttons">
            <a href="<?php echo esc_url( home_url( '/aula/' ) ); ?>" class="enyf-cta__btn enyf-cta__btn--white"><?php esc_html_e( 'Aula Virtual', 'escuela-lms' ); ?></a>
            <a href="https://wa.me/5492942337884?text=Hola%2C%20quiero%20consultar%20por%20las%20formaciones" class="enyf-cta__btn enyf-cta__btn--outline-white" target="_blank" rel="noopener"><?php esc_html_e( 'Consultar por WhatsApp', 'escuela-lms' ); ?></a>
        </div>
    </div>
</section>
