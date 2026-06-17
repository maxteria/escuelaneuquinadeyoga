## Exploration: aula-virtual-flow-hardening

### Current State

- Key pages (verified in DB):
  - `/aula/` — Page ID 1652, post_content: `[la_escuela_hub]` (shortcode renders Aula dashboard). Evidence: wp_posts lookup.
  - `/profile/` — Page ID 10, post_content: `<!-- wp:learndash/ld-profile /-->` (LearnDash profile block).
  - `/registration-2/` — Page ID 107, post_content: `<!-- wp:learndash/ld-registration /-->` (LearnDash registration block).
  - `/registro-completado/` — Page ID 105, post_content: `[la_escuela_registration_success]` (custom success view).
  - `/courses/` — Page ID 11 (archive/landing handled by plugin virtual template and post_type archive logic).
  - `/courses/formacion-en-meditacion/` — Course post ID 1514 (post_type `sfwd-courses`).

- Shortcodes and implementations (project-owned):
  - `la_escuela_hub` — implemented in `wp-content/plugins/escuela-lms-page-content/escuela-lms-page-content.php` (renders guest/login modal, student dashboard for enrolled users, includes template `templates/aula-dashboard.php`).
  - `la_escuela_registration_success` — implemented in same plugin (HTML success screen linking to `/aula/` and `/courses/`).
  - LearnDash shortcodes used on pages: `learndash/ld-registration` (registration block) and `learndash/ld-profile` (profile block).

- Login/registration stack & redirects:
  - LearnDash controls registration pages via option `learndash_settings_registration_pages` (registration => page 107, registration_success => page 105). Evidence: option value in DB.
  - The site uses the LearnDash `learndash_login` shortcode for modal login (the LD30 shortcode accepts `login_model="yes"` for modal behavior).
  - `escuela-lms-page-content` implements an early `template_redirect` (priority 1) that, when the route is identified as Aula and the logged-in user is an Aula student and has exactly one active course, issues a `wp_safe_redirect` to that single course permalink (this implements the "single active redirect" behaviour).
  - There is an additional redirect rule mapping legacy `registration-success-2` requests to `/registro-completado/`.

- User `alumno_demo` (ID 2):
  - user_login: `alumno_demo`, display_name: "Alumno Demo".
  - Role: subscriber (wp_capabilities meta = subscriber).
  - User meta: `show_admin_bar_front` = `false` (admin bar hidden), `course_1514_access_from` and `learndash_course_1514_enrolled_at` present → enrolled / access metadata for course 1514.

- Security & access controls observed:
  - Guest access: `escuela-lms-student-access` plugin redirects guests requesting `/profile/` to `/aula/` (function enya_redirect_guests_from_profile).
  - Subscriber wp-admin access: same plugin redirects subscribers from `/wp-admin/*` (except `/wp-admin/profile.php`) to `/profile/` (function enya_redirect_subscribers_to_profile).
  - Admin bar: same plugin ensures admin bar hidden for subscribers and sets `show_admin_bar_front` user meta on login.
  - Logout links (LearnDash login shortcode) use `wp_logout_url()` (which produces a nonce-protected logout URL) — evidence in `sfwd-lms/themes/ld30/includes/shortcodes.php`.

### Affected Areas
- `wp-content/plugins/escuela-lms-page-content/escuela-lms-page-content.php` — primary implementation of Aula dashboard, shortcodes `[la_escuela_hub]` and `[la_escuela_registration_success]`, virtual template for `/courses/` archive, single-active-course redirect, translation mappings.
- `wp-content/plugins/escuela-lms-student-access/escuela-lms-student-access.php` — redirects for `/profile/`, subscriber wp-admin redirection, admin bar suppression.
- `wp-content/plugins/sfwd-lms/` — LearnDash core shortcodes & login/registration implementation that the site relies on (`ld-registration`, `learndash_login`, logout URL generation).
- `wp-content/themes/kadence/header.php` + WP menus — navigation / login/logout may surface LearnDash login links; header uses Kadence hooks to render menus and blocks.
- Database (wp_posts, wp_options, wp_usermeta) — key configuration and user/course enrollment state.

### Approaches
1. **Audit-and-fix (Conservative)** — Document and test current flow end-to-end (guest->registration->success; student login->/aula/ redirect rules; subscriber admin lockout). Fix any edge-case bugs (typos, parse errors, modal JS). Minimal code changes, prefer config/filters.
   - Pros: Low risk, fast, preserves existing UX and content translations.
   - Cons: May leave subtle race conditions or legacy redirects in place.
   - Effort: Low–Medium.

2. **Harden & centralize flow** — Consolidate redirection logic into a single responsibly-tested handler (move early-redirect logic into escuela-lms-student-access or a small dedicated plugin), add explicit unit/functional checks, and add logging/metrics for redirect decisions. Remove legacy redirect fragments and ensure login/registration redirects use wp_safe_redirect with strict allowlists.
   - Pros: Clearer control, easier to reason about, fewer surprises during future changes.
   - Cons: Medium development effort and requires thorough verification in staging (affects many users flow).
   - Effort: Medium–High.

3. **Replace page-level shortcodes with explicit templates** — Turn virtual `/courses/` landing and the Aula dashboard into theme templates (or block templates) rather than relying on giant PHP shortcodes. Improves maintainability but is the heaviest change.
   - Pros: Cleaner separation, easier testing and templating, less PHP-in-plugins inline HTML.
   - Cons: High effort, risks regressions in layout and translations; not recommended as first step.
   - Effort: High.

