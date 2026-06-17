# Decisions — Escuela Neuquina de Yoga

## Decisiones técnicas

### WP-CLI (2026-05-12)
- **Problema:** WP-CLI no funcionaba por puerto MySQL incorrecto
- **Solución:** Editar `conf/php/php.ini.hbs` (mysqli.default_port=10011) + crear runtime php.ini
- **Puerto MySQL real:** 10011 (php.ini original tenía 10005)

### Idioma (2026-05-12)
- WPLANG vacío no activaba español
- Solución: `wp language core install es_ES` + `wp language core activate es_ES`

### WooCommerce (2026-05-12)
- Decisión: Eliminar completamente (no iba a usarse)
- Alcance: plugins, opciones (189), tablas (20), páginas (4), imagen placeholder
- Resultado: 0 rastros

### Kadence Starter Templates (2026-05-12)
- Decisión: Eliminar plugin y contenido demo, mantener uploads/2022/ por logo
- Alcance: plugin, 7 páginas demo, 7 posts demo, 4 menús, 1 curso demo extra
- Pendiente: eliminar uploads/2022/ cuando se reemplace el logo

### Arquitectura de páginas (2026-05-12)
- Estructura pública: Inicio, Formaciones, Tradición (con subpáginas)
- Área registrados: Profile, Registration, Reset Password (LearnDash nativo)
- Contacto: WhatsApp flotante (no página dedicada)
- Menú público: solo 3 items + submenú Tradición
- Botón "Entrar a la Escuela":指向 `/la-escuela/` ✅ (actualizado 2026-05-13)

### Paleta de colores (propuesta, no implementada)
- Primary: #6B7F59 (sage green)
- Dark text: #3D3229 (warm brown)
- Background: #FAF7F4 (off-white)
- Tipografías: mantener Gilda Display, Raleway, Cabin

### Páginas eliminadas
- 1484 (Contacto) — reemplazada por WhatsApp
- 1482 (Sobre Nosotros) — reemplazada por Tradición
- 17 (Refund Returns) — residual WooCommerce
- 7 (Registration) — huérfana LD
- 8 (Registration Success) — huérfana LD
- 1315, 1318, 1321, 1323, 1325, 1327, 1476 — demo Starter Templates
- 1406-1418 (7 posts) — demo Starter Templates
- 1330 (curso diet) — demo Starter Templates
- 27, 28, 29, 30 — menús demo

### LearnDash settings inspeccionados
- learndash_settings_payments_defaults: currency y country vacíos
- learndash_settings_emails_sender: noreply@gmail.com (placeholder)
- learndash_settings_custom_labels: todos vacíos (usar defaults LD)
- learndash_settings_theme_ld30: focus_mode=yes, colores default
- learndash_settings_registration_pages: registration=107, success=105

### Fase 2: Configuración visual (2026-05-12)
- **Método:** Customizer UI + JavaScript API (wp.customize)
- **Merge seguro:** Solo se modificaron claves específicas, conservando existentes
- **Claves modificadas:**
  - `kadence_global_palette`: palette1=#6b7f59, palette2=#8b9f72, palette7=#faf7f4
  - `theme_mods_kadence`: header_button_label, header_button_link, header_button_background
  - `site_background`, `content_background`: #faf7f4
  - `footer_html_content`: "Escuela Neuquina de Yoga — Formación integral en yoga"
- **No modificado:** LearnDash, páginas, menú, plugins, CSS
- **Validación:** Chrome MCP (desktop + mobile), WP-CLI confirmar

### Plugins propios creados (2026-05-12)

#### WhatsApp Float
- Ubicación: `wp-content/plugins/escuela-lms-whatsapp-float/`
- Funcionalidad: botón flotante bottom-right, link a wa.me
- Teléfono: +54 9 2942 33-7884
- Validación: Chrome MCP desktop/mobile

#### Student Access
- Ubicación: `wp-content/plugins/escuela-lms-student-access/`
- Funcionalidad: redirigir subscribers de wp-admin a /profile/, ocultar admin bar
- Hooks: `admin_init`, `after_setup_theme`, `wp_login`
- Validación: alumno_demo redirigido, maxteria acceso normal

