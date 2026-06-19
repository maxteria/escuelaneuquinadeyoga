# Escuela LMS Page Content

WordPress plugin that owns page-specific content and layouts for the Escuela Neuquina de Yoga LMS site.

## What it does

- **Aula Virtual landing** (`/aula/`): renders the guest, restricted and authenticated dashboard views for the virtual classroom.
- **LearnDash template overrides**: custom layouts for courses, lessons, topics and Focus Mode chrome.
- **Page-specific CSS**: scoped styles for `/aula/`, formaciones landing, La Escuela, etc.
- **Content injections**: adds helper markup and shortcodes used across LMS pages.

## Aula Virtual guest view

File: `escuela-lms-page-content.php`  
Styles: `assets/css/aula.css`

### Behavior

- Logged-out visitors see a hero section with the LearnDash login form rendered **inline**.
- The original LearnDash shortcode opens a modal on click; this plugin moves the modal back into the hero and overrides its styles so the user can type username/password immediately.
- The registration CTA is shown below the login form.

### How the inline form works

1. The shortcode `[learndash_login login_label="Iniciar sesión o registrarse"]` is rendered inside `[data-aula-login]`.
2. LearnDash moves the login modal wrapper to `<body>` via JavaScript.
3. An inline script uses a `MutationObserver` to detect when the wrapper is appended and moves it back into the hero CTA container.
4. `aula.css` hides the trigger button, forces the modal wrapper to be visible, and styles the form fields and buttons to match the site.

### Files changed

- `escuela-lms-page-content.php`: changed the LearnDash shortcode attribute and added the inline modal-reposition script.
- `assets/css/aula.css`: added overrides to show the login form inline and style it consistently with the brand.

## Changelog

### Unreleased
- Aula guest view now shows the login form inline instead of inside a modal, removing the extra click to log in.
