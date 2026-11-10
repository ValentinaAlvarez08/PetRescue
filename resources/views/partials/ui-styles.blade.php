{{-- Capa visual global: animaciones, microinteracciones y detalles de pulido --}}
<style>
    :root { --brand: #f97316; --brand-dark: #c2410c; --rose: #f43f5e; --emerald: #059669; }
    html { scroll-behavior: smooth; }
    ::selection { background: #fed7aa; color: #7c2d12; }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 11px; height: 11px; }
    ::-webkit-scrollbar-track { background: #f7f5f2; }
    ::-webkit-scrollbar-thumb { background: linear-gradient(#fdba74, #f97316); border-radius: 99px; border: 3px solid #f7f5f2; }

    /* Fondo con textura sutil de huellas */
    body { background-image: radial-gradient(rgba(249,115,22,.06) 1px, transparent 1px); background-size: 22px 22px; }

    /* Barra de progreso de carga / navegación */
    #page-progress { position: fixed; top: 0; left: 0; height: 3px; width: 0; z-index: 2000;
        background: linear-gradient(90deg, #f97316, #f43f5e, #059669); box-shadow: 0 0 10px rgba(249,115,22,.6);
        transition: width .4s ease, opacity .4s ease; }
    #scroll-progress { position: absolute; bottom: -1px; left: 0; height: 2px; width: 0;
        background: linear-gradient(90deg, #f97316, #f43f5e); }

    /* Header que se compacta al bajar */
    #site-header { transition: box-shadow .3s ease, background-color .3s ease; }
    #site-header.is-scrolled { box-shadow: 0 10px 30px -12px rgba(28,25,23,.18); background-color: rgba(255,255,255,.97); }
    #site-header .header-inner { transition: height .3s ease; }
    #site-header.is-scrolled .header-inner { height: 3.5rem; }

    .logo-mark { transition: transform .5s cubic-bezier(.34,1.56,.64,1); }
    a:hover > .logo-mark { transform: rotate(-12deg) scale(1.1); }

    /* Enlaces del menú con subrayado animado */
    .nav-link { position: relative; }
    .nav-link::after { content: ''; position: absolute; left: 12px; right: 12px; bottom: 4px; height: 2px; border-radius: 2px;
        background: var(--brand); transform: scaleX(0); transform-origin: left; transition: transform .3s ease; }
    .nav-link:hover::after, .nav-link.is-active::after { transform: scaleX(1); }

    /* Menú móvil animado */
    @media (max-width: 1023px) {
        #nav-menu { transform-origin: top; animation: none; }
        #nav-menu.is-open { display: flex; animation: menuIn .28s cubic-bezier(.2,.8,.2,1); }
    }
    @keyframes menuIn { from { opacity: 0; transform: translateY(-8px) scaleY(.96); } to { opacity: 1; transform: none; } }

    /* Aparición al hacer scroll */
    .reveal { opacity: 0; transform: translateY(26px); transition: opacity .7s ease, transform .7s cubic-bezier(.2,.8,.2,1); }
    .reveal.is-visible { opacity: 1; transform: none; }

    /* Brillo que recorre los botones principales */
    .btn-shine { position: relative; overflow: hidden; isolation: isolate; }
    .btn-shine::before { content: ''; position: absolute; inset: 0; z-index: -1; transform: translateX(-120%) skewX(-20deg);
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.35), transparent); transition: transform .7s ease; }
    .btn-shine:hover::before { transform: translateX(120%) skewX(-20deg); }

    /* Ondas al hacer clic */
    .ripple { position: absolute; border-radius: 50%; transform: scale(0); background: rgba(255,255,255,.45);
        animation: ripple .65s linear; pointer-events: none; }
    @keyframes ripple { to { transform: scale(4); opacity: 0; } }

    /* Tarjetas con inclinación 3D */
    .tilt { transform-style: preserve-3d; will-change: transform; transition: transform .25s ease, box-shadow .25s ease; }
    .tilt .tilt-glare { position: absolute; inset: 0; pointer-events: none; opacity: 0; transition: opacity .3s ease;
        background: radial-gradient(circle at var(--gx, 50%) var(--gy, 50%), rgba(255,255,255,.35), transparent 55%); }
    .tilt:hover .tilt-glare { opacity: 1; }

    /* Imagen con carga suave */
    img.lazy-fade { opacity: 0; transition: opacity .6s ease, transform .7s cubic-bezier(.2,.8,.2,1); }
    img.lazy-fade.is-loaded { opacity: 1; }
    .shimmer { background: linear-gradient(100deg, #efebe5 30%, #f7f5f2 50%, #efebe5 70%); background-size: 200% 100%;
        animation: shimmer 1.4s linear infinite; }
    @keyframes shimmer { to { background-position: -200% 0; } }

    /* Punto que late (casos activos) */
    .pulse-dot { position: relative; display: inline-block; width: 8px; height: 8px; border-radius: 99px; background: currentColor; }
    .pulse-dot::after { content: ''; position: absolute; inset: 0; border-radius: inherit; background: currentColor;
        animation: pulse 1.8s ease-out infinite; }
    @keyframes pulse { 0% { transform: scale(1); opacity: .7; } 100% { transform: scale(3); opacity: 0; } }

    /* Animaciones decorativas */
    @keyframes float { 0%,100% { transform: translateY(0) rotate(var(--r, 0deg)); } 50% { transform: translateY(-14px) rotate(var(--r, 0deg)); } }
    .float { animation: float 6s ease-in-out infinite; }
    @keyframes blob { 0%,100% { border-radius: 42% 58% 70% 30% / 45% 45% 55% 55%; transform: translate(0,0) scale(1); }
        33% { border-radius: 70% 30% 46% 54% / 30% 39% 61% 70%; transform: translate(30px,-20px) scale(1.05); }
        66% { border-radius: 30% 70% 70% 30% / 53% 30% 70% 47%; transform: translate(-20px,20px) scale(.97); } }
    .blob { animation: blob 18s ease-in-out infinite; }
    @keyframes gradientMove { 0%,100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
    .text-gradient { background: linear-gradient(90deg, #ea580c, #f43f5e, #f97316, #ea580c); background-size: 300% 100%;
        -webkit-background-clip: text; background-clip: text; color: transparent; animation: gradientMove 8s ease infinite; }
    .bg-animated { background-size: 200% 200%; animation: gradientMove 10s ease infinite; }

    /* Palabra rotativa */
    .rotator { display: inline-grid; vertical-align: bottom; }
    .rotator > span { grid-area: 1 / 1; opacity: 0; transform: translateY(40%); transition: opacity .5s ease, transform .5s ease; }
    .rotator > span.is-current { opacity: 1; transform: none; }
    .rotator > span.is-leaving { opacity: 0; transform: translateY(-40%); }

    /* Carrusel infinito */
    .marquee { mask-image: linear-gradient(90deg, transparent, #000 10%, #000 90%, transparent); }
    .marquee-track { display: flex; width: max-content; animation: marquee 40s linear infinite; }
    .marquee:hover .marquee-track { animation-play-state: paused; }
    @keyframes marquee { to { transform: translateX(-50%); } }

    /* Huellas al hacer clic */
    .paw-burst { position: fixed; pointer-events: none; z-index: 3000; font-size: 18px; animation: pawUp .9s ease-out forwards; }
    @keyframes pawUp { from { opacity: 1; transform: translate(-50%,-50%) scale(.6); }
        to { opacity: 0; transform: translate(calc(-50% + var(--dx)), calc(-50% - 60px)) scale(1.2) rotate(var(--rot)); } }

    /* Notificación flotante */
    .toast { animation: toastIn .5s cubic-bezier(.34,1.56,.64,1); }
    .toast.is-leaving { animation: toastOut .35s ease forwards; }
    @keyframes toastIn { from { opacity: 0; transform: translateY(20px) scale(.95); } to { opacity: 1; transform: none; } }
    @keyframes toastOut { to { opacity: 0; transform: translateY(20px) scale(.95); } }
    .toast-bar { animation: toastBar 6s linear forwards; }
    @keyframes toastBar { from { width: 100%; } to { width: 0; } }

    /* Botón volver arriba */
    #to-top { opacity: 0; transform: translateY(16px) scale(.9); pointer-events: none; transition: all .3s ease; }
    #to-top.is-visible { opacity: 1; transform: none; pointer-events: auto; }

    /* Campos de formulario más vivos */
    input:not([type=checkbox]):not([type=radio]):not([type=file]):not([type=hidden]), select, textarea {
        transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease; }
    input:not([type=checkbox]):not([type=radio]):focus, select:focus, textarea:focus {
        border-color: #fb923c !important; box-shadow: 0 0 0 4px rgba(251,146,60,.18) !important; outline: none; }
    :focus-visible { outline: 3px solid rgba(249,115,22,.55); outline-offset: 2px; border-radius: 6px; }

    /* Mapas */
    .leaflet-popup-content-wrapper { border-radius: 16px !important; box-shadow: 0 12px 30px -10px rgba(0,0,0,.3) !important; }
    .pin { transition: transform .2s ease; }
    .leaflet-marker-icon:hover .pin { transform: rotate(-45deg) scale(1.15); }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after { animation-duration: .001ms !important; animation-iteration-count: 1 !important; transition-duration: .001ms !important; }
        .reveal { opacity: 1; transform: none; }
    }
</style>
<style>
    /* Gráficas que crecen al aparecer */
    .reveal .grow-y { transform: scaleY(0); transform-origin: bottom; transition: transform 1s cubic-bezier(.2,.8,.2,1), background-color .2s; }
    .reveal .grow-x { transform: scaleX(0); transform-origin: left; transition: transform 1.1s cubic-bezier(.2,.8,.2,1) .2s; }
    .reveal.is-visible .grow-y, .reveal.is-visible .grow-x { transform: none; }
</style>
