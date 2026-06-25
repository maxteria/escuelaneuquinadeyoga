# Design: courses-page-audit

## Technical Approach
Create a Kadence child theme and a mu-plugin that overrides `[formaciones_landing]`. The handler builds the course list via `WP_Query`, caches the rendered markup, and loads a theme partial that reproduces the existing ENY sections. Cache invalidation hooks keep results fresh. Rollback is a flag/rename of the mu-plugin.

## Architecture Decisions

### Decision: Shortcode override via mu-plugin
**Choice**: Register a replacement handler in `wp-content/mu-plugins/eny-courses-landing.php` on `wp_loaded` priority 9999.
**Alternatives**: Modify plugin code (forbidden), wrap `the_content` output (brittle). 
**Rationale**: Mu-plugin stays outside vendor code, loads early, and is easy to roll back.

### Decision: Template placement
**Choice**: `wp-content/themes/kadence-child/template-parts/courses/landing.php` loaded with `locate_template`.
**Alternatives**: Use parent theme (would be overwritten), render entire HTML in mu-plugin.
**Rationale**: Template belongs with presentational concerns and allows CSS adjustments in child theme.

### Decision: Caching strategy
**Choice**: Transient keyed by template mtime (`eny_courses_landing_grid_v{mtime}`) with TTL 300s.
**Alternatives**: Object cache, no cache.
**Rationale**: Lightweight and avoids full page caches; template mtime invalidates on deploy.

### Decision: Remove featured module for now
**Choice**: Omit legacy “Formación destacada” block; future iteration adds controlled featured logic.
**Alternatives**: Keep static featured markup. 
**Rationale**: Scope creep; user agreed to remove for first slice.

## Data Flow

```
Mu plugin (shortcode)
   ├─ WP_Query sfwd-courses (publish)
   ├─ set_transient( grid_html )
   └─ locate_template('template-parts/courses/landing.php', …)
            ├─ Render hero (page content)
            ├─ Loop courses -> card markup
            └─ Empty state fallback
```

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `wp-content/mu-plugins/eny-courses-landing.php` | Create | Shortcode override, feature flag, caching, invalidation, CLI helper |
| `wp-content/themes/kadence-child/style.css` | Create | Child theme header |
| `wp-content/themes/kadence-child/functions.php` | Create | Enqueue parent + child CSS |
| `wp-content/themes/kadence-child/template-parts/courses/landing.php` | Create | Hero + course grid markup |
| `wp-content/themes/kadence-child/assets/css/courses-landing.css` | Create | Minimal tweaks for cards/grid |
| `wp-content/themes/kadence-child/assets/images/course-placeholder-768x432.png` | Create | Placeholder image |
| `openspec/changes/courses-page-audit/tests/test-courses-landing.sh` | Create | Acceptance helper script |
| `openspec/changes/courses-page-audit/README.md` | Create | Feature flag, cache key, rollback notes |

## Interfaces / Contracts

```php
// mu-plugin API
function eny_formaciones_landing_shortcode( array $atts = [], $content = null ): string;
function eny_courses_landing_flush_cache(): void;
// Option: bool|string `eny_override_courses_landing`
// CLI: wp eny courses-landing flush
```

Template expects `$courses` array via `locate_template` args or global fallback, plus `$page_id` for hero context.

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Manual UI | `/courses/` hero + grid layout | Browser smoke on local mirror |
| Manual data | Publish/unpublish course -> cache invalidation | WP-CLI create post + run shortcode |
| Manual empty state | No published courses | Temporarily unpublish or use staging clone |
| Scripted acceptance | Shortcode output contains `.enyf-card` entries | Shell script invoking wp-cli |

## Migration / Rollout
No data migration. Rollout order: deploy mu-plugin + child theme -> flush caches. Rollback by disabling override option or removing mu-plugin and deactivating child theme.

## Open Questions
- [ ] Future: how should the “featured course” be modelled (taxonomy, meta, manual selection)?
- [ ] Pagination or load-more behaviour once course count grows?
