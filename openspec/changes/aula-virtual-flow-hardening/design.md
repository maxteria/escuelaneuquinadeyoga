# Design: Aula Virtual Flow Hardening

## Technical Approach

Deliver a minimal, test-first hardening that (1) fixes PHP parse/warnings in
wp-content/plugins/escuela-lms-page-content, (2) removes the automatic single‑course
redirect so `/aula/` always renders the hub, (3) centralizes redirect decisioning
into the project plugin `escuela-lms-student-access`, and (4) injects a persistent
"Volver al aula" CTA into course templates and Focus Mode via hooks. All changes
remain in project-owned plugins (per spec: openspec/changes/.../spec.md).

## Architecture Decisions

### Decision: Redirect handler location
**Choice**: Move decisioning to escuela-lms-student-access (new includes/redirects.php)
**Alternatives considered**: keep logic in escuela-lms-page-content; create new plugin
**Rationale**: student-access already controls login/profile/admin flows; central handler
reduces conflicts and simplifies capability/allowlist guards.

### Decision: Fix vs rewrite escuela-lms-page-content
**Choice**: Targeted fixes (syntax, guards, file_exists checks) and PHP linting.
**Alternatives considered**: rewrite the plugin entirely
**Rationale**: minimal, faster, lower-risk; tests validate no new parse errors.

### Decision: CTA injection strategy
**Choice**: Plugin-based injection via LearnDash hooks when available and fallback to
`the_content` filter for singular sfwd-courses. Styles enqueued from plugin CSS.
**Rationale**: No theme or core edits; hooks maintain Focus Mode compatibility.

## Data / Control Flow

User login → Request /aula/ (page-content renders hub shortcode)
  ├─ escuela-lms-page-content: render hub (no auto-redirect)
  ├─ escuela-lms-student-access: centralized redirect hooks (only for non-hub cases,
  │  behind allowlist & capability guard; feature-flag disabled by default)
  └─ Course pages: CTA injected via hook/filter (focus header hook → fallback to content)

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `app/public/wp-content/plugins/escuela-lms-page-content/escuela-lms-page-content.php` | Modify | Remove single‑active-course redirect block; run PHP lint fixes (unclosed braces, stray text, safer string concatenation). |
| `app/public/wp-content/plugins/escuela-lms-page-content/templates/aula-dashboard.php` | Modify | Ensure safe usage of $aula_user/$aula_data and expose return_url variable. |
| `app/public/wp-content/plugins/escuela-lms-student-access/includes/redirects.php` | Create | Central redirect handler: allowlist, capability guard, structured logging, feature-flag gate. |
| `app/public/wp-content/plugins/escuela-lms-student-access/includes/cta.php` | Create | CTA injection helpers (hooks for Focus Mode + the_content fallback) and enqueue assets. |
| `app/public/wp-content/plugins/escuela-lms-student-access/assets/css/cta.css` | Create | Lightweight, responsive, accessible styles for CTA. |
| `bin/wp-cli/smoke/aula_smoke.php` | Create | WP‑CLI smoke checks (eval-file) to exercise shortcode rendering and template_redirect safely. |
| `docs/qa/chrome-mcp/aula_flow_checklist.md` | Create | Chrome MCP manual checklist + selectors and expected screenshots. |

## Interfaces / Contracts

PHP prototypes (added to student-access):

```php
function escuela_lms_should_redirect( string $target, array $context = [] ): bool;
/**
 * Filter: 'escuela_lms_redirect_allowlist' (array of allowed hosts/paths)
 */
```

Log format (error_log): "enya-redirect [ts] user={id} from={uri} to={target} reason={code}"

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| WP‑CLI smoke | shortcode render, template_redirect path, no parse errors | `wp eval-file bin/wp-cli/smoke/aula_smoke.php` (checks return codes, captures output) |
| Integration | Logged-in /aula/ renders hub; course pages show CTA; no auto-redirect | Manual Chrome MCP checklist + devtools network logs (docs/qa/chrome-mcp) |
| Linting | PHP syntax & security scans | `php -l` and `wp vip-scanner` or `phpstan` (where available) |

Commands (examples):
- wp eval-file bin/wp-cli/smoke/aula_smoke.php
- wp plugin deactivate --all && wp plugin activate escuela-lms-page-content escuela-lms-student-access

## Migration / Rollout

No DB migration required. Deployment steps:
1. Deploy page-content fixes (no behavior change) to staging.
2. Deploy student-access changes with feature flag `escuela_lms_enable_single_course_redirect` = false (default).
3. Run WP‑CLI smoke tests and Chrome MCP checklist.
4. Enable in a limited environment only if needed.

Rollback: re-enable previous plugin PHP file (git revert) or set feature flag, then clear object/cache and page cache.

## Risks & Mitigations

- Risk: redirect loops or duplicates — Mitigate: strict allowlist, require `wp_validate_redirect`, log and short-circuit on repeated targets.
- Risk: permission leakage — Mitigate: capability guard (user_can read) and filter `escuela_lms_is_aula_student` respected.
- Risk: parse errors remain — Mitigate: php -l + wp eval smoke tests before merge; include CI lint step.

## Open Questions

- Confirm canonical /aula/ page ID(s) to avoid false negatives (spec references page slug and ID — confirm in staging).
- Preferred logging destination (PHP error log vs uploads/escuela-logs) — default: PHP error log for visibility.

## Traceability
- Maps to spec: openspec/changes/aula-virtual-flow-hardening/spec.md (requirements: fix parse/warnings; remove single-course redirect; inject "Volver al aula" CTA; add WP‑CLI + Chrome validation).
