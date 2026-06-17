# Design: Aula Header and Course Landing Refresh

## Technical Approach

Create `Escuela_Aula_Dashboard_Service` inside `wp-content/plugins/escuela-lms-page-content/escuela-lms-page-content.php` to fetch LearnDash enrollments with `learndash_user_get_enrolled_courses`, `learndash_user_get_course_progress`, and `ld_course_access_from`. The existing `la_escuela_hub` shortcode will load a new template (e.g., `templates/aula-dashboard.php`) that renders active and completed grids, an empty state, and per-card controls using that dataset plus `learndash_user_progress_get_first_incomplete_step` for resume URLs. A shared cache (static property or global) lets `template_redirect` reuse the dataset to detect the single-active-course case and issue a `wp_safe_redirect` unless `?aula-dashboard=1` is present. Header work moves to PHP: `fix-header-branding.php` receives a `wp_nav_menu_items` filter for the Kadence primary menu so student users on `/aula/` see “Formaciones”, “Mi perfil”, and a `wp_logout_url( home_url() )` “Salir del aula” CTA; focus mode gets a visible “Volver al aula” button via `learndash-focus-header-nav-after` plus a dropdown fallback from `learndash_focus_header_user_dropdown_items`. Styling lives in `assets/css/aula.css` (grid, cards, responsive breakpoints) and `assets/css/learndash-focus-overrides.css` (focus header button), reusing existing color tokens and media queries while adding `aria`-friendly progress bars.

## Architecture Decisions

### Decision: Enrollment data source
| Option | Trade-offs | Decision |
| --- | --- | --- |
| LearnDash shortcodes (`[ld_course_list]`, `[ld_course_resume]`) embedded in the shortcode | Zero custom PHP but no way to split active vs completed, no control over ordering, redirect logic, or failure/empty states. | Rejected |
| Custom PHP service using LearnDash APIs | Requires bespoke loop and caching, but exposes progress/status metadata needed for grouping, redirect gating, and error handling. | **Chosen** |

### Decision: Continue URL resolver
| Option | Trade-offs | Decision |
| --- | --- | --- |
| Link every card to the course permalink | Simple but drops learners at the syllabus even if they left mid-lesson; violates the “continuar” expectation. | Rejected |
| Compute next step with `learndash_user_progress_get_first_incomplete_step` + `learndash_get_step_permalink`, falling back to the course URL when complete | Slightly heavier and needs guards for completed courses, but preserves LearnDash progression semantics. | **Chosen** |
| Persist custom “last lesson” meta | Requires new storage & migration, risks diverging from LearnDash’s activity data. | Rejected |

### Decision: Header augmentation strategy
| Option | Trade-offs | Decision |
| --- | --- | --- |
| Clone/manipulate DOM via footer JS (current branding fix pattern) | Causes flicker, duplicates on mobile, and breaks if Kadence markup changes; inaccessible in focus mode. | Rejected |
| Server-side hooks (`wp_nav_menu_items`, `learndash-focus-header-nav-after`, `learndash_focus_header_user_dropdown_items`) | Keeps markup consistent across desktop/mobile & focus mode, enables capability checks, and avoids layout shift. | **Chosen** |
| Override Kadence templates in the theme | Violates “no theme hacks”, complicates updates, and affects every page. | Rejected |

