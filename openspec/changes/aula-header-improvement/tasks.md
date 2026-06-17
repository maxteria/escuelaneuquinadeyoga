# Tasks: Aula Header and Course Landing Refresh

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 420-480 |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Suggested split | PR 1 → PR 2 |
| Delivery strategy | ask-on-risk (ask-always) |
| Chain strategy | pending |

Decision needed before apply: Yes
Chained PRs recommended: Yes
Chain strategy: pending
400-line budget risk: High

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Service + redirect foundation in `escuela-lms-page-content.php` | PR 1 | Base = main; delivers helper, data service, redirect gating. |
| 2 | Dashboard template, CSS, Kadence header wiring | PR 2 | Base = PR 1 once chain strategy picked; includes template, styles, MU plugin hooks. |

## Phase 1: Foundation / Data & Access

- [x] 1.1 Implement `Escuela_Aula_Dashboard_Service::get()` in `wp-content/plugins/escuela-lms-page-content/escuela-lms-page-content.php` to gather enrollments, split active/completed, compute resume URLs, and cache meta/error states. (Est: 5h | Dep: — | Tests: `wp eval` fixtures for multi active/completed learners.)
- [x] 1.2 Add `escuela_lms_is_aula_student()` helper and guard shortcode execution for admins, instructors, and guests to preserve legacy behavior. (Est: 2h | Dep: 1.1 | Tests: load `/aula/` as admin and guest to confirm fallback.)
- [x] 1.3 Hook `template_redirect` to call the service, enforce single-active redirect with `?aula-dashboard=1` bypass, and exit after `wp_safe_redirect`. (Est: 2h | Dep: 1.1-1.2 | Tests: subscriber with 1 vs 2 courses; confirm no loops and bypass works.)

## Phase 2: Dashboard Rendering

- [x] 2.1 Create `wp-content/plugins/escuela-lms-page-content/templates/aula-dashboard.php` rendering Activas/Completadas grids, per-card CTAs, empty/error states, and accessible progress bars. (Est: 4h | Dep: 1.1 | Tests: render shortcode for multi/empty states and inspect markup.)
- [x] 2.2 Refactor `la_escuela_hub` shortcode to pass dataset into the template, compute continue links, and expose Formaciones CTA for students without courses. (Est: 3h | Dep: 2.1 | Tests: subscriber with mixed courses; verify URLs and empty-state copy.)
- [x] 2.3 Register/enqueue updated `assets/css/aula.css` and focus overrides when shortcode or focus mode loads, removing obsolete card assets. (Est: 1.5h | Dep: 2.2 | Tests: inspect enqueued styles on `/aula/` and in focus mode.)

## Phase 3: Interface Styling & Header Integration

- [x] 3.1 Redesign `assets/css/aula.css` for responsive grids, card states, progress bars, and mobile stacking using existing color tokens. (Est: 3h | Dep: 2.1 | Tests: desktop/mobile visual QA for Activas/Completadas.)
- [x] 3.2 Extend `assets/css/learndash-focus-overrides.css` to style the "Volver al aula" focus header button with accessible contrast and spacing. (Est: 1.5h | Dep: 3.1 | Tests: focus mode keyboard navigation and contrast check.)
- [x] 3.3 Update `wp-content/mu-plugins/fix-header-branding.php` so the Aula header component shows "Aula Virtual" CTA for guests/admins and a "Mi Aula" dropdown (Volver al aula, Mi perfil, Salir del aula) for students across all pages, reusing focus-mode return hooks and preserving mobile behaviour. (Est: 3h | Dep: 1.2 | Tests: header desktop/mobile logged in/out, course + public pages.)

## Phase 4: Testing & Verification

- [x] 4.1 QA `/aula/` flows for subscribers with zero, one, and multiple active courses using `?aula-dashboard=1` toggle to validate redirect, listing order, and resume links. (Est: 2h | Dep: 2.2 | Tests: manual desktop/mobile checks.)
- [x] 4.2 Validate header links and focus-mode "Volver al aula" visibility for subscriber vs admin vs guest across breakpoints, confirming logout clears session. (Est: 1.5h | Dep: 3.3 | Tests: browser devtools + keyboard navigation.)
- [x] 4.3 Simulate LearnDash data failure via temporary filter to confirm error fallback renders and caches reset without redirect loops. (Est: 1h | Dep: 1.1-2.2 | Tests: observe fallback UI and restored state after removing filter.)
