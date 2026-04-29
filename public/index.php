<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PanApp | Sistema de Panadería</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --orange: #F97316;
            --orange-dark: #EA6A0A;
            --orange-light: #FED7AA;
            --orange-soft: #FFF7ED;
            --cream: #FEF3E8;
            --text-dark: #1C0A00;
            --text-mid: #6B4F3A;
            --text-light: #A87D5C;
            --border: #F3D5B5;
            --white: #FFFFFF;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--cream);
            color: var(--text-dark);
            min-height: 100vh;
        }

        /* ── Background bread pattern ── */
        .bg-pattern {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }
        .bg-icon {
            position: absolute;
            font-size: 36px;
            opacity: 0.08;
            animation: floatIcon 9s ease-in-out infinite;
            user-select: none;
        }
        @keyframes floatIcon {
            0%,100% { transform: translateY(0) rotate(0deg); }
            50%      { transform: translateY(-14px) rotate(10deg); }
        }

        /* ── NAV ── */
        nav {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 0 32px;
            height: 64px;
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
            width: 38px; height: 38px;
            background: var(--orange);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            box-shadow: 0 3px 10px rgba(249,115,22,0.35);
        }
        .nav-name {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            color: var(--text-dark);
        }
        .nav-name span { color: var(--orange); }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-links a {
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            color: var(--text-mid);
            padding: 6px 14px;
            border-radius: 8px;
            transition: color 0.2s, background 0.2s;
        }
        .nav-links a:hover,
        .nav-links a.active { color: var(--orange); background: rgba(249,115,22,0.08); }
        .nav-links a.btn-nav {
            background: var(--orange);
            color: #fff;
            padding: 8px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(249,115,22,0.3);
            transition: background 0.2s, transform 0.15s;
        }
        .nav-links a.btn-nav:hover {
            background: var(--orange-dark);
            color: #fff;
            transform: translateY(-1px);
        }

        /* ── HERO ── */
        .hero {
            position: relative;
            z-index: 1;
            min-height: calc(100vh - 64px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 60px 24px 40px;
        }
        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(249,115,22,0.12);
            border: 1px solid rgba(249,115,22,0.3);
            color: var(--orange-dark);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 99px;
            margin-bottom: 24px;
            animation: fadeUp 0.5s ease both;
        }
        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(36px, 6vw, 68px);
            line-height: 1.12;
            color: var(--text-dark);
            max-width: 780px;
            animation: fadeUp 0.55s ease both 0.08s;
        }
        .hero h1 span { color: var(--orange); }
        .hero p {
            margin-top: 18px;
            font-size: 16px;
            font-weight: 600;
            color: var(--text-mid);
            max-width: 480px;
            line-height: 1.6;
            animation: fadeUp 0.6s ease both 0.16s;
        }
        .hero-btn {
            margin-top: 32px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--orange);
            color: #fff;
            font-family: 'Nunito', sans-serif;
            font-size: 15px;
            font-weight: 900;
            padding: 14px 32px;
            border-radius: 14px;
            text-decoration: none;
            box-shadow: 0 8px 24px rgba(249,115,22,0.38);
            transition: transform 0.15s, box-shadow 0.15s, background 0.2s;
            animation: fadeUp 0.65s ease both 0.24s;
        }
        .hero-btn:hover {
            background: var(--orange-dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(249,115,22,0.45);
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── SECTION LABEL ── */
        .section-label {
            text-align: center;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text-light);
            margin-bottom: 28px;
        }

        /* ── CARDS GRID ── */
        .cards-section {
            position: relative;
            z-index: 1;
            padding: 0 32px 80px;
            max-width: 1100px;
            margin: 0 auto;
        }
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 28px 24px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
            animation: fadeUp 0.5s ease both;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(249,115,22,0.12);
            border-color: var(--orange-light);
        }
        .card-icon-wrap {
            width: 52px; height: 52px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
            margin-bottom: 4px;
        }
        .card h3 {
            font-size: 16px;
            font-weight: 900;
            color: var(--text-dark);
        }
        .card p {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-light);
            line-height: 1.5;
            flex: 1;
        }
        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 6px;
        }
        .badge {
            font-size: 11px;
            font-weight: 800;
            padding: 3px 10px;
            border-radius: 99px;
        }
        .badge-emp  { background: #EDE9FE; color: #6D28D9; }
        .badge-admin{ background: #D1FAE5; color: #065F46; }
        .badge-all  { background: #FEF3C7; color: #92400E; }
        .arrow-icon { color: var(--text-light); font-size: 14px; transition: color 0.2s, transform 0.2s; }
        .card:hover .arrow-icon { color: var(--orange); transform: translateX(4px); }

        /* Card delay stagger */
        .card:nth-child(1) { animation-delay: 0.3s; }
        .card:nth-child(2) { animation-delay: 0.38s; }
        .card:nth-child(3) { animation-delay: 0.46s; }
        .card:nth-child(4) { animation-delay: 0.54s; }

        /* ── FOOTER ── */
        footer {
            position: relative;
            z-index: 1;
            border-top: 1px solid var(--border);
            padding: 20px 32px;
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-light);
        }
        footer span.brand { color: var(--orange); font-weight: 900; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .cards-grid { grid-template-columns: 1fr; }
            .nav-links a:not(.btn-nav) { display: none; }
            nav { padding: 0 20px; }
            .cards-section { padding: 0 20px 60px; }
        }
        @media (min-width: 769px) and (max-width: 1024px) {
            .cards-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

    <!-- Background bread pattern -->
    <div class="bg-pattern">
        <span class="bg-icon" style="top:5%;left:4%;animation-delay:0s">🥐</span>
        <span class="bg-icon" style="top:12%;left:90%;animation-delay:1.5s">🍞</span>
        <span class="bg-icon" style="top:30%;left:2%;animation-delay:3s">🧁</span>
        <span class="bg-icon" style="top:50%;left:94%;animation-delay:0.8s">🥖</span>
        <span class="bg-icon" style="top:70%;left:8%;animation-delay:2.2s">🍩</span>
        <span class="bg-icon" style="top:85%;left:85%;animation-delay:1s">🥐</span>
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
            <a href="#ventas">Ventas</a>
            <a href="#inventario">Inventario</a>
            <a href="#reportes">Reportes</a>
            <a href="/PanApp/views/usuarios/login.php" class="btn-nav">Iniciar sesión</a>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero" id="inicio">
        <div class="hero-tag">🥖 Sistema de gestión para panaderías</div>
        <h1>El mejor aroma es el del<br><span>pan recién hecho</span></h1>
        <p>Registra ventas, controla tu inventario y genera reportes sin complicaciones. Hecho para panaderías.</p>
        <a href="/PanApp/views/usuarios/login.php" class="hero-btn">
            🗝️ Ingresar ahora
        </a>
    </section>

    <!-- CARDS -->
    <section class="cards-section">
        <p class="section-label">¿Qué quieres hacer hoy?</p>
        <div class="cards-grid">

            <a href="views/ventas/index.php" class="card" id="ventas">
                <div class="card-icon-wrap" style="background:#FFF7ED;">🛒</div>
                <h3>Registrar venta</h3>
                <p>Agrega productos y cobra rápido.</p>
                <div class="card-footer">
                    <span class="badge badge-emp">Empleado</span>
                    <i class="fas fa-arrow-right arrow-icon"></i>
                </div>
            </a>

            <a href="views/inventario/index.php" class="card" id="inventario">
                <div class="card-icon-wrap" style="background:#F0FDF4;">📦</div>
                <h3>Inventario</h3>
                <p>Consulta y actualiza el stock.</p>
                <div class="card-footer">
                    <span class="badge badge-emp">admin</span>
                    <i class="fas fa-arrow-right arrow-icon"></i>
                </div>
            </a>

            <a href="views/reportes/index.php" class="card" id="reportes">
                <div class="card-icon-wrap" style="background:#F0FDF4;">📊</div>
                <h3>Reportes</h3>
                <p>Ventas diarias, semanales y más.</p>
                <div class="card-footer">
                    <span class="badge badge-admin">Admin</span>
                    <i class="fas fa-arrow-right arrow-icon"></i>
                </div>
            </a>
                </div>
            </a>

        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <span class="brand">PanApp</span> &nbsp;·&nbsp; Hecho con ❤️ para panaderías colombianas &nbsp;·&nbsp; 2026
    </footer>

</body>
</html>