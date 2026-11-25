<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $schoolName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
 <style>
    :root{
        --text:#1c1917;
        --muted:#57534e;
        --primary:#166534;
        --primary-dark:#14532d;
        --primary-light:#22c55e;
        --accent:#a16207;
        --accent-light:#f59e0b;
        --bg:#fafaf9;
        --border:#e7e5e4;
    }
    *{box-sizing:border-box}
    html{scroll-behavior:smooth;scroll-padding-top:5rem}
    body{
        margin:0;
        min-height:100vh;
        display:flex;
        flex-direction:column;
        font-family:"Poppins",system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;
        font-size:16px;
        line-height:1.5;
        color:var(--text);
        background:var(--bg);
        overflow-x:hidden;
    }
    .ar{font-family:"Amiri","Traditional Arabic",serif;direction:rtl}

    /* Duubaa sochii qabu (HTML hin barbaachisu) */
    body::before,body::after{
        content:"";
        position:fixed;
        z-index:-1;
        border-radius:50%;
        filter:blur(70px);
        opacity:.55;
        pointer-events:none;
    }
    body::before{
        width:26rem;height:26rem;
        top:-6rem;right:-6rem;
        background:radial-gradient(circle,#86efac,transparent 70%);
        animation:float 12s ease-in-out infinite;
    }
    body::after{
        width:24rem;height:24rem;
        bottom:-6rem;left:-6rem;
        background:radial-gradient(circle,#fde68a,transparent 70%);
        animation:float 15s ease-in-out infinite reverse;
    }

    header,main,footer{width:100%;max-width:68rem;margin:auto;padding:1.5rem}

    /* ===== Topbar (sticky nav) ===== */
    .topbar{
        position:sticky;top:0;z-index:50;
        background:rgba(250,250,249,.82);
        backdrop-filter:blur(12px);
        -webkit-backdrop-filter:blur(12px);
        transition:box-shadow .3s ease;
    }
    .topbar.scrolled{box-shadow:0 6px 24px rgba(22,101,52,.12)}
    .progress{position:absolute;left:0;bottom:0;height:3px;width:0;background:linear-gradient(90deg,var(--primary-light),var(--accent-light))}

    /* Header */
    header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        flex-wrap:wrap;
        gap:1rem;
        border-bottom:1px solid var(--border);
        padding-top:.9rem;padding-bottom:.9rem;
        animation:slideDown .7s ease both;
    }
    .brand{
        display:flex;align-items:center;gap:.6rem;
        font-weight:800;
        font-size:1.25rem;
        letter-spacing:-.01em;
        text-decoration:none;
    }
    .brand .logo{
        width:2.4rem;height:2.4rem;border-radius:50%;
        display:grid;place-items:center;font-size:1.3rem;
        background:linear-gradient(135deg,var(--primary),var(--primary-light));
        color:#fff;box-shadow:0 6px 14px rgba(22,101,52,.3);
        animation:glow 3s ease-in-out infinite;
    }
    .brand .name{
        background:linear-gradient(90deg,var(--primary),var(--primary-light),var(--accent));
        background-size:200% auto;
        -webkit-background-clip:text;
        background-clip:text;
        color:transparent;
        animation:shimmer 6s linear infinite;
    }
    header nav{flex-wrap:wrap;display:flex;align-items:center;gap:.25rem}
    .admin{
        position:relative;
        color:var(--muted);
        text-decoration:none;
        font-size:.9rem;
        font-weight:600;
        padding:.5rem .9rem;
        border-radius:999px;
        border:1px solid transparent;
        transition:color .25s ease,background .25s ease,border-color .25s ease,transform .25s ease,box-shadow .25s ease;
        cursor:pointer;
        list-style:none;
    }
    .admin::after{
        content:"";
        position:absolute;
        left:50%;bottom:.25rem;
        width:0;height:2px;
        background:var(--primary-light);
        border-radius:2px;
        transform:translateX(-50%);
        transition:width .25s ease;
    }
    .admin:hover,.admin.active{
        color:var(--primary);
        background:#f0fdf4;
        border-color:#bbf7d0;
        transform:translateY(-2px);
        box-shadow:0 6px 14px rgba(22,101,52,.12);
    }
    .admin:hover::after,.admin.active::after{width:55%}
    .admin:active{transform:translateY(0)}

    /* Dropdown (Sign in) */
    details.dd{position:relative}
    details.dd summary::-webkit-details-marker{display:none}
    details.dd summary::before{content:"🔐 "}
    .dd-panel{
        position:absolute;right:0;top:calc(100% + .5rem);min-width:13rem;
        background:#fff;border:1px solid var(--border);border-radius:1rem;
        padding:.5rem;box-shadow:0 18px 40px rgba(22,101,52,.18);
        display:flex;flex-direction:column;gap:.15rem;
        animation:fadeUp .3s ease both;
    }
    .dd-panel .admin{display:block;border-radius:.6rem}

    .lang{display:flex;gap:.25rem;margin-left:.5rem;padding-left:.75rem;border-left:1px solid var(--border);font-size:.85rem}
    .lang a{color:var(--muted);text-decoration:none;font-weight:600;padding:.3rem .6rem;border-radius:999px;transition:.25s}
    .lang a:hover{background:#fef3c7;color:var(--accent)}

    .menu-btn{
        display:none;border:1px solid #bbf7d0;background:#f0fdf4;color:var(--primary);
        width:2.8rem;height:2.8rem;border-radius:50%;font-size:1.3rem;cursor:pointer;
        transition:transform .25s ease,background .25s ease;
    }
    .menu-btn:hover{background:var(--primary);color:#fff;transform:rotate(90deg)}

    /* Hero */
    main{
        flex:1;
        padding-top:10vh;
        padding-bottom:12vh;
        position:relative;
    }
    .hero-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:2.5rem;align-items:center}
    .bismillah{
        font-size:clamp(1.6rem,4vw,2.4rem);color:var(--primary);
        margin:0 0 .6rem;animation:fadeUp .8s ease both;
        text-shadow:0 2px 12px rgba(34,197,94,.25);
    }
    .eyebrow{
        display:inline-flex;
        align-items:center;
        gap:.5rem;
        color:var(--accent);
        background:#fef3c7;
        border:1px solid #fde68a;
        padding:.4rem .9rem;
        border-radius:999px;
        font-weight:700;
        letter-spacing:.12em;
        text-transform:uppercase;
        font-size:.75rem;
        animation:fadeUp .8s ease .1s both;
    }
    .eyebrow::before{
        content:"";
        width:.5rem;height:.5rem;
        border-radius:50%;
        background:var(--accent-light);
        animation:pulse 1.8s ease-out infinite;
    }
    h1{
        font-size:clamp(2.5rem,7vw,5rem);
        font-weight:800;
        line-height:1.05;
        letter-spacing:-.03em;
        max-width:13ch;
        margin:1rem 0 1.2rem;
        background:linear-gradient(120deg,var(--text) 0%,var(--primary) 55%,var(--primary-light) 100%);
        -webkit-background-clip:text;
        background-clip:text;
        color:transparent;
        animation:fadeUp .8s ease .25s both;
    }
    p{
        max-width:42rem;
        color:var(--muted);
        font-size:1.15rem;
        line-height:1.75;
        margin:0;
        animation:fadeUp .8s ease .4s both;
    }
    .welcome-ar{
        font-size:clamp(1.6rem,4.5vw,2.6rem);font-weight:700;color:var(--accent);
        margin:.2rem 0 .3rem;animation:fadeUp .8s ease .3s both;
    }
    .welcome-om{font-weight:600;color:var(--primary);margin-bottom:.6rem;animation:fadeUp .8s ease .35s both}
    .cta{display:flex;flex-wrap:wrap;gap:.9rem;align-items:center}
    .button{
        position:relative;
        display:inline-flex;
        align-items:center;
        gap:.6rem;
        overflow:hidden;
        margin-top:1.8rem;
        background:linear-gradient(135deg,var(--primary),var(--primary-light));
        color:#fff;
        padding:.95rem 1.7rem;
        border-radius:.75rem;
        text-decoration:none;
        font-weight:700;
        font-size:1rem;
        box-shadow:0 8px 20px rgba(22,101,52,.28);
        transition:transform .25s ease,box-shadow .25s ease,filter .25s ease;
        animation:fadeUp .8s ease .55s both;
    }
    .button::before{
        content:"";
        position:absolute;
        top:0;left:-80%;
        width:50%;height:100%;
        background:linear-gradient(120deg,transparent,rgba(255,255,255,.45),transparent);
        transform:skewX(-20deg);
    }
    .button::after{
        content:"→";
        transition:transform .25s ease;
    }
    .button:hover{
        transform:translateY(-3px) scale(1.02);
        box-shadow:0 14px 30px rgba(22,101,52,.38);
        filter:brightness(1.05);
    }
    .button:hover::before{
        left:130%;
        transition:left .7s ease;
    }
    .button:hover::after{transform:translateX(5px)}
    .button:active{transform:translateY(0) scale(.98)}
    .button.ghost{
        background:#fff;color:var(--primary);border:2px solid var(--primary-light);box-shadow:none;
    }
    .button.ghost::after{content:"↓"}
    .button:focus-visible,.admin:focus-visible{
        outline:3px solid #86efac;
        outline-offset:3px;
    }

    /* Quran emblem */
    .emblem{position:relative;display:grid;place-items:center;animation:fadeUp 1s ease .4s both}
    .emblem .ring{
        position:absolute;width:100%;aspect-ratio:1;border-radius:50%;
        border:2px dashed rgba(22,101,52,.35);animation:spin 40s linear infinite;
    }
    .emblem .ring.two{width:78%;border-color:rgba(245,158,11,.5);animation-direction:reverse;animation-duration:30s}
    .emblem svg{width:62%;height:auto;filter:drop-shadow(0 18px 28px rgba(22,101,52,.3));animation:bob 5s ease-in-out infinite}
    .emblem .rub{
        position:absolute;top:6%;font-size:2.4rem;color:var(--accent-light);
        text-shadow:0 0 18px rgba(245,158,11,.6);animation:pulseText 3s ease-in-out infinite;
    }
    .verse-chip{
        position:absolute;bottom:2%;background:#fff;border:1px solid #fde68a;border-radius:1rem;
        padding:.5rem 1rem;text-align:center;box-shadow:0 10px 24px rgba(161,98,7,.15);
    }
    .verse-chip .ar{font-size:1.3rem;color:var(--primary-dark)}
    .verse-chip small{display:block;color:var(--muted);font-size:.7rem}

    /* Floating Islamic symbols */
    .floaters{position:fixed;inset:0;pointer-events:none;z-index:-1;overflow:hidden}
    .floaters span{
        position:absolute;bottom:-3rem;opacity:.18;color:var(--primary);
        animation:rise linear infinite;
    }

    /* ===== Sections ===== */
    .wrap{max-width:68rem;margin:auto;padding:4.5rem 1.5rem}
    .sec-head{text-align:center;margin-bottom:2.5rem}
    .sec-head .ar{font-size:1.6rem;color:var(--accent);display:block}
    .sec-head h2{font-size:clamp(1.8rem,4vw,2.6rem);margin:.3rem 0 .6rem;color:var(--primary-dark);letter-spacing:-.02em}
    .sec-head .bar{display:block;width:5rem;height:4px;border-radius:4px;margin:0 auto;background:linear-gradient(90deg,var(--primary-light),var(--accent-light))}
    section p,footer p{animation:none;max-width:none;font-size:1rem}
    .divider{text-align:center;color:var(--accent-light);letter-spacing:.6em;font-size:1.1rem;margin:0}

    .grid{display:grid;gap:1.25rem}
    .grid.cols3{grid-template-columns:repeat(3,1fr)}
    .grid.cols2{grid-template-columns:repeat(2,1fr)}
    .card{
        background:rgba(255,255,255,.9);border:1px solid var(--border);border-radius:1.25rem;
        padding:1.6rem;position:relative;overflow:hidden;
        transition:transform .3s ease,box-shadow .3s ease,border-color .3s ease;
    }
    .card::before{
        content:"";position:absolute;inset:0 0 auto 0;height:4px;
        background:linear-gradient(90deg,var(--primary-light),var(--accent-light));
        transform:scaleX(0);transform-origin:left;transition:transform .4s ease;
    }
    .card:hover{transform:translateY(-8px);box-shadow:0 22px 40px rgba(22,101,52,.15);border-color:#bbf7d0}
    .card:hover::before{transform:scaleX(1)}
    .card .ico{
        width:3.2rem;height:3.2rem;border-radius:1rem;display:grid;place-items:center;font-size:1.6rem;
        background:#f0fdf4;border:1px solid #bbf7d0;margin-bottom:1rem;transition:transform .3s ease;
    }
    .card:hover .ico{transform:scale(1.15) rotate(-6deg)}
    .card h3{margin:0 0 .4rem;font-size:1.15rem;color:var(--primary-dark)}
    .card p{color:var(--muted);font-size:.95rem;line-height:1.6}

    /* About band */
    .band{background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:#fff;position:relative;overflow:hidden}
    .band::before{
        content:"۞";position:absolute;font-size:22rem;line-height:1;right:-4rem;top:-4rem;opacity:.07;
        animation:spin 80s linear infinite;
    }
    .band .sec-head h2{color:#fff}
    .band .sec-head .ar{color:#fde68a}
    .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-top:2rem}
    .stat{text-align:center;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);border-radius:1rem;padding:1.2rem .5rem;transition:.3s}
    .stat:hover{background:rgba(255,255,255,.16);transform:translateY(-4px)}
    .stat b{display:block;font-size:2rem;color:#fde68a}
    .stat span{font-size:.85rem;opacity:.9}
    .band p.lead{color:rgba(255,255,255,.92);max-width:46rem;margin:0 auto;text-align:center;font-size:1.05rem;line-height:1.8}

    /* Quotes */
    .quotes{background:linear-gradient(180deg,transparent,#f0fdf4 30%,#fefce8)}
    .quote{
        background:#fff;border:1px solid #fde68a;border-radius:1.25rem;padding:1.6rem 1.5rem 1.3rem;position:relative;
        transition:transform .3s ease,box-shadow .3s ease;
    }
    .quote::before{
        content:"❝";position:absolute;top:.4rem;left:1rem;font-size:3rem;color:#fde68a;line-height:1;
    }
    .quote:hover{transform:translateY(-6px) rotate(-.5deg);box-shadow:0 20px 38px rgba(161,98,7,.16)}
    .quote .ar{font-size:1.65rem;line-height:2;color:var(--primary-dark);text-align:center;margin:.8rem 0 .6rem}
    .quote .om{font-style:italic;color:var(--text);text-align:center;font-size:.98rem;line-height:1.6}
    .quote .ref{display:block;text-align:center;margin-top:.8rem;font-size:.78rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--accent)}
    .note{text-align:center;color:var(--muted);font-size:.8rem;margin-top:1.5rem}

    /* Admission CTA */
    .cta-box{
        text-align:center;border-radius:1.75rem;padding:3.2rem 1.5rem;color:#fff;position:relative;overflow:hidden;
        background:linear-gradient(135deg,var(--primary),var(--primary-light));
        box-shadow:0 24px 50px rgba(22,101,52,.3);
    }
    .cta-box::before,.cta-box::after{content:"☪";position:absolute;font-size:9rem;opacity:.1;animation:float 10s ease-in-out infinite}
    .cta-box::before{left:2%;top:-1rem}
    .cta-box::after{right:2%;bottom:-2rem;animation-delay:2s}
    .cta-box h2{margin:.2rem 0 .6rem;font-size:clamp(1.6rem,4vw,2.4rem)}
    .cta-box p{color:rgba(255,255,255,.95);margin:0 auto 1rem;max-width:36rem}
    .cta-box .button{background:#fff;color:var(--primary);margin-top:.6rem}
    .cta-box .ar{font-size:1.8rem;color:#fde68a}

    /* Footer */
    .site-footer{background:#0f2e1a;color:#d1fae5;margin-top:auto;border-top:4px solid var(--accent-light);animation:fadeUp .8s ease .7s both}
    .foot-grid{max-width:68rem;margin:auto;padding:3rem 1.5rem 1.5rem;display:grid;grid-template-columns:1.4fr 1fr 1fr;gap:2rem}
    .foot-grid h4{margin:0 0 .8rem;color:#fde68a;font-size:1rem;letter-spacing:.05em}
    .foot-grid a{display:block;color:#bbf7d0;text-decoration:none;padding:.2rem 0;font-size:.93rem;transition:color .25s,transform .25s}
    .foot-grid a:hover{color:#fff;transform:translateX(6px)}
    .foot-grid p{color:#a7f3d0 !important;font-size:.92rem !important}
    .foot-grid .ar{font-size:1.5rem;color:#fde68a;margin:.3rem 0}
    .foot-bottom{max-width:68rem;margin:auto;padding:1.2rem 1.5rem 1.6rem;border-top:1px solid rgba(255,255,255,.12);text-align:center;font-size:.85rem;color:#86efac}
    footer{
        border-top:1px solid var(--border);
        color:#78716c;
        font-size:.9rem;
        text-align:center;
    }
    .site-footer footer{border:0;color:#86efac;padding:0;max-width:none}

    /* Reveal on scroll */
    .reveal{opacity:0;transform:translateY(32px);transition:opacity .8s ease,transform .8s ease}
    .reveal.show{opacity:1;transform:none}
    .reveal.d1{transition-delay:.1s}.reveal.d2{transition-delay:.2s}.reveal.d3{transition-delay:.3s}

    /* Animations */
    @keyframes fadeUp{
        from{opacity:0;transform:translateY(24px)}
        to{opacity:1;transform:translateY(0)}
    }
    @keyframes slideDown{
        from{opacity:0;transform:translateY(-20px)}
        to{opacity:1;transform:translateY(0)}
    }
    @keyframes float{
        0%,100%{transform:translate(0,0) scale(1)}
        50%{transform:translate(30px,40px) scale(1.12)}
    }
    @keyframes shimmer{
        to{background-position:200% center}
    }
    @keyframes pulse{
        0%{box-shadow:0 0 0 0 rgba(245,158,11,.6)}
        100%{box-shadow:0 0 0 10px rgba(245,158,11,0)}
    }
    @keyframes spin{to{transform:rotate(360deg)}}
    @keyframes bob{0%,100%{transform:translateY(0)}50%{transform:translateY(-14px)}}
    @keyframes glow{0%,100%{box-shadow:0 6px 14px rgba(22,101,52,.3)}50%{box-shadow:0 0 22px rgba(34,197,94,.7)}}
    @keyframes pulseText{0%,100%{transform:scale(1);opacity:.85}50%{transform:scale(1.2);opacity:1}}
    @keyframes rise{
        from{transform:translateY(0) rotate(0)}
        to{transform:translateY(-115vh) rotate(360deg)}
    }

    /* Responsive */
    @media(max-width:900px){
        .menu-btn{display:grid;place-items:center}
        header nav{
            display:none;width:100%;flex-direction:column;align-items:stretch;
            background:#fff;border:1px solid var(--border);border-radius:1rem;padding:.6rem;
            box-shadow:0 16px 34px rgba(22,101,52,.14);
        }
        header nav.open{display:flex;animation:fadeUp .3s ease both}
        header nav .admin{text-align:left}
        .dd-panel{position:static;box-shadow:none;border:0;padding:.2rem 0 .2rem 1rem;animation:none}
        .lang{margin:.4rem 0 0;padding:.6rem 0 0;border-left:0;border-top:1px solid var(--border)}
        .hero-grid{grid-template-columns:1fr}
        .emblem{max-width:22rem;margin:1rem auto 0}
        .grid.cols3{grid-template-columns:repeat(2,1fr)}
        .stats{grid-template-columns:repeat(2,1fr)}
        .foot-grid{grid-template-columns:1fr 1fr}
    }
    @media(max-width:700px){
        header{flex-wrap:wrap}
        main{padding-top:6vh}
        p{font-size:1.05rem}
        .button{width:100%;justify-content:center}
        body::before,body::after{opacity:.35}
        .grid.cols3,.grid.cols2{grid-template-columns:1fr}
        .foot-grid{grid-template-columns:1fr}
        .wrap{padding:3rem 1.1rem}
    }

    /* Nama sochii hin barbaadne */
    @media(prefers-reduced-motion:reduce){
        *,*::before,*::after{
            animation:none !important;
            transition:none !important;
        }
        .reveal{opacity:1;transform:none}
    }
</style>
</head>
<body>

<!-- Symbols fiduu (decor) -->
<div class="floaters" aria-hidden="true" id="floaters"></div>

<div class="topbar" id="topbar">
<header>
    <a class="brand" href="#home">
        <span class="logo">☪</span>
        <span class="name">{{ $schoolName }}</span>
    </a>
    <button class="menu-btn" id="menuBtn" aria-label="Menu" aria-expanded="false">☰</button>
    <nav id="mainNav">
        <a class="admin" href="#home">Mana</a>
        <a class="admin" href="#about">Waa'ee Keenya</a>
        <a class="admin" href="#programs">Sagantaalee</a>
        <a class="admin" href="#quotes">Jechoota</a>
        <a class="admin" href="#admission">Galmee</a>
        <details class="dd">
            <summary class="admin">Sign in</summary>
            <div class="dd-panel">
                <a class="admin" href="{{ route('student.login') }}">Student sign in</a>
                <a class="admin" href="{{ route('teacher.login') }}">Teacher sign in</a>
                <a class="admin" href="{{ route('parent.login') }}">Parent sign in</a>
                <a class="admin" href="/admin/login">Staff sign in</a>
            </div>
        </details>
        <div class="lang">
            <a href="{{ route('lang.switch', 'om') }}">Afaan Oromoo</a> |
            <a href="{{ route('lang.switch', 'en') }}">English</a>
        </div>
    </nav>
</header>
<div class="progress" id="progress"></div>
</div>

<main id="home">
    <div class="hero-grid">
        <div>
            <p class="bismillah ar" lang="ar">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</p>
            <div class="eyebrow">School admissions</div>
            <h1>Learning with purpose.</h1>
            <div class="welcome-ar ar" lang="ar">أَهْلاً وَسَهْلاً بِكُمْ</div>
            <div class="welcome-om">Baga nagaan dhuftan! · السلام عليكم ورحمة الله وبركاته</div>
            <p>Apply for admission to {{ $schoolName }}. Applications are accepted only during the school’s configured registration period.</p>
            <div class="cta">
                <a class="button" href="{{ route('admissions.create') }}">Apply for admission</a>
                <a class="button ghost" href="#about">Barbaadi</a>
            </div>
        </div>

        <!-- Mallattoo Qur'aanaa -->
        <div class="emblem" aria-hidden="true">
            <div class="ring"></div>
            <div class="ring two"></div>
            <span class="rub">۞</span>
            <svg viewBox="0 0 140 110" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="pg" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0" stop-color="#22c55e"/>
                        <stop offset="1" stop-color="#14532d"/>
                    </linearGradient>
                </defs>
                <path d="M70 26C52 12 26 12 8 22V92C26 82 52 82 70 96Z" fill="url(#pg)" stroke="#f59e0b" stroke-width="3" stroke-linejoin="round"/>
                <path d="M70 26C88 12 114 12 132 22V92C114 82 88 82 70 96Z" fill="url(#pg)" stroke="#f59e0b" stroke-width="3" stroke-linejoin="round"/>
                <g stroke="#fde68a" stroke-width="2" stroke-linecap="round" opacity=".85">
                    <path d="M20 36C32 32 46 33 58 40"/><path d="M20 48C32 44 46 45 58 52"/><path d="M20 60C32 56 46 57 58 64"/>
                    <path d="M120 36C108 32 94 33 82 40"/><path d="M120 48C108 44 94 45 82 52"/><path d="M120 60C108 56 94 57 82 64"/>
                </g>
                <path d="M70 26V96" stroke="#f59e0b" stroke-width="3"/>
                <path d="M70 2l5 9 10 1-7 7 2 10-10-5-10 5 2-10-7-7 10-1z" fill="#f59e0b"/>
            </svg>
            <div class="verse-chip">
                <div class="ar" lang="ar">اقْرَأْ بِاسْمِ رَبِّكَ</div>
                <small>Maqaa Gooftaa kee jedhiitiin dubbisi</small>
            </div>
        </div>
    </div>
</main>

<p class="divider" aria-hidden="true">✦ ❖ ۞ ❖ ✦</p>

<!-- ABOUT -->
<section id="about" class="band">
    <div class="wrap">
        <div class="sec-head reveal">
            <span class="ar" lang="ar">عَنْ مَدْرَسَتِنَا</span>
            <h2>Waa'ee Keenya</h2>
            <span class="bar"></span>
        </div>
        <p class="lead reveal d1">
            {{ $schoolName }} manni barumsaa ilmaan keenya Qur'aanaa fi ilmii Islaamaa waliin
            barnoota ammayyaa kutaa 1–8 baratan, akhlaaqa gaarii fi amantii cimaa qabaatanii
            guddatan ti. Ifa beekumsaa fi imaanaatiin dhaloota boruu ijaarra.
        </p>
        <div class="stats reveal d2">
            <div class="stat"><b>📖</b><span>Qur'aana &amp; Tajwiid</span></div>
            <div class="stat"><b>🕌</b><span>Ilmii Islaamaa</span></div>
            <div class="stat"><b>🔤</b><span>Afaan Arabaa</span></div>
            <div class="stat"><b>🎓</b><span>Kutaa 1–8</span></div>
        </div>
    </div>
</section>

<!-- PROGRAMS -->
<section id="programs">
    <div class="wrap">
        <div class="sec-head reveal">
            <span class="ar" lang="ar">بَرَامِجُنَا</span>
            <h2>Sagantaalee Keenya</h2>
            <span class="bar"></span>
        </div>
        <div class="grid cols3">
            <div class="card reveal"><div class="ico">📖</div><h3>Qur'aana Barachuu</h3><p>Qiraa'aa, Tajwiid fi gabaabbii Qur'aanaa qabachuu (Hifz) ogeessota biratti.</p></div>
            <div class="card reveal d1"><div class="ico">🕌</div><h3>Ilmii Islaamaa</h3><p>Aqiidaa, Fiqhii, Hadiisaa fi seenaa Islaamaa sirna barnootaa qabuun.</p></div>
            <div class="card reveal d2"><div class="ico">🔤</div><h3>Afaan Arabaa</h3><p>Dubbisuu, barreessuu fi dubbachuu Afaan Arabaa kutaa kutaadhaan.</p></div>
            <div class="card reveal"><div class="ico">🎓</div><h3>Barnoota Idilee</h3><p>Herrega, saayinsii, Afaan Oromoo fi Ingiliffaa kutaa 1–8.</p></div>
            <div class="card reveal d1"><div class="ico">🤲</div><h3>Akhlaaqa Gaarii</h3><p>Amanamummaa, kabaja, obsaa fi wal-gargaarsa daa'immanitti ijaaruu.</p></div>
            <div class="card reveal d2"><div class="ico">👨‍👩‍👧</div><h3>Maatii waliin Hojjechuu</h3><p>Maatiin Parent portal'iin guddina ijoollee isaanii hordofu.</p></div>
        </div>
    </div>
</section>

<p class="divider" aria-hidden="true">✦ ❖ ۞ ❖ ✦</p>

<!-- QUOTES -->
<section id="quotes" class="quotes">
    <div class="wrap">
        <div class="sec-head reveal">
            <span class="ar" lang="ar">حِكَمٌ وَآيَاتٌ</span>
            <h2>Jechoota Bareedaa</h2>
            <span class="bar"></span>
        </div>
        <div class="grid cols3">
            <div class="quote reveal">
                <div class="ar" lang="ar">اقْرَأْ بِاسْمِ رَبِّكَ الَّذِي خَلَقَ</div>
                <div class="om">“Maqaa Gooftaa kee kan uume jedhiitiin dubbisi.”</div>
                <span class="ref">Suuraa Al-ʿAlaq 96:1</span>
            </div>
            <div class="quote reveal d1">
                <div class="ar" lang="ar">وَقُلْ رَبِّ زِدْنِي عِلْمًا</div>
                <div class="om">“Jedhi: Yaa Gooftaa koo, beekumsa naaf dabali.”</div>
                <span class="ref">Suuraa Ṭaha 20:114</span>
            </div>
            <div class="quote reveal d2">
                <div class="ar" lang="ar">هَلْ يَسْتَوِي الَّذِينَ يَعْلَمُونَ وَالَّذِينَ لَا يَعْلَمُونَ</div>
                <div class="om">“Warri beekanii fi warri hin beekne wal-qixxaataa?”</div>
                <span class="ref">Suuraa Az-Zumar 39:9</span>
            </div>
            <div class="quote reveal">
                <div class="ar" lang="ar">طَلَبُ الْعِلْمِ فَرِيضَةٌ عَلَى كُلِّ مُسْلِمٍ</div>
                <div class="om">“Beekumsa barbaaduun Muslimaa hundaaf faradha.”</div>
                <span class="ref">Hadiisa · Ibn Maajah</span>
            </div>
            <div class="quote reveal d1">
                <div class="ar" lang="ar">خَيْرُكُمْ مَنْ تَعَلَّمَ الْقُرْآنَ وَعَلَّمَهُ</div>
                <div class="om">“Isin keessaa kan caalu, kan Qur'aana baratee nama biraa barsiisudha.”</div>
                <span class="ref">Hadiisa · Al-Bukhaariy</span>
            </div>
            <div class="quote reveal d2">
                <div class="ar" lang="ar">إِنَّمَا يَخْشَى اللَّهَ مِنْ عِبَادِهِ الْعُلَمَاءُ</div>
                <div class="om">“Gooftaa isa sodaatan, tajaajiltoota isaa keessaa ogeeyyiidha.”</div>
                <span class="ref">Suuraa Faaṭir 35:28</span>
            </div>
        </div>
        <p class="note">Tarjumaan Afaan Oromoo hiika gabaabaa dha. Mudaa yoo jiraate ogeessa (ustaazii) waliin mirkaneeffadhu.</p>
    </div>
</section>

<!-- ADMISSION CTA -->
<section id="admission">
    <div class="wrap">
        <div class="cta-box reveal">
            <div class="ar" lang="ar">بَارَكَ اللَّهُ فِيكُمْ</div>
            <h2>Daa'imman kee nu waliin ifaan guddisi</h2>
            <p>Apply for admission to {{ $schoolName }} · Galmeen yeroo galmee qofa banama.</p>
            <a class="button" href="{{ route('admissions.create') }}">Apply for admission</a>
        </div>
    </div>
</section>

<!-- FOOTER -->
<div class="site-footer">
    <div class="foot-grid">
        <div>
            <h4>☪ {{ $schoolName }}</h4>
            <div class="ar" lang="ar">مَدْرَسَةُ النُّورِ الإِسْلَامِيَّةُ</div>
            <p>Ifa beekumsaa fi imaanaa, ijoollee keenyaaf.</p>
        </div>
        <div>
            <h4>Linkii Ariifataa</h4>
            <a href="#home">Mana</a>
            <a href="#about">Waa'ee Keenya</a>
            <a href="#programs">Sagantaalee</a>
            <a href="#quotes">Jechoota</a>
            <a href="{{ route('admissions.create') }}">Galmee</a>
        </div>
        <div>
            <h4>Sign in</h4>
            <a href="{{ route('student.login') }}">Student sign in</a>
            <a href="{{ route('teacher.login') }}">Teacher sign in</a>
            <a href="{{ route('parent.login') }}">Parent sign in</a>
            <a href="/admin/login">Staff sign in</a>
        </div>
    </div>
    <div class="foot-bottom">
        <footer>{{ $schoolName }} · Grades 1–8 · © {{ date('Y') }}</footer>
        <div class="ar" lang="ar" style="margin-top:.4rem;font-size:1.2rem;color:#fde68a">جَزَاكُمُ اللَّهُ خَيْرًا</div>
    </div>
</div>

<script>
(function(){
    /* Mobile menu */
    var btn=document.getElementById('menuBtn'),nav=document.getElementById('mainNav');
    btn.addEventListener('click',function(){
        var o=nav.classList.toggle('open');
        btn.setAttribute('aria-expanded',o);
        btn.textContent=o?'✕':'☰';
    });
    nav.querySelectorAll('a[href^="#"]').forEach(function(a){
        a.addEventListener('click',function(){nav.classList.remove('open');btn.textContent='☰';});
    });

    /* Sticky shadow + scroll progress */
    var top=document.getElementById('topbar'),bar=document.getElementById('progress');
    function onScroll(){
        var h=document.documentElement,max=h.scrollHeight-h.clientHeight;
        top.classList.toggle('scrolled',window.scrollY>10);
        bar.style.width=(max>0?(window.scrollY/max*100):0)+'%';
    }
    window.addEventListener('scroll',onScroll,{passive:true});onScroll();

    /* Reveal on scroll */
    var io=new IntersectionObserver(function(es){
        es.forEach(function(e){if(e.isIntersecting){e.target.classList.add('show');io.unobserve(e.target);}});
    },{threshold:.15});
    document.querySelectorAll('.reveal').forEach(function(el){io.observe(el);});

    /* Active menu link */
    var links=[].slice.call(nav.querySelectorAll('a.admin[href^="#"]'));
    var sio=new IntersectionObserver(function(es){
        es.forEach(function(e){
            if(e.isIntersecting){
                links.forEach(function(l){l.classList.toggle('active',l.getAttribute('href')==='#'+e.target.id);});
            }
        });
    },{rootMargin:'-45% 0px -50% 0px'});
    ['home','about','programs','quotes','admission'].forEach(function(id){
        var el=document.getElementById(id);if(el)sio.observe(el);
    });

    /* Floating Islamic symbols */
    var wrap=document.getElementById('floaters'),sy=['☪','۞','✦','❖','📖','✧'];
    for(var i=0;i<12;i++){
        var s=document.createElement('span');
        s.textContent=sy[i%sy.length];
        s.style.left=(Math.random()*95)+'%';
        s.style.fontSize=(1+Math.random()*1.8)+'rem';
        s.style.animationDuration=(18+Math.random()*22)+'s';
        s.style.animationDelay=(-Math.random()*30)+'s';
        wrap.appendChild(s);
    }
})();
</script>
</body>
</html>