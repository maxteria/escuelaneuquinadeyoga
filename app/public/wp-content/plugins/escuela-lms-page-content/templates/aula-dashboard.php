<?php
/**
 * Aula dashboard template.
 *
 * @var WP_User $aula_user Current Aula user.
 * @var array   $aula_data Dataset from Escuela_Aula_Dashboard_Service.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! isset( $aula_user, $aula_data ) ) {
    return;
}

$active_courses    = isset( $aula_data['active'] ) && is_array( $aula_data['active'] ) ? $aula_data['active'] : array();
$completed_courses = isset( $aula_data['completed'] ) && is_array( $aula_data['completed'] ) ? $aula_data['completed'] : array();
$meta              = isset( $aula_data['meta'] ) && is_array( $aula_data['meta'] ) ? $aula_data['meta'] : array();


$error_message     = isset( $meta['error'] ) ? (string) $meta['error'] : '';
// Defensive: user properties may be null or unset; cast to string to avoid trim(NULL) warnings.
$first_name        = trim( (string) ( $aula_user->first_name ?? '' ) );

if ( '' === $first_name ) {
    $first_name = trim( (string) ( $aula_user->display_name ?? '' ) );
}

if ( '' === $first_name ) {
    $first_name = __( 'Estudiante', 'escuela-lms' );
}

$formaciones_url = home_url( '/courses/' );
$profile_url     = home_url( '/profile/' );
// Global return-to-Aula URL (used by header/footer/CTA hooks). Provided for template context.
$return_url      = home_url( '/aula/' );

$status_labels = array(
    'not_started' => __( 'Sin comenzar', 'escuela-lms' ),
    'in_progress' => __( 'En progreso', 'escuela-lms' ),
    'completed'   => __( 'Completado', 'escuela-lms' ),
);

?>

<section class="aula-dashboard" aria-live="polite">
    <header class="aula-dashboard__header">
        <div class="aula-dashboard__heading">
            <p class="aula-dashboard__eyebrow">Hola, <?php echo esc_html( $first_name ); ?></p>
            <h1 class="aula-dashboard__title">Tu aula virtual</h1>
            <?php if ( ! empty( $active_courses ) ) : ?>
                <p class="aula-dashboard__subtitle">Retomá tus formaciones activas y seguí el recorrido propuesto por La Escuela.</p>
            <?php elseif ( ! empty( $completed_courses ) ) : ?>
                <p class="aula-dashboard__subtitle">No tenés formaciones activas, pero podés volver sobre las que ya completaste o sumar nuevas propuestas.</p>
            <?php else : ?>
                <p class="aula-dashboard__subtitle">Todavía no hay cursos asignados a tu cuenta. Elegí una formación y comenzá cuando quieras.</p>
            <?php endif; ?>
        </div>
        <div class="aula-dashboard__cta-group">
            <a class="aula-dashboard__cta" href="<?php echo esc_url( $formaciones_url ); ?>">Ver formaciones</a>
            <a class="aula-dashboard__cta aula-dashboard__cta--ghost" href="<?php echo esc_url( $profile_url ); ?>">Ir a mi perfil</a>
        </div>
    </header>

    <?php if ( $error_message ) : ?>
        <div class="aula-dashboard__alert" role="alert">
            <strong>Ocurrió un problema al cargar tus formaciones.</strong>
            <span><?php echo esc_html( $error_message ); ?></span>
            <a class="aula-dashboard__alert-link" href="<?php echo esc_url( $formaciones_url ); ?>">Ver opciones disponibles</a>
        </div>
    <?php endif; ?>

    <?php if ( ! empty( $active_courses ) ) : ?>
        <section class="aula-section aula-section--active" aria-label="Formaciones activas">
            <div class="aula-section__header">
                <h2 class="aula-section__title">Formaciones activas</h2>
                <p class="aula-section__meta">
                    <?php
                    $active_count = count( $active_courses );
                    /* translators: %d: number of active courses */
                    printf( esc_html( _n( '%d formación en curso', '%d formaciones en curso', $active_count, 'escuela-lms' ) ), $active_count );
                    ?>
                </p>
            </div>
            <div class="aula-grid" role="list">
                <?php foreach ( $active_courses as $course ) :
                    $course_id     = isset( $course['course_id'] ) ? (int) $course['course_id'] : 0;
                    $title         = isset( $course['title'] ) ? $course['title'] : '';
                    $resume_url    = isset( $course['resume_url'] ) ? $course['resume_url'] : ''; 
                    $course_url    = isset( $course['course_url'] ) ? $course['course_url'] : ''; 
                    $status_key    = isset( $course['status'] ) ? $course['status'] : 'in_progress';
                    $status_label  = isset( $status_labels[ $status_key ] ) ? $status_labels[ $status_key ] : $status_labels['in_progress'];
                    $progress      = isset( $course['progress'] ) && is_array( $course['progress'] ) ? $course['progress'] : array();
                    $progress_total    = isset( $progress['total'] ) ? max( 0, (int) $progress['total'] ) : 0;
                    $progress_completed = isset( $progress['completed'] ) ? max( 0, (int) $progress['completed'] ) : 0;
                    $progress_percent   = isset( $progress['percent'] ) ? max( 0, min( 100, (int) $progress['percent'] ) ) : 0;
                    $last_activity      = isset( $course['last_activity'] ) ? (int) $course['last_activity'] : 0;
                    $last_activity_text = $last_activity ? date_i18n( get_option( 'date_format' ), $last_activity ) : __( 'Sin actividad reciente', 'escuela-lms' );
                    ?>
                    <article class="aula-card aula-card--active aula-card--status-<?php echo esc_attr( $status_key ); ?>" role="listitem">
                        <div class="aula-card__status">
                            <span class="aula-card__badge"><?php echo esc_html( $status_label ); ?></span>
                            <span class="aula-card__last-activity">Actualizado el <?php echo esc_html( $last_activity_text ); ?></span>
                        </div>
                        <h3 class="aula-card__title"><?php echo esc_html( $title ); ?></h3>
                        <div class="aula-card__progress" role="progressbar" aria-valuemin="0" aria-valuemax="<?php echo esc_attr( $progress_total ?: 100 ); ?>" aria-valuenow="<?php echo esc_attr( $progress_total ? $progress_completed : $progress_percent ); ?>">
                            <span class="screen-reader-text">
                                <?php
                                if ( $progress_total > 0 ) {
                                    /* translators: 1: completed steps, 2: total steps */
                                    printf( esc_html__( '%1$d de %2$d pasos completados', 'escuela-lms' ), $progress_completed, $progress_total );
                                } else {
                                    /* translators: %d: percentage */
                                    printf( esc_html__( '%d%% completado', 'escuela-lms' ), $progress_percent );
                                }
                                ?>
                            </span>
                            <span class="aula-card__progress-track">
                                <span class="aula-card__progress-fill" style="--progress: <?php echo esc_attr( $progress_percent ); ?>%;"></span>
                            </span>
                            <span class="aula-card__progress-label"><?php echo esc_html( $progress_percent ); ?>%</span>
                        </div>
                        <div class="aula-card__actions">
                            <?php if ( $resume_url ) : ?>
                                <a class="aula-card__btn aula-card__btn--primary" href="<?php echo esc_url( $resume_url ); ?>">Continuar</a>
                            <?php endif; ?>
                            <?php if ( $course_url ) : ?>
                                <a class="aula-card__btn aula-card__btn--ghost" href="<?php echo esc_url( $course_url ); ?>">Ver programa</a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ( ! empty( $completed_courses ) ) : ?>
        <section class="aula-section aula-section--completed" aria-label="Formaciones completadas">
            <div class="aula-section__header">
                <h2 class="aula-section__title">Formaciones completadas</h2>
                <p class="aula-section__meta">
                    <?php
                    $completed_count = count( $completed_courses );
                    /* translators: %d: number of completed courses */
                    printf( esc_html( _n( '%d formación completada', '%d formaciones completadas', $completed_count, 'escuela-lms' ) ), $completed_count );
                    ?>
                </p>
            </div>
            <div class="aula-grid aula-grid--completed" role="list">
                <?php foreach ( $completed_courses as $course ) :
                    $title      = isset( $course['title'] ) ? $course['title'] : '';
                    $course_url = isset( $course['course_url'] ) ? $course['course_url'] : '';
                    $finished   = isset( $course['last_activity'] ) ? (int) $course['last_activity'] : 0;
                    $finished_text = $finished ? date_i18n( get_option( 'date_format' ), $finished ) : __( 'Fecha no disponible', 'escuela-lms' );
                    ?>
                    <article class="aula-card aula-card--completed" role="listitem">
                        <div class="aula-card__status">
                            <span class="aula-card__badge aula-card__badge--success">Completado</span>
                            <span class="aula-card__last-activity">Finalizado el <?php echo esc_html( $finished_text ); ?></span>
                        </div>
                        <h3 class="aula-card__title"><?php echo esc_html( $title ); ?></h3>
                        <p class="aula-card__summary">Podés volver a revisar el material o acompañar a tus compañeras en la comunidad.</p>
                        <?php if ( $course_url ) : ?>
                            <a class="aula-card__btn aula-card__btn--outline" href="<?php echo esc_url( $course_url ); ?>">Ver formación</a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ( empty( $active_courses ) && empty( $completed_courses ) && ! $error_message ) : ?>
        <div class="aula-empty" role="status">
            <h2 class="aula-empty__title">Todavía no tenés formaciones asignadas</h2>
            <p class="aula-empty__text">Elegí una formación para comenzar. Vas a recibir el acceso automático al finalizar la inscripción.</p>
            <a class="aula-empty__cta" href="<?php echo esc_url( $formaciones_url ); ?>">Explorar formaciones</a>
        </div>
    <?php endif; ?>
</section>
