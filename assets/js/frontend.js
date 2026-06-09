/**
 * Table of Content Generator - High Performance UI/UX Engine
 */
document.addEventListener('DOMContentLoaded', () => {
    const containers = document.querySelectorAll('.table-of-content-generator-container');
    if (!containers.length) return;

    containers.forEach(container => {
        // --- 1. Collapse Logic Engine ---
        const trigger = container.querySelector('.toc-collapse-trigger-element');
        if (trigger) {
            trigger.addEventListener('click', () => {
                const isExpanded = trigger.getAttribute('aria-expanded') === 'true';
                trigger.setAttribute('aria-expanded', !isExpanded);
                container.classList.toggle('toc-state-collapsed-active');
            });
        }

        // --- 2. Smooth Scrolling Matrix Engine ---
        const links = container.querySelectorAll('.toc-navigation-link');
        links.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = link.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    const offset = 30; // Execution safety visual ceiling offset bounds
                    const bodyRect = document.body.getBoundingClientRect().top;
                    const elementRect = targetElement.getBoundingClientRect().top;
                    const elementPosition = elementRect - bodyRect;
                    const offsetPosition = elementPosition - offset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });

                    // Direct silent history update parameter matching fallback routines safely
                    if (history.pushState) {
                        history.pushState(null, null, '#' + targetId);
                    } else {
                        location.hash = '#' + targetId;
                    }
                }
            });
        });

        // --- 3. Intersection Observer Focus Trace Engine ---
        if (container.classList.contains('toc-intersection-tracking') && 'IntersectionObserver' in window) {
            const headingIds = Array.from(links).map(l => l.getAttribute('href').substring(1));
            const headings = headingIds.map(id => document.getElementById(id)).filter(h => h !== null);

            let activeLink = null;
            
            const observerOptions = {
                root: null,
                rootMargin: '0px 0px -70% 0px', // Target detection upper third segment focal layer bounds
                threshold: 0
            };

            const observerCallback = (entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const targetId = entry.target.getAttribute('id');
                        
                        // Strip focus vectors across active nodes
                        links.forEach(l => l.classList.remove('toc-viewport-active-focus'));
                        
                        const matchedLink = container.querySelector(`.toc-navigation-link[href="#${targetId}"]`);
                        if (matchedLink) {
                            matchedLink.classList.add('toc-viewport-active-focus');
                            activeLink = matchedLink;
                        }
                    }
                });
            };

            const observer = new IntersectionObserver(observerCallback, observerOptions);
            headings.forEach(heading => observer.observe(heading));
        }
    });
});