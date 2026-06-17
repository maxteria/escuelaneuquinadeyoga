# Escuela LMS Custom Plugins

Use this skill when creating or modifying project-owned plugins.

Project-owned plugins:
- escuela-lms-whatsapp-float
- escuela-lms-student-access
- escuela-lms-page-content
- future custom plugins

Rules:
- Never modify WordPress core.
- Never modify plugin core.
- Keep each plugin small and focused.
- Prefer reversible plugins over theme hacks.
- Prefix functions/classes/hooks with enya_ or escuela_lms_.
- Escape output with esc_html, esc_attr, esc_url.
- Sanitize input before use.
- Enqueue CSS/JS properly.
- Avoid inline CSS/JS unless justified.
- Do not store secrets in code.
- Validate with WP-CLI and Chrome MCP after changes.

Plugin boundaries:
- WhatsApp floating button → escuela-lms-whatsapp-float
- Student access redirects/admin bar → escuela-lms-student-access
- Public page shortcodes + LearnDash visual overrides → escuela-lms-page-content

Before modifying:
1. Inspect current plugin file.
2. Report intended changes.
3. Modify only approved plugin.
4. Validate frontend/admin.
5. Update progress/decisions/tasks when relevant.