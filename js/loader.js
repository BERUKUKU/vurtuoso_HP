(() => {
    const overlay = document.getElementById('loader');
    if (!overlay) return;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const started = performance.now();
    document.body.classList.add('has-loader', 'is-loading');
    const alignLogo = () => {
        const heroLogo = document.querySelector('.hero-brand .brand-wordmark');
        const lockup = overlay.querySelector('.lockup');
        if (!heroLogo || !lockup) return;
        // Layout offsets exclude the hero's entrance animation transforms.
        let top = 0;
        let left = 0;
        for (let element = heroLogo; element; element = element.offsetParent) {
            top += element.offsetTop;
            left += element.offsetLeft;
        }
        Object.assign(lockup.style, {
            position: 'fixed',
            top: `${top - window.scrollY}px`,
            left: `${left - window.scrollX}px`,
            width: `${heroLogo.offsetWidth}px`,
            height: `${heroLogo.offsetHeight}px`,
        });
    };
    window.addEventListener('resize', alignLogo);
    document.fonts.ready.then(alignLogo);
    let finished = false;
    let finishTimer;
    const finish = () => {
        if (finished) return;
        finished = true;
        clearTimeout(finishTimer);
        clearTimeout(fallback);
        alignLogo();
        clearTimeout(window.virtuosoLoaderWatchdog);
        document.documentElement.classList.remove('loader-pending');
        overlay.classList.add('done');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.add('is-revealing');
        document.body.classList.remove('is-loading');
        setTimeout(() => {
            overlay.remove();
            window.removeEventListener('resize', alignLogo);
            document.body.classList.remove('is-revealing');
        }, reducedMotion ? 0 : 2800);
    };
    const ready = () => {
        finishTimer = setTimeout(finish, Math.max(0, (reducedMotion ? 0 : 3300) - (performance.now() - started)));
    };
    // A slow external resource must not leave the page covered indefinitely.
    const fallback = setTimeout(finish, 8000);
    const prepareHero = () => {
        alignLogo();
        const image = document.querySelector('.hero-img');
        if (!image || !image.decode) return ready();
        image.decode().catch(() => {}).then(ready);
    };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', prepareHero, { once: true });
    } else {
        prepareHero();
    }
})();
