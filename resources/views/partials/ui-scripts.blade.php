{{-- Interacciones globales: progreso, scroll, apariciones, contadores, tilt, ondas, notificaciones --}}
<script>
(function () {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

    /* ---------- Barra de progreso de navegación ---------- */
    const bar = document.getElementById('page-progress');
    const finish = () => { bar.style.width = '100%'; setTimeout(() => { bar.style.opacity = '0'; }, 350); };
    bar.style.width = '70%';
    if (document.readyState === 'complete') finish(); else window.addEventListener('load', finish);
    const start = () => { bar.style.transition = 'none'; bar.style.opacity = '1'; bar.style.width = '0';
        requestAnimationFrame(() => { bar.style.transition = 'width 8s cubic-bezier(.1,.7,.2,1)'; bar.style.width = '85%'; }); };
    document.addEventListener('click', e => {
        const a = e.target.closest('a[href]');
        if (!a || e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || a.target === '_blank') return;
        const url = new URL(a.href, location.href);
        if (url.origin !== location.origin || (url.pathname === location.pathname && url.hash)) return;
        start();
    });
    document.addEventListener('submit', e => { if (!e.defaultPrevented) start(); });
    window.addEventListener('pageshow', e => { if (e.persisted) finish(); });

    /* ---------- Header y progreso de lectura ---------- */
    const header = document.getElementById('site-header');
    const scrollBar = document.getElementById('scroll-progress');
    const toTop = document.getElementById('to-top');
    const onScroll = () => {
        const y = window.scrollY;
        header.classList.toggle('is-scrolled', y > 10);
        toTop.classList.toggle('is-visible', y > 600);
        const max = document.documentElement.scrollHeight - innerHeight;
        scrollBar.style.width = (max > 0 ? (y / max) * 100 : 0) + '%';
    };
    addEventListener('scroll', onScroll, { passive: true }); onScroll();
    toTop.addEventListener('click', () => scrollTo({ top: 0, behavior: 'smooth' }));

    /* ---------- Menú móvil ---------- */
    const toggle = document.getElementById('nav-toggle');
    const menu = document.getElementById('nav-menu');
    toggle.addEventListener('click', () => {
        const open = menu.classList.toggle('hidden') === false;
        menu.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', open);
        toggle.textContent = open ? '✕' : '☰';
    });

    /* ---------- Aparición al hacer scroll ---------- */
    const autoReveal = [
        'main section > div > h2', 'main [data-reveal]',
        'main .grid > a', 'main .grid > div.rounded-3xl', 'main .grid > article',
        'main section.rounded-3xl', 'main header', 'main form.rounded-3xl', 'main figure', 'main details.rounded-3xl',
    ];
    const revealEls = $$(autoReveal.join(',')).filter(el => !el.closest('.leaflet-container') && !el.querySelector('.leaflet-container'));
    revealEls.forEach(el => {
        el.classList.add('reveal');
        const siblings = el.parentElement ? Array.from(el.parentElement.children) : [];
        const i = Math.min(siblings.indexOf(el), 8);
        el.style.transitionDelay = (i > 0 ? i * 70 : 0) + 'ms';
    });
    if ('IntersectionObserver' in window && !reduceMotion) {
        const io = new IntersectionObserver(entries => entries.forEach(en => {
            if (!en.isIntersecting) return;
            en.target.classList.add('is-visible');
            const el = en.target;
            const cleanup = ev => {
                if (ev.target !== el || ev.propertyName !== 'opacity') return;
                el.removeEventListener('transitionend', cleanup);
                // Deja que las gráficas terminen de crecer antes de soltar la clase.
                setTimeout(() => { el.style.transitionDelay = ''; el.classList.remove('reveal', 'is-visible'); }, 1400);
            };
            el.addEventListener('transitionend', cleanup);
            io.unobserve(en.target);
        }), { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
        revealEls.forEach(el => io.observe(el));
    } else {
        revealEls.forEach(el => el.classList.add('is-visible'));
    }

    /* ---------- Contadores animados ---------- */
    const fmt = new Intl.NumberFormat('es-CO');
    const countUp = el => {
        const target = parseInt(el.dataset.count, 10) || 0;
        if (reduceMotion || target === 0) { el.textContent = fmt.format(target) + (el.dataset.suffix || ''); return; }
        const dur = 1400, t0 = performance.now();
        const step = t => {
            const p = Math.min((t - t0) / dur, 1), eased = 1 - Math.pow(1 - p, 4);
            el.textContent = fmt.format(Math.round(target * eased)) + (el.dataset.suffix || '');
            if (p < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    };
    const counters = $$('[data-count]');
    if ('IntersectionObserver' in window) {
        const co = new IntersectionObserver(entries => entries.forEach(en => {
            if (en.isIntersecting) { countUp(en.target); co.unobserve(en.target); }
        }), { threshold: 0.5 });
        counters.forEach(el => co.observe(el));
    } else counters.forEach(countUp);

    /* ---------- Tarjetas con inclinación 3D ---------- */
    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    if (finePointer && !reduceMotion) {
        $$('.tilt').forEach(card => {
            if (getComputedStyle(card).position === 'static') card.style.position = 'relative';
            const glare = document.createElement('span'); glare.className = 'tilt-glare'; card.appendChild(glare);
            card.addEventListener('mousemove', e => {
                const r = card.getBoundingClientRect();
                const x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
                card.style.transform = `perspective(900px) rotateX(${(0.5 - y) * 7}deg) rotateY(${(x - 0.5) * 7}deg) translateY(-4px)`;
                card.style.setProperty('--gx', (x * 100) + '%'); card.style.setProperty('--gy', (y * 100) + '%');
            });
            card.addEventListener('mouseleave', () => { card.style.transform = ''; });
        });
    }

    /* ---------- Brillo + ondas en botones ---------- */
    $$('a[class*="bg-brand-5"], a[class*="bg-rose-5"], a[class*="bg-emerald-6"], a[class*="bg-ink-9"], button[class*="bg-brand-5"], button[class*="bg-rose-5"], button[class*="bg-emerald-6"], button[class*="bg-ink-9"]')
        .forEach(b => b.classList.add('btn-shine'));
    document.addEventListener('pointerdown', e => {
        const b = e.target.closest('.btn-shine');
        if (!b) return;
        const r = b.getBoundingClientRect(), size = Math.max(r.width, r.height);
        const s = document.createElement('span'); s.className = 'ripple';
        s.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX - r.left - size / 2}px;top:${e.clientY - r.top - size / 2}px`;
        b.appendChild(s); setTimeout(() => s.remove(), 700);
    });

    /* ---------- Huellitas al hacer clic ---------- */
    if (!reduceMotion) {
        document.addEventListener('click', e => {
            if (e.target.closest('input, select, textarea, label, .leaflet-container, [contenteditable]')) return;
            for (let i = 0; i < 3; i++) {
                const p = document.createElement('span'); p.className = 'paw-burst'; p.textContent = '🐾';
                p.style.left = e.clientX + 'px'; p.style.top = e.clientY + 'px';
                p.style.setProperty('--dx', ((i - 1) * 26 + (Math.random() * 10 - 5)) + 'px');
                p.style.setProperty('--rot', ((i - 1) * 25) + 'deg');
                p.style.animationDelay = (i * 60) + 'ms';
                document.body.appendChild(p); setTimeout(() => p.remove(), 1100);
            }
        });
    }

    /* ---------- Imágenes con carga suave ---------- */
    $$('main img').forEach(img => {
        if (img.closest('.leaflet-container')) return;
        img.classList.add('lazy-fade');
        const done = () => { img.classList.add('is-loaded'); img.parentElement && img.parentElement.classList.remove('shimmer'); };
        if (img.complete && img.naturalWidth) done();
        else { img.parentElement && img.parentElement.classList.add('shimmer'); img.addEventListener('load', done); img.addEventListener('error', done); }
    });

    /* ---------- Palabra rotativa ---------- */
    $$('.rotator').forEach(rot => {
        const words = Array.from(rot.children); if (words.length < 2 || reduceMotion) return;
        let i = 0;
        setInterval(() => {
            const cur = words[i]; cur.classList.remove('is-current'); cur.classList.add('is-leaving');
            setTimeout(() => cur.classList.remove('is-leaving'), 500);
            i = (i + 1) % words.length; words[i].classList.add('is-current');
        }, 2600);
    });

    /* ---------- Notificaciones ---------- */
    $$('.toast').forEach(t => {
        const close = () => { t.classList.add('is-leaving'); setTimeout(() => t.remove(), 350); };
        t.querySelector('[data-toast-close]')?.addEventListener('click', close);
    });
})();
</script>