### Área alumno frontend (2026-05-12)
- Decisión: alumnos (subscribers) deben usar área frontend LearnDash, NO wp-admin
- Implementación: plugin escuela-lms-student-access
- Resultado: /profile/ como hub del alumno, wp-admin bloqueado para subscribers

### Contenido de páginas públicas (2026-05-12)
- **Plugin:** escuela-lms-page-content (creado para manejar shortcodes)
- **Método:** WP-CLI `wp post update` (NO MySQL directa para contenido)
- **Shortcodes definidos:**
  - `[inicio_hero]` — título "Formación Integral En Yoga", subtítulo, CTA
  - `[inicio_features]` — 3 columns: Formaciones, Talleres, Comunidad
  - `[tradicion_hero]` — título "Una Tradición De Estudio", texto, link a instructora
  - `[tradicion_features]` — 3 columns: Práctica, Estudio, Experiencia
  - `[instructora_bio]` — título, bio, imagen (uploads/2022/03/About-2-scaled-1-342x1024.jpeg), CTA
  - `[escuela_info]` — título, descripción, WhatsApp link, ubicación (Neuquén)
- **Validación Chrome MCP:** todas las páginas renderizan correctamente
- **WhatsApp:** visible en todas las páginas

### Validación completa del flujo base (2026-05-12)
- **Herramienta:** Chrome MCP (desktop + mobile viewport 375x667)
- **Flujo público:** Inicio → Formaciones → Tradición ✓
- **Registro/Login:** /profile/ → /registration-2/ → autenticación ✓
- **Perfil:** stats (1 curso, 0 completado), lista cursos ✓
- **Curso:** LearnDash 101 accesible, progreso 0%, 42 pasos ✓
- **Lección:** video Vimeo, completar funcional, progreso 2% (1/42) ✓
- **Mobile:** home, courses, lesson todos funcionales ✓
- **Student access:** subscriber → wp-admin redirigido a /profile/ ✓
- **WhatsApp:** visible en todas las páginas ✓

### Creación "Formación en Meditación" (2026-05-13)
- **Categoría:** "Formaciones" (ID 32) — aprendida de la estructura real de LD
- **Curso:** post_type=sfwd-courses, ID 1514, publish
- **Lecciones:** post_type=sfwd-lessons, IDs 1515-1526, publish
- **Quizzes:** post_type=sfwd-quiz, IDs 1527-1528, publish
- **Asignación:** meta `course_id=1514` en cada lesson/quiz
- **Sections:** NO son post_type — se guardan en meta `course_sections` como JSON
- **Orden:** se guarda en meta `ld_course_steps` (PHP serialized array)
- **Aprendizaje:** Course Builder debe armarse manualmente en wp-admin
- **No creado:** Topics (pendiente), PDFs (pendiente), ld_course_steps (requiere UI)

### Validación "Formación en Meditación" (2026-05-13)
- **Problema resuelto:** Lecciones/quizzes estaban en draft → 404 en frontend
- **Solución:** `wp post update [IDs] --post_status=publish`
- **Slug:** se generan automáticamente al publicar (ej: 1-1-meditacion-conceptos-origenes-y-beneficios)
- **Validación Chrome MCP:** curso carga, lecciones funcionan, navegación OK
- **Impacto en otros cursos:** LearnDash 101 intacto, sin cambios

### Ajuste visual LearnDash Focus Mode (2026-05-13)
- **Problema:** LearnDash Focus Mode usa `#235af3` (azul hardcodeado) en botones y estados completados
- **Diagnóstico:** Variable `--ld-color-button-bg` hardcodeada en LearnDash CSS core (no configurable desde settings)
- **Solución:** CSS override en plugin propio `escuela-lms-page-content/assets/css/learndash-focus-overrides.css`
- **Método:** `wp_enqueue_scripts` + archivo CSS separado
- **Selectores:** `.ld-navigation__progress-mark-complete-button`, `.ld-navigation__label--completed`, `.ld-navigation__progress-completed-action`
- **Colores:** `#6B7F59` (verde primary), `#8B9F72` (verde hover)
- **Validación:** Chrome MCP confirmó eliminación total de `#235af3` en todas las lecciones verificadas

