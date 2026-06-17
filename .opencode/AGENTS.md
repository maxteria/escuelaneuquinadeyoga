# AGENTS.md
# Escuela Neuquina de Yoga — Agent Guide

Local WordPress project using WordPress + LearnDash + WooCommerce + Kadence + Kadence Blocks.

## Reglas de oro

- Inspeccionar antes de asumir.
- Si una afirmación es verificable con una tool disponible, verificarla antes de escribirla.
- No asumir valores, nombres, IDs, slugs, paths, handles, option names, versiones ni estados. Consultar primero, concluir después.
- Si algo no fue verificado, marcarlo como hipótesis, ejemplo o pendiente de confirmar.
- Nunca modificar WordPress core.
- Nunca modificar archivos internos de plugins.
- Nunca modificar archivos internos de LearnDash, WooCommerce, Kadence o Kadence Blocks.
- Priorizar configuración nativa antes que código personalizado.
- Código de negocio personalizado → `wp-content/plugins/escuela-lms-ops/`.
- Presentación → theme settings, Kadence, blocks, CSS controlado o child/custom theme.
- Después de modificar, mostrar diff o resumen exacto del cambio. No tocar nada más.

## Skills

Skills live in `.opencode/skills/`.

Read only the relevant skill before acting:

- WordPress inspection/debug: `wp-project-triage`
- WP-CLI/admin ops: `wp-wpcli-and-ops`
- Custom plugin work: `wp-plugin-development`
- Theme/block/layout: `wp-block-themes`
- Performance: `wp-performance`
- UI/design: `frontend-design`
- Accessibility: `accessibility`
- SEO: `seo`
- Project custom plugins: `escuela-lms-custom-plugins`

## Workflow

For non-trivial tasks:

1. Inspect.
2. Report findings.
3. Propose plan.
4. Wait for approval before implementation.
5. Implement only approved changes.
6. Validate with WP-CLI and/or Chrome MCP.
7. Summarize changed files and results.

## Tool Priority

1. WP-CLI
2. Direct edits only in project-owned files
3. MySQL MCP read-only
4. Chrome MCP validation
5. wp-admin when useful