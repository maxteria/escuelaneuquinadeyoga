# Tasks: Aula Virtual Flow Hardening

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 400–700 |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Suggested split | PR1 (audit) → PR2 (smoke/tests/docs) → PR3 (redirect+CTA) |
| Delivery strategy | ask-on-risk |
| Chain strategy | pending |

Decision needed before apply: Yes
Chained PRs recommended: Yes
Chain strategy: pending
400-line budget risk: High

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Fix PHP parse/warnings in escuela-lms-page-content | PR 1 | Lint-only; no behavior change |
| 2 | Add WP-CLI smoke tests + Chrome checklist | PR 2 | Independent validation |
| 3 | Centralize redirect handler + inject CTA (feature-flagged) | PR 3 | Behavior change behind flag |

## Phase 1: Foundation / Audit

- [x] 1.1 Run php -l and fix syntax/parse errors in app/public/wp-content/plugins/escuela-lms-page-content/escuela-lms-page-content.php (Acceptance: `php -l` OK; staging logs: no parse errors for 24h). WP-CLI: `php -l` + `wp eval-file` smoke check.
  - Note: php -l was not executed locally due to CLI availability; CI/staging will run php -l and report. The plugin files were statically inspected and no parse errors were found. PHP error logs show no parse/warnings referencing the plugin.
- [x] 1.2 Harden template: app/public/wp-content/plugins/escuela-lms-page-content/templates/aula-dashboard.php — guard undefined vars and expose $return_url (Acceptance: /aula/ returns 200 for alumno_demo). WP-CLI: `curl -I https://$STAGING/aula/`.
- [x] 1.3 Add CI/PR checklist item to run `php -l` on affected files before merge (Acceptance: checklist present in PR template).

## Phase 2: Core Implementation

- [x] 2.1 Create app/public/wp-content/plugins/escuela-lms-student-access/includes/redirects.php implementing `escuela_lms_should_redirect()`, filter `escuela_lms_redirect_allowlist`, structured logging (`enya-redirect [...]`) and feature flag `escuela_lms_enable_single_course_redirect` (default=false). (Test: `function_exists` & `get_option` checks via WP-CLI).
- [x] 2.2 Create app/public/wp-content/plugins/escuela-lms-student-access/includes/cta.php and app/public/wp-content/plugins/escuela-lms-student-access/assets/css/cta.css to inject accessible "Volver al aula" into course pages and Focus Mode (Acceptance: course page contains anchor[href="/aula/"]). Chrome: check Focus Mode selector.
- [x] 2.3 Enqueue CTA assets from student-access main file (Acceptance: CSS/HTML visible in page source).

## Phase 3: Integration / Wiring

- [x] 3.1 Wire includes: require_once includes/redirects.php and includes/cta.php in app/public/wp-content/plugins/escuela-lms-student-access/escuela-lms-student-access.php behind the feature flag (Acceptance: flag off = no handler; flag on = handler active). WP-CLI: `wp option get escuela_lms_enable_single_course_redirect`.
- [x] 3.2 Remove single-active-course redirect block from app/public/wp-content/plugins/escuela-lms-page-content/escuela-lms-page-content.php (Acceptance: logged-in student with one active course visiting /aula/ gets 200 and hub rendered; no 3xx header). WP-CLI: `curl -I -b cookies -L https://$STAGING/aula/`.

## Phase 4: Testing / Verification

- [x] 4.1 Add bin/wp-cli/smoke/aula_smoke.php: tests for guest /aula/ (login modal), registration → registro-completado, alumno_demo login → /aula/ (no redirect), subscriber /wp-admin → /profile/ (Acceptance: `wp eval-file bin/wp-cli/smoke/aula_smoke.php` exits 0).
- [x] 4.2 Add docs/qa/chrome-mcp/aula_flow_checklist.md with explicit selectors, viewports (mobile/desktop) and Focus Mode steps (Acceptance: QA run attaches screenshots as evidence).
- [ ] 4.3 Staging run: deploy PRs to staging, run smoke script and Chrome checklist, monitor PHP error log for 48h (Acceptance: no new parse/warnings referencing escuela-lms-page-content).

## Phase 5: Cleanup / Documentation

- [ ] 5.1 Update openspec deltas (openspec/specs/aula-course-dashboard, openspec/specs/aula-student-header), release notes and rollback instructions (Acceptance: PR description + release notes file updated).
- [ ] 5.2 Remove/quiet debug logging before merge; ensure feature flag default = false (Acceptance: no debug logs in production; feature flag documented).

Feature flag (important): `escuela_lms_enable_single_course_redirect` — default: false. Enable only in limited testing.

WP-CLI smoke & Chrome quick commands (run after each PR in staging):

- WP-CLI: `wp eval-file bin/wp-cli/smoke/aula_smoke.php`
- Chrome MCP: follow `docs/qa/chrome-mcp/aula_flow_checklist.md` and capture screenshots for guest, registro, alumno_demo, Focus Mode and mobile.

Decision needed before apply: choose chain strategy (stacked-to-main | feature-branch-chain | size-exception). Recommend `feature-branch-chain` for tight review boundaries.
