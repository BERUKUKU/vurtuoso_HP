// Match the header to the surface currently passing behind it.
document.addEventListener('DOMContentLoaded', () => {
    const nav = document.getElementById('nav');
    if (!nav) return;
    const darkSurfaces = [...document.querySelectorAll('.hero-scene, .subpage-hero, #philosophy, .footer')];
    let queued = false;
    const update = () => {
        queued = false;
        const headerHeight = nav.getBoundingClientRect().height;
        document.documentElement.style.setProperty('--site-header-height', `${headerHeight}px`);
        const sampleY = headerHeight / 2;
        const dark = darkSurfaces.some((surface) => {
            const rect = surface.getBoundingClientRect();
            return rect.top <= sampleY && rect.bottom > sampleY;
        });
        nav.classList.toggle('on-dark', dark);
        nav.classList.toggle('scrolled', window.scrollY > 32);
    };
    const schedule = () => {
        if (queued) return;
        queued = true;
        requestAnimationFrame(update);
    };
    update();
    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', schedule);
    window.addEventListener('load', update, { once: true });
    document.fonts.ready.then(update);
});

// Mobile navigation
document.addEventListener('DOMContentLoaded', () => {
    const nav = document.getElementById('nav');
    const toggle = document.querySelector('.nav-toggle');
    const links = document.querySelector('.nav-links');
    if (!nav || !toggle || !links) return;

    const closeMenu = () => {
        links.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'メニューを開く');
    };

    toggle.addEventListener('click', () => {
        const isOpen = toggle.getAttribute('aria-expanded') === 'true';
        links.classList.toggle('is-open', !isOpen);
        toggle.setAttribute('aria-expanded', String(!isOpen));
        toggle.setAttribute('aria-label', isOpen ? 'メニューを開く' : 'メニューを閉じる');
    });

    links.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeMenu);
    });

    document.addEventListener('click', (event) => {
        if (nav.contains(event.target)) return;
        closeMenu();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeMenu();
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) closeMenu();
    });
});

// A small depth shift follows native scrolling without pinning the page.
document.addEventListener('DOMContentLoaded', () => {
    const hero = document.getElementById('hero');
    const image = hero?.querySelector('.hero-img');
    const mist = hero?.querySelector('.hero-mist');
    const scene = hero?.closest('.hero-scene');
    if (!hero || !image) return;
    const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let ticking = false;

    const update = () => {
        ticking = false;
        if (motion.matches) {
            (scene || hero).style.removeProperty('--hero-content-opacity');
            image.style.removeProperty('translate');
            if (mist) mist.style.removeProperty('transform');
            return;
        }
        const rect = hero.getBoundingClientRect();
        if (scene) {
            const travelled = Math.max(0, -scene.getBoundingClientRect().top);
            const fade = Math.min(1, Math.max(0, (travelled / rect.height - 0.02) / 0.2));
            scene.style.setProperty('--hero-content-opacity', String(1 - fade));
        }
        const progress = Math.min(1, Math.max(0, -rect.top / rect.height));
        // Stay inside the existing image crop so no empty edges appear.
        image.style.translate = `0 ${progress * rect.height * 0.04}px`;
        if (mist) mist.style.transform = `translateY(${progress * 18}px)`;
    };
    const schedule = () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(update);
    };
    update();
    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', schedule);
    motion.addEventListener('change', schedule);
});

// Set current year in footer
document.addEventListener('DOMContentLoaded', () => {
    const yearSpan = document.getElementById('year');
    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }
});

// Create animated particles
function createParticles(containerId, count) {
    const container = document.getElementById(containerId);
    if (!container) return;

    for (let i = 0; i < count; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = `${Math.random() * 100}%`;
        particle.style.top = `${Math.random() * 100}%`;
        particle.style.animationDelay = `${Math.random() * 2}s`;
        particle.style.animationDuration = `${3 + Math.random() * 4}s`;
        container.appendChild(particle);
    }
}

