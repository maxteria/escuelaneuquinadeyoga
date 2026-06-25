# Tasks: courses-page-audit

## Review Workload Forecast

| Field | Value |
|---|---|
| Estimated changed lines | 220–340 |
| 400-line budget risk | Medium |
| Chained PRs recommended | No |
| Delivery strategy | ask-always |

Decision needed before apply: Yes
Chained PRs recommended: No
Chain strategy: pending
400-line budget risk: Medium

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|---|---|---|---|
| 1 | Foundation: mu-plugin, feature-flag, cache skeleton | PR 1 | Reversible; smallest autonomous unit |
| 2 | Template partial + CSS + markup | PR 1 | Same PR but separate commits for review clarity |
| 3 | Tests, cache invalidation wiring, docs | PR 1 | Acceptance script + instructions |

## Phase 1: Foundation / Infrastructure
- [x] 1.1 Create mu-plugin `mu-plugins/eny-courses-landing.php` that registers `formaciones_landing` on plugins_loaded, reads option `eny_override_courses_landing` (default true) and provides cache helpers. (mu-plugins/eny-courses-landing.php)
- [x] 1.2 Add transient key `eny_courses_landing_grid_v{version}` logic and TTL (300s) inside the mu-plugin. (mu-plugins/eny-courses-landing.php)
- [x] 1.3 Wire invalidation hooks in mu-plugin: `save_post_sfwd-courses`, `transition_post_status`, `deleted_post`, `trash_post` to clear the transient. (mu-plugins/eny-courses-landing.php)

## Phase 2: Core Implementation
- [x] 2.1 Create template partial `wp-content/themes/kadence-child/template-parts/courses/landing.php` that renders hero (page content || translatable fallback) and the `.enyf-cards-grid`. (wp-content/themes/kadence-child/template-parts/courses/landing.php)
- [x] 2.2 Implement WP_Query: `post_type=sfwd-courses`, `post_status=publish`, `posts_per_page=-1`, `orderby=date`, `order=DESC` and loop to emit `.enyf-card` with title (linked), excerpt (trim to 24 words), image (fallback priority: child → parent → plugin). (template partial)
- [x] 2.3 Ensure all strings use textdomain `escuela-lms` and alt text uses course title fallback per design. (template partial)
- [x] 2.4 Add/modify stylesheet `wp-content/themes/kadence-child/assets/css/courses-landing.css` for minimal spacing fixes; reference from theme. (wp-content/themes/kadence-child/assets/css/courses-landing.css)

## Phase 3: Integration / Wiring
- [x] 3.1 Shortcode handler in mu-plugin must call `locate_template('template-parts/courses/landing.php', true, false)` and pass $courses (or include with scoped vars). (mu-plugins/eny-courses-landing.php)
- [x] 3.2 Confirm active theme child folder name; if `kadence-child` is not present, create and set it active (document manual activation). (manual)

## Phase 4: Testing / Verification
- [x] 4.1 Manual: Scenario "Renders published courses" — with at least one published `sfwd-courses`, load `/courses/` and assert one `.enyf-card` per published course and NO featured module. (local)
- [ ] 4.2 Manual: Empty-state — with zero published courses assert hero + accessible empty-state message (plugin textdomain) and no `.enyf-card`. (local/staging clone)
- [x] 4.3 Manual: Image & alt tests — verify featured image shows and alt equals title; verify fallback image appears when none exists. (local)
- [ ] 4.4 Manual: Translation — set locale `es_AR` and verify strings come from `escuela-lms` .mo files. (local)
- [x] 4.5 Manual/CI: Cache invalidation — publish a draft course and confirm transient is cleared so the new card appears (or document required cache purge steps). (local)
- [x] 4.6 Automated: Add `tests/acceptance/test-courses-landing.sh` (WP-CLI) or small PHPUnit integration test that creates a published `sfwd-courses` post, runs `do_shortcode('[formaciones_landing]')` and asserts `.enyf-card` and course title present. (tests/acceptance/test-courses-landing.sh)

## Phase 5: Cleanup / Documentation
- [x] 5.1 Add short README `openspec/changes/courses-page-audit/README.md` documenting feature-flag, cache keys, and rollback steps. (openspec/changes/courses-page-audit/README.md)
- [x] 5.2 Remove any temporary debug output and ensure mu-plugin honors `eny_override_courses_landing=false` for rollback. (mu-plugins/eny-courses-landing.php)

## Notes & Constraints
- Keep changes reversible (feature-flag or remove mu-plugin file).
- Do NOT modify plugin files under `wp-content/plugins/escuela-lms-page-content/`.
- Testing must cover the spec scenarios: course grid, empty state, translation, cache invalidation.
