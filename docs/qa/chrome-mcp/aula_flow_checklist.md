# Chrome MCP — Aula Virtual Flow Checklist

## Setup
- URL base: `https://escuelaneuquinadeyoga.local`
- Viewports: desktop (1440×900), mobile (375×812)
- Clear cookies/session before guest tests.

---

## 1. Guest /aula/

**Steps:**
1. Open `/aula/` as guest (no session).
2. Wait for page to fully load.

**Assertions:**
- [ ] Page loads without 3xx redirect.
- [ ] A login button or modal trigger is visible (text "Iniciar sesión" or "Ingresar").
- [ ] Registration option or link is visible.
- [ ] No course content is shown (guest cannot access enrolled content).

**Desktop screenshot:** `screenshots/01-guest-aula-desktop.png`  
**Mobile screenshot:** `screenshots/01-guest-aula-mobile.png` (375px)

---

## 2. /registration-2/ Registration page

**Steps:**
1. Navigate to `/registration-2/`.
2. Wait for the LearnDash registration block to render.

**Assertions:**
- [ ] Page loads with HTTP 200.
- [ ] Registration form fields are present (name, email, password, etc.).
- [ ] All visible text is in Spanish (no English-only labels).
- [ ] No layout issues (scrollbars, overlapping).

**Screenshot:** `screenshots/02-registration-page.png`

---

## 3. /registro-completado/ Success page

**Steps:**
1. Navigate to `/registro-completado/`.

**Assertions:**
- [ ] Page loads with HTTP 200.
- [ ] Success message is shown (e.g., "registro completado" or similar).
- [ ] Link back to `/aula/` is present.
- [ ] Link to `/courses/` is present.
- [ ] Text is in Spanish.

**Screenshot:** `screenshots/03-registration-success.png`

---

## 4. alumno_demo logged in — /aula/

**Steps:**
1. Log in as `alumno_demo` (ID 2).
2. Navigate to `/aula/`.

**Assertions:**
- [ ] Page loads with HTTP 200 (no automatic redirect to course).
- [ ] Dashboard content is visible (heading "Tu aula virtual" or similar).
- [ ] Active courses are listed (course card visible).
- [ ] "Ver formaciones" button is visible.
- [ ] "Cerrar sesión" or logout option is visible in header/nav.

**Desktop screenshot:** `screenshots/04-alumno-aula-desktop.png`  
**Mobile screenshot:** `screenshots/04-alumno-aula-mobile.png` (375px)

---

## 5. Course page — "Volver al aula" CTA

**Steps:**
1. While logged in as alumno_demo, navigate to a course page (e.g., `/courses/formacion-en-meditacion/`).
2. If in Focus Mode, verify CTA in Focus Mode chrome.

**Assertions:**
- [ ] Course page loads.
- [ ] "Volver al aula" link/button is visible.
- [ ] Link points to `/aula/`.
- [ ] The CTA is keyboard-accessible (can tab to it).

**Desktop screenshot:** `screenshots/05-course-cta-desktop.png`  
**Focus Mode screenshot (if applicable):** `screenshots/05-course-cta-focusmode.png`

**Mobile:**  
- [ ] CTA is visible at 375px without horizontal scroll.  
**Screenshot:** `screenshots/05-course-cta-mobile.png`

---

## 6. Logout

**Steps:**
1. While logged in as alumno_demo, click "Cerrar sesión" / logout.

**Assertions:**
- [ ] After logout, user is redirected to `/aula/`.
- [ ] Page shows guest view (login modal/button).
- [ ] No private content is visible.
- [ ] Back/forward navigation does not expose private content (session cleared).

**Screenshot:** `screenshots/06-logout-guest-aula.png`

---

## 7. Subscriber /wp-admin/ redirect

**Steps:**
1. Log in as alumno_demo (subscriber role).
2. Navigate to `/wp-admin/`.

**Assertions:**
- [ ] User is redirected to `/profile/` (not to dashboard).
- [ ] After redirect, no admin menu or dashboard is visible.

**Screenshot:** `screenshots/07-subscriber-wpadmin-redirect.png`

---

## 8. Admin /wp-admin/ access

**Steps:**
1. Log in as admin.
2. Navigate to `/wp-admin/`.

**Assertions:**
- [ ] Admin dashboard loads normally (HTTP 200).
- [ ] Users menu is accessible (Users → All Users).
- [ ] Admin bar is visible.

**Screenshot:** `screenshots/08-admin-dashboard.png`

---

## 9. Mobile viewport checks

**Steps:**
1. Set viewport to 375×812.
2. Test key pages: `/aula/` (guest), `/aula/` (logged in), `/courses/formacion-en-meditacion/`.

**Assertions:**
- [ ] No horizontal scroll on any of the tested pages.
- [ ] "Volver al aula" CTA is visible and not cut off.
- [ ] Tap targets are appropriately sized (≥44px).
- [ ] Dashboard cards stack vertically and are readable.

**Screenshots:**
- `screenshots/09-mobile-guest-aula.png`
- `screenshots/09-mobile-alumno-aula.png`
- `screenshots/09-mobile-course-cta.png`

---

## Summary

| Test | Pass | Notes |
|------|------|-------|
| 1. Guest /aula/ | ☐ | |
| 2. Registration page | ☐ | |
| 3. Registration success | ☐ | |
| 4. alumno_demo /aula/ | ☐ | |
| 5. "Volver al aula" CTA | ☐ | |
| 6. Logout | ☐ | |
| 7. Subscriber /wp-admin/ | ☐ | |
| 8. Admin /wp-admin/ | ☐ | |
| 9. Mobile | ☐ | |

**QA Verdict:** [PASS / FAIL / BLOCKED]  
**Date:**  
**Tester:**
