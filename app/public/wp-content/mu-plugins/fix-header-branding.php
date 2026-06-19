<?php
/**
 * Fix: Ensure logo + site title appear in header at all breakpoints.
 *
 * Problem: Kadence header layout has no branding item configured for desktop,
 * and mobile/tablet hide the site title.
 *
 * Solution:
 *   1. Inject branding into desktop header via JS (cloned from mobile)
 *   2. Force site title visible via CSS at all breakpoints
 */
add_action('wp_footer', function() {
    ?>
    <script>
    (function() {
        var leftSection = document.querySelector('.site-header-upper-wrap .site-header-main-section-left');
        if (!leftSection || leftSection.children.length > 0) return;
        var mobileBranding = document.querySelector('.site-mobile-header-wrap .site-branding');
        if (!mobileBranding) return;
        var clone = mobileBranding.cloneNode(true);
        clone.classList.remove('mobile-site-branding');
        clone.classList.add('desktop-site-branding');
        leftSection.insertBefore(clone, leftSection.firstChild);
    })();
    (function() {
        var btn = document.querySelector('.header-button');
        if (!btn || btn.dataset.enyfIcon) return;
        btn.dataset.enyfIcon = '1';
        btn.style.display = 'inline-flex';
        btn.style.alignItems = 'center';
        btn.style.gap = '8px';
        var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('width', '16');
        svg.setAttribute('height', '16');
        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('fill', 'none');
        svg.setAttribute('stroke', 'currentColor');
        svg.setAttribute('stroke-width', '2');
        svg.setAttribute('stroke-linecap', 'round');
        svg.setAttribute('stroke-linejoin', 'round');
        svg.setAttribute('aria-hidden', 'true');
        svg.innerHTML = '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>';
        btn.insertBefore(svg, btn.firstChild);
    })();
    </script>
    <?php
});

add_action('wp_head', function() {
    ?>
    <style>
    /* Force site title visible in mobile branding */
    .mobile-site-branding .site-title {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        font-size: clamp(13px, 1.5vw, 18px);
        white-space: nowrap;
        line-height: 1.2;
    }
    /* Logo sizing for mobile */
    .mobile-site-branding .custom-logo {
        max-height: 42px;
        width: auto;
    }
    /* Desktop branding */
    .desktop-site-branding .site-title {
        display: block !important;
        font-size: clamp(16px, 1.3vw, 22px);
        white-space: nowrap;
        line-height: 1.2;
    }
    .desktop-site-branding .custom-logo {
        max-height: 48px;
        width: auto;
    }
    /* Branding layout */
    .site-branding {
        display: flex !important;
        align-items: center;
        gap: 10px;
    }
    .site-branding .brand {
        display: flex !important;
        align-items: center;
        gap: 10px;
    }
    </style>
    <?php
});