### Recommendation
Start with Approach 1 (Audit-and-fix) to stabilize current behavior, then move to Approach 2 for medium-term hardening. Immediate priorities:
1. Verify the single-active-course redirect behaves as intended for all student roles and that the `aula-dashboard` bypass query param is respected where expected.
2. Test and fix any PHP warnings/parse errors found in logs (there are parse errors referencing `escuela-lms-page-content` in the PHP error log). Resolve those before any deployment.
3. Add automated smoke tests (WP‑CLI scripted checks) for the happy paths: guest /aula (login modal shown), registration -> registro-completado, alumno_demo login -> redirected to course 1514, subscriber attempt to access /wp-admin -> redirected to /profile/.

### Risks
- Live PHP parse errors found in server logs referencing `escuela-lms-page-content` — these could break page rendering intermittently (evidence: php error log entries).
- Redirect logic runs early (template_redirect priority 1). Mistakes or malformed URLs could cause redirect loops or send users to unsafe locations if redirect validation is not strict.
- Multiple places influencing the same flow (LearnDash settings, `escuela-lms-page-content` template_redirect, and `escuela-lms-student-access`) increase chance of conflicting behavior.
- Role & capability detection currently uses `user_can( $user, 'read' )` with exclusions — ambiguous role surface (custom roles with `read`) could be misclassified as students.

### Ready for Proposal
Yes — after the team confirms acceptance of the conservative audit scope (Approach 1). The proposal phase should include specific test cases and a short deployment plan with rollback instructions.

---

Commands and evidence (selected outputs trimmed):
- Read plugin: `wp-content/plugins/escuela-lms-page-content/escuela-lms-page-content.php` — contains `add_shortcode('la_escuela_hub', 'escuela_lms_render_aula_shortcode')` and `add_shortcode('la_escuela_registration_success', ...)` and `template_redirect` logic for Aula (file inspected).
- Read plugin: `wp-content/plugins/escuela-lms-student-access/escuela-lms-student-access.php` — guest/profile and wp-admin redirect functions (file inspected).
- WP-CLI: `wp --info` — confirmed WP-CLI available in environment.
- MySQL: `SELECT ID, post_name, post_type FROM wp_posts WHERE post_name IN ('aula','profile','registration-2','registro-completado','courses','formacion-en-meditacion');` — returned IDs 1652, 10, 107, 105, 11, 1514.
- MySQL: `SELECT ID, post_content FROM wp_posts WHERE ID IN (1652,11,1514,10,107,105);` — showed `[la_escuela_hub]`, `<!-- wp:learndash/ld-profile /-->`, `<!-- wp:learndash/ld-registration /-->`, `[la_escuela_registration_success]`.
- MySQL: `SELECT option_value FROM wp_options WHERE option_name = 'learndash_settings_registration_pages';` — returns serialized array mapping registration=>107, registration_success=>105.
- MySQL: `SELECT meta_key, meta_value FROM wp_usermeta WHERE user_id = 2;` — shows `wp_capabilities` contains subscriber, `show_admin_bar_front` = `false`, and `course_1514_access_from` and `learndash_course_1514_enrolled_at` present.
- Read LearnDash: `wp-content/plugins/sfwd-lms/includes/payments/ld-login-registration-functions.php` and `themes/ld30/includes/shortcodes.php` — registration output and `learndash_login` implementation inspected (logout uses `wp_logout_url()`, `login_model` behaviour documented).

### Open Questions / Gaps
1. Are there any other custom plugins or mu-plugins (outside the inspected ones) that mutate login/registration redirects? (I scanned plugins folder but please confirm none are active in mu-plugins.)
2. Is the single-active-course auto-redirect intended for ALL students or only for a subset (e.g., trial accounts)? Clarify business intent so we can add guardrails (e.g., only redirect for course types X).
3. The PHP error log contains parse errors referencing `escuela-lms-page-content` — are these known and scheduled to be fixed, or should I include their remediation in the proposal? I recommend addressing them before broader hardening.
4. Do you want the student detection predicate (`escuela_lms_is_aula_student`) to remain as-is (capability-based) or to be tightened to a specific role or meta flag?
5. Would you like browser-level verification recorded (Chrome MCP screenshots) as part of the proposal/verify steps? I can run an interactive Chrome trace if you permit.

### Recommended next steps (proposal preparation)
1. Run the conservative audit (Approach 1): write tests (WP-CLI scripts) for the key flows and fix any immediate errors found (PHP parse warnings first).
2. Confirm acceptance criteria for single-active-course redirect and the student predicate.
3. Prepare a proposal that implements small, well-tested changes: explicit redirect allowlist, centralized redirect handler, and a small test matrix to validate in staging.

---

Files inspected (paths):
- app/public/wp-content/plugins/escuela-lms-page-content/escuela-lms-page-content.php
- app/public/wp-content/plugins/escuela-lms-student-access/escuela-lms-student-access.php
- app/public/wp-content/plugins/sfwd-lms/includes/payments/ld-login-registration-functions.php
- app/public/wp-content/plugins/sfwd-lms/themes/ld30/includes/shortcodes.php
- app/public/wp-content/themes/kadence/header.php
- DB tables: wp_posts, wp_options (learndash_settings_registration_pages), wp_usermeta (user_id=2)
