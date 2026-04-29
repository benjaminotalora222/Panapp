<?php
session_start();
$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PanApp | Iniciar Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            --error-bg: #FFF1F0;
            --error-border: #FFD3CC;
            --error-text: #D94F2E;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--cream);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            background-image: radial-gradient(circle at 15% 20%, rgba(249,115,22,0.07) 0%, transparent 50%),
                              radial-gradient(circle at 85% 80%, rgba(249,115,22,0.05) 0%, transparent 50%);
        }

        .bg-pattern { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
        .bg-icon { position: absolute; font-size: 32px; opacity: 0.08; animation: floatIcon 9s ease-in-out infinite; user-select: none; }
        @keyframes floatIcon {
            0%,100% { transform: translateY(0) rotate(0deg); }
            50%      { transform: translateY(-14px) rotate(10deg); }
        }

        .top-nav {
            position: relative; z-index: 10;
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 28px;
            animation: fadeDown 0.5s ease both;
        }
        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .nav-logo {
            width: 42px; height: 42px; background: var(--orange);
            border-radius: 12px; display: flex; align-items: center; justify-content: center;
            font-size: 22px; box-shadow: 0 4px 14px rgba(249,115,22,0.35);
        }
        .nav-name { font-family: 'Playfair Display', serif; font-size: 24px; color: var(--text-dark); }
        .nav-name span { color: var(--orange); }

        .card {
            position: relative; z-index: 10; width: 100%; max-width: 900px;
            background: #fff; border-radius: 28px;
            box-shadow: 0 8px 40px rgba(249,115,22,0.10), 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid var(--border); overflow: hidden;
            display: flex; min-height: 520px;
            animation: fadeUp 0.55s ease both 0.1s;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .left-panel {
            width: 45%;
            background: linear-gradient(150deg, #F97316 0%, #FB923C 60%, #FDBA74 100%);
            padding: 48px 40px; display: flex; flex-direction: column;
            justify-content: space-between; position: relative; overflow: hidden;
        }
        .left-panel::before {
            content: ''; position: absolute; top: -50px; right: -50px;
            width: 200px; height: 200px; background: rgba(255,255,255,0.08); border-radius: 50%;
        }
        .left-panel::after {
            content: ''; position: absolute; bottom: -40px; left: -30px;
            width: 160px; height: 160px; background: rgba(255,255,255,0.06); border-radius: 50%;
        }
        .left-top h2 {
            font-family: 'Playfair Display', serif; font-size: 30px; color: #fff;
            margin-bottom: 14px; line-height: 1.2; position: relative; z-index: 1;
        }
        .left-top p {
            font-size: 14px; font-weight: 600; color: rgba(255,255,255,0.82);
            line-height: 1.65; position: relative; z-index: 1;
        }
        .left-bottom { display: flex; align-items: center; gap: 14px; position: relative; z-index: 1; }
        .left-bottom .icon-box {
            width: 52px; height: 52px; background: rgba(255,255,255,0.22);
            border-radius: 14px; display: flex; align-items: center; justify-content: center;
            font-size: 22px; backdrop-filter: blur(4px);
            border: 1.5px solid rgba(255,255,255,0.3); flex-shrink: 0;
        }
        .left-bottom p { font-size: 12.5px; font-weight: 600; color: rgba(255,255,255,0.85); font-style: italic; line-height: 1.5; }
        .bread-deco { position: absolute; opacity: 0.12; font-size: 48px; animation: floatIcon 7s ease-in-out infinite; }

        .right-panel { flex: 1; padding: 48px 44px; display: flex; flex-direction: column; justify-content: center; }

        .right-header { margin-bottom: 30px; }
        .right-header .icon-wrap {
            width: 56px; height: 56px; background: var(--orange-soft);
            border-radius: 16px; display: flex; align-items: center; justify-content: center;
            font-size: 26px; margin-bottom: 16px; border: 1.5px solid var(--border);
        }
        .right-header h1 { font-family: 'Playfair Display', serif; font-size: 24px; color: var(--text-dark); margin-bottom: 4px; }
        .right-header p { font-size: 13px; font-weight: 600; color: var(--text-light); }

        .alert {
            display: flex; align-items: center; gap: 10px;
            background: var(--error-bg); border: 1px solid var(--error-border);
            border-radius: 12px; padding: 11px 16px; margin-bottom: 20px;
            font-size: 13.5px; font-weight: 700; color: var(--error-text);
        }

        .field { display: flex; flex-direction: column; gap: 5px; margin-bottom: 18px; }
        label { font-size: 12.5px; font-weight: 800; color: var(--text-mid); letter-spacing: 0.3px; text-transform: uppercase; }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: var(--text-light); font-size: 14px; pointer-events: none;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%; padding: 12px 14px 12px 40px;
            background: var(--orange-soft); border: 1.5px solid var(--border);
            border-radius: 14px; font-family: 'Nunito', sans-serif;
            font-size: 14px; font-weight: 600; color: var(--text-dark);
            outline: none; transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        }
        input::placeholder { color: #C9A880; font-weight: 600; }
        input:focus { border-color: var(--orange); background: #fff; box-shadow: 0 0 0 3px rgba(249,115,22,0.12); }

        .toggle-pass {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            color: var(--text-light); cursor: pointer; font-size: 15px;
            background: none; border: none; padding: 0; transition: color 0.2s;
        }
        .toggle-pass:hover { color: var(--orange); }

        .meta-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .remember { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: var(--text-mid); cursor: pointer; }
        .remember input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--orange); padding: 0; }
        .forgot { font-size: 13px; font-weight: 800; color: var(--orange); text-decoration: none; transition: color 0.2s; }
        .forgot:hover { color: var(--orange-dark); text-decoration: underline; }

        .btn-submit {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #F97316, #FB923C); color: #fff;
            font-family: 'Nunito', sans-serif; font-size: 15px; font-weight: 900;
            border: none; border-radius: 14px; cursor: pointer;
            box-shadow: 0 6px 20px rgba(249,115,22,0.35);
            transition: transform 0.15s, box-shadow 0.15s, background 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(249,115,22,0.42); background: linear-gradient(135deg, #EA6A0A, #F97316); }
        .btn-submit:active { transform: translateY(0); }

        .divider { display: flex; align-items: center; gap: 12px; margin: 20px 0 16px; color: var(--text-light); font-size: 12px; font-weight: 700; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        .footer-link { text-align: center; font-size: 13px; font-weight: 700; color: var(--text-light); }
        .footer-link a { color: var(--orange); text-decoration: none; font-weight: 900; }
        .footer-link a:hover { text-decoration: underline; }

        .back-link {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            font-size: 13px; font-weight: 700; color: var(--text-light);
            text-decoration: none; margin-top: 8px; transition: color 0.2s;
        }
        .back-link:hover { color: var(--orange); }

        @media (max-width: 680px) {
            .left-panel { display: none; }
            .right-panel { padding: 36px 28px; }
            .card { max-width: 460px; }
        }
    </style>
</head>
<body>

    <div class="bg-pattern">
        <span class="bg-icon" style="top:6%;left:4%;animation-delay:0s">🥐</span>
        <span class="bg-icon" style="top:14%;left:90%;animation-delay:1.4s">🍞</span>
        <span class="bg-icon" style="top:38%;left:2%;animation-delay:2.8s">🧁</span>
        <span class="bg-icon" style="top:58%;left:94%;animation-delay:0.7s">🥖</span>
        <span class="bg-icon" style="top:78%;left:7%;animation-delay:2s">🍩</span>
        <span class="bg-icon" style="top:88%;left:82%;animation-delay:3.2s">🥐</span>
        <span class="bg-icon" style="top:22%;left:50%;animation-delay:1.8s">🧇</span>
        <span class="bg-icon" style="top:65%;left:45%;animation-delay:0.4s">🍰</span>
    </div>

    <nav class="top-nav">
        <div class="nav-logo">🥐</div>
        <div class="nav-name">Pan<span>App</span></div>
    </nav>

    <div class="card">

        <div class="left-panel">
            <span class="bread-deco" style="top:10%;right:12%;animation-delay:0s">🥐</span>
            <span class="bread-deco" style="bottom:18%;right:8%;font-size:36px;animation-delay:1.5s">🍞</span>
            <div class="left-top">
                <h2>¡Bienvenido de nuevo!</h2>
                <p>Accede a tu panadería para gestionar ventas, controlar el inventario y revisar reportes en tiempo real.</p>
            </div>
            <div class="left-bottom">
                <div class="icon-box">🔒</div>
                <p>Acceso seguro y protegido para tu negocio.</p>
            </div>
        </div>

        <div class="right-panel">

            <div class="right-header">
                <div class="icon-wrap">🗝️</div>
                <h1>Iniciar Sesión</h1>
                <p>Ingresa tus datos para acceder al sistema</p>
            </div>

            <?php if ($alert && $alert['icon'] === 'error'): ?>
            <div class="alert">
                <span>⚠️</span>
                <span><?= htmlspecialchars($alert['text']) ?></span>
            </div>
            <?php endif; ?>

            <form action="../../controllers/AuthController.php" method="POST">

                <div class="field">
                    <label>Correo Electrónico</label>
                    <div class="input-wrap">
                        <i class="input-icon fas fa-envelope"></i>
                        <!-- ✅ name="email" coincide con AuthController y la BD -->
                        <input type="email" name="email" placeholder="correo@ejemplo.com" required>
                    </div>
                </div>

                <div class="field">
                    <label>Contraseña</label>
                    <div class="input-wrap">
                        <i class="input-icon fas fa-lock"></i>
                        <input type="password" name="password" id="passField" placeholder="••••••••" required>
                        <button type="button" class="toggle-pass" onclick="togglePass()">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="meta-row">
                    <label class="remember">
                        <input type="checkbox" name="recordar">
                        Recordarme
                    </label>
                    <a href="#" class="forgot">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="btn-submit">
                    Ingresar <i class="fas fa-arrow-right"></i>
                </button>

            </form>

            <div class="divider">o</div>

            <div class="footer-link">
                ¿No tienes cuenta? <a href="registre.php">Regístrate aquí</a>
            </div>
            <a href="../../index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Volver al inicio
            </a>

        </div>
    </div>

    <script>
        function togglePass() {
            const input = document.getElementById('passField');
            const icon  = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>

    <?php if ($alert): ?>
    <script>
        Swal.fire({
            icon: '<?= htmlspecialchars($alert['icon']) ?>',
            title: '<?= htmlspecialchars($alert['title']) ?>',
            text: '<?= htmlspecialchars($alert['text']) ?>',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#F97316'
        });
    </script>
    <?php endif; ?>

</body>
</html>