# Progress — Escuela Neuquina de Yoga

## Fase 1: Estructura pública ✅ COMPLETA (2026-05-12)

## Fase 2: Configuración visual ✅ COMPLETA (2026-05-12)

### Páginas publicadas
| ID | Título | Slug | Padre | Status | URL |
|----|--------|------|-------|--------|-----|
| 1483 | Inicio | inicio | - | publish | `/` (front page) |
| 1491 | Tradición | tradicion | - | publish | `/tradicion/` |
| 1493 | Instructora | instructora | 1491 | publish | `/tradicion/instructora/` |
| 1492 | La Escuela | la-escuela | 1491 | publish | `/tradicion/la-escuela/` |
| 11 | Courses | courses | - | publish | `/courses/` (LearnDash grid) |
| 10 | Profile | profile | - | publish | `/profile/` |
| 107 | Registration | registration-2 | - | publish | `/registration-2/` |
| 105 | Registro completado | registro-completado | - | publish | `/registro-completado/` |
| 9 | Reset Password | reset-password | - | publish | `/reset-password/` |
| 3 | Privacy Policy | privacy-policy | - | draft | (no publicada) |

### Home estática
- `show_on_front` = `page`
- `page_on_front` = `1483` (Inicio)

### Menú Principal (ID 31, location: primary)
| # | Título | URL |
|---|--------|-----|
| 1 | Inicio | `/` |
| 2 | Formaciones | `/courses/` |
| 3 | Tradición | `/tradicion/` |
|   | ↳ Instructora | `/tradicion/instructora/` |
|   | ↳ La Escuela | `/tradicion/la-escuela/` |

### Jerarquía de páginas
```
/                           → Inicio (front page)
/courses/                   → Formaciones (grid LearnDash)
/tradicion/                 → Tradición
/tradicion/instructora/     → Instructora
/tradicion/la-escuela/      → La Escuela
```

### LearnDash
- Plugin: sfwd-lms v5.0.3 (activo)
- Cursos:
  - LearnDash 101 (ID 19) — demo, se mantiene
  - Formación en Meditación (ID 1514) — draft, en construcción
- Focus Mode: habilitado
- Registration: ID 107, Success: ID 105
- Certificate Builder: v1.1.5 (activo)

### Formación en Meditación (ID 1514) ✅ VALIDADO (2026-05-13)
- Estado: publish
- Categoría: Formaciones (ID 32)
- Estructura:
  - Módulo 1: Lecciones 1515-1521 (7 lecciones) — publish
  - Módulo 2: Lecciones 1522-1526 (5 lecciones) — publish
  - Quiz Módulo 1 (ID 1527) — publish
  - Quiz Módulo 2 (ID 1528) — publish
- Todos los elementos tienen `course_id=1514`
- Slugs generados automáticamente al publicar
- Course Builder: 2 secciones (Módulo 1, Módulo 2)
- Validación Chrome MCP: curso carga, lecciones 1515/1525 funcionan
- Causa 404 anterior: lessons/quizzes en draft → ahora publicado
- Pendiente: Topics (no creados en esta pasada)
- Pendiente: PDFs (no subidos todavía)

### WooCommerce
- Eliminado completamente (plugin, opciones, tablas, páginas)
- 0 rastros restantes

### Kadence Starter Templates
- Plugin eliminado
- Contenido demo eliminado (páginas, posts, menús)
- uploads/2022/ mantenido por ahora (contiene logo)

### Configuración visual Kadence ✅ COMPLETA (2026-05-12)
- **Paleta global**: #6B7F59 (primary), #8B9F72 (secondary), #3D3229 (dark), #5C4F43 (body), #FAF7F4 (background), #FFFFFF (white)
- **Header button**: "Entrar a la Escuela" → `/la-escuela/` ✅
- **Footer**: "Escuela Neuquina de Yoga — Formación integral en yoga"
- **Site background**: #FAF7F4

### Theme mods actuales (referencia)
- Heading font: Gilda Display
- Body font: Raleway (19px)
- Nav font: Cabin (20px, weight 500)
- Logo: uploads/2022/01/logo-hd.png

### Plugins activos
| Plugin | Versión |
|--------|---------|
| kadence-blocks | 3.7.0 |
| sfwd-lms | 5.0.3 |
| learndash-certificate-builder | 1.1.5 |
| escuela-lms-whatsapp-float | 1.0.0 |
| escuela-lms-student-access | 1.0.0 |
| escuela-lms-page-content | 1.0.0 | ✅ (2026-05-12)
| escuela-lms-retiros | 1.0.0 | ✅ (2026-06-05)

### Shortcodes activos (escuela-lms-page-content)
| Página | ID | Shortcode |
|--------|----|-----------|
| Inicio | 1483 | `[inicio_landing]` | ✅ (2026-05-14) |
| Tradición | 1491 | `[tradicion_hero][tradicion_features]` |
| Instructora | 1493 | `[instructora_bio]` |
| La Escuela | 1492 | `[la_escuela_hub]` | ✅ (2026-05-13) |
| Registro completado | 105 | `[la_escuela_registration_success]` | ✅ (2026-05-14) |

