# Aula Course Dashboard Specification

## Purpose
Define `/aula/` as the student dashboard powered by LearnDash, including redirects, listings, and fallback messaging.

## Requirements

### Requirement: Active enrollment listing
The system MUST render every in-progress LearnDash enrollment for the current student at the top of `/aula/` using supported LearnDash APIs.

#### Scenario: Student with multiple active courses
- GIVEN a logged-in student enrolled in two active courses
- WHEN they visit `/aula/`
- THEN an "Activas" section MUST list both courses with title, progress, and continue action
- AND items MUST be ordered by most recent activity
- AND each card MUST surface the standard progression CTAs ("Continuar" resume link and "Ver programa" overview) without adding a "Volver al aula" control.

#### Scenario: Status refresh after completion
- GIVEN a student completes a course and reloads `/aula/`
- WHEN LearnDash marks the course complete
- THEN the course MUST disappear from the active section on the next page load.

### Requirement: Completed enrollment section
The system MUST show a "Completadas" section containing every LearnDash enrollment marked complete, including completion status cues.

#### Scenario: Completed-only learner
- GIVEN a student with no active courses and three completed courses
- WHEN they open `/aula/`
- THEN the dashboard MUST render the completed section with all three entries
- AND MUST omit an empty active section.

#### Scenario: Mixed progress learner
- GIVEN a student with one active and one completed course
- WHEN `/aula/` loads
- THEN the active course MUST appear only under "Activas"
- AND the completed course MUST appear only under "Completadas".

### Requirement: Hub-first behavior (no auto-redirect)
The system MUST render `/aula/` as the landing hub for ALL authenticated students regardless of enrollment count. Students MUST NOT be automatically redirected to a course, even when they have exactly one active enrollment. A persistent "Volver al aula" link MUST be available within course pages (Focus Mode and standard) for navigation back to the hub.

#### Scenario: Exactly one active course
- GIVEN a student with a single active course
- WHEN they request `/aula/`
- THEN the response MUST be 200 OK with the dashboard rendered (no redirect)
- AND the course card MUST show "Continuar" and "Ver programa" CTAs
- AND the resulting course page MUST show a visible "Volver al aula" link pointing to `/aula/`.

#### Scenario: Zero or multiple active courses
- GIVEN a student with zero or more than one active enrollment
- WHEN they hit `/aula/`
- THEN no redirect MUST occur
- AND the dashboard cards MUST retain only their course progression CTAs (e.g., "Continuar", "Ver programa") without introducing a "Volver al aula" button.

### Requirement: Empty-state guidance
The system MUST display an empty-state panel when the student has no active or completed enrollments, including copy explaining the state and a call-to-action linking to the public Formaciones page.

#### Scenario: Student without enrollments
- GIVEN a logged-in student with zero LearnDash enrollments
- WHEN they load `/aula/`
- THEN the empty-state panel MUST appear
- AND its primary action MUST link to the public Formaciones catalog.

#### Scenario: LearnDash error fallback
- GIVEN LearnDash APIs fail to return enrollment data
- WHEN `/aula/` renders
- THEN the dashboard MUST show a temporary error message
- AND MUST still present the Formaciones link.

### Requirement: Role and access safeguards
The system MUST require authentication, scope the dashboard to student-capable roles (`read` without instructor/admin permissions), and fall back for other audiences.

#### Scenario: Administrator visit
- GIVEN an administrator visits `/aula/`
- WHEN the page executes
- THEN the student dashboard MUST NOT render
- AND existing admin behavior MUST continue.

#### Scenario: Guest visit
- GIVEN a guest user requests `/aula/`
- WHEN the request is handled
- THEN the guest MUST be routed to the public Aula intro or login per current flow
- AND no student course data MUST be exposed.

## Data & Permission Dependencies
- Use LearnDash helpers such as `learndash_user_get_enrolled_courses()` or `[ld_course_list]` with status filters; direct SQL MUST NOT be used.
- Determine roles via `current_user_can( 'read' )` while excluding `group_leader`, `administrator`, and other management roles.
- Redirects MUST rely on `wp_safe_redirect( learndash_get_course_url() )` and stop execution afterward.

## Validation
- Test with fixtures covering single active, multiple active, completed-only, unenrolled, and admin users.
- Verify redirect headers and the presence of "Volver al aula" using browser devtools.
- Confirm empty-state copy and Formaciones link render on desktop and mobile.
