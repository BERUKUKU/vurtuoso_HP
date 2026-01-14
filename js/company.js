// Navigation scroll effect
const nav = document.getElementById('nav');
const navBg = document.getElementById('nav-bg');

window.addEventListener('scroll', () => {
    const scrollY = window.scrollY;
    
    // Navigation background
    if (scrollY > 100) {
        navBg.classList.add('bg-white/80', 'shadow-lg', 'border-b', 'border-purple-200/50');
    } else {
        navBg.classList.remove('bg-white/80', 'shadow-lg', 'border-b', 'border-purple-200/50');
    }
});

// Animated backgrounds
function animateBackground(elementId, duration, animations) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    let index = 0;
    setInterval(() => {
        element.style.background = animations[index];
        index = (index + 1) % animations.length;
    }, duration);
}

// About background animation
animateBackground('about-bg', 5000, [
    'linear-gradient(135deg, rgba(239,246,255,0.4) 0%, rgba(243,232,255,0.5) 50%, rgba(252,231,243,0.4) 100%)',
    'linear-gradient(135deg, rgba(252,231,243,0.4) 0%, rgba(239,246,255,0.5) 50%, rgba(243,232,255,0.4) 100%)',
    'linear-gradient(135deg, rgba(243,232,255,0.4) 0%, rgba(252,231,243,0.5) 50%, rgba(239,246,255,0.4) 100%)'
]);

// Contact CTA background animation
animateBackground('contact-cta-bg', 5000, [
    'linear-gradient(135deg, rgba(239,246,255,0.3) 0%, rgba(243,232,255,0.4) 50%, rgba(252,231,243,0.3) 100%)',
    'linear-gradient(135deg, rgba(252,231,243,0.3) 0%, rgba(239,246,255,0.4) 50%, rgba(243,232,255,0.3) 100%)',
    'linear-gradient(135deg, rgba(243,232,255,0.3) 0%, rgba(252,231,243,0.4) 50%, rgba(239,246,255,0.3) 100%)'
]);

// Decorative circles animation
function animateCircle(elementId, duration, keyframes) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    let progress = 0;
    const animate = () => {
        progress += 0.01;
        if (progress > 1) progress = 0;
        
        const easeInOut = (t) => t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
        const t = easeInOut(progress);
        
        const scale = 1 + (keyframes.scaleRange[1] - keyframes.scaleRange[0]) * Math.sin(t * Math.PI);
        const x = keyframes.xRange * Math.sin(t * Math.PI * 2);
        const y = keyframes.yRange * Math.sin(t * Math.PI * 2);
        
        element.style.transform = `scale(${scale}) translate(${x}px, ${y}px)`;
        
        requestAnimationFrame(animate);
    };
    animate();
}

animateCircle('about-circle-1', 15000, { scaleRange: [1, 1.3], xRange: 40, yRange: -30 });
animateCircle('about-circle-2', 18000, { scaleRange: [1, 1.2], xRange: -30, yRange: 40 });
animateCircle('contact-cta-circle-1', 10000, { scaleRange: [1, 1.3], xRange: 30, yRange: -20 });
animateCircle('contact-cta-circle-2', 12000, { scaleRange: [1, 1.2], xRange: -20, yRange: 30 });

// Gradient text animation
function animateGradientText() {
    let position = 0;
    const elements = document.querySelectorAll('h1, h2');
    
    setInterval(() => {
        position += 1;
        if (position > 100) position = 0;
        
        elements.forEach(el => {
            if (el.classList.contains('bg-clip-text')) {
                el.style.backgroundPosition = `${position}% 50%`;
            }
        });
    }, 30);
}

animateGradientText();

// Fade-in on scroll
function observeElements() {
    const options = {
        root: null,
        threshold: 0.1,
        rootMargin: '-100px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, options);
    
    const elements = document.querySelectorAll('.company-card, .about-underline, .contact-underline');
    elements.forEach(el => {
        el.classList.add('fade-in');
        observer.observe(el);
    });
}

window.addEventListener('DOMContentLoaded', observeElements);
