// Navigation scroll effect
window.addEventListener('scroll', () => {
    const nav = document.getElementById('nav');
    if (window.scrollY > 100) {
        nav.classList.add('scrolled');
    } else {
        nav.classList.remove('scrolled');
    }
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

    // Ensure hero video starts; some browsers pause autoplay even when muted
    const heroVideo = document.querySelector('.hero-img');
    if (heroVideo) {
        heroVideo.muted = true;
        heroVideo.playsInline = true;
        heroVideo.preload = 'auto';
        heroVideo.play().catch(() => {});
    }
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

// Auto play gallery videos immediately
document.addEventListener('DOMContentLoaded', () => {
    const galleryVideos = document.querySelectorAll('.gallery-video');
    if (!galleryVideos.length) return;

    galleryVideos.forEach(video => {
        video.muted = true;
        video.playsInline = true;
        video.preload = 'auto';
        video.play().catch(() => {});
    });
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

// Fade out loader once assets are ready
window.addEventListener('load', () => {
    const loader = document.getElementById('loader');
    if (!loader) return;
    loader.classList.add('hide');
    setTimeout(() => loader.remove(), 700);
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
    
    let current = '';
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.clientHeight;
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
        if (href === 'index.html' && current === 'hero') {
            link.classList.add('active');
        } else if (href.includes('#') && href.includes(current)) {
            link.classList.add('active');
        }
    });
});

// Parallax effect for hero section
window.addEventListener('scroll', () => {
    const heroContent = document.querySelector('.hero-content');
    
    // Keep hero content visible; simple clamp if needed later
    if (heroContent) {
        heroContent.style.opacity = 1;
    }
});
