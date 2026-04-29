<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PanApp | Sistema de Panadería</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Playfair+Display:ital,wght@0,700;0,800;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --orange: #F97316;
            --orange-dark: #C2520C;
            --orange-light: #FED7AA;
            --orange-soft: #FFF7ED;
            --cream: #FEF3E8;
            --cream-dark: #FAE8D0;
            --text-dark: #1C0A00;
            --text-mid: #6B4F3A;
            --text-light: #A87D5C;
            --border: #F3D5B5;
            --white: #FFFFFF;
            --green: #10B981;
            --purple: #8B5CF6;
            --blue: #3B82F6;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--cream);
            color: var(--text-dark);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── Scroll reveal base ── */
        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.65s ease, transform 0.65s ease;
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ── Background pattern ── */
        .bg-pattern {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }
        .bg-icon {
            position: absolute;
            font-size: 34px;
            opacity: 0.07;
            animation: floatIcon 10s ease-in-out infinite;
            user-select: none;
        }
        @keyframes floatIcon {
            0%,100% { transform: translateY(0) rotate(0deg); }
            50%      { transform: translateY(-16px) rotate(12deg); }
        }

        /* ── NAV ── */
        nav {
            position: sticky;
            top: 0;
            z-index: 200;
            background: rgba(254,243,232,0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            padding: 0 40px;
            height: 66px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .nav-logo {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 14px rgba(249,115,22,0.4);
        }
        .nav-name {
            font-family: 'Playfair Display', serif;
            font-size: 23px;
            color: var(--text-dark);
        }
        .nav-name span { color: var(--orange); }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .nav-links a {
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            color: var(--text-mid);
            padding: 7px 15px;
            border-radius: 9px;
            transition: color 0.2s, background 0.2s;
        }
        .nav-links a:hover,
        .nav-links a.active { color: var(--orange); background: rgba(249,115,22,0.09); }
        .nav-links a.btn-nav {
            background: linear-gradient(135deg, var(--orange), #EA6A0A);
            color: #fff;
            padding: 9px 22px;
            border-radius: 11px;
            box-shadow: 0 4px 14px rgba(249,115,22,0.35);
            transition: transform 0.15s, box-shadow 0.15s;
            margin-left: 6px;
        }
        .nav-links a.btn-nav:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 8px 22px rgba(249,115,22,0.5);
        }

        /* ── HERO ── */
        .hero {
            position: relative;
            z-index: 1;
            min-height: calc(100vh - 66px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 70px 24px 20px;
            overflow: hidden;
        }

        /* Gradient orbs */
        .hero::before {
            content: '';
            position: absolute;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(249,115,22,0.13) 0%, transparent 70%);
            top: -100px; left: 50%;
            transform: translateX(-50%);
            pointer-events: none;
            animation: pulsOrb 6s ease-in-out infinite;
        }
        .hero::after {
            content: '';
            position: absolute;
            width: 320px; height: 320px;
            background: radial-gradient(circle, rgba(249,115,22,0.09) 0%, transparent 70%);
            bottom: 60px; right: 5%;
            pointer-events: none;
            animation: pulsOrb 8s ease-in-out infinite reverse;
        }
        @keyframes pulsOrb {
            0%,100% { transform: translateX(-50%) scale(1); }
            50%      { transform: translateX(-50%) scale(1.1); }
        }

        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(249,115,22,0.11);
            border: 1.5px solid rgba(249,115,22,0.28);
            color: var(--orange-dark);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 6px 16px;
            border-radius: 99px;
            margin-bottom: 26px;
            animation: fadeUp 0.5s ease both;
        }
        .hero-tag .dot {
            width: 6px; height: 6px;
            background: var(--orange);
            border-radius: 50%;
            animation: blink 2s ease-in-out infinite;
        }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(38px, 6.5vw, 72px);
            line-height: 1.1;
            color: var(--text-dark);
            max-width: 820px;
            animation: fadeUp 0.55s ease both 0.08s;
        }
        .hero h1 span { color: var(--orange); font-style: italic; }
        .hero p {
            margin-top: 20px;
            font-size: 16px;
            font-weight: 600;
            color: var(--text-mid);
            max-width: 500px;
            line-height: 1.65;
            animation: fadeUp 0.6s ease both 0.16s;
        }

        .hero-actions {
            margin-top: 34px;
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            justify-content: center;
            animation: fadeUp 0.65s ease both 0.24s;
        }
        .hero-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--orange), #EA6A0A);
            color: #fff;
            font-family: 'Nunito', sans-serif;
            font-size: 15px;
            font-weight: 900;
            padding: 15px 34px;
            border-radius: 14px;
            text-decoration: none;
            box-shadow: 0 10px 30px rgba(249,115,22,0.38);
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .hero-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 38px rgba(249,115,22,0.5);
        }
        .hero-btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: transparent;
            color: var(--text-mid);
            font-size: 14px;
            font-weight: 800;
            padding: 14px 24px;
            border-radius: 14px;
            text-decoration: none;
            border: 1.5px solid var(--border);
            transition: border-color 0.2s, color 0.2s, background 0.2s;
        }
        .hero-btn-ghost:hover {
            border-color: var(--orange-light);
            color: var(--orange);
            background: rgba(249,115,22,0.05);
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── STATS BAND ── */
        .stats-band {
            position: relative;
            z-index: 1;
            background: var(--white);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            padding: 26px 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            flex-wrap: wrap;
        }
        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px 48px;
            border-right: 1px solid var(--border);
        }
        .stat-item:last-child { border-right: none; }
        .stat-num {
            font-family: 'Playfair Display', serif;
            font-size: 30px;
            color: var(--orange);
            line-height: 1;
        }
        .stat-label {
            font-size: 11px;
            font-weight: 800;
            color: var(--text-light);
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        /* ── SECTION HEADER ── */
        .section-header {
            text-align: center;
            margin-bottom: 48px;
        }
        .section-eyebrow {
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--orange);
            margin-bottom: 12px;
            display: block;
        }
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(26px, 3.5vw, 38px);
            color: var(--text-dark);
            line-height: 1.15;
        }
        .section-title span { color: var(--orange); font-style: italic; }
        .section-sub {
            margin-top: 12px;
            font-size: 15px;
            font-weight: 600;
            color: var(--text-light);
            max-width: 460px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        /* ── CARDS SECTION ── */
        .cards-section {
            position: relative;
            z-index: 1;
            padding: 72px 40px 80px;
            max-width: 1140px;
            margin: 0 auto;
        }
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }
        .card {
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: 22px;
            padding: 28px 22px 22px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: transform 0.22s, box-shadow 0.22s, border-color 0.22s;
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent 60%, rgba(249,115,22,0.04));
            opacity: 0;
            transition: opacity 0.3s;
        }
        .card:hover::before { opacity: 1; }
        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 40px rgba(249,115,22,0.14);
            border-color: var(--orange-light);
        }
        .card-icon-wrap {
            width: 54px; height: 54px;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 26px;
            margin-bottom: 2px;
            transition: transform 0.2s;
        }
        .card:hover .card-icon-wrap { transform: scale(1.1) rotate(-4deg); }
        .card h3 {
            font-size: 16px;
            font-weight: 900;
            color: var(--text-dark);
        }
        .card p {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-light);
            line-height: 1.55;
            flex: 1;
        }
        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 8px;
            padding-top: 14px;
            border-top: 1px solid var(--border);
        }
        .badge {
            font-size: 10px;
            font-weight: 900;
            padding: 3px 10px;
            border-radius: 99px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .badge-emp   { background: #EDE9FE; color: #6D28D9; }
        .badge-admin { background: #D1FAE5; color: #065F46; }
        .badge-all   { background: #FEF3C7; color: #92400E; }
        .badge-blue  { background: #DBEAFE; color: #1E40AF; }
        .arrow-icon  { color: var(--text-light); font-size: 13px; transition: color 0.2s, transform 0.2s; }
        .card:hover .arrow-icon { color: var(--orange); transform: translateX(5px); }

        /* ── WHY PANAPP ── */
        .features-section {
            position: relative;
            z-index: 1;
            background: var(--white);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            padding: 80px 40px;
        }
        .features-inner {
            max-width: 1100px;
            margin: 0 auto;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            margin-top: 0;
        }
        .feature-item {
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 32px 28px;
            border-radius: 20px;
            border: 1.5px solid var(--border);
            transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
        }
        .feature-item:hover {
            border-color: var(--orange-light);
            box-shadow: 0 8px 28px rgba(249,115,22,0.1);
            transform: translateY(-3px);
        }
        .feature-num {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            color: var(--orange-light);
            line-height: 1;
            font-weight: 800;
        }
        .feature-item h4 {
            font-size: 18px;
            font-weight: 900;
            color: var(--text-dark);
        }
        .feature-item p {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-light);
            line-height: 1.65;
        }
        .feature-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--orange);
            background: rgba(249,115,22,0.1);
            border-radius: 99px;
            padding: 3px 10px;
            width: fit-content;
        }

        /* ── WAVE DIVIDER ── */
        .wave-divider {
            position: relative;
            z-index: 1;
            line-height: 0;
            overflow: hidden;
        }
        .wave-divider svg { display: block; width: 100%; }

        /* ── CTA STRIP ── */
        .cta-strip {
            position: relative;
            z-index: 1;
            background: linear-gradient(135deg, var(--orange), #EA6A0A);
            padding: 64px 40px;
            text-align: center;
            overflow: hidden;
        }
        .cta-strip::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
            top: -200px; left: -100px;
        }
        .cta-strip::after {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
            bottom: -150px; right: 5%;
        }
        .cta-strip h2 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(26px, 4vw, 42px);
            color: #fff;
            margin-bottom: 14px;
            position: relative;
            z-index: 1;
        }
        .cta-strip p {
            font-size: 15px;
            font-weight: 600;
            color: rgba(255,255,255,0.82);
            margin-bottom: 32px;
            position: relative;
            z-index: 1;
        }
        .cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            color: var(--orange-dark);
            font-weight: 900;
            font-size: 15px;
            padding: 14px 32px;
            border-radius: 14px;
            text-decoration: none;
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
            transition: transform 0.15s, box-shadow 0.15s;
            position: relative;
            z-index: 1;
        }
        .cta-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 32px rgba(0,0,0,0.24);
        }

        /* ── FOOTER ── */
        footer {
            position: relative;
            z-index: 1;
            background: var(--white);
            border-top: 1px solid var(--border);
            padding: 36px 40px 28px;
        }
        .footer-inner {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }
        .footer-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .footer-logo {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px;
        }
        .footer-name {
            font-family: 'Playfair Display', serif;
            font-size: 19px;
            color: var(--text-dark);
        }
        .footer-name span { color: var(--orange); }
        .footer-links {
            display: flex;
            gap: 20px;
        }
        .footer-links a {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-light);
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-links a:hover { color: var(--orange); }
        .footer-copy {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-light);
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            margin-top: 20px;
            max-width: 1100px;
            margin-left: auto;
            margin-right: auto;
        }
        .footer-copy span { color: var(--orange); font-weight: 900; }

        /* ── RESPONSIVE ── */
        @media (max-width: 1024px) {
            .cards-grid { grid-template-columns: repeat(2, 1fr); }
            .features-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            nav { padding: 0 20px; }
            .nav-links a:not(.btn-nav) { display: none; }
            .cards-section { padding: 60px 20px; }
            .cards-grid { grid-template-columns: 1fr; }
            .features-section { padding: 60px 20px; }
            .features-grid { grid-template-columns: 1fr; }
            .stats-band { padding: 20px; gap: 0; }
            .stat-item { padding: 10px 24px; }
            footer { padding: 28px 20px 20px; }
            .footer-inner { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

    <!-- Background pattern -->
    <div class="bg-pattern">
        <span class="bg-icon" style="top:5%;left:4%;animation-delay:0s">🥐</span>
        <span class="bg-icon" style="top:12%;left:90%;animation-delay:1.5s">🍞</span>
        <span class="bg-icon" style="top:30%;left:2%;animation-delay:3s">🧁</span>
        <span class="bg-icon" style="top:50%;left:94%;animation-delay:0.8s">🥖</span>
        <span class="bg-icon" style="top:70%;left:8%;animation-delay:2.2s">🍩</span>
        <span class="bg-icon" style="top:85%;left:86%;animation-delay:1s">🥐</span>
        <span class="bg-icon" style="top:22%;left:48%;animation-delay:2.8s">🧇</span>
        <span class="bg-icon" style="top:62%;left:42%;animation-delay:0.4s">🍰</span>
        <span class="bg-icon" style="top:40%;left:72%;animation-delay:1.8s">🥨</span>
        <span class="bg-icon" style="top:78%;left:28%;animation-delay:3.5s">🍪</span>
    </div>

    <!-- NAV -->
    <nav>
        <a href="#" class="nav-brand">
            <div class="nav-logo">🥐</div>
            <div class="nav-name">Pan<span>App</span></div>
        </a>
        <div class="nav-links">
            <a href="#inicio" class="active">Inicio</a>
            <a href="#modulos">Módulos</a>
            <a href="#por-que">¿Por qué?</a>
            <a href="#reportes">Reportes</a>
            <a href="/PanApp/views/usuarios/login.php" class="btn-nav">🗝️ Iniciar sesión</a>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero" id="inicio">
        <div class="hero-tag">
            <span class="dot"></span>
            🥖 Sistema de gestión para panaderías
        </div>
        <h1>El mejor aroma es el del<br><span>pan recién hecho</span></h1>
        <p>Registra ventas, controla tu inventario y genera reportes sin complicaciones. Hecho con ❤️ para panaderías colombianas.</p>
        <div class="hero-actions">
            <a href="/PanApp/views/usuarios/login.php" class="hero-btn">
                🗝️ Ingresar ahora
            </a>
            <a href="#modulos" class="hero-btn-ghost">
                <i class="fas fa-arrow-down"></i> Ver módulos
            </a>
        </div>
    </section>

    <!-- STATS BAND -->
    <div class="stats-band reveal">
        <div class="stat-item">
            <span class="stat-num">+500</span>
            <span class="stat-label">Ventas registradas</span>
        </div>
        <div class="stat-item">
            <span class="stat-num">100%</span>
            <span class="stat-label">Sin papel</span>
        </div>
        <div class="stat-item">
            <span class="stat-num">3</span>
            <span class="stat-label">Módulos activos</span>
        </div>
        <div class="stat-item">
            <span class="stat-num">24/7</span>
            <span class="stat-label">Disponible siempre</span>
        </div>
    </div>

    <!-- MÓDULOS / CARDS -->
    <section class="cards-section" id="modulos">
        <div class="section-header reveal">
            <span class="section-eyebrow">✦ Módulos del sistema</span>
            <h2 class="section-title">¿Qué quieres hacer <span>hoy?</span></h2>
            <p class="section-sub">Accede rápido a cada área de tu panadería desde un solo lugar.</p>
        </div>

        <div class="cards-grid">
            <a href="views/ventas/index.php" class="card reveal" style="transition-delay:0.05s">
                <div class="card-icon-wrap" style="background:#FFF7ED;">🛒</div>
                <h3>Registrar venta</h3>
                <p>Agrega productos al carrito y cobra rápido con un flujo sin fricciones.</p>
                <div class="card-footer">
                    <span class="badge badge-emp">Empleado</span>
                    <i class="fas fa-arrow-right arrow-icon"></i>
                </div>
            </a>

            <a href="views/inventario/index.php" class="card reveal" style="transition-delay:0.12s">
                <div class="card-icon-wrap" style="background:#F0FDF4;">📦</div>
                <h3>Inventario</h3>
                <p>Consulta el stock disponible, actualiza cantidades y evita quiebres.</p>
                <div class="card-footer">
                    <span class="badge badge-admin">Admin</span>
                    <i class="fas fa-arrow-right arrow-icon"></i>
                </div>
            </a>

            <a href="views/reportes/index.php" class="card reveal" style="transition-delay:0.19s">
                <div class="card-icon-wrap" style="background:#EFF6FF;">📊</div>
                <h3>Reportes</h3>
                <p>Ventas diarias, semanales y mensuales con gráficas y exportación.</p>
                <div class="card-footer">
                    <span class="badge badge-admin">Admin</span>
                    <i class="fas fa-arrow-right arrow-icon"></i>
                </div>
            </a>

            <a href="views/usuarios/index.php" class="card reveal" style="transition-delay:0.26s">
                <div class="card-icon-wrap" style="background:#FDF4FF;">👥</div>
                <h3>Usuarios</h3>
                <p>Gestiona empleados y administradores, controla permisos y accesos.</p>
                <div class="card-footer">
                    <span class="badge badge-blue">Super Admin</span>
                    <i class="fas fa-arrow-right arrow-icon"></i>
                </div>
            </a>
        </div>
    </section>

    <!-- WHY PANAPP -->
    <section class="features-section" id="por-que">
        <div class="features-inner">
            <div class="section-header reveal">
                <span class="section-eyebrow">✦ ¿Por qué PanApp?</span>
                <h2 class="section-title">Hecho para el día a día<br>de tu <span>panadería</span></h2>
                <p class="section-sub">Sin complicaciones, sin papelería, sin excusas.</p>
            </div>
            <div class="features-grid">
                <div class="feature-item reveal" style="transition-delay:0.05s">
                    <div class="feature-num">01</div>
                    <span class="feature-tag">⚡ Rapidez</span>
                    <h4>Cobros en segundos</h4>
                    <p>El flujo de ventas está diseñado para ser tan rápido como sacar el pan del horno. Sin pasos de más, sin confusión.</p>
                </div>
                <div class="feature-item reveal" style="transition-delay:0.12s">
                    <div class="feature-num">02</div>
                    <span class="feature-tag">📦 Control</span>
                    <h4>Inventario en tiempo real</h4>
                    <p>Cada venta descuenta automáticamente del stock. Sabe exactamente cuánto tienes antes de producir más.</p>
                </div>
                <div class="feature-item reveal" style="transition-delay:0.19s">
                    <div class="feature-num">03</div>
                    <span class="feature-tag">📊 Datos</span>
                    <h4>Reportes que hablan</h4>
                    <p>Visualiza tus mejores productos, días pico y totales de ventas. Toma decisiones con información real.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA STRIP -->
    <section class="cta-strip reveal">
        <h2>¿Todo listo para empezar?</h2>
        <p>Entra a tu panel y gestiona tu panadería desde donde estés.</p>
        <a href="/PanApp/views/usuarios/login.php" class="cta-btn">
            🗝️ Ingresar ahora
        </a>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="footer-inner">
            <a href="#" class="footer-brand">
                <div class="footer-logo">🥐</div>
                <div class="footer-name">Pan<span>App</span></div>
            </a>
            <div class="footer-links">
                <a href="#inicio">Inicio</a>
                <a href="#modulos">Módulos</a>
                <a href="#por-que">¿Por qué?</a>
                <a href="/PanApp/views/usuarios/login.php">Iniciar sesión</a>
            </div>
        </div>
        <p class="footer-copy">
            <span>PanApp</span> &nbsp;·&nbsp; Hecho con ❤️ para panaderías colombianas &nbsp;·&nbsp; 2026
        </p>
    </footer>

    <script>
        // Scroll reveal
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('visible');
                    observer.unobserve(e.target);
                }
            });
        }, { threshold: 0.12 });

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        // Active nav link on scroll
        const sections = document.querySelectorAll('section[id], a[id]');
        const navLinks = document.querySelectorAll('.nav-links a:not(.btn-nav)');
        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(s => {
                if (window.scrollY >= s.offsetTop - 100) current = s.id;
            });
            navLinks.forEach(a => {
                a.classList.remove('active');
                if (a.getAttribute('href') === '#' + current) a.classList.add('active');
            });
        }, { passive: true });
    </script>
</body>
</html>