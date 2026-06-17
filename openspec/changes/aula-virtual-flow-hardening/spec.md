# Delta: Aula Virtual Flow Hardening

Purpose: Harden the Aula student flow (guest → registration → login → /aula/ → course) by removing the single-course auto-redirect, fixing PHP parse/warnings in escuela-lms-page-content, centralizing redirect decisioning, adding a persistent "Volver al aula" CTA, and providing WP‑CLI + browser validation.

Affected baseline specs (reference):
- openspec/specs/aula-course-dashboard/spec.md
- openspec/specs/aula-student-header/spec.md

## ADDED Requirements

### Requirement: Flow validation suite (aula-flow-validation)
The system MUST provide a repeatable validation suite (WP‑CLI smoke tests + Chrome MCP browser checks) that covers guest, new user, alumno_demo, admin, and mobile happy paths and selected edge-cases. Tests MUST be runnable in staging and CI.

#### Scenario: Run smoke suite
- GIVEN a staging environment with the test fixtures
- WHEN the engineer runs the provided WP‑CLI script and Chrome checks
- THEN all checks MUST pass (200 responses, no unsafe redirects, CTA present) and a report MUST be produced.

## MODIFIED Requirements

### Domain: aula-course-dashboard
Reference: openspec/specs/aula-course-dashboard/spec.md

### Requirement: Single active enrollment behavior
The system MUST render `/aula/` even when a student has exactly one active LearnDash enrollment. The dashboard MUST surface clear course CTAs (e.g., "Continuar") and MUST NOT auto-redirect from `/aula/` to a course permalink. Course pages MUST include a persistent, accessible "Volver al aula" control that navigates to `/aula/` (visible in Focus Mode and standard templates) injected via project-owned plugin or theme hook.
(Previously: students were automatically redirected from `/aula/` to the single active course permalink.)

#### Scenario: Exactly one active course
- GIVEN a logged-in student with one active enrollment
- WHEN they request `/aula/`
- THEN `/aula/` MUST return 200 OK with the dashboard rendered
- AND no redirect header MUST be issued
- AND the course experience MUST include a visible "Volver al aula" link.

### Requirement: Plugin error-free pages (escuela-lms-page-content)
The system MUST NOT emit PHP parse errors or warnings originating from files under `wp-content/plugins/escuela-lms-page-content` in staging or production. All fixes MUST live in project-owned plugins and be validated before deploy.

#### Scenario: No parse/warnings in staging
- GIVEN staging PHP error logging enabled
- WHEN the patch is deployed to staging and exercised for 48h
- THEN no new parse/warning entries referencing escuela-lms-page-content MUST appear in logs.

### Domain: aula-student-header
Reference: openspec/specs/aula-student-header/spec.md

### Requirement: Return-to-Aula link & header visibility
The header MUST surface a single, non-duplicative "Volver al aula" control in course contexts for authenticated students. It MUST be keyboard-accessible, detect LearnDash Focus Mode, and avoid duplication with LearnDash UI. Injection MUST be via project-owned plugin or theme hooks (no LearnDash core edits). Role-based visibility MUST hide Aula links for admins, instructors, group leaders, and guests.
(Previously: header required a "Volver al aula" link but did not mandate injection method or Focus Mode compatibility.)

#### Scenario: Course in Focus Mode
- GIVEN a student opens a course in Focus Mode
- WHEN the page renders
- THEN the header or focus chrome MUST include a single accessible "Volver al aula" link to `/aula/`.

### Requirement: Logout & redirect safety
The "Salir del aula" control MUST use WordPress logout mechanics with nonce validation and MUST NOT permit open-redirects. Logout failures due to nonce MUST present a retry/error state and leave the session intact.

#### Scenario: Logout nonce validated
- GIVEN a student clicks "Salir del aula"
- WHEN the logout URL is invoked
- THEN the nonce MUST be validated and the user logged out and redirected to the configured public page; if the nonce fails, no partial logout occurs and an error is shown.

## Validation (acceptance + CLI/browser checks)
- WP‑CLI: create fixtures, create test user, assign enrollment, and assert HTTP headers and body via curl: e.g. `wp user create test user@example.com --role=subscriber` and `curl -i -b cookies -L https://$STAGING/aula/` (validate 200, absence of 3xx to course).
- Chrome MCP: scripted flows to capture screenshots for guest /aula (login modal), registro-completado, alumno_demo login → /aula → course page (CTA present), mobile viewport checks, Focus Mode check.
- Logs: verify PHP error log contains no new parse/warnings for 48h post-deploy; verify redirect decision logs include allowlist decisions.

## Guardrails & Non-goals
- MUST NOT edit WordPress, LearnDash, Kadence core or third-party plugin internals.
- Changes MUST live in project-owned plugins (escuela-lms-page-content, escuela-lms-student-access) or theme hooks.
- MUST NOT write user/meta programmatically without approval. Quizzes, certificates, email templates, and major theme template rewrites are OUT OF SCOPE.

## Risks & Rollback
- Risk: parse errors break pages — Mitigation: block production deploy until staging logs clean; rollback: revert plugin commits, disable handler, flush caches.
- Risk: user surprise from removed auto-redirect — Mitigation: prominent "Continuar" CTAs, release notes, monitor support for 2 weeks.

## Deliverables & Next steps
- Create change PR for plugin fixes (staging-only first), add WP‑CLI smoke scripts and Chrome checks, run validation, then promote to production.
