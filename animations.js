/**
 * AGRIC DSS - Scroll Animation Engine
 * Intersection Observer for scroll-triggered reveals
 */
(function() {
    'use strict';

    // Scroll-triggered animations via Intersection Observer
    function initScrollAnimations() {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        observer.unobserve(entry.target); // Only animate once
                    }
                });
            },
            {
                threshold: 0.1,
                rootMargin: '0px 0px -40px 0px'
            }
        );

        // Observe all elements with data-anim attribute
        document.querySelectorAll('[data-anim]').forEach(el => {
            observer.observe(el);
        });
    }

    // Auto-tag common elements for scroll animation if not already tagged
    function autoTagElements() {
        // Cards that should animate on scroll (only if they're below the fold)
        const selectors = [
            { sel: '.stat-card', anim: 'fade-up' },
            { sel: '.action-card', anim: 'fade-up' },
            { sel: '.farmer-card', anim: 'fade-up' },
            { sel: '.buyer-card', anim: 'fade-up' },
            { sel: '.product-card', anim: 'fade-up' },
            { sel: '.notif-card', anim: 'fade-left' },
            { sel: '.card-box', anim: 'fade-up' },
            { sel: '.panel-card', anim: 'fade-up' },
            { sel: '.quick-link-card', anim: 'fade-up' },
            { sel: '.gallery-card', anim: 'fade-up' },
            { sel: '.feature-card', anim: 'fade-up' },
            { sel: '.step-card', anim: 'fade-up' },
            { sel: '.cta-section', anim: 'zoom-in' }
        ];

        const viewportHeight = window.innerHeight;

        selectors.forEach(({ sel, anim }) => {
            document.querySelectorAll(sel).forEach((el, index) => {
                // Only auto-tag elements that are below the fold
                const rect = el.getBoundingClientRect();
                if (rect.top > viewportHeight * 0.85) {
                    if (!el.hasAttribute('data-anim')) {
                        el.setAttribute('data-anim', anim);
                        // Stagger delay based on sibling index
                        el.setAttribute('data-delay', Math.min(index + 1, 8));
                    }
                }
            });
        });
    }

    // Animated counter for stat numbers
    function animateCounters() {
        document.querySelectorAll('.stat-info h4, .mini-stat h5, .header-stat h3').forEach(el => {
            const text = el.textContent;
            const match = text.match(/[\d,.]+/);
            if (!match) return;

            const finalNum = parseFloat(match[0].replace(/,/g, ''));
            if (isNaN(finalNum) || finalNum === 0) return;

            const prefix = text.substring(0, text.indexOf(match[0]));
            const suffix = text.substring(text.indexOf(match[0]) + match[0].length);
            const hasDecimals = match[0].includes('.');
            const duration = 800;
            const start = performance.now();

            function update(now) {
                const elapsed = now - start;
                const progress = Math.min(elapsed / duration, 1);
                // Ease out cubic
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = finalNum * eased;

                if (hasDecimals) {
                    el.textContent = prefix + current.toLocaleString('en', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + suffix;
                } else {
                    el.textContent = prefix + Math.round(current).toLocaleString() + suffix;
                }

                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }

            // Only animate if element is visible
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        requestAnimationFrame(update);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.3 });

            observer.observe(el);
        });
    }

    // Initialize everything on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            autoTagElements();
            initScrollAnimations();
            animateCounters();
        });
    } else {
        autoTagElements();
        initScrollAnimations();
        animateCounters();
    }
})();