### Ajuste Course Content links hover (2026-05-13)
- **Problema:** Hover/focus en `.ld-accordion__item-title` usaba `--ld-color-brand-primary: #235af3`
- **Selector:** `.ld-accordion__item-title:is(a):hover`, `.ld-accordion__item-title:is(a):focus`
- **Origen:** `wp-content/plugins/sfwd-lms/themes/ld30/assets/css/modern.css` líneas 2114-2119
- **Solución:** Override en mismo archivo CSS de overrides
- **Validación:** Chrome MCP confirmó hover/focus ahora usa `#6B7F59`

### Hub "La Escuela" como área restringida (2026-05-13)
- **Decisión:** `/la-escuela/` reemplaza `/profile/` como punto de entrada para alumnos
- **Login usado:** LearnDash Native (LD30 templates) — ya estaba configurado
- **Shortcode `[la_escuela_hub]`:** detecta estado de login, muestra contenido diferenciado
- **Redirect guests:** plugin `escuela-lms-student-access` redirige `/profile/` → `/la-escuela/` para no-logged-in
- **Header button:** actualizado vía `theme_mods_kadence` → `header_button_link` = `/la-escuela/`
- **Flujo final:**
  - Guest → `/la-escuela/` → ve login/registro
  - Guest → `/profile/` → redirige a `/la-escuela/`
  - Logueado → `/la-escuela/` → ve bienvenida + link a `/profile/`
  - Logueado → `/profile/` → dashboard LearnDash

### Imagen duplicada en páginas de curso (2026-05-13)
- **Problema:** `_thumbnail_id` renderizado por Kadence y LearnDash simultáneamente
- **Kadence:** `.post-top-featured` — imagen completa, placement limpio (offset 160px)
- **LearnDash:** `.ld-featured-image--course` — imagen recortada, placement redundante (offset 969px, tab panel)
- **Solución:** `.single-sfwd-courses .ld-featured-image--course { display: none !important; }`
- **Plugin:** `escuela-lms-page-content/assets/css/learndash-focus-overrides.css`
- **Validado:** `/courses/formacion-en-meditacion/` y `/courses/learndash-101/` sin efectos colaterales

### Preparación demo "Formación en Meditación" (2026-05-13)
- **Enfoque Pareto:** contenido minimal funcional, no perfeccionismo
- **Lecciones key:** solo 1515, 1516, 1521 actualizadas con contenido real — resto placeholder
- **Quiz Pro requiere:** questions en `wp_learndash_pro_quiz_question` (no solo wp_posts)
- **Quiz necesita:** master entry + form fields + questions en Pro tables para renderizar
- **Progress almacenamiento:** `_sfwd-course_progress` en usermeta, no solo activity table
- **Encoding workaround:** Get-Content PowerShell → mb_convert_encoding PHP para acentos

### PDFs asociados a 12 lecciones (2026-05-13)
- **Método:** Bloque `.eny-lesson-material` en post_content de cada lección
- **Estructura:** `<div class="eny-lesson-material"><h4>📄 Material de estudio</h4><a class="eny-lesson-material__button" href="URL" target="_blank" rel="noopener">...</a></div>`
- **Estilos:** Centralizados en `learndash-focus-overrides.css`, NO inline styles
- **Update:** `$wpdb->update` directo (wp_update_post corrompía tags via kses)
- **Problema resuelto:** 9 lecciones tenían contenido plano sin HTML tags — texto bare "PDF: [nombre].pdf — Pendiente" directamente en post_content (sin `<p>` ni `<em>`)
- **Validación:** 12/12 lecciones con bloque + href + target="_blank" + rel="noopener" + correcto PDF filename
- **CSS confirmado:** background #f0ede8, border-left #6B7F59, button #6B7F59

### Homepage Landing Page (2026-05-14)
- **Decisión:** Crear shortcode `[inicio_landing]` con 7 secciones en lugar de 2 shortcodes separados
- **Razón:** landing page requiere layout cohesivo con secciones dependientes (hero → value props → cards → metodología → founder → testimonials → CTA), más fácil de mantener como una sola unidad
- **CSS:** todas las clases con prefijo `enya-` (Escuela Neuquina Argentina) para evitar conflictos
- **Hero parallax:** 3 bg layers animados con CSS keyframes, estructura lista para agregar imagen real de fondo; capas son `aria-hidden` para no afectar accessibility
- **BEM naming:** adoptado para consistencia: `.enya-card__title`, `.enya-step__number`, `.enya-testimonial__author`
- **Imágenes:** usando existentes de `uploads/2022/03/` (instructora) y `uploads/2026/05/` (formación meditación) — no se subieron assets nuevos
- **Demo content:** testimonios 100% fake, cards de cursos "Próximamente" con链接 reales a `/courses/`
- **Validación:** 7 secciones desktop + mobile (375x667), hero 643px height, todos los CTAs funcionales

