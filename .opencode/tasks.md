# Tasks — Escuela Neuquina de Yoga

## Todas las fases ✅ COMPLETA (2026-05-12)

---

## Fase 1: Estructura pública ✅ COMPLETA

- [x] Fix WP-CLI (puerto MySQL)
- [x] Configurar idioma español
- [x] Configurar timezone, blogname, blogdescription
- [x] Eliminar contenido dummy (IDs 1, 2)
- [x] Eliminar WooCommerce completamente
- [x] Eliminar Kadence Starter Templates y contenido demo
- [x] Inspeccionar LearnDash settings
- [x] Crear páginas: Inicio, Tradición, Instructora, La Escuela
- [x] Configurar menú Principal (Inicio, Formaciones, Tradición)
- [x] Publicar páginas
- [x] Configurar home estática (Inicio como front page)
- [x] Validar Chrome MCP

## Fase 2: Configuración visual ✅ COMPLETA (2026-05-12)

- [x] Configurar paleta sage green (#6B7F59, #8B9F72, #3D3229, #5C4F43, #FAF7F4)
- [x] Configurar site background (#FAF7F4)
- [x] Agregar botón "Entrar a la Escuela" al header (label + link /profile/)
- [x] Configurar footer ("Escuela Neuquina de Yoga — Formación integral en yoga")
- [x] Configurar WhatsApp flotante (+54 9 2942 33-7884)
- [x] Crear plugin escuela-lms-student-access (redirigir wp-admin a /profile/)
- [x] Crear usuario demo alumno_demo (subscriber) para pruebas
- [ ] Actualizar logo (reemplazar uploads/2022/01/logo-hd.png)
- [ ] Eliminar uploads/2022/ después de reemplazar logo
- [x] Validar Chrome MCP desktop y mobile
- [x] Validar WP-CLI confirmar cambios

## Fase 3: Contenido de páginas ✅ COMPLETA (2026-05-12)

- [x] Crear plugin escuela-lms-page-content con shortcodes
- [x] Activar plugin escuela-lms-page-content
- [x] Insertar shortcode Inicio: `[inicio_hero][inicio_features]`
- [x] Insertar shortcode Tradición: `[tradicion_hero][tradicion_features]`
- [x] Insertar shortcode Instructora: `[instructora_bio]`
- [x] Insertar shortcode La Escuela: `[escuela_info]`
- [x] Validar WP-CLI (post_content verificado)
- [x] Validar Chrome MCP: /, /tradicion/, /tradicion/instructora/, /tradicion/la-escuela/
- [x] Validar WhatsApp flotante en todas las páginas
- [x] Validar LearnDash courses (/courses/) sin interferencia

## Fase 4: LearnDash ✅ PARCIAL (2026-05-13)

- [x] Crear categoría "Formaciones" (ID 32)
- [x] Crear curso "Formación en Meditación" (ID 1514, publish)
- [x] Crear 12 lecciones (IDs 1515-1526)
- [x] Crear 2 quizzes (IDs 1527-1528)
- [x] Asignar course_id=1514 a todas las lecciones/quizzes
- [x] Armar Course Builder (secciones Módulo 1 y Módulo 2) — hecho en wp-admin
- [x] Publicar lecciones/quizzes para validación (resolve 404)
- [x] Validar curso funciona (Chrome MCP)
- [x] Validar lección 1515 funciona
- [x] Validar lección 1525 funciona
- [x] Validar LearnDash 101 intacto
- [x] Subir PDFs asociados a 12 lecciones (2026-05-13)
- [x] Asociar PDFs a lecciones con bloque .eny-lesson-material (2026-05-13)
- [x] Validar visualmente 5 lecciones: 1515, 1516, 1521, 1525, 1526 (2026-05-13)
- [x] Validar CSS block rendering + target=_blank + rel=noopener (2026-05-13)
- [ ] Crear topics dentro de lecciones — pendiente
- [ ] Configurar certificados
- [ ] Configurar emails LearnDash
- [ ] Eliminar curso demo LearnDash 101 (opcional, de momento se mantiene)

## Fase 5: Validación ✅ COMPLETA (2026-05-12)

- [x] Validar flujo público: Inicio → Formaciones → Tradición (Chrome MCP)
- [x] Validar / (Inicio): hero + features renderizan
- [x] Validar /tradicion/: hero + features renderizan
- [x] Validar /tradicion/instructora/: bio + imagen renderizan
- [x] Validar /tradicion/la-escuela/: info + ubicación renderizan
- [x] Validar /courses/: LearnDash curso visible
- [x] Validar flujo registro: /profile/ → /registration-2/ → login/register funciona
- [x] Validar login demo: alumno_demo autenticado correctamente
- [x] Validar profile: muestra stats (1 curso, 0 completado), lista de cursos
- [x] Validar curso: accesible, progreso 0%, 42 pasos
- [x] Validar lección: video Vimeo, botón completar funcional
- [x] Validar progreso: 2% completado (1/42), lección marcada "COMPLETE"
- [x] Validar WhatsApp flotante (todas las páginas)
- [x] Validar student access (wp-admin redirigido para subscribers)
- [x] Validar responsive mobile: home, courses, lesson todos OK

## Fase 6: Ajuste visual LearnDash Focus Mode ✅ COMPLETA (2026-05-13)

- [x] Diagnosticar remanentes azules #235af3 en Focus Mode (Chrome MCP)
- [x] Identificar selector `.ld-navigation__progress-mark-complete-button`
- [x] Identificar selector `.ld-navigation__label--completed`
- [x] Crear archivo CSS: `assets/css/learndash-focus-overrides.css`
- [x] Enqueue CSS desde plugin `escuela-lms-page-content.php`
- [x] Implementar CSS con paleta verde del sitio (#6B7F59, #8B9F72)
- [x] Validar LearnDash 101 lección completada (sin azul)
- [x] Validar Formación en Meditación lección 1515 (sin azul)
- [x] Validar Formación en Meditación lección 1525 (sin azul)
- [x] Confirmar eliminación total de #235af3

## Fase 6b: Ajuste Course Content links hover ✅ COMPLETA (2026-05-13)

- [x] Diagnosticar hover azul en links de lecciones (Chrome MCP)
- [x] Identificar selector `.ld-accordion__item-title:is(a):hover`
- [x] Agregar CSS override en `learndash-focus-overrides.css`
- [x] Validar hover/focus en /courses/formacion-en-meditacion/ (sin azul)
- [x] Validar LearnDash 101 sigue funcionando

## Fase 7b: Imagen duplicada en páginas de curso ✅ COMPLETA (2026-05-13)

- [x] Diagnosticar imagen duplicada en /courses/formacion-en-meditacion/ (Chrome MCP)
- [x] Identificar fuentes: Kadence `.post-top-featured` + LearnDash `.ld-featured-image--course`
- [x] Decidir mantener imagen Kadence, ocultar LearnDash
- [x] Agregar CSS `.single-sfwd-courses .ld-featured-image--course { display: none !important; }`
- [x] Validar /courses/formacion-en-meditacion/ — una sola imagen
- [x] Validar /courses/learndash-101/ — sin efectos colaterales

## Fase 7: Hub "La Escuela" como área restringida ✅ COMPLETA (2026-05-13)

- [x] Crear shortcode `[la_escuela_hub]` en `escuela-lms-page-content`
- [x] Implementar lógica guest (título + botón login/registro)
- [x] Implementar lógica logueado (título + botón "Ir a mi perfil")
- [x] Actualizar página ID 1492 con shortcode
- [x] Agregar redirect guest /profile/ → /la-escuela/ en `escuela-lms-student-access`
- [x] Validar guest en /la-escuela/ → ve acceso login/registro
- [x] Validar guest en /profile/ → redirige a /la-escuela/
- [x] Validar alumno_demo en /la-escuela/ → ve acceso a perfil
- [x] Validar alumno_demo en /profile/ → ve dashboard LearnDash
- [x] Cambiar header button link de /profile/ a /la-escuela/ (theme_mods_kadence)
- [x] Validar header button apunta a /la-escuela/

## Fase 8: Preparación demo "Formación en Meditación" ✅ COMPLETA (2026-05-13)

- [x] Mejorar post_excerpt y post_content del curso 1514
- [x] Actualizar lección 1515 con contenido estructurado (UTF-8)
- [x] Actualizar lección 1516 con contenido estructurado (UTF-8)
- [x] Actualizar lección 1521 con contenido estructurado (UTF-8)
- [x] Crear 10 preguntas de quiz (1563-1572) en wp_posts
- [x] Insertar 10 questions en wp_learndash_pro_quiz_question
- [x] Crear quiz_master entry con passing_percent=70
- [x] Crear quiz_form fields
- [x] Actualizar _sfwd-course_progress para usuario 2 (12 lecciones completadas)
- [x] Validar flujo completo con Chrome MCP (login → curso → lección → progreso)
- [x] Limpiar archivos PHP temporales de wp-content/

## Fase 10: Registration Success Page ✅ COMPLETA (2026-05-14)

- [x] Crear shortcode `[la_escuela_registration_success]` en `escuela-lms-page-content`
- [x] Implementar contenido: título, texto, botones (Entrar a La Escuela + Ver formaciones)
- [x] Actualizar página ID 105: título "Registro completado", slug `/registro-completado/`
- [x] Agregar redirect 301 `/registration-success-2/` → `/registro-completado/` (hook `init`)
- [x] Verificar que LearnDash mantiene ID 105 como success page (no requiere cambio)
- [x] Validar con Chrome MCP: página nueva carga, botones funcionan, redirect funciona

## Fase 11: Homepage Landing Page ✅ COMPLETA (2026-05-14)

- [x] Crear shortcode `[inicio_landing]` con 7 secciones en `escuela-lms-page-content`
- [x] Implementar CSS ~400 líneas con clases BEM-style (enya-hero, enya-value-props, enya-card, etc.)
- [x] Preparar hero con `.enya-hero__bg-layers` y 3 capas animadas para pseudo-parallax futuro
- [x] Actualizar página ID 1483: `[inicio_hero][inicio_features]` → `[inicio_landing]`
- [x] Validar desktop: 7 secciones renderizan correctamente
- [x] Validar mobile (375x667): layout adapta sin romper
- [x] Verificar CTAs funcionan: /courses/, /la-escuela/, /tradicion/instructora/
- [x] Verificar flujo existente intacto: /la-escuela/, /courses/, /registro-completado/, login

## Fase 12b: Hero full-bleed para pages públicas ✅ COMPLETA (2026-05-15)

- [x] Diagnosticar estructura DOM de /courses/ (entry-content, entry-content-wrap, container widths)
- [x] Confirmar overflow: clip en .site.wp-site-blocks y overflow-x: hidden en html
- [x] Probar wrapper full-bleed con `width: 100vw; margin-left: calc(50% - 50vw); margin-right: calc(50% - 50vw)` — funciona sin valores mágicos
- [x] Confirmar que `position: relative` en wrapper evita que overflow: clip corte el hero
- [x] Wrapper `.enyf-fullbleed-wrapper` en `[formaciones_landing]` (PHP)
- [x] Wrapper `.enya-fullbleed-wrapper` en `[inicio_landing]` (PHP)
- [x] CSS en `formaciones-landing.css` (enyf-fullbleed-wrapper) + `learndash-focus-overrides.css` (enya-fullbleed-wrapper genérico)
- [x] Limpiar CSS residual: remover 589px, 1178px, 1585px, calc(50vw-589px), offsets hardcodeados
- [x] Validar /courses/ en 375px, 768px, 1280px, 1920px (Chrome MCP): hero left=-7, width=viewport ✅
- [x] Validar / en 375px, 1600px (Chrome MCP): hero left=-7, width=viewport ✅
- [x] Validar /courses/formacion-en-meditacion/: LearnDash intacto, sin wrapper fullbleed ✅
- [x] Validar /courses/learndash-101/: LearnDash intacto, sin wrapper fullbleed ✅
- [ ] `/tradicion/` y `/tradicion/instructora/`: estas páginas NO tienen hero en shortcode (usan `[tradicion_hero]` inline en página, no shortcode). Si en el futuro se migran a shortcode con hero, usar el patrón de wrapper full-bleed.

## Fase 13: LearnDash Aula Virtual rebranding ✅ COMPLETA (2026-05-25)

- [x] Reemplazar todas las ocurrencias de "Entrar a La Escuela" con "Aula Virtual" + SVG icon
- [x] Actualizar 4 shortcodes en `escuela-lms-page-content.php` (gettext_with_context + gettext)
- [x] Actualizar `header_button_label` en `theme_mods_kadence` a "Aula Virtual"
- [x] Agregar SVG icon injection JS en `fix-header-branding.php` (header button con esc_html)
- [x] Validar /, /courses/, /la-escuela/, /registro-completado/, /aula/
- [x] Validar /courses/formacion-en-meditacion/ backlink intacto
- [x] Validar /courses/learndash-101/ sin cambios
- [x] Confirmar 0 remanentes "Entrar a la Escuela"

## Fase 14: Traducciones puntuales LearnDash vía gettext ✅ COMPLETA (2026-05-25)

- [x] Inspeccionar métodos de traducción activos en el sitio
- [x] Identificar archivos involucrados: escuela-lms-page-content.php (gettext filters), mu-plugins (ninguno), functions.php (ninguno), .po/.mo oficiales (learndash-es_ES.mo, desactualizado 2021), Loco Translate (no instalado)
- [x] Confirmar que no se tocó LearnDash core, templates LD30, ni archivos .po/.mo
- [ ] Para producción: migrar a .po/.mo custom en wp-content/languages/plugins/ o Loco Translate

## Fase 15: Hero full-width real en home ✅ COMPLETA (2026-05-25)

- [x] Diagnosticar por qué el hero de home no era full width (public-pages.css no encolado en home)
- [x] Identificar que `learndash-focus-overrides.css` solo tenía `margin-top: 0` para `.enya-fullbleed-wrapper`
- [x] Agregar patrón full-bleed aprobado scoped a `body.page-id-1483 .enya-fullbleed-wrapper` en `learndash-focus-overrides.css`
- [x] Validar desktop 1280px: hero.left=0, hero.width=1280, hero.right=1280
- [x] Validar mobile 375px: hero.left=0, hero.width=375, hero.right=375
- [x] Validar sin scroll horizontal
- [x] Validar secciones posteriores contenidas
- [x] Confirmar `/courses/`, `/aula/`, cursos LearnDash sin cambios

## Fase 12: /courses/ Formaciones Landing Page ✅ COMPLETA (2026-05-14)

- [x] Diagnosticar por qué `the_content` filter solo no reemplazaba el grid LearnDash (LearnDash archive template renderiza después)
- [x] Diagnosticar por qué `$wp_query->posts = []` causaba 404 (no había posts para WordPress)
- [x] Implementar virtual WP_Post en `template_redirect` priority 1 con `post_type='page'` + pre-processed content
- [x] Crear archivo CSS `formaciones-landing.css` (~480 líneas, clases `enyf-*`)
- [x] Enqueue condicional de CSS: `is_page('courses') || is_post_type_archive('sfwd-courses') || is_page(11)`
- [x] Verificar Page ID 11 tiene post_content = `[formaciones_landing]`
- [x] Validar con Chrome MCP: `/courses/` muestra landing page con 7 secciones
- [x] Validar con Chrome MCP: `/courses/formacion-en-meditacion/` → curso LearnDash intacto
- [x] Validar con Chrome MCP: `/courses/learndash-101/` → curso LearnDash intacto
- [x] Documentar en decisions.md: why the_content filter alone failed + solution rationale

## Fase 16: Plugin escuela-lms-retiros ✅ COMPLETA (2026-06-05)

- [x] Crear estructura wp-content/plugins/escuela-lms-retiros/
- [x] Registrar CPT `retiro` con slug /retiros/ (público, show_in_rest, sin archive)
- [x] Crear metabox con campos: fecha, tipo, estado, duración, lugar, CTA WhatsApp
- [x] Implementar save_meta con nonce + capability check + sanitize
- [x] Crear shortcode `[retiros_listado]` con query dinámico + estado vacío
- [x] Crear shortcode `[retiro_destacado id=X]`
- [x] Crear assets/css/retiros-cpt.css (cards grid, badges, responsive)
- [x] Enqueue CSS en /retiros/ y singular retiro
- [x] Integrar con /retiros/ via `the_content` filter (append listado dinámico)
- [x] Activar plugin via WP-CLI
- [x] Flush rewrite rules para slug /retiros/
- [x] Crear retiro demo ID 1655 con todos los campos de metabox
- [x] Asignar featured image como flyer
- [x] Validar /retiros/ con Chrome MCP (hero estático + card dinámica + CTA WhatsApp)
- [x] Validar /courses/ intacto
- [x] Validar /aula/ intacto
- [x] Validar LearnDash 101 intacto
- [x] Documentar en progress.md (fase 16)
- [x] Documentar en decisions.md (plugin retiros)
- [x] Documentar en tasks.md (fase 16 completa)
- [x] Verificar que el sitio en navegador siga funcionando después del cambio DB_HOST en wp-config.php (Chrome MCP confirmó /retiros/, /courses/, /aula/, LearnDash 101 OK)

### Fase 16b: Settings API + helpers ✅ COMPLETA (2026-06-05)
- [x] Agregar submenu page Retiros → Ajustes (Settings API)
- [x] Registrar setting group `enyr_retiros_settings` (array serializado)
- [x] Agregar campo: expired_behavior (radio: show_finished/hide/archive_section)
- [x] Agregar campo: expired_show_cta (checkbox)
- [x] Agregar campo: expired_cta_text (text)
- [x] Agregar campo: expired_cta_url (url)
- [x] Agregar campo: auto_update_status (checkbox, solo visual)
- [x] Agregar campo: hide_full_retiros (checkbox)
- [x] Implementar sanitize callback con validaciones por tipo
- [x] Implementar `enyr_retiros_get_settings()` con `wp_parse_args` + defaults
- [x] Implementar helpers: `enyr_is_retiro_expired`, `enyr_get_retiro_display_status`, `enyr_should_show_retiro`
- [x] Implementar `enyr_retiros_get_card_meta()` unificado
- [x] Implementar `enyr_retiros_render_card()` reutilizable
- [x] Bump version 1.0.0 → 1.1.0

### Fase 16c: Shortcode refactor + admin columns ✅ COMPLETA (2026-06-05)
- [x] Refactor [retiros_listado] para soportar archive_section/hide/show_finished
- [x] Separar retiros en upcoming/past según expired_behavior
- [x] Renderizar section "Retiros anteriores" con separador visual si archive_section
- [x] Implementar expired CTA logic (hide o alternativo)
- [x] Agregar admin columns: Fecha, Tipo, Estado, Estado visual
- [x] Columna Estado visual muestra "Finalizado automático" si fecha pasada

### Fase 16d: Test data ✅ COMPLETA (2026-06-05)
- [x] Crear retiro ID 1659 (profundizacion, meditación, fecha 2026-08-15, img 1637)
- [x] Crear retiro ID 1660 (principiantes, yoga y naturaleza, fecha 2026-09-12, img 1633)
- [x] Crear retiro ID 1661 (principiantes, bienestar, fecha 2026-10-05, img 1632)
- [x] Crear retiro ID 1662 (profundizacion, otoño, fecha 2026-04-15, vencido)
- [x] Crear retiro ID 1663 (principiantes, fin de año, fecha 2026-12-20, completo)
- [x] Validar archive_section default: 5 próximos + 1 anterior
- [x] Validar hide: pasado invisible, solo 5 futuros
- [x] Validar show_finished: 6 retiros lista única
- [x] Validar expired CTA: OFF muestra "Retiro finalizado", ON muestra alternativo
- [x] Validar hide_full_retiros: oculta ID 1663
- [x] Validar admin columns en wp-admin

### Fase 16e: Single retiro template ✅ COMPLETA (2026-06-05)
- [x] Crear `templates/single-retiro.php` dentro del plugin
- [x] Implementar `single_template` filter en plugin principal
- [x] Layout: hero full-width con featured image + overlay + título + badges
- [x] Layout: back link "← Volver a retiros"
- [x] Layout: info box con duración + lugar + íconos SVG
- [x] Layout: post content del editor (entry-content single-content)
- [x] Layout: CTA respeta settings (vencido sin CTA → "Retiro finalizado")
- [x] Layout: hero alternativo para retiros sin featured image
- [x] CSS: hero, overlay gradient, back link, info box, CTA section
- [x] CSS: self-contained .enyr-container, .enyr-btn base utilities
- [x] CSS: responsive (≤900px, ≤480px)
- [x] Bump version 1.1.0 → 1.1.1
- [x] Validar retiro activo (1659) — hero + badges + info + CTA
- [x] Validar retiro vencido (1662) — FINALIZADO, "Retiro finalizado"
- [x] Validar retiro completo futuro (1663) — COMPLETO, CTA WhatsApp
- [x] Validar mobile 375px — sin scroll horizontal
- [x] Validar /retiros/ listado intacto
- [x] Validar /courses/ intacto
- [x] Validar /aula/ intacto
