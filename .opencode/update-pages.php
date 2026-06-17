<?php
require_once __DIR__ . '/app/public/wp-load.php';

// Inicio (1483) - Simple HTML
$inicio = '
<div style="background:#faf7f4;padding:80px 20px;text-align:center;">
    <h1 style="font-family:Gilda Display;font-size:48px;color:#3D3229;margin-bottom:20px;">Formación integral en yoga</h1>
    <p style="font-family:Raleway;font-size:20px;color:#5C4F43;max-width:700px;margin:0 auto 40px;line-height:1.8;">El yoga es una práctica ancestral que invita al estudio profundo del ser. En la Escuela Neuquina de Yoga acompañamos a quienes buscan una formación seria, presente y transformadora.</p>
    <a href="/courses/" style="background:#6B7F59;color:#fff;padding:18px 40px;text-decoration:none;border-radius:4px;font-family:Cabin;font-weight:500;">Ver formaciones</a>
</div>

<div style="background:#fff;padding:80px 20px;">
    <div style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(3,1fr);gap:40px;">
        <div>
            <h3 style="font-family:Gilda Display;font-size:24px;color:#3D3229;">Formaciones</h3>
            <p style="font-family:Raleway;font-size:16px;color:#5C4F43;line-height:1.6;">Programas estructurados de formación en yoga. Cursos progresivos que acompañan tu desarrollo como practitioner e instructor.</p>
        </div>
        <div>
            <h3 style="font-family:Gilda Display;font-size:24px;color:#3D3229;">Talleres</h3>
            <p style="font-family:Raleway;font-size:16px;color:#5C4F43;line-height:1.6;">Profundización en áreas específicas. Sesiones monográficas sobre pranayama, meditación, anatomía y filosofía.</p>
        </div>
        <div>
            <h3 style="font-family:Gilda Display;font-size:24px;color:#3D3229;">Comunidad</h3>
            <p style="font-family:Raleway;font-size:16px;color:#5C4F43;line-height:1.6;">Un espacio de práctica compartida. Acompañamos a cada estudiante en su camino de práctica con atención personalizada.</p>
        </div>
    </div>
</div>
';

wp_update_post(array(
    'ID' => 1483,
    'post_content' => $inicio
));
echo "Updated Inicio (1483)\n";

// Tradición (1491)
$tradicion = '
<div style="background:#faf7f4;padding:80px 20px;text-align:center;">
    <h1 style="font-family:Gilda Display;font-size:48px;color:#3D3229;margin-bottom:20px;">Una tradición de estudio</h1>
    <p style="font-family:Raleway;font-size:20px;color:#5C4F43;max-width:700px;margin:0 auto 40px;line-height:1.8;">La Escuela Neuquina de Yoga nace de la convicción de que el yoga es una práctica de estudio, no solo de postura. Enseñar yoga es acompañar a cada persona en su propio camino de práctica y descubrimiento.</p>
    <a href="/tradicion/instructora/" style="background:#6B7F59;color:#fff;padding:18px 40px;text-decoration:none;border-radius:4px;font-family:Cabin;font-weight:500;">Conocer a la instructora</a>
</div>

<div style="background:#fff;padding:80px 20px;">
    <div style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(3,1fr);gap:40px;">
        <div>
            <h3 style="font-family:Gilda Display;font-size:24px;color:#3D3229;">Práctica</h3>
            <p style="font-family:Raleway;font-size:16px;color:#5C4F43;line-height:1.6;">El cuerpo como puerta de entrada. A través de las asanas exploramos la estructura corporal, el movimiento y la conciencia corporal.</p>
        </div>
        <div>
            <h3 style="font-family:Gilda Display;font-size:24px;color:#3D3229;">Estudio</h3>
            <p style="font-family:Raleway;font-size:16px;color:#5C4F43;line-height:1.6;">La teoría sustenta la práctica. Filosofía, anatomía, textos tradicionales y ciencia del yoga para comprender qué hacemos y por qué.</p>
        </div>
        <div>
            <h3 style="font-family:Gilda Display;font-size:24px;color:#3D3229;">Experiencia</h3>
            <p style="font-family:Raleway;font-size:16px;color:#5C4F43;line-height:1.6;">La transformación viene con la práctica sostenida. Acompañamos a cada estudiante en su proceso personal, con atención y respeto por su ritmo.</p>
        </div>
    </div>