### Registration Success Page mejorada (2026-05-14)
- **Problema:** LearnDash apuntaba a ID 105 con slug `/registration-success-2/` y contenido "Welcome"
- **Decisión:** Crear shortcode propio `[la_escuela_registration_success]` con diseño prolijo
- **Slug:** Cambiado a `/registro-completado/` (mantiene ID 105 — LearnDash usa post ID, no slug)
- **Redirect:** 301 de `/registration-success-2/` a `/registro-completado/` via hook `init`
- **Aprendizaje:** LearnDash almacena `learndash_settings_registration_pages` con post ID, no slug — cambiar slug de una página no rompe la configuración de LearnDash
- **Contenido:** título, texto, dos botones (primario "Entrar a La Escuela", secundario "Ver formaciones")
- **Estilo:** alineado al sitio (#faf7f4, #6B7F59, #3D3229, tipografías Gilda Display + Raleway)

### /courses/ routing — why the_content filter alone didn't work (2026-05-14)
- **Problema inicial:** `the_content` filter on `is_post_type_archive('sfwd-courses')` returned shortcode but LearnDash archive template still rendered its own grid on top
- **Causa:** LearnDash archive template (`archive-sfwd-courses.php`) renders content AFTER the_content filter — the filter replaced the content variable but LearnDash's own template rendering (via its own hooks at priority 20) still ran and replaced the grid
- **Primera solución fallida:** Setting `$wp_query->posts = []` caused 404 template to render instead of page template
- **Solución final:** Create virtual `WP_Post` in `template_redirect` at priority 1 (earliest):
  - `post_type = 'page'` (forces page template)
  - `post_content = do_shortcode('[formaciones_landing]')` (pre-processed)
  - Set `$wp_query->posts = [$virtual_post]`, `$wp_query->post_count = 1`
  - Set `$wp_query->queried_object` and `queried_object_id`
  - Override `$wp_query->is_archive = false`, `$wp_query->is_page = true`
- **Resultado:** Kadence renders page template with the shortcode output, bypassing LearnDash archive entirely
- **Aprendizaje:** WordPress template selection depends on `$wp_query->posts` — a post with `post_type='page'` triggers page template logic even on an archive URL

### Layout full-bleed para heroes públicos (2026-05-15)
- **Problema:** Kadence mantiene containers centrados en `.entry-content` (~1178px). Los heroes de los shortcodes quedaban "de costado" dentro del container, sin ocupar full width real.
- **Estructura Kadence:** `site.wp-site-blocks` → `content-area` → `content-container.site-container` (1290px max) → `site-main` (1242px) → `entry-content-wrap` (1242px, padding 32px) → `entry-content.single-content` (1178px)
- **Overflow:** `html { overflow-x: hidden }`, `.site.wp-site-blocks { overflow: clip }` — cualquier wrapper que intente breakout con `margin-left: calc(50% - 50vw)` puede ser cortado si el parent tiene `overflow: hidden/clip`
- **Solución:** Wrapper full-bleed estructural (sin números mágicos):
  ```css
  .enya-fullbleed-wrapper, .enyf-fullbleed-wrapper {
    width: 100vw;
    margin-left: calc(50% - 50vw);
    margin-right: calc(50% - 50vw);
    position: relative;
  }
  ```
- **Por qué funciona:** El hero se envuelve en un div propio con `position: relative`. El wrapper usa la técnica pura `calc(50% - 50vw)` que no depende de anchos del container. El `position: relative` es clave: hace que el wrapper seubstack en el stacking context correcto, evitando que `overflow: clip` del site lo corte.
- **Patrón aprobado:** wrapper PHP en shortcode + CSS estándar breakout
- **No usar:** valores mágicos (589px, 1178px, 1585px), calc basado en anchos específicos, offsets hardcodeados tipo `calc(50vw - 589px)`
- **Cursos LearnDash:** no usan wrappers full-bleed (usan templates propios de LearnDash, no los shortcodes del plugin)
- **Wrappers creados:** `.enya-fullbleed-wrapper` (homepage), `.enyf-fullbleed-wrapper` (formaciones)
- **Aplica a:** `/` (`inicio_landing`), `/courses/` (`formaciones_landing`)
- **Nota:** `/tradicion/` usa contenido inline en la página (no shortcode con hero). `/tradicion/instructora/` usa `[instructora_bio]` (bio, no hero). Si en el futuro se migran a shortcode con hero, usar el mismo patrón de wrapper.

### Traducciones puntuales LearnDash vía gettext (2026-05-25)
- **Problema:** LearnDash `learndash-es_ES.mo` (2021) deja strings clave sin traducir en la UI de enrollment/login (LD30): "Enroll in this %s", "Log In", "Log In to Enroll", "or", "Includes"
- **Alternativas descartadas:**
  - Modificar `.po`/`.mo` de LearnDash → rompe con cada update del plugin
  - Loco Translate → no está instalado, agregaría complejidad ahora
  - Sobrescribir templates LD30 → mantenimiento pesado, se rompe con updates
  - Plugin de traducción (Polylang/WPML) → sobreingeniería para 5 strings
- **Decisión:** Filtros `gettext` / `gettext_with_context` desde `escuela-lms-page-content.php`
- **Razón:** Mínimo impacto, 0 dependencies, 0 filesystem changes, reversible editando 1 archivo
- **Guardia:** `if ($domain !== 'learndash') return $translated;` — no afecta otros textdomains
- **Prioridad:** 20 (se ejecuta después de traducciones nativas, las sobreescribe si existen)
- **Strings traducidos:**
  - `gettext_with_context`: `Enroll in this %s` → `Inscribirse en este %s`
  - `gettext`: `Log In` → `Iniciar sesión`, `Log In to Enroll` → `Iniciar sesión para inscribirse`, `or` → `o`, `Includes` → `Incluye`
- **Futuro:** Para producción, migrar a `.po`/`.mo` custom en `wp-content/languages/plugins/` o instalar Loco Translate para gestión visual
- **Riesgo:** Si LearnDash cambia los string keys originales (hardcoded en PHP), los filtros dejan de matchear y se cae a inglés — son strings estables de LD30 templates, riesgo bajo

### Hero full-width real en home (2026-05-25)
- **Problema:** El hero de `/` no ocupaba el ancho completo del viewport. El wrapper `.enya-fullbleed-wrapper` estaba limitado a ~802px (ancho de `.entry-content.single-content`).
- **Causa:** El patrón full-bleed correcto (`width: 100vw; margin-left: calc(50% - 50vw); margin-right: calc(50% - 50vw); position: relative;`) existía en `public-pages.css`, pero ese archivo no se encolaba en la home. `learndash-focus-overrides.css` solo tenía `margin-top: 0` para el wrapper, sin breakout.
- **Decisión:** Agregar el patrón full-bleed aprobado directamente en `learndash-focus-overrides.css`, scoped a `body.page-id-1483`.
- **Razón:** Mínimo cambio (1 regla CSS), sin tocar PHP, sin tocar enqueue, sin tocar Kadence. Reutiliza patrón existente probado.
- **Patrón:**
  ```css
  body.page-id-1483 .enya-fullbleed-wrapper {
    width: 100vw;
    margin-left: calc(50% - 50vw);
    margin-right: calc(50% - 50vw);
    position: relative;
  }
  ```
- **Validado:** Desktop 1280px (hero left=0, width=1280, right=1280) y mobile 375px (hero left=0, width=375, right=375). Sin scroll horizontal. Secciones posteriores contenidas.
- **No afectado:** `/courses/`, `/aula/`, cursos LearnDash, otras páginas.

### Plugin escuela-lms-retiros (2026-06-05)
- **Decisión:** Crear CPT `retiro` como entidad independiente, NO como curso LearnDash
- **Razón:** Los retiros son eventos presenciales independientes de las formaciones. No tienen lecciones, quizzes, progreso ni certificados. No deben aparecer en /courses/ ni en el Aula Virtual.
- **CPT:** `retiro` con slug `/retiros/`, público, show_in_rest, sin archive
- **Metabox:** Fecha, tipo (profundizacion/principiantes), estado (proximo/abierto/completo/finalizado), duración, lugar, texto CTA WhatsApp
- **Imagen destacada:** Usada como flyer en las cards
- **Shortcodes:** `[retiros_listado]` (grid dinámico) y `[retiro_destacado id=X]`
- **Integración:** `the_content` filter en `/retiros/` — append del listado después del contenido estático existente
- **Método:** No se modificó `escuela-lms-page-content.php`. El nuevo plugin se engancha independientemente.
- **Validación:** WP-CLI (creación, meta, featured image) + Chrome MCP (/retiros/, /courses/, /aula/, LearnDash 101)
- **Nota WP-CLI:** Se modificó DB_HOST en wp-config.php a `localhost:10011` para que WP-CLI pueda conectar al MySQL de LocalWP (puerto no estándar 10011 vs default 3306). El sitio en navegador sigue funcionando correctamente porque el PHP del runtime ya usaba el puerto correcto via php.ini.

### Settings API — Retiros (2026-06-05)
- **Formato:** Array serializado en `wp_options` bajo key `enyr_retiros_settings` (no option individual por campo)
- **Razón:** Settings API nativa de WordPress + grupo único evita múltiples queries. Un solo `get_option()` + `wp_parse_args()` para defaults.
- **Defaults:** `archive_section` (mostrar vencidos en sección separada), CTA vencidos desactivado, hide_full_retiros desactivado
- **Sanitize:** Callback dedicado con validación específica por tipo (in_array para radio, intval/empty para checkboxes, esc_url_raw para URL)

### Estado visual vs estado real (2026-06-05)
- **Decisión:** No modificar `_enyr_retiro_estado` automáticamente
- `auto_update_status` checkbox existe en settings pero está desactivado por defecto y su funcionalidad automática no está implementada
- En su lugar, `enyr_get_retiro_display_status()` calcula el estado visual comparando fecha con `current_time('Y-m-d')`
- El resultado "finalizado_auto" aparece en admin column "Estado visual" con estilo gris, permitiendo al admin ver qué retiros aparecen como finalizados por fecha pasada

### CTA en retiros vencidos (2026-06-05)
- **Default:** Desactivado (no mostrar botón WhatsApp en retiros vencidos)
- Al desactivarse, se muestra texto "Retiro finalizado" en lugar del botón
- Si se activa, usa texto alternativo y URL alternativo configurables desde settings
- Texto default alternativo: "Consultar próximos retiros"
- URL default alternativo: `https://wa.me/5492942337884` (sin mensaje predefinido)

### Single retiro template — método (2026-06-05)
- **Decisión:** `single_template` filter (no `the_content`, no `template_include` genérico)
- **Razón:** `single_template` es el hook específico para reemplazar templates de single posts. No requiere incluir `<main>` manualmente (Kadence lo maneja via `get_header()`/`get_footer()`).
- **Archivo:** `templates/single-retiro.php` dentro del plugin `escuela-lms-retiros`
- **No se usó** `the_content` filter: hubiera requerido ocultar el título default de Kadence (`.entry-header`) vía CSS, lo cual es frágil. `single_template` da control total sobre el layout.
- **No se usó** override de Kadence `single-{post_type}.php` en el theme: viola la regla de no modificar temas.

### Single retiro — diseño (2026-06-05)
- **Hero full-width:** Imagen destacada como hero con overlay gradient oscuro + título + badges de fecha/tipo/estado
- **Sin hero image:** Hero alternativo con fondo gris `#f5f2ee`, título centrado, mismo layout de badges
- **Info box:** Fondo `#f9f7f5`, íconos SVG inline (reloj y mapa), duración + lugar
- **Content:** `.entry-content.single-content` para mantener estilos Kadence del contenido del editor
- **CTA:** Respeta settings del plugin: si retiro vencido y CTA off → "Retiro finalizado"; si vencido y CTA on → CTA alternativo; si no vencido → CTA WhatsApp normal
- **Responsive:** 3 breakpoints (desktop, ≤900px, ≤480px) consistentes con el resto del sitio
- **Base utilities self-contained:** `.enyr-container`, `.enyr-btn` definidos en `retiros-cpt.css` para que el single funcione independientemente del page-content plugin