## Data Flow
```
Visitor hits /aula/
    ↓ template_redirect (escuela-lms-page-content)
    ├─ calls Escuela_Aula_Dashboard_Service::get($user_id)
    │      ├─ gathers enrollments → splits active/completed → caches
    │      └─ if exactly one active and no ?aula-dashboard=1 → wp_safe_redirect to course URL (exit)
    ↓ shortcode render (la_escuela_hub)
        └─ loads templates/aula-dashboard.php → prints sections, progress, CTA
             ↓
Kadence primary menu filter injects Formaciones / Mi perfil / Salir del aula (students only)
Focus mode course view
    ↓ learndash-focus-header-nav-after adds “Volver al aula” button
    ↓ learndash_focus_header_user_dropdown_items adds fallback link
```

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `wp-content/plugins/escuela-lms-page-content/escuela-lms-page-content.php` | Modify | Add dashboard service/helpers, update shortcode output, hook template_redirect, centralize student capability checks. |
| `wp-content/plugins/escuela-lms-page-content/templates/aula-dashboard.php` | Create | Server-rendered layout for active/completed course sections, empty state, and error fallback. |
| `wp-content/plugins/escuela-lms-page-content/assets/css/aula.css` | Modify | Replace static card styles with responsive grid, progress bar, and card controls (desktop/mobile). |
| `wp-content/plugins/escuela-lms-page-content/assets/css/learndash-focus-overrides.css` | Modify | Style “Volver al aula” focus-mode button and ensure contrast/spacing. |
| `wp-content/mu-plugins/fix-header-branding.php` | Modify | Append server-side nav filters for Aula links, add focus-mode hook registration, keep existing branding script intact. |

## Interfaces / Contracts

- `Escuela_Aula_Dashboard_Service::get( int $user_id ): array` returns
  ```
  [
    'active' => [
      [
        'course_id' => 123,
        'title' => 'Formación en Meditación',
        'resume_url' => 'https://…/lesson-3/',
        'course_url' => 'https://…/courses/formacion…/',
        'progress' => [ 'completed' => 3, 'total' => 12, 'percent' => 25 ],
        'status' => 'in_progress', // or 'not_started'/'completed'
        'last_activity' => 1718150400,
        'return_url' => home_url('/aula/')
      ],
      …
    ],
    'completed' => [ … same shape … ],
    'meta' => [ 'single_active' => bool, 'has_courses' => bool, 'error' => string|null ]
  ]
  ```
- Helper `escuela_lms_is_student( ?WP_User $user ): bool` encapsulates capability/role checks (`current_user_can( 'read' )` but excluding `manage_options`, editors, `learndash_is_group_leader_user`).
- Redirect bypass query: `?aula-dashboard=1`.
- Hooks touched: `template_redirect`, `wp_nav_menu_items`, `learndash-focus-header-nav-after`, `learndash_focus_header_user_dropdown_items`, `wp_enqueue_scripts` (to register template stylesheet if split), existing `add_shortcode('la_escuela_hub', ...)`.
- Progress elements expose `role="progressbar"` with `aria-valuenow/min/max`; cards include visually hidden status text for screen readers.

## Testing Strategy

| Layer | What to Test | Approach |
|-------|--------------|----------|
| Unit (ad hoc) | `Escuela_Aula_Dashboard_Service::get` grouping & ordering | Use `wp eval` in local to dump service output for users seeded with active/completed courses; verify single-active flag, progress math, last_activity ordering. |
| Integration | Redirect + header filters | Manually visit `/aula/` as student with 1 vs 2 active courses; assert redirect headers with browser devtools and confirm Kadence menu items / focus-mode button render once. |
| E2E | UX flows & accessibility | Browser QA on desktop + mobile: logged out hero, student with no enrollments (empty state), multi-active list (cards ordered, “Continuar” link, per-card “Volver al aula”), focus mode return link keyboard focus, logout CTA returning to home. |

## Migration / Rollout

No data migration required. Clear page cache/CDN after deploying CSS. Rollback by restoring `escuela-lms-page-content.php`, removing new template, and reverting MU plugin; dashboard falls back to previous static card and header links vanish.

## Open Questions

- [x] Specs say “each active course card MUST present its own ‘Volver al aula’ control linking to `/aula/`.” On the dashboard this link would be self-referential—confirm whether the intent is a secondary action on the card or strictly the in-course header button. (Resolved: return control lives only inside course experiences; dashboard cards keep their existing CTAs.)
- [ ] Besides `subscriber`, should any other roles (e.g., `customer`) see the Aula header links and dashboard redirect logic?
