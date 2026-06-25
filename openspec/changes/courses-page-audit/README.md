# courses-page-audit — README

Feature flag
- Option: `eny_override_courses_landing` (default: `true`). Set to `false` to roll back to the original plugin handler (best-effort; closures may prevent automatic re-registration).

Cache key
- Transient prefix: `eny_courses_landing_grid_v{template_mtime}` — template mtime is used to incorporate template updates in the key.
- TTL: 300 seconds.

Rollback
- Disable override: `wp option update eny_override_courses_landing 0 --path=app/public`
- Or remove/rename the mu-plugin file: `wp-content/mu-plugins/eny-courses-landing.php` and flush caches.

CLI
- Flush cache: `php -c C:\\Users\\Usuario\\AppData\\Local\\Temp\\opencode\\php-cli.ini C:\\Users\\Usuario\\AppData\\Local\\Temp\\opencode\\wp-cli.phar --path=app/public eny courses-landing flush`

Activation
- The child theme `kadence-child` is created by this change. Activate locally with:
  `php -c C:\\Users\\Usuario\\AppData\\Local\\Temp\\opencode\\php-cli.ini C:\\Users\\Usuario\\AppData\\Local\\Temp\\opencode\\wp-cli.phar --path=app/public theme activate kadence-child`

Notes
- The template partial preserves the original `.enyf-*` markup; the legacy "Formación destacada" module was intentionally removed for this slice.