### Usuario demo
- ID: 2
- user_login: alumno_demo
- role: subscriber
- Propósito: pruebas de flujo LearnDash
- Restricciones: redirigido de wp-admin a /profile/, sin admin bar

### Archivos pendientes
- uploads/2022/01/ — 262 imágenes demo (19.34 MB) + logo
- uploads/2022/03/ — 51 imágenes demo (4.09 MB)

## Fase 5: Validación ✅ COMPLETA (2026-05-12)

### Flujo público validado
- `/` (Inicio): hero + features renderizan correctamente
- `/tradicion/`: hero + features renderizan correctamente
- `/tradicion/instructora/`: bio + imagen renderizan correctamente
- `/tradicion/la-escuela/`: info + ubicación + WhatsApp link
- `/courses/`: curso demo "LearnDash 101" visible

### Flujo registro/login validado
- `/profile/`: muestra stats (1 curso, 0 completado), lista de cursos
- `/registration-2/`: formulario registro + login funcionales
- Login demo: `alumno_demo` autentificado correctamente

### Flujo alumno validado
- Profile → Curso: curso accesible con progreso 0%
- Lección: video Vimeo + botón "Marcar como completado" funcional
- Progreso: 2% completado (1/42 pasos), "LECCIÓN COMPLETE" actualizado

### Responsive mobile validado
- Home: logo, menú hamburguesa, hero, features, footer
- Courses: curso visible + CTA funcional
- Lesson: video, progreso, navegación funcionan

### Student access validado
- Subscriber redirigido de wp-admin a /profile/
- Admin bar oculta para subscribers
- Usuario demo `alumno_demo` funciona correctamente

### WhatsApp flotante
- Visible en todas las páginas públicas
- Link: https://wa.me/5492942337884

## Fase 6: Ajuste visual LearnDash Focus Mode ✅ COMPLETA (2026-05-13)

### Diagnóstico
- **Problema:** LearnDash Focus Mode usa azul default `#235af3` en:
  - Botón "Marcar como completado"
  - Estados "Lección Marked Complete"
- **Origen:** Variable CSS hardcodeada en LearnDash core (`--ld-color-button-bg: #235af3`)
- **No configurable:** desde dashboard de LearnDash ni Kadence Customizer

