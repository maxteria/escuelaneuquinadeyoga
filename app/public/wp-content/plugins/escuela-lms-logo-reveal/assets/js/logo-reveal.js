/* ==========================================================================
   ENY Logo Reveal — JavaScript
   ========================================================================== */

(function () {
  'use strict';

  var hero = document.querySelector('.enya-hero');
  var logoReveal = document.querySelector('.eny-logo-reveal');

  if (!hero || !logoReveal) return;

  /* ---- Reduced motion fallback ---- */
  var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (prefersReducedMotion) {
    logoReveal.setAttribute('data-progress', 'static');
    return;
  }

  /* ---- Breathing animation via CSS variable ---- */
  var breathPhase = 0;

  function updateBreath(timestamp) {
    breathPhase = (timestamp % 6000) / 6000;
    var breathValue = Math.sin(breathPhase * Math.PI * 2) * 0.5 + 0.5;
    document.documentElement.style.setProperty('--eny-logo-breath', breathValue.toFixed(4));
    requestAnimationFrame(updateBreath);
  }

  requestAnimationFrame(updateBreath);

  /* ---- Scroll-driven assembly ---- */
  var ticking = false;

  function onScroll() {
    if (!ticking) {
      requestAnimationFrame(updateScroll);
      ticking = true;
    }
  }

  function updateScroll() {
    ticking = false;

    var heroRect = hero.getBoundingClientRect();
    var heroHeight = heroRect.height;
    var scrolled = window.scrollY;

    /* Progress: 0 = top of hero visible, 1 = hero scrolled past */
    var progress = Math.max(0, Math.min(1, scrolled / heroHeight));

    document.documentElement.style.setProperty('--eny-logo-progress', progress.toFixed(4));

    if (progress > 0.05) {
      logoReveal.setAttribute('data-progress', 'assembling');
    } else {
      logoReveal.setAttribute('data-progress', 'breathing');
    }
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  updateScroll();

})();