# Proposal: aula-virtual-flow-hardening

## Intent

Stabilize and document the full Aula Virtual flow (guest → registration → success → login → aula/profile → course access → logout). Prioritize remediation of PHP parse/warning errors in wp-content/plugins/escuela-lms-page-content, harden redirect and logout/login behavior, and keep changes limited to project-owned plugins (escuela-lms-page-content, escuela-lms-student-access). Delivery strategy: ask-on-risk — pause and request approval before any high-risk production changes. Important: remove the automatic single-active-course redirect so students always land on the Aula hub and select their course.

## Scope

### In Scope
- Audit & fix PHP parse/warnings in wp-content/plugins/escuela-lms-page-content
- Add WP-CLI smoke tests and Chrome checks for guest, new user, alumno_demo, admin, and mobile
- Harden redirect logic: centralize decision, add allowlist + logging (in escuela-lms-student-access or a tiny handler)
- Update openspec delta for aula-course-dashboard and aula-student-header
- Documentation: test steps, deployment checklist, rollback instructions
 - Implement persistent "Volver al aula" CTA inside course pages (visible in Focus Mode and standard templates), injected via project-owned plugin or theme hooks (no LearnDash core edits)
 - Remove single-active-course auto-redirect: disable template_redirect behavior that forwards /aula/ to a single course; ensure /aula/ renders for authenticated students

### Out of Scope
- LearnDash core, Kadence core, quizzes, certificates, emails, automations
- Major theme/template refactors or replacing shortcodes with full templates

## Capabilities

### New Capabilities
- aula-flow-validation: WP-CLI and browser smoke-test suite covering flow happy-paths and edge cases

### Modified Capabilities
- aula-course-dashboard: add explicit redirect validation, error-handling for PHP warnings, and mandatory smoke tests
- aula-student-header: require logout nonce validation and cross-role visibility checks
- aula-course-dashboard: REQUIREMENT CHANGE — remove single-active-course auto-redirect. Dashboard MUST render even when exactly one active enrollment exists; surface clear CTAs to continue into a course.
 - aula-course-dashboard / aula-course-experience: REQUIREMENT ADDITION — course pages MUST include a visible, accessible "Volver al aula" link/button that navigates to /aula/; implement via plugin/theme injection (escuela-lms-student-access or escuela-lms-page-content), ensure no LearnDash core changes.

## Approach

Two phases: Phase A (Audit & Fix — 1–3 days): locate and fix PHP parse/warnings in escuela-lms-page-content, add WP-CLI smoke tests, validate registration-success flow. Phase B (Harden & Remove Redirect — 2–4 days): remove the single-active-course redirect from escuela-lms-page-content, centralize remaining redirect decisioning into escuela-lms-student-access or a tiny handler, implement wp_safe_redirect allowlist and logging, inject persistent "Volver al aula" CTA into course pages via plugin/theme hooks (ensure Focus Mode compatibility), add browser verification scripts, and prepare delta specs to reflect the hub-first behavior.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| wp-content/plugins/escuela-lms-page-content/*.php | Modified | Fix parse/warnings; harden shortcodes [la_escuela_hub], [la_escuela_registration_success] |
| wp-content/plugins/escuela-lms-student-access/*.php | Modified | Centralize redirect handler, guest/profile and wp-admin redirects, admin-bar suppression |
| wp-content/themes/<child-theme> or plugin injection | Modified | Inject "Volver al aula" CTA into course header or course template via hooks (no core theme or LearnDash edits). Prefer plugin-based injection in escuela-lms-student-access or escuela-lms-page-content. |
| openspec/specs/ | Modified/New | Delta specs for aula-course-dashboard & aula-student-header; new validation spec (aula-flow-validation) |
| Database (wp_options, wp_usermeta) | Read | Use LearnDash registration options and user meta in tests |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| PHP parse/warnings break pages or block deploy | High | Prioritize fixes, block production deploy until staging logs are clean; run linter/CI on PHP files |
| Removing auto-redirect increases click friction and surprises returning users used to direct entry | Med | Improve hub UX: make primary "Continuar" CTA prominent, add first-time hint; communicate change in release notes and monitor support for 2 weeks after deploy |
| CTA duplication or layout break in LearnDash Focus Mode or custom course templates | Med | Detect Focus Mode and template types; only inject CTA where appropriate; test across Focus Mode and theme course templates; add CSS/JS fallback and toggle to disable if collisions detected. |
| Redirect loops or open-redirects from early template_redirect | Med | Implement allowlist, strict wp_safe_redirect, staging verification, telemetry/logging |
| Misclassification of users (wrong redirects or visibility) | Med | Tighten student predicate, add unit tests and sample fixtures (alumno_demo); role-based visibility checks in header |

## Rollback Plan

If issues appear after deploy: revert plugin commits (git), or rollback to previous plugin zip, disable new handler via feature flag, flush caches, and re-run smoke tests. Maintain clear plugin diff for quick revert.

## Dependencies

- Staging environment, PHP error log access, WP-CLI, QA time for browser checks

## Success Criteria

- [ ] No PHP parse/warnings in staging logs for 48h
- [ ] WP-CLI smoke tests pass for guest, registration, alumno_demo, admin, mobile
- [ ] No PHP parse/warnings in staging logs for 48h
- [ ] WP-CLI smoke tests pass for guest, registration, alumno_demo, admin, mobile
- [ ] alumno_demo login lands on /aula/ and shows course 1514 with a visible "Continuar" CTA (no automatic redirect)
- [ ] Subscriber /wp-admin access remains blocked and redirects to /profile/
- [ ] No redirect loops in staging after changes
 - [ ] Persistent "Volver al aula" CTA/button appears on course pages (standard and Focus Mode), is keyboard accessible, and points to /aula/
 - [ ] CTA does not duplicate or clash with LearnDash UI in Focus Mode; visual tests pass on desktop & mobile

## Proposal question round

1. With auto-redirect removed, should we keep a persistent "Volver al aula" link inside course pages (Yes — recommended) or hide it?
2. Deploy fixes to staging first (recommended) or apply hotfix to production for parse errors?
3. Approve centralizing redirect logic into escuela-lms-student-access or prefer a separate tiny plugin?

---

Evidence & references: openspec/changes/aula-virtual-flow-hardening/exploration.md (pages, shortcodes, redirect rules, alumno_demo user), files: wp-content/plugins/escuela-lms-page-content/escuela-lms-page-content.php, wp-content/plugins/escuela-lms-student-access/escuela-lms-student-access.php
