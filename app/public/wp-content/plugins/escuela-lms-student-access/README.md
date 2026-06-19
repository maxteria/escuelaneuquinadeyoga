# Escuela LMS Student Access

WordPress plugin that customizes the student experience for the Escuela Neuquina de Yoga LMS site.

## What it does

- **Aula Virtual access control**: redirects non-students away from the `/aula/` dashboard and shows a friendly login CTA.
- **Admin bar & dashboard guard**: hides the WordPress admin bar from subscribers and redirects `wp-admin` attempts to the student profile.
- **Single-course redirect** (feature flag): when enabled, students with access to exactly one course are redirected straight to that course from `/aula/`.
- **Header user component**: replaces the legacy "Mi Aula" dropdown with a stateful control in the Kadence primary menu.

## Header user component

File: `includes/user-header-component.php`
Styles: `assets/css/user-header-component.css`
Script: `assets/js/user-header-component.js`

### Behavior

- **Logged out**: shows a pill CTA labeled **"Entrar al aula"** in the primary menu, linking to `/aula/`.
- **Logged in**: shows the user's greeting, avatar and a dropdown with links to the Aula and logout.
- Works on desktop and mobile (Kadence drawer).

### Styling notes

The CTA intentionally matches the LearnDash login/register button defined in `escuela-lms-page-content/assets/css/aula.css`:

- Pill shape (`border-radius: 999px`).
- Accent green background (`--aula-accent`, fallback to Kadence button color).
- White text and icon.
- Subtle shadow that grows on hover.

Because the CTA lives inside a Kadence menu item, Kadence's more specific selectors try to set the link color to the menu text color and force `text-transform: capitalize`. The component CSS uses `!important` on `color` and `text-transform` to keep the intended appearance.

## Feature flags

Enable the single-course redirect with:

```bash
wp option update escuela_lms_enable_single_course_redirect 1
```

## Changelog

### Unreleased
- Header CTA restyled to match the existing LearnDash login button.
- Fixed CTA text/icon color being overridden by Kadence menu styles.
- Preserved lowercase "Entrar al aula" label against Kadence capitalization.