### Solución CSS
- **Plugin:** `escuela-lms-page-content`
- **Archivo:** `assets/css/learndash-focus-overrides.css`
- **Enqueue:** agregado en `escuela-lms-page-content.php` (hook `wp_enqueue_scripts`)
- **CSS:** reemplaza `#235af3` con paleta verde del sitio (#6B7F59, #8B9F72)

### Selectores corregidos
| Selector | Elemento |
|----------|---------|
| `.ld-navigation__progress-mark-complete-button` | Botón "Marcar como completado" |
| `.ld-navigation__label--completed` | Texto "Lección Marked Complete" |
| `.ld-navigation__progress-completed-action` | Contenedor estado completado |
| `:root --ld-color-button-bg` | Variable global LearnDash |

### Validación (Chrome MCP)
| Verificación | Resultado |
|--------------|-----------|
| LearnDash 101 - Lección completada | rgb(107, 127, 89) ✅ |
| Formación en Meditación - Lección 1515 | Sin remanentes azules ✅ |
| Formación en Meditación - Lección 1525 | Sin remanentes azules ✅ |

### Restricción cumplida
- No se tocó LearnDash core
- No se tocó Kadence core
- No se tocaron templates
- No se tocó LearnDash 101

### CSS completo aplicado
```css
/* Botón "Marcar como completado" y estados */
.ld-navigation__progress-mark-complete-button { background-color: #6B7F59 !important; }
.ld-navigation__progress-mark-complete-button:hover { background-color: #8B9F72 !important; }
.ld-status-complete, .ld-navigation__label--completed { color: #6B7F59 !important; }

/* Links de lecciones en Course Content */
.ld-accordion__item-title:is(a):hover,
.ld-accordion__item-title:is(a):focus {
  color: #6B7F59 !important;
  outline-color: #6B7F59 !important;
}
```

## Fase 7b: Imagen duplicada en páginas de curso ✅ COMPLETA (2026-05-13)

### Diagnóstico
- **Problema:** La imagen destacada del curso aparecía duplicada en `/courses/formacion-en-meditacion/`
- **Causa:** Dos renderizadores independientes de `_thumbnail_id`:
  1. **Kadence** — `.post-top-featured` → imagen completa (1672×941) arriba del contenido
  2. **LearnDash LD30** — `.ld-featured-image--course` → imagen recortada (1024×576) dentro del tab panel de contenido

### Decisión
- Mantener imagen superior de Kadence (colocación limpia, imagen completa)
- Ocultar imagen interna de LearnDash con CSS

### CSS agregado
```css
.single-sfwd-courses .ld-featured-image--course {
  display: none !important;
}
```

### Archivo modificado
- `wp-content/plugins/escuela-lms-page-content/assets/css/learndash-focus-overrides.css`

### Validación (Chrome MCP)
- `/courses/formacion-en-meditacion/`: una sola imagen visible (Kadence), duplicación eliminada
- `/courses/learndash-101/`: funcionamiento normal, sin efectos colaterales

### Restricción cumplida
- No se tocó Kadence Customizer
- No se tocó LearnDash core
- No se tocó Kadence core
- No se tocaron templates
- No se tocaron imágenes

## Fase 7: Hub "La Escuela" como área restringida ✅ COMPLETA (2026-05-13)

### Implementación
- **Shortcode `[la_escuela_hub]`** en `escuela-lms-page-content`
- **Página ID 1492** actualizada con `[la_escuela_hub]`
- **Redirect para guests** en `escuela-lms-student-access`
- **Header button link** actualizado a `/la-escuela/` vía `theme_mods_kadence`

### Clave modificada
- `theme_mods_kadence` → `header_button_link`: `/profile/` → `/la-escuela/`

### Comportamiento
| Rol | /la-escuela/ | /profile/ | Header button |
|-----|-------------|-----------|--------------|
| Guest | Ve título "Entrar a la Escuela" + botón login/registro | Redirige a /la-escuela/ | → /la-escuela/ |
| Logueado | Ve título "Ya estás dentro" + botón "Ir a mi perfil" | Dashboard LearnDash | → /la-escuela/ |

### Archivos modificados
- `escuela-lms-page-content/escuela-lms-page-content.php` — shortcode `[la_escuela_hub]`
- `wp_post ID 1492` — post_content = `[la_escuela_hub]`
- `escuela-lms-student-access/escuela-lms-student-access.php` — redirect template_redirect
- `theme_mods_kadence` → header_button_link = `/la-escuela/`

## Fase 9: Registration Success Page ✅ COMPLETA (2026-05-14)

### Problema
- LearnDash redirigía a `/registration-success-2/` después de registro
- Página ID 105 solo mostraba "Welcome" — contenido minimal en inglés

### Solución
- **Shortcode `[la_escuela_registration_success]`** en `escuela-lms-page-content`
- **Página ID 105** actualizada:
  - Título: "Registro completado"
  - Slug: `/registro-completado/` (antes `/registration-success-2/`)
  - Contenido: `[la_escuela_registration_success]`
- **Redirect 301:** `/registration-success-2/` → `/registro-completado/` (hook `init`)
- **LearnDash:** mantiene ID 105 como success page (no requiere cambio de setting)

### Shortcode contenido
- Título: "Registro completado"
- Texto: "Tu cuenta fue creada correctamente. Ya podés entrar a La Escuela y comenzar tu recorrido."
- Botón principal: "Entrar a La Escuela" → `/la-escuela/` (fondo #6B7F59)
- Botón secundario: "Ver formaciones" → `/courses/` (outline #6B7F59)
- Estilo: fondo #faf7f4, texto #3D3229, tipografías Gilda Display + Raleway

### Archivos modificados
- `escuela-lms-page-content/escuela-lms-page-content.php` — shortcode + redirect

### Validación (2026-05-14)
- `/registro-completado/` carga con contenido correcto
- Botón "Entrar a La Escuela" → `/la-escuela/`
- Botón "Ver formaciones" → `/courses/`
- `/registration-success-2/` → redirect 301 a `/registro-completado/`

## Fase 11: Homepage Landing Page ✅ COMPLETA (2026-05-14)

### Objetivo
- Transformar homepage sparse en landing page completa para demo
- Todas las secciones con contenido fake/polished pero creíble

### Shortcode `[inicio_landing]`
- 7 secciones en un solo shortcode
- Todas las secciones usan clases BEM-style (enya-hero, enya-value-props, etc.)
- CSS centralizado en `learndash-focus-overrides.css`

### Secciones implementadas
| # | Sección | Clase CSS | Descripción |
|---|---------|-----------|-------------|
| 1 | Hero | `.enya-hero` | Bg layers para parallax futuro, headline, subtítulo, 2 CTAs, scroll hint |
| 2 | Value Props | `.enya-value-props` | 4 items: clases, PDF, videos, evaluaciones |
| 3 | Featured | `.enya-featured` | 3 cards con imagen + badge "Destacado" en formación meditación |
| 4 | Methodology | `.enya-methodology` | 4 pasos: Estudiá, Practicá, Integrá, Avanzá |
| 5 | Founder | `.enya-founder` | Andrea con imagen, bio, CTA a instructora |
| 6 | Testimonials | `.enya-testimonials` | 3 testimonios con avatar, nombre, rol |
| 7 | Final CTA | `.enya-cta` | Dark background, 2 botones |

### Hero preparado para pseudo-parallax
- `.enya-hero__bg-layers` contenedor con 3 capas `.enya-hero__layer`
- Capas animadas con CSS keyframes (`enya-float-1/2/3`)
- Estructura ready: agregar imagen de fondo real a `.enya-hero__layer` via CSS
- Capas son `aria-hidden="true"` (decorativas)

### CSS implementado
- ~400 líneas de CSS en `learndash-focus-overrides.css`
- BEM naming: `.enya-section`, `.enya-btn--primary`, `.enya-card__body`, etc.
- Responsive: breakpoint 768px y 480px
- Paleta: #6B7F59, #3D3229, #5C4F43, #FAF7F4, #fff

### Validación (2026-05-14)
- Desktop: todas las 7 secciones renderizan ✅
- Mobile (375x667): todas las secciones visibles ✅
- Hero height: 643px ✅
- CTAs funcionando: /courses/, /la-escuela/ ✅
- Card links funcionando: /courses/formacion-en-meditacion/ + /courses/ ✅
- /la-escuela/ → 200 ✅
- /courses/ → 200 ✅
- /registro-completado/ → 200 ✅
- /tradicion/instructora/ → 200 ✅
- LearnDash 101 intacto ✅
- Login flow intacto ✅
- No se tocó ningún core ni template ✅

### Archivos modificados
- `escuela-lms-page-content/escuela-lms-page-content.php` — shortcode `[inicio_landing]` (reemplaza `[inicio_hero]` + `[inicio_features]`)
- `escuela-lms-page-content/assets/css/learndash-focus-overrides.css` — ~400 líneas de homepage CSS
- `wp_post ID 1483` — post_content = `[inicio_landing]`

## Fase 9: PDFs asociados a lecciones ✅ COMPLETA (2026-05-13)

### Problema
- 9 lecciones (1517-1520, 1522-1526) tenían contenido plano sin HTML — solo texto "PDF: [nombre].pdf — Pendiente de carga" sin tags
- wp_update_post stripped `<p><em>` tags al guardar via kses filters
- Solución: `$wpdb->update` directo (bypass WordPress filters)

### 12 PDFs asociados
| Lección | PDF | Link |
|---------|-----|------|
| 1515 | meditacion-modulo-1-conceptos-origenes-beneficios.pdf | /wp-content/uploads/2026/05/ |
| 1516 | meditacion-modulo-1-posturas.pdf | /wp-content/uploads/2026/05/ |
| 1517 | meditacion-modulo-1-elementos-postura.pdf | /wp-content/uploads/2026/05/ |
| 1518 | Significados-de-palabras-.pdf | /wp-content/uploads/2026/05/ |
| 1519 | meditacion-modulo-1-varna-yoga.pdf | /wp-content/uploads/2026/05/ |
| 1520 | meditacion-modulo-1-visualizaciones.pdf | /wp-content/uploads/2026/05/ |
| 1521 | meditacion-modulo-1-yoga-respiracion.pdf | /wp-content/uploads/2026/05/ |
| 1522 | meditacion-modulo-2-espacio-momento.pdf | /wp-content/uploads/2026/05/ |
| 1523 | meditacion-modulo-2-nada-yoga-sonidos.pdf | /wp-content/uploads/2026/05/ |
| 1524 | meditacion-modulo-2-pranayama.pdf | /wp-content/uploads/2026/05/ |
| 1525 | meditacion-modulo-2-tratak.pdf | /wp-content/uploads/2026/05/ |
| 1526 | meditacion-modulo-2-aromaterapia.pdf | /wp-content/uploads/2026/05/ |

### Validación (2026-05-13)
- Script DB: 12/12 lecciones con `.eny-lesson-material` + href http + correct PDF filename
- Chrome MCP (5 lecciones): 1515, 1516, 1521, 1525, 1526 — todas renderizan bloque + link correcto
- CSS computado verificado: background rgb(240,237,232), border-left 4px solid #6B7F59, button background #6B7F59
- PDF abrió en nueva pestaña (target=_blank + rel=noopener)

### Archivos modificados
- `learndash-focus-overrides.css` — estilos `.eny-lesson-material` + `.eny-lesson-material__button`
- 12 lecciones (1515-1526) — post_content actualizado con bloque HTML

### Archivos temporales limpiados
- Todos los .php de debugging en `wp-content/` eliminados después de uso

## Fase 12b: Hero full-bleed para pages públicas ✅ COMPLETA (2026-05-15)

### Diagnóstico
- Kadence mantiene `.entry-content` centrado en 1178px. Los heroes de shortcodes quedaban limitados a ese ancho.
- Intentar breakout con `margin-left: calc(50% - 50vw)` o `calc(-50vw + 50%)` no funcionaba: el hero heredaba el ancho del parent (1178px) y no podía romperlo.
- Intentar `calc(50vw - 589px)` funcionó solo con valores hardcodeados dependientes del viewport actual — frágil y no responsive real.
- Solución mágica `calc(50% - 172px - 50vw)` funcionó en pruebas pero la técnica es incorrecta conceptualmente.

### Solución estructural
- **Wrapper PHP:** `<div class="enya-fullbleed-wrapper">` / `<div class="enyf-fullbleed-wrapper">` alrededor del hero en cada shortcode
- **CSS:**
  ```css
  .enya-fullbleed-wrapper, .enyf-fullbleed-wrapper {
    width: 100vw;
    margin-left: calc(50% - 50vw);
    margin-right: calc(50% - 50vw);
    position: relative;
  }
  ```
- **`position: relative`** es clave: asegura que el wrapper no sea cortado por `overflow: clip` del `.site.wp-site-blocks` ancestro

### Wrappers implementados
| Shortcode | Wrapper | Archivo CSS |
|-----------|---------|-------------|
| `inicio_landing` | `.enya-fullbleed-wrapper` | `learndash-focus-overrides.css` |
| `formaciones_landing` | `.enyf-fullbleed-wrapper` | `formaciones-landing.css` |

### CSS limpio
- Todos los valores `589px`, `1178px`, `1585px`, offsets hardcodeados removidos del CSS
- `.enya-hero` ya no tiene `width:`, `margin-left:`, `margin-right:`, `left:` hardcodeados
- `.enyf-hero` ya no tiene margin calc magic

### Validación (Chrome MCP) — /courses/
| Viewport | hero left | hero width | window width | Resultado |
|----------|-----------|-----------|--------------|-----------|
| 375px | -7 | 375 | 375 | ✅ full-bleed |
| 768px | -7 | 768 | 768 | ✅ full-bleed |
| 1280px | -7 | 1280 | 1280 | ✅ full-bleed |
| 1920px (1600 VP) | -7 | 1920 | 1920 | ✅ full-bleed |

### Validación (Chrome MCP) — /
| Viewport | hero left | hero width | window width | Resultado |
|----------|-----------|-----------|--------------|-----------|
| 375px | -7 | 375 | 375 | ✅ full-bleed |
| 1600px | -7 | 1600 | 1600 | ✅ full-bleed |

### Cursos LearnDash intactos
- `/courses/formacion-en-meditacion/`: estructura LearnDash completa, sin wrapper fullbleed ✅
- `/courses/learndash-101/`: estructura LearnDash completa, sin wrapper fullbleed ✅
- Los cursos usan templates LearnDash propios (LD30), no renderizan los shortcodes del plugin

### Archivos modificados
- `escuela-lms-page-content/escuela-lms-page-content.php` — `[inicio_landing]`: agregó wrapper `.enya-fullbleed-wrapper`, cambió `return '...'` a `$html = '...'; return $html;`
- `escuela-lms-page-content/escuela-lms-page-content.php` — `[formaciones_landing]`: agregó wrapper `.enyf-fullbleed-wrapper`, cambió `return '...'` a `$html = '...'; return $html;`
- `formaciones-landing.css`: wrapper `.enyf-fullbleed-wrapper` + wrapper `.enya-fullbleed-wrapper` (genérico), limpió CSS residual
- `learndash-focus-overrides.css`: wrapper genérico `.enya-fullbleed-wrapper`, limpió CSS residual (margin calc, width hardcodeado)

## Fase 12: /courses/ Formaciones Landing Page ✅ COMPLETA (2026-05-14)

### Objetivo
- Reemplazar grid LearnDash en `/courses/` con landing page `[formaciones_landing]`
- Mantener acceso a cursos individuales y LearnDash 101

### Problema
- `the_content` filter solo no funcionaba — LearnDash archive template seguía renderizando su grid
- `$wp_query->posts = []` causaba 404 template

### Solución
- `template_redirect` priority 1: crear virtual `WP_Post` con `post_type='page'` y `post_content` pre-procesado
- Esto fuerza Kadence a usar page template en vez de LearnDash archive
- Aprendido: WordPress template selection depende de `$wp_query->posts` — post_type='page' fuerza page template

### Shortcode `[formaciones_landing]`
- 7 secciones: Hero, Intro, Proposal Types, Catalog (7 cards), Featured Formation, Methodology, Final CTA
- Clases BEM con prefijo `enyf-` (formaciones)
- CSS en `formaciones-landing.css` (~480 líneas)
- Enqueue condicional: `is_page('courses') || is_post_type_archive('sfwd-courses') || is_page(11)`

### Cards del catálogo
| # | Título | Tipo | Estado | URL |
|---|--------|------|--------|-----|
| 1 | Formación en Meditación | Formación | Disponible ✅ | /courses/formacion-en-meditacion/ |
| 2 | Formación Integral en Yoga | Formación | Próximamente |
| 3 | Curso de Pranayama | Curso | Próximamente |
| 4 | Taller de Introducción a la Meditación | Taller | Próximamente |
| 5 | Curso de Filosofía del Yoga | Curso | Próximamente |
| 6 | Taller de Sonido, Mantra y Nada Yoga | Taller | Próximamente |
| 7 | Curso de Anatomía Aplicada al Yoga | Curso | Próximamente |

### Validación (2026-05-14)
- `/courses/` → landing page con 7 secciones ✅
- `/courses/formacion-en-meditacion/` → curso LearnDash intacto ✅
- `/courses/learndash-101/` → curso LearnDash intacto ✅
- CSS `formaciones-landing.css` enqueueado en /courses/ ✅
- LearnDash 101 intacto ✅

### Restricción cumplida
- No se tocó LearnDash core
- No se tocó Kadence core
- No se tocó LearnDash 101
- No se tocó Course Builder
- No se tocaron quizzes
- No se tocó login flow

### Archivos modificados
- `escuela-lms-page-content/escuela-lms-page-content.php` — `[inicio_landing]`: wrapper `.enya-fullbleed-wrapper`, cambió `return '...'` a `$html = '...'; return $html;`
- `escuela-lms-page-content/escuela-lms-page-content.php` — `[formaciones_landing]`: wrapper `.enyf-fullbleed-wrapper`, cambió `return '...'` a `$html = '...'; return $html;`
- `formaciones-landing.css`: wrapper `.enyf-fullbleed-wrapper` + `.enya-fullbleed-wrapper`, limpió CSS residual (margin calc, 589px, 1178px, offsets hardcodeados)
- `learndash-focus-overrides.css`: wrapper genérico `.enya-fullbleed-wrapper`, limpió CSS residual (margin calc, width hardcodeado, left hardcodeado)

## Fase 8: Preparación demo "Formación en Meditación" ✅ COMPLETA (2026-05-13)

### A. Curso ID 1514
- **post_excerpt** (descripción corta): "Un recorrido de dos módulos por los fundamentos y las técnicas de la meditación yóguica."
- **post_content** (descripción completa): actualizada con estructura de Módulo 1 y Módulo 2
- Imagen destacada (ID 1547): sin cambios ✅

### B. Lecciones clave actualizadas
| ID | Título | Contenido | Material |
|----|--------|-----------|----------|
| 1515 | 1.1. Meditación: conceptos, orígenes y beneficios | ~500 palabras, objetivos, beneficios, relación yoga | PDF pendiente + video placeholder |
| 1516 | 1.2. Posturas de meditación | ~600 palabras, 5 posturas clásicas, principios | PDF pendiente + video placeholder |
| 1521 | 1.7. Yoga de la respiración / Shvasan Yoga | ~600 palabras, Ujjayi, Nadi Shodhana, Kapalabhati | PDF pendiente + video placeholder |

- Todas con estructura: título, objetivos, desarrollo, material de estudio, práctica sugerida
- Encoding UTF-8 corregido (Get-Content workaround)

### C. Quiz Módulo 1 (ID 1527) — Configurado
- **10 preguntas** creadas (IDs 1563-1572) en `sfwd-question`
- **wp_learndash_pro_quiz_question**: 10 entries con respuesta correcta
- **wp_learndash_pro_quiz_form**: 5 form fields (quiz_title, quiz_description, quiz_options, retry, time)
- **wp_learndash_pro_quiz_master**: entry creado con passing_percent=70
- **Estructura**: 6 opción múltiple, 4 verdadero/falso
- **Temas**: dhyana, Patañjali, asana, sukhasana, pranayama, ida/pingala, kapalabhati, nadi shodhana, padmasana, samadhi
- Passing score: 70%

### D. Validación flujo demo
- Login alumno_demo → exitoso
- Curso "Formación en Meditación" visible → 92% (12/13 pasos completados)
- Lección 1.1 renderiza contenido completo en español → correcto
- Lección marcada como completada → botón funcional
- Sidebar muestra 12 lecciones completadas (menos 2 quizzes)
- Progreso almacenado en `_sfwd-course_progress` (usuario 2, curso 1514)
- Quiz Módulo 1 accesible (lecciones previas completadas) — descripción visible

### Archivos temporales limpiados
- Todos los .php de debugging en `wp-content/` eliminados después de uso

## Fase 14: Traducciones puntuales LearnDash vía gettext ✅ COMPLETA (2026-05-25)

### Objetivo
Traducir 5 strings específicos de la UI de LearnDash (frontend course enrollment/login) que el `.po` oficial de LearnDash (`learndash-es_ES.mo`, 2021) deja sin traducir o con traducción incorrecta.

### Método
Filtros **gettext de WordPress** desde `escuela-lms-page-content.php`. Sin tocar LearnDash core, templates, ni archivos `.po`/`.mo`.

### Strings traducidos

| Original | Traducción | Hook |
|----------|-----------|------|
| `Enroll in this %s` | `Inscribirse en este %s` | `gettext_with_context` (línea 594) |
| `Log In` | `Iniciar sesión` | `gettext` (línea 605) |
| `Log In to Enroll` | `Iniciar sesión para inscribirse` | `gettext` (línea 605) |
| `or` | `o` | `gettext` (línea 605) |
| `Includes` | `Incluye` | `gettext` (línea 605) |

### Hooks exactos

```php
add_filter('gettext_with_context', function($translated, $text, $context, $domain) {
    if ($domain !== 'learndash') return $translated;
    $map = [
        'Enroll in this %s' => 'Inscribirse en este %s',
    ];
    if (isset($map[$text])) return $map[$text];
    return $translated;
}, 20, 4);

add_filter('gettext', function($translated, $text, $domain) {
    if ($domain !== 'learndash') return $translated;
    $map = [
        'Log In'           => "Iniciar sesi\u{f3}n",
        'Log In to Enroll' => "Iniciar sesi\u{f3}n para inscribirse",
        'or'               => 'o',
        'Includes'         => 'Incluye',
    ];
    if (isset($map[$text])) return $map[$text];
    return $translated;
}, 20, 3);
```

### Scope
- Domain: solo `learndash` (guardia `if ($domain !== 'learndash')`)
- Prioridad: 20 (después de traducciones nativas)
- Reversible: sí — eliminar los dos `add_filter`

### No se tocó
- LearnDash core
- LearnDash templates (LD30)
- Archivos `.po`/`.mo`
- Kadence core
- Ningún otro plugin

### Archivo modificado
- `escuela-lms-page-content/escuela-lms-page-content.php` — líneas 594–617

### Próximos pasos (recomendación)
- Para producción: migrar a `.po`/`.mo` custom en `wp-content/languages/plugins/` o usar Loco Translate para gestión visual

## Fase 15: Hero full-width real en home ✅ COMPLETA (2026-05-25)

### Problema
El hero de `/` visualmente estaba encerrado en un ancho máximo (~802px). La imagen de fondo no ocupaba todo el ancho del viewport.

### Causa raíz
El patrón full-bleed correcto existía en `public-pages.css` (`.enya-fullbleed-wrapper { width: 100vw; margin-left: calc(50% - 50vw); ... }`), pero ese archivo **no se encolaba en la home**. La home solo cargaba `learndash-focus-overrides.css`, donde `.enya-fullbleed-wrapper` tenía únicamente `margin-top: 0`, sin las propiedades de breakout.

### Solución
Se agregó el patrón full-bleed aprobado en `learndash-focus-overrides.css`, scoped a `body.page-id-1483`:

```css
body.page-id-1483 .enya-fullbleed-wrapper {
  width: 100vw;
  margin-left: calc(50% - 50vw);
  margin-right: calc(50% - 50vw);
  position: relative;
}
```

Sin valores mágicos, sin hacks. Patrón reutilizado de la solución existente en `public-pages.css`.

### No se tocó
- PHP del shortcode
- Kadence core
- LearnDash
- `/courses/`
- Otras páginas

### Validación Chrome MCP

| Medición | Desktop 1280×800 | Mobile 375×667 |
|----------|-----------------|----------------|
| hero.left | 0 ✅ | 0 ✅ |
| hero.width | 1280 ✅ | 375 ✅ |
| hero.right | 1280 ✅ | 375 ✅ |
| gap header→hero | 0 ✅ | 0 ✅ |
| hero_min_height | 720px ✅ | 607px ✅ |
| scroll horizontal | no ✅ | no ✅ |
| secciones posteriores | contenidas ✅ | contenidas ✅ |

### Archivo modificado
- `escuela-lms-page-content/assets/css/learndash-focus-overrides.css` — regla `body.page-id-1483 .enya-fullbleed-wrapper`

## Fase 16: Plugin escuela-lms-retiros ✅ COMPLETA (2026-06-05)

### Objetivo
- Módulo propio para gestionar retiros como entidad independiente del sitio
- Retiros NO son cursos LearnDash
- Retiros NO impactan /courses/, /aula/, ni Course Builder

### Archivos creados
| Archivo | Descripción |
|---------|-------------|
| `wp-content/plugins/escuela-lms-retiros/escuela-lms-retiros.php` | Bootstrap + CPT + metabox + shortcodes + integración |
| `wp-content/plugins/escuela-lms-retiros/assets/css/retiros-cpt.css` | Cards grid, estado badges, responsive |

### CPT registrado
- **post_type:** `retiro`
- **label:** Retiros
- **slug público:** `/retiros/` (rewrite, sin archive)
- **supports:** title, editor, excerpt, thumbnail, revisions
- **show_in_rest:** true
- **menu_icon:** dashicons-palmtree
- **menu_position:** 25

### Campos metabox
| Campo | Meta key | Tipo |
|-------|----------|------|
| Fecha del retiro | `_enyr_retiro_fecha` | date |
| Tipo de retiro | `_enyr_retiro_tipo` | select: profundizacion / principiantes |
| Estado | `_enyr_retiro_estado` | select: proximo / abierto / completo / finalizado |
| Duración | `_enyr_retiro_duracion` | text |
| Lugar | `_enyr_retiro_lugar` | text |
| Texto CTA WhatsApp | `_enyr_retiro_cta_text` | text |

### Shortcodes
- **`[retiros_listado]`** — Cards dinámicas ordenadas por fecha ascendente. Acepta `limit` y `tipo`. Sin retiros → mensaje "Próximamente..." + botón WhatsApp.
- **`[retiro_destacado id=X]`** — Card destacada para un retiro específico.

### Integración con /retiros/
- Página /retiros/ (ID 1647) mantiene contenido existente (hero + intro de `[retiros_landing]`)
- Plugin se engancha via `the_content` filter para **append** el listado dinámico
- CSS se enqueuea en `is_page('retiros')` e `is_singular('retiro')`
- Sin modificar `escuela-lms-page-content.php` ni ningún otro archivo existente

### Demo validado
- Retiro demo ID 1655 creado y publicado
- Metabox guarda: fecha=2026-07-15, tipo=profundizacion, estado=abierto, duracion="3 días, 2 noches", lugar="Cerro de los Siete Colores"
- Featured image ID 1640 asignada como flyer
- Card pública muestra: fecha formateada, tipo, estado (Abierto), título, excerpt, duración, lugar, CTA WhatsApp personalizado
- /retiros/ carga correctamente (hero estático + card dinámica)

### Settings API — Retiros → Ajustes (v1.1.0)
| Campo | Key | Default |
|-------|-----|---------|
| Comportamiento vencidos | `expired_behavior` | `archive_section` |
| CTA en vencidos | `expired_show_cta` | `false` (0) |
| Texto CTA alternativo | `expired_cta_text` | "Consultar próximos retiros" |
| URL CTA alternativo | `expired_cta_url` | `https://wa.me/5492942337884` |
| Actualización automática | `auto_update_status` | `false` (0) |
| Ocultar completos | `hide_full_retiros` | `false` (0) |

- Settings API, submenu under `edit.php?post_type=retiro`, array serializado en `wp_options` bajo `enyr_retiros_settings`
- Defaults vía `wp_parse_args`

### Helpers (v1.1.0)
| Función | Descripción |
|---------|-------------|
| `enyr_is_retiro_expired($post_id)` | Compara fecha con `current_time('Y-m-d')` |
| `enyr_get_retiro_display_status($post_id)` | "finalizado_auto" si vencido, estado manual si no |
| `enyr_should_show_retiro($post_id, $context)` | Aplica expired_behavior + hide_full_retiros |
| `enyr_retiros_get_card_meta($post_id)` | Unifica toda la metadata + CTA logic |
| `enyr_retiros_render_card($meta)` | Template reutilizable de card |

### Shortcode [retiros_listado] refactorizado (v1.1.0)
| Behavior | Resultado |
|----------|-----------|
| `archive_section` (default) | Dos secciones: "Próximos retiros" + "Retiros anteriores" (past descendente, separador visual) |
| `hide` | Filtra vencidos del listado |
| `show_finished` | Lista única con vencidos como "Finalizado" |
| CTA en vencidos OFF | Muestra "Retiro finalizado" sin botón |
| CTA en vencidos ON | Muestra CTA alternativo (texto/URL configurables) |

### Admin columns (v1.1.0)
Columnas agregadas al listado CPT: Fecha, Tipo, Estado, Estado visual ("Finalizado automático" si fecha pasada).

### Test data (v1.1.0)
| ID | Título | Fecha | Estado | Featured image |
|----|--------|-------|--------|----------------|
| 1655 | Retiro de profundización en la montaña | 2026-07-15 | abierto | 1640 |
| 1659 | Retiro de Profundización en Meditación | 2026-08-15 | abierto | 1637 |
| 1660 | Retiro Inicial de Yoga y Naturaleza | 2026-09-12 | próximo | 1633 |
| 1661 | Experiencia de Bienestar y Pausa | 2026-10-05 | abierto | 1632 |
| 1662 | Retiro de Otoño 2026 | 2026-04-15 | abierto | 1635 (vencido) |
| 1663 | Retiro de Fin de Año | 2026-12-20 | completo | 1636 |

### Single retiro template (v1.1.1)
- **Método:** `single_template` filter → `templates/single-retiro.php` dentro del plugin
- **No tocó:** Kadence core, WordPress core, templates del theme
- **Layout:**
  - Hero full-width con featured image + gradient overlay + título + badges
  - Back link "← Volver a retiros"
  - Info box (duración + lugar) con íconos SVG, fondo gris `#f9f7f5`
  - Post content (max 720px centrado)
  - CTA WhatsApp o texto "Retiro finalizado" según settings
- **Comportamiento por estado:**
  | Estado | Hero | Badge | CTA |
  |--------|------|-------|-----|
  | Activo (abierto/próximo) | Imagen + título | Abierto/Próximo | WhatsApp normal |
  | Vencido (finalizado_auto) | Imagen + título | Finalizado | "Retiro finalizado" (o CTA alternativo si configurado) |
  | Completo | Imagen + título | Completo | WhatsApp normal |
  | Sin featured image | Hero alternativo fondo gris | Normal | Normal |
- **CSS:** agregado en `retiros-cpt.css` (hero, overlay, back link, info box, CTA, responsive)
- **Base utilities self-contained:** `.enyr-container`, `.enyr-btn`, `.enyr-btn--primary`, `.enyr-btn--large` definidos en `retiros-cpt.css` para no depender de page-content plugin
- **Validación Chrome MCP:**
  | Escenario | Resultado |
  |-----------|-----------|
  | Retiro activo (1659) | Hero + badges + info + CTA ✅ |
  | Retiro vencido (1662) | Badge FINALIZADO, sin CTA, "Retiro finalizado" ✅ |
  | Retiro completo futuro (1663) | Badge COMPLETO, CTA WhatsApp ✅ |
  | Mobile 375px | Sin scroll horizontal, hero 40vh, info column ✅ |
  | /retiros/ listado | Intacto (5 próximos + 1 anterior) ✅ |
  | /courses/ | Intacto ✅ |
  | /aula/ | Intacto ✅ |

### No se tocó
- LearnDash core
- Kadence core
- WordPress core
- Course Builder
- Quizzes
- PDFs
- /courses/
- /aula/
- /profile/
- LearnDash 101
