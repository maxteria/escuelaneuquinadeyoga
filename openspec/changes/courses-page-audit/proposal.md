# Proposal: courses-page-audit

## Intent

Render the `/courses` landing page from real LearnDash course data while preserving the existing ENY visual language and without editing plugin code. Today the `escuela-lms-page-content` shortcode outputs hard-coded cards plus a featured section, so new courses never appear and the featured module must be edited in PHP.

## Scope

### In Scope
- Override `[formaciones_landing]` via repo-controlled mu-plugin.
- Move markup into a theme partial inside a Kadence child theme.
- Render hero + course grid with dynamic LearnDash data and keep existing `.enyf-*` class structure.
- Provide caching + invalidation for performance and document rollback.

### Out of Scope
- Introduce pagination/filtering (capture as follow-up).
- Implement “featured course” controls (remove the block entirely for now).
- Restyle beyond minimal adjustments required to keep the current look.

## Capabilities

### New Capabilities
- `courses-landing`: LearnDash courses landing renders dynamically via shortcode override.

### Modified Capabilities
- None.

## Approach

Implement a mu-plugin that re-registers `[formaciones_landing]`, queries published `sfwd-courses`, caches output, and loads a Kadence child-theme partial. The partial reproduces the hero copy and cards layout using LearnDash data (title, excerpt, featured image). The featured module is removed for this slice. Translations reuse the plugin textdomain `escuela-lms`.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `wp-content/mu-plugins` | New | Override shortcode, caching, feature flag, CLI helper |
| `wp-content/themes/kadence-child` | New | Child theme scaffolding, template partial, CSS |
| `uploads/assets/images/` | New | Course placeholder image asset |
| `openspec/changes/courses-page-audit` | New | Tasks, docs, acceptance helper |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Cache becomes stale when courses change | Medium | Hook into `save_post_sfwd-courses`, `transition_post_status`, etc., and document manual flush |
| Visual regressions after moving markup | Medium | Keep `.enyf-*` classes and verify hero/grid in multiple breakpoints |
| Missing translations | Low | Reuse `escuela-lms` textdomain for new strings |

## Rollback Plan

Set option `eny_override_courses_landing=false` or remove the mu-plugin file and flush caches. Reactivate the parent Kadence theme if child theme causes issues. Documented in README.

## Dependencies
- LearnDash plugin already registered with `sfwd-courses` post type.
- Kadence theme (parent) loaded; child theme can be created safely.

## Success Criteria
- [ ] `/courses/` renders published LearnDash courses with original ENY styling and no hard-coded cards.
- [ ] Removing the mu-plugin/child theme restores current behavior (rollback works).
- [ ] Publishing/unpublishing a course refreshes the grid within one cache TTL.