// Create philosophy particles
function createPhilosophyParticles() {
    const container = document.getElementById('philosophyParticles');
    if (!container) return;

    for (let i = 0; i < 15; i++) {
        const particle = document.createElement('div');
        particle.className = 'philosophy-particle';
        particle.style.left = `${Math.random() * 100}%`;
        particle.style.top = `${Math.random() * 100}%`;
        particle.style.animationDelay = `${Math.random() * 2}s`;
        particle.style.animationDuration = `${4 + Math.random() * 3}s`;
        container.appendChild(particle);
    }
}

// Initialize particles
document.addEventListener('DOMContentLoaded', () => {
    createParticles('particles', 20);
    createPhilosophyParticles();
});

// Intersection Observer for fade-up animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '-100px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, observerOptions);

// Observe all fade-up elements
document.addEventListener('DOMContentLoaded', () => {
    const fadeUpElements = document.querySelectorAll('.fade-up');
    fadeUpElements.forEach(el => observer.observe(el));
});

// Service cards shine animation on load
document.addEventListener('DOMContentLoaded', () => {
    const serviceCards = document.querySelectorAll('.service-card');
    serviceCards.forEach((card, index) => {
        const shineElement = card.querySelector('.service-shine-on-load');
        if (shineElement) {
            setTimeout(() => {
                shineElement.style.animation = 'none';
                shineElement.style.transform = 'translateX(-100%) skewX(-20deg)';
                
                setTimeout(() => {
                    shineElement.style.animation = '';
                    shineElement.style.transition = 'transform 1s ease';
                    shineElement.style.transform = 'translateX(200%) skewX(-20deg)';
                }, 100);
            }, 500 + index * 300);
        }
    });
});

// Flow section dots and bottom lines animation
document.addEventListener('DOMContentLoaded', () => {
    const flowCardsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const dot = entry.target.querySelector('.flow-dot');
                const bottomLine = entry.target.querySelector('.flow-bottom-line');
                
                if (dot) {
                    setTimeout(() => {
                        dot.style.animation = 'none';
                        dot.style.transform = 'translateX(-50%) scale(1)';
                    }, 400 + Array.from(entry.target.parentElement.children).indexOf(entry.target) * 100);
                }
                
                if (bottomLine) {
                    setTimeout(() => {
                        bottomLine.style.transition = 'transform 0.8s ease';
                        bottomLine.style.transform = 'scaleX(1)';
                    }, 800 + Array.from(entry.target.parentElement.children).indexOf(entry.target) * 100);
                }
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '-100px 0px'
    });

    const flowCards = document.querySelectorAll('.flow-card');
    flowCards.forEach(card => flowCardsObserver.observe(card));
});

// Flow line animation
document.addEventListener('DOMContentLoaded', () => {
    const flowLine = document.querySelector('.flow-line');
    if (!flowLine) return;

    const flowLineObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const gradientLine = document.createElement('div');
                gradientLine.style.cssText = `
                    height: 100%;
                    background: linear-gradient(90deg, #fb7185, #8b5cf6, #64748b, #6366f1);
                    transform: scaleX(0);
                    transform-origin: left;
                    transition: transform 1.5s ease 0.5s;
                `;
                flowLine.appendChild(gradientLine);
                
                setTimeout(() => {
                    gradientLine.style.transform = 'scaleX(1)';
                }, 100);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '-100px 0px'
    });

    const flowSection = document.querySelector('.flow');
    if (flowSection) {
        flowLineObserver.observe(flowSection);
    }
});

