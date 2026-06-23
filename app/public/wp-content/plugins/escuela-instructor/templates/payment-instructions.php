<?php
defined( 'ABSPATH' ) || exit;

// $course_id must be provided
if ( empty( $course_id ) ) {
    return;
}

$course = get_post( $course_id );
if ( ! $course ) {
    echo '<p>Curso no encontrado.</p>';
    return;
}

$title = get_the_title( $course_id );

$cbu    = get_post_meta( $course_id, 'cbu', true );
$alias  = get_post_meta( $course_id, 'alias', true );
$banco  = get_post_meta( $course_id, 'banco', true );
$titular = get_post_meta( $course_id, 'titular', true );

echo '<div class="escuela-payment-instructions">';
echo '<h2>Instrucciones de pago — ' . esc_html( $title ) . '</h2>';

if ( $cbu || $alias || $banco || $titular ) {
    echo '<ul>';
    if ( $cbu ) {
        echo '<li><strong>CBU:</strong> ' . esc_html( $cbu ) . '</li>';
    }
    if ( $alias ) {
        echo '<li><strong>Alias:</strong> ' . esc_html( $alias ) . '</li>';
    }
    if ( $banco ) {
        echo '<li><strong>Banco:</strong> ' . esc_html( $banco ) . '</li>';
    }
    if ( $titular ) {
        echo '<li><strong>Titular:</strong> ' . esc_html( $titular ) . '</li>';
    }
    echo '</ul>';
} else {
    echo '<p>No hay datos de pago configurados para este curso. Por favor contactá al instructor para recibir las instrucciones de pago.</p>';
}

// WhatsApp link with prefilled text (opens WhatsApp chat creator)
$text = rawurlencode( sprintf( "Hola, quiero confirmar mi pago para el curso '%s'", $title ) );
$wa_link = 'https://wa.me/?text=' . $text;

echo '<p><a class="button" href="' . esc_url( $wa_link ) . '" target="_blank" rel="noopener">Contactar por WhatsApp</a></p>';

echo '</div>';
