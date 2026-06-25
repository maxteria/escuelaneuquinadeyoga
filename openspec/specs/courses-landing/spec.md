# Specification: courses-landing

## Summary
The `/courses` landing page MUST render from live LearnDash data via the `[formaciones_landing]` shortcode. The implementation moves rendering into repo-controlled code (mu-plugin + theme partial) while preserving the existing `.enyf-*` markup and visual layout. The legacy "featured course" block is removed for now.

## Requirements

### Shortcode Override
1. The mu-plugin MUST register `[formaciones_landing]` before page render so it replaces the plugin version without editing plugin code.
2. The handler MUST query published `sfwd-courses` posts ordered by newest first.
3. The override MUST provide a feature flag (`eny_override_courses_landing`, default true) to fall back to the original handler.
4. Output MUST be cached in a transient (`eny_courses_landing_grid_v{version}`) with a TTL of 300 seconds.
5. Cache MUST be invalidated when a LearnDash course is created, updated, trashed, deleted, or its status changes.

### Theme Partial & Markup
1. Rendering MUST occur in `template-parts/courses/landing.php` inside a Kadence child theme.
2. The partial MUST emit the same top-level sections and `.enyf-*` class names as the current landing page (intro section, proposal types, catalog grid, methodology, CTA, etc.).
3. The cards grid MUST render one `.enyf-card` per published course, including:
   - Course title linked to the single course permalink.
   - Excerpt trimmed to 24 words (use post excerpt if present, fallback to stripped content).
   - Featured image (fallback chain: child theme placeholder → parent theme placeholder → plugin placeholder) with accessible alt text (existing attachment alt or “Course: {title}”).
   - CTA button text localised via `escuela-lms` textdomain.
4. The legacy "Formación destacada" module MUST NOT appear.
5. An accessible empty-state message MUST display when no courses are published.

### Hero Content
1. Hero copy MUST prefer the editable `/courses` page content (hero title + paragraph). If empty, fallback string `__('Una escuela para estudiar, practicar e integrar', 'escuela-lms')` (or similar existing copy).
2. Hero output MUST keep `.enyf-intro` and `.enyf-intro__text` structure to reuse existing styling.

### Styles
1. Child theme styles MUST be scoped to `.enyf-*` classes and keep layout consistent with current design.
2. Additional CSS MUST be minimal and loaded after parent styles.

### Translations
1. All new visible strings MUST use `escuela-lms` textdomain.
2. Fallback hero/empty messages MUST be translatable.

### Caching / CLI
1. The mu-plugin MUST expose a programmatic flush helper (`eny_courses_landing_flush_cache()`).
2. Providing a WP-CLI command `wp eny courses-landing flush` is RECOMMENDED.

### Documentation & Testing
1. Add README documenting feature flag, cache key, and rollback steps.
2. Provide an acceptance helper script (shell or PHP) to render the shortcode and confirm cards.
3. Manual QA MUST cover: normal render, empty state, cache invalidation after publish/unpublish, translation usage.

## Non-Goals
- Pagination, filtering, or featured course selection (future iteration).
- Migrating hero copy editing experience to custom fields.

## Verification
1. GIVEN the override is active, WHEN visiting `/courses/`, THEN cards reflect published `sfwd-courses` and old mock cards/featured section are gone.
2. GIVEN cache TTL 300s, WHEN publishing a new course, THEN card count updates after invalidation (no stale result after flush).
3. GIVEN no published courses, WHEN loading the page, THEN the empty state text is shown.
4. GIVEN es_AR locale, WHEN rendering the page, THEN new strings appear translated via `escuela-lms` domain.
5. GIVEN `eny_override_courses_landing=false`, WHEN loading the page, THEN the original plugin output is restored.
