/* ==========================================================================
   Smart University Complaint Portal — client-side script
   ========================================================================== */

(function () {
    'use strict';

    /**
     * Auto-dismiss flash alerts after 5 seconds.
     */
    function autoDismissAlerts() {
        document.querySelectorAll('.alert.alert-dismissible').forEach((alert) => {
            setTimeout(() => {
                if (window.bootstrap && bootstrap.Alert) {
                    bootstrap.Alert.getOrCreateInstance(alert).close();
                }
            }, 5000);
        });
    }

    /**
     * Block double-submission of forms (helps prevent duplicate complaints).
     */
    function preventDoubleSubmit() {
        document.querySelectorAll('form').forEach((form) => {
            form.addEventListener('submit', function () {
                const button = form.querySelector('button[type="submit"]');
                if (!button) return;
                button.disabled = true;
                const original = button.innerHTML;
                button.dataset.originalText = original;
                button.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1"></span>Please wait…';

                // Re-enable after 10s as a safety fallback
                setTimeout(() => {
                    button.disabled = false;
                    button.innerHTML = original;
                }, 10000);
            });
        });
    }

    /**
     * Confirm before navigating to logout / destructive links marked data-confirm="...".
     */
    function confirmDestructiveLinks() {
        document.querySelectorAll('[data-confirm]').forEach((el) => {
            el.addEventListener('click', function (e) {
                const message = el.dataset.confirm || 'Are you sure?';
                if (!window.confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    }

    /**
     * Add shadow to navbar after a few px of scroll.
     */
    function navbarScrollEffect() {
        const nav = document.getElementById('mainNavbar');
        if (!nav) return;
        const onScroll = () => {
            if (window.scrollY > 8) nav.classList.add('navbar-scrolled');
            else nav.classList.remove('navbar-scrolled');
        };
        document.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    document.addEventListener('DOMContentLoaded', function () {
        autoDismissAlerts();
        preventDoubleSubmit();
        confirmDestructiveLinks();
        navbarScrollEffect();
    });
})();