// Gallery scroll-in reveal with staggered timing for a luxe feel
document.addEventListener('DOMContentLoaded', () => {
    const galleryRows = document.querySelectorAll('.gallery-row');
    if (!galleryRows.length) return;

    const galleryObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.22,
        rootMargin: '0px 0px -3%'
    });

    galleryRows.forEach((row, rowIndex) => {
        const cards = Array.from(row.querySelectorAll('.gallery-card'));
        if (!cards.length) return;

        const shuffledCards = [...cards].sort(() => Math.random() - 0.5);
        const baseDelays = new Map();

        shuffledCards.forEach((card, order) => {
            baseDelays.set(card, order * 120);
        });

        let previousDelay = -Infinity;
        cards.forEach(card => {
            let delay = baseDelays.get(card) ?? 0;
            if (delay - previousDelay < 160) {
                delay = previousDelay + 160;
            }

            const rowOffset = rowIndex > 0 ? -200 : 0;
            const finalDelay = Math.max(0, delay + rowOffset);
            card.style.setProperty('--gallery-delay', `${finalDelay}ms`);
            previousDelay = delay;

            galleryObserver.observe(card);
        });
    });
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href === '#') return;
        
        e.preventDefault();
        const target = document.querySelector(href);
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Set active nav link based on scroll position
window.addEventListener('scroll', () => {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    const isHomePage = Boolean(document.getElementById('hero'));

    if (!isHomePage) {
        const currentPage = window.location.pathname.split('/').pop() || '/';
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            link.classList.toggle('active', href === currentPage);
        });
        return;
    }
    
    let current = '';
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        if (window.pageYOffset >= sectionTop - 200) {
            current = section.getAttribute('id');
        }
    });

    // For homepage, check if we're at the top
    if (window.pageYOffset < 300) {
        current = 'hero';
    }

    navLinks.forEach(link => {
        link.classList.remove('active');
        const href = link.getAttribute('href');
        
        // Handle different page links
        if ((href === '/' || href === 'index.html') && current === 'hero') {
            link.classList.add('active');
        } else if (href.includes('#') && href.includes(current)) {
            link.classList.add('active');
        }
    });
});

// Shared page background switching
document.addEventListener('DOMContentLoaded', () => {
    const bgLayer = document.getElementById('scrollBgGlobal');
    if (!bgLayer || document.body.classList.contains('home-page') || document.body.classList.contains('subpage')) return;

    const sectionToneMap = [
        { selector: '#gallery', tone: 'soft' },
        { selector: '#service', tone: 'base' },
        { selector: '#flow', tone: 'soft' },
        { selector: '#contact', tone: 'base' },
        { selector: '.about-section', tone: 'base' },
        { selector: '.contact-form-section', tone: 'soft' },
        { selector: '.privacy-section', tone: 'base' },
        { selector: '.thanks-shell', tone: 'soft' },
        { selector: '.footer', tone: 'base' }
    ];

    const trackedSections = sectionToneMap
        .map(({ selector, tone }) => {
            const el = document.querySelector(selector);
            return el ? { el, tone } : null;
        })
        .filter(Boolean);

    if (!trackedSections.length) return;

    const startSection = trackedSections[0].el;
    const endSection = trackedSections[trackedSections.length - 1].el;
    const pageTop = (el) => el.getBoundingClientRect().top + window.scrollY;
    const pageBottom = (el) => pageTop(el) + el.offsetHeight;

    const updateVisibility = () => {
        const y = window.scrollY;
        // The lower plane must be visible as soon as Gallery enters below Philosophy.
        const revealOffset = document.querySelector('.hero-scene') ? window.innerHeight : 0;
        const startY = pageTop(startSection) - revealOffset - 24;
        const endY = pageBottom(endSection) - window.innerHeight + 24;
        const visible = y >= startY && y <= Math.max(startY, endY);
        bgLayer.classList.toggle('is-visible', visible);

    };

    const setTone = (tone) => {
        bgLayer.dataset.tone = tone;
    };

    setTone(trackedSections[0].tone);
    updateVisibility();

    const observer = new IntersectionObserver((entries) => {
        let nextTone = null;
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const match = trackedSections.find((s) => s.el === entry.target);
                if (match) nextTone = match.tone;
            }
        });
        if (nextTone) setTone(nextTone);
    }, {
        root: null,
        rootMargin: '-35% 0px -35% 0px',
        threshold: 0
    });

    trackedSections.forEach(({ el }) => observer.observe(el));

    window.addEventListener('scroll', updateVisibility, { passive: true });
    window.addEventListener('resize', updateVisibility);
});
