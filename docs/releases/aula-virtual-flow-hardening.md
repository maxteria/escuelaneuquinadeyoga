# Aula Virtual Flow Hardening — Release Notes

## Changes

### Behavior change
- **Hub-first**: eliminado el redirect automático al único curso activo. El alumno siempre aterriza en `/aula/` y elige su curso.
- **CTA persistente "Volver al aula"**: inyectado en cursos (hook `the_content`) y Focus Mode (`learndash-focus-header-nav-after`), apuntando a `/aula/`.

### New modules (escuela-lms-student-access)
- `includes/redirects.php` — handler centralizado con feature flag, allowlist y logging (WP_DEBUG only).
- `includes/cta.php` — inyección del CTA "Volver al aula" en páginas de curso.
- `assets/css/cta.css` — estilos del CTA.

### Removed
- Bloque de redirect automático en `escuela-lms-page-content.php` (template\_redirect handler).

### Testing
- `bin/wp-cli/smoke/aula_smoke.php` — 7 tests WP-CLI (guest, registro, login, hub, subscriber redirect, logout).
- `docs/qa/chrome-mcp/aula_flow_checklist.md` — 9 tests Chrome MCP (guest, registro, alumno\_demo, Focus Mode, mobile, logout).

### PR template
- `.github/PULL_REQUEST_TEMPLATE.md` — checklist pre-merge con `php -l`, smoke tests, Chrome QA.

## Feature Flags

| Flag | Default | Description |
|------|---------|-------------|
| `escuela_lms_enable_single_course_redirect` | `false` | Habilita el redirect handler legacy (desactivado por defecto). |

## Rollback Instructions

1. **CTA**: comentar `require_once __DIR__ . '/includes/cta.php'` en `escuela-lms-student-access.php`.
2. **Redirect handler**: comentar `require_once __DIR__ . '/includes/redirects.php'` en `escuela-lms-student-access.php`.
3. **Logging**: no requiere rollback; está detrás de `WP_DEBUG`.
4. **Redirect original**: restaurar bloque `template_redirect` de `escuela-lms-page-content.php` desde git: `git checkout HEAD~1 -- app/public/wp-content/plugins/escuela-lms-page-content/escuela-lms-page-content.php`.
5. **Descartar PR template**: `git rm .github/PULL_REQUEST_TEMPLATE.md` (si no se usa en otros cambios).

Si se revierte todo: `git revert <merge-commit>` y verificar que `/aula/` funcione y que no haya errores PHP.