</div>
';

wp_update_post(array(
    'ID' => 1491,
    'post_content' => $tradicion
));
echo "Updated Tradición (1491)\n";

// Instructora (1493)
$instructora = '
<div style="background:#faf7f4;padding:80px 20px;text-align:center;">
    <div style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:300px 1fr;gap:60px;align-items:center;text-align:left;">
        <div>
            <img src="https://escuelaneuquinadeyoga.local/wp-content/uploads/2022/03/About-2-scaled-1-342x1024.jpeg" alt="Instructora" style="width:100%;max-width:300px;border-radius:8px;">
        </div>
        <div>
            <h1 style="font-family:Gilda Display;font-size:42px;color:#3D3229;margin-bottom:10px;">La instructora</h1>
            <p style="font-family:Raleway;font-size:18px;color:#5C4F43;line-height:1.8;margin-bottom:20px;">Formada en la tradición del yoga clásico, acompaña a estudiantes desde hace más de 15 años en su camino de práctica y estudio. Su enfoque combina la tradición yogan con una comprensión profunda del cuerpo y la respiración.</p>
            <p style="font-family:Raleway;font-size:18px;color:#5C4F43;line-height:1.8;margin-bottom:30px;"> Cree que enseñar yoga es crear un espacio seguro donde cada persona pueda explorar su propia práctica, con paciencia, atención y respeto por el proceso individual.</p>
            <a href="/courses/" style="background:#6B7F59;color:#fff;padding:18px 40px;text-decoration:none;border-radius:4px;font-family:Cabin;font-weight:500;">Ver formaciones</a>
        </div>
    </div>
</div>
';

wp_update_post(array(
    'ID' => 1493,
    'post_content' => $instructora
));
echo "Updated Instructora (1493)\n";

// La Escuela (1492)
$escuela = '
<div style="background:#faf7f4;padding:80px 20px;text-align:center;">
    <h1 style="font-family:Gilda Display;font-size:48px;color:#3D3229;margin-bottom:20px;">La Escuela</h1>
    <p style="font-family:Raleway;font-size:20px;color:#5C4F43;max-width:700px;margin:0 auto 40px;line-height:1.8;">Fundada en Neuquén, la Escuela Neuquina de Yoga ofrece formación presencial en un ambiente de estudio serio y acompañamos a cada estudiante en su camino de práctica.</p>
    <p style="font-family:Raleway;font-size:18px;color:#5C4F43;max-width:700px;margin:0 auto 40px;line-height:1.8;">Creemos en una formación que integra cuerpo, respiración y mente. No es solo aprender posturas, es comprender la tradición, desarrollar la conciencia y transformar la vida a través de la práctica sostenida.</p>
    <a href="https://wa.me/5492942337884?text=Hola%2C%20quiero%20consultar%20por%20la%20Escuela%20Neuquina%20de%20Yoga." style="background:#6B7F59;color:#fff;padding:18px 40px;text-decoration:none;border-radius:4px;font-family:Cabin;font-weight:500;">Consultar</a>
</div>

<div style="background:#fff;padding:80px 20px;text-align:center;">
    <p style="font-family:Raleway;font-size:18px;color:#5C4F43;">📍 Neuquén, Argentina</p>
    <p style="font-family:Raleway;font-size:16px;color:#8B9F72;margin-top:10px;">Formación seria. Práctica transformadora.</p>
</div>
';

wp_update_post(array(
    'ID' => 1492,
    'post_content' => $escuela
));
echo "Updated La Escuela (1492)\n";

echo "\n✅ All pages updated!";