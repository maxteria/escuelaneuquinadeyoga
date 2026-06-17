# Proposal: Aula Header and Course Landing Refresh

## Intent

Improve the logged-in Aula hub so students immediately see their LearnDash progress, avoid dead-end messaging, and gain consistent navigation back to `/aula/`. Replace the static card with live course data, ensure the Kadence header exposes student actions, and keep LearnDash flows native.

## Scope

### In Scope
- Render active LearnDash enrollments on `/aula/` using supported shortcodes/APIs.
- Show completed formations beneath active ones with clear status cues.
- Auto-redirect to the sole active course while providing in-course "Volver al aula" navigation.
- Extend the Kadence header on `/aula/` with student-only links to Formaciones, Mi perfil, and Salir del aula.

### Out of Scope
- Instructor or group leader header variations.
- Changes to LearnDash Focus Mode layouts or behaviors.
- Custom enrolment/eligibility logic beyond existing LearnDash capabilities.

## Capabilities

### New Capabilities
- `aula-course-dashboard`: Defines the `/aula/` student dashboard layout pulling LearnDash course states and empty-state messaging.
- `aula-student-header`: Specifies Aula-specific header actions, roles scoping, and return-link behavior.

### Modified Capabilities
- None.

## Approach

Enhance the `escuela-lms-page-content` shortcode rendering `/aula/` to fetch LearnDash enrollments for the logged-in student via existing helper functions. Split courses into active/completed sections styled via `assets/css/aula.css`. Detect a single active course server-side and perform a safe `wp_redirect` to its permalink while injecting a persistent "Volver al aula" link within course templates (via hook or shortcode wrapper). Update `fix-header-branding.php` (Kadence MU plugin) to conditionally append the Aula action links when on `/aula/` and `current_user_can( 'read' )` but not instructor roles. Leverage LearnDash shortcodes or REST endpoints only—no direct SQL.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `wp-content/plugins/escuela-lms-page-content/escuela-lms-page-content.php` | Modified | Update Aula shortcode to render active/completed sections and handle single-course redirect logic. |
| `wp-content/plugins/escuela-lms-page-content/assets/css/aula.css` | Modified | Add styles for two-section layout, empty state, and Aula-specific header accents. |
| `wp-content/mu-plugins/fix-header-branding.php` | Modified | Inject Aula header links for student roles and ensure logout returns to home. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Redirect loop or loss of access if return link misconfigured | Medium | Add explicit checks for course context and fallback to `/aula/`; QA with multiple role types. |
| LearnDash shortcode mismatch due to caching or role restrictions | Low | Use documented shortcodes/APIs and test with demo student. |
| Header changes impacting non-student viewers | Low | Scope logic to subscribers and current Aula route; verify as admin and student. |

## Rollback Plan

Revert changes to the Aula shortcode and CSS to restore the previous static card. Remove conditional header links from `fix-header-branding.php`. Clear any added hooks and flush page cache. Validate that `/aula/` again shows the original content and header behavior.

## Dependencies

- Existing LearnDash enrollment data and shortcodes must remain available.

## Success Criteria

- [ ] Student landing on `/aula/` with one active course is redirected into the course and sees a working "Volver al aula" action.
- [ ] Students with multiple courses view active first, completed second, with sections populated via LearnDash data.
- [ ] `/aula/` header displays Formaciones, Mi perfil, and Salir del aula links only for student roles.
- [ ] Students without active courses receive a link leading to the public Formaciones page.
