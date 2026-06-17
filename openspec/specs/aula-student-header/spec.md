# Aula Student Header Specification

## Purpose
Define Kadence header behavior for student navigation on `/aula/` and related LearnDash contexts.

## Requirements

### Requirement: Aula header action set
The system MUST inject "Formaciones", "Mi perfil", and "Salir del aula" links into the Kadence header when a logged-in student views `/aula/`, preserving existing header structure.

#### Scenario: Student on desktop Aula
- GIVEN a logged-in subscriber loads `/aula/` on desktop
- WHEN the header renders
- THEN those three links MUST appear in the main header navigation
- AND other header elements MUST remain unchanged.

#### Scenario: Student on mobile menu
- GIVEN the same student opens the mobile menu on `/aula/`
- WHEN the drawer expands
- THEN the three links MUST appear together within the mobile navigation layout.

### Requirement: Return-to-Aula link in course contexts
The system MUST surface a "Volver al aula" link within the course chrome (standard template and Focus Mode) whenever a student views a LearnDash course reachable from the Aula dashboard. The link MUST point to `/aula/` and be accessible via `the_content` filter and `learndash-focus-header-nav-after` hook.

#### Scenario: Student navigates from hub to course
- GIVEN a student clicks a course CTA from `/aula/`
- WHEN the course page renders
- THEN the CTA "Volver al aula" MUST appear, providing a direct path back to the hub.
- WHEN Focus Mode is active, the link MUST appear via `learndash-focus-header-nav-after`.

#### Scenario: Direct-access student in course
- GIVEN a student bookmarks a course URL and visits it directly
- WHEN the course loads
- THEN the same "Volver al aula" link MUST appear and navigate to `/aula/`.

### Requirement: Role-based visibility
The system MUST hide Aula-only header links for administrators, instructors, group leaders, and guests while keeping their default navigation intact.

#### Scenario: Administrator on Aula
- GIVEN an administrator visits `/aula/`
- WHEN the header renders
- THEN the Aula header links MUST NOT appear
- AND the admin header MUST match existing behavior.

#### Scenario: Guest visitor
- GIVEN a logged-out visitor hits `/aula/`
- WHEN the header renders
- THEN the Aula header links MUST NOT appear
- AND the guest MUST be directed to existing login or marketing links.

### Requirement: Salir del aula control
The "Salir del aula" link MUST log the student out via WordPress' logout mechanics and redirect them to the public home (or configured login) without leaving stale sessions.

#### Scenario: Successful logout
- GIVEN a student is on `/aula/`
- WHEN they click "Salir del aula"
- THEN `wp_logout_url( home_url() )` MUST execute, log them out, and redirect to home showing a logged-out header.

#### Scenario: Logout nonce failure
- GIVEN the logout nonce is invalid
- WHEN the student triggers "Salir del aula"
- THEN the system MUST present an error or retry message
- AND the student MUST remain logged in with no partial logout.

## Data & Permission Dependencies
- Uses Kadence hooks like `kadence_header_navigation` or `kadence_mobile_header` within the MU plugin to inject links.
- Capability checks MUST rely on `current_user_can( 'read' )` and negative checks for roles containing `manage_options`, `group_leader`, or LearnDash admin caps.
- Logout URL MUST incorporate `wp_logout_url( home_url() )` with `wp_create_nonce( 'log-out' )` and guard against open redirects.

## Validation
- Validate header on desktop and mobile for student, admin, and guest accounts.
- Confirm "Volver al aula" visibility inside LearnDash Focus Mode and non-focus templates.
- Verify logout clears cookies and returns to home without exposing protected content.
