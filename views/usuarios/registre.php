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
    <title>PanApp | Registro</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --orange: #F97316;
            --orange-dark: #EA6A0A;
            --orange-light: #FED7AA;
            --orange-soft: #FFF7ED;
            --cream: #FFFBF5;
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
            padding: 24px 16px 48px;
            background-image: radial-gradient(circle at 20% 20%, rgba(249,115,22,0.07) 0%, transparent 50%),
                              radial-gradient(circle at 80% 80%, rgba(249,115,22,0.05) 0%, transparent 50%);
        }

        .bg-icons { position: fixed; inset: 0; overflow: hidden; pointer-events: none; z-index: 0; }
        .bg-icon { position: absolute; font-size: 28px; opacity: 0.09; animation: floatIcon 8s ease-in-out infinite; }
        @keyframes floatIcon {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(8deg); }
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
            position: relative; z-index: 10; width: 100%; max-width: 520px;
            background: #fff; border-radius: 28px;
            box-shadow: 0 8px 40px rgba(249,115,22,0.10), 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid var(--border); overflow: hidden;
            animation: fadeUp 0.55s ease both 0.1s;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .card-header {
            background: linear-gradient(135deg, #F97316 0%, #FB923C 100%);
            padding: 32px 36px 28px; text-align: center;
            position: relative; overflow: hidden;
        }
        .card-header::before {
            content: ''; position: absolute; top: -30px; right: -30px;
            width: 120px; height: 120px; background: rgba(255,255,255,0.08); border-radius: 50%;
        }
        .card-header::after {
            content: ''; position: absolute; bottom: -20px; left: -20px;
            width: 80px; height: 80px; background: rgba(255,255,255,0.06); border-radius: 50%;
        }
        .icon-wrap {
            width: 68px; height: 68px; background: rgba(255,255,255,0.22);
            border-radius: 20px; display: flex; align-items: center; justify-content: center;
            font-size: 30px; margin: 0 auto 14px;
            backdrop-filter: blur(4px); border: 1.5px solid rgba(255,255,255,0.3);
        }
        .card-header h2 { font-family: 'Playfair Display', serif; font-size: 22px; color: #fff; margin-bottom: 4px; }
        .card-header p { font-size: 13px; color: rgba(255,255,255,0.82); font-weight: 600; }

        .card-body { padding: 32px 36px 36px; }

        .alert {
            display: flex; align-items: center; gap: 10px;
            background: var(--error-bg); border: 1px solid var(--error-border);
            border-radius: 12px; padding: 11px 16px; margin-bottom: 24px;
            font-size: 13.5px; font-weight: 700; color: var(--error-text);
        }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .field { display: flex; flex-direction: column; gap: 5px; margin-bottom: 16px; }
        label {
            font-size: 12.5px; font-weight: 800; color: var(--text-mid);
            letter-spacing: 0.3px; text-transform: uppercase;
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: var(--text-light); font-size: 14px; pointer-events: none;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%; padding: 12px 14px 12px 40px;
            background: var(--orange-soft); border: 1.5px solid var(--border);
            border-radius: 14px; font-family: 'Nunito', sans-serif;
            font-size: 14px; font-weight: 600; color: var(--text-dark);
            outline: none; transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        }
        input::placeholder { color: #C9A880; font-weight: 600; }
        input:focus {
            border-color: var(--orange); background: #fff;
            box-shadow: 0 0 0 3px rgba(249,115,22,0.12);
        }
        .toggle-pass {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            color: var(--text-light); cursor: pointer; font-size: 15px;
            background: none; border: none; padding: 0; transition: color 0.2s;
        }
        .toggle-pass:hover { color: var(--orange); }

        .terms { display: flex; align-items: flex-start; gap: 10px; margin-top: 22px; }
        .terms input[type="checkbox"] {
            width: 17px; height: 17px; min-width: 17px;
            accent-color: var(--orange); margin-top: 1px; padding: 0;
        }
        .terms label {
            font-size: 12.5px; font-weight: 600; color: var(--text-light);
            text-transform: none; letter-spacing: 0; cursor: pointer;
        }
        .terms label a { color: var(--orange); text-decoration: none; font-weight: 800; }
        .terms label a:hover { text-decoration: underline; }

        .btn-submit {
            margin-top: 24px; width: 100%; padding: 15px;
            background: linear-gradient(135deg, #F97316, #FB923C); color: #fff;
            font-family: 'Nunito', sans-serif; font-size: 15px; font-weight: 900;
            border: none; border-radius: 16px; cursor: pointer;
            box-shadow: 0 6px 20px rgba(249,115,22,0.35);
            transition: transform 0.15s, box-shadow 0.15s, background 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-submit:hover {
            transform: translateY(-2px); box-shadow: 0 10px 28px rgba(249,115,22,0.40);
            background: linear-gradient(135deg, #EA6A0A, #F97316);
        }
        .btn-submit:active { transform: translateY(0); }

        .divider {
            display: flex; align-items: center; gap: 12px;
            margin: 22px 0 18px; color: var(--text-light); font-size: 12px; font-weight: 700;
        }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        .footer-link { text-align: center; font-size: 13px; font-weight: 700; color: var(--text-light); }
        .footer-link a { color: var(--orange); text-decoration: none; font-weight: 900; }
        .footer-link a:hover { text-decoration: underline; }

        .back-link {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            font-size: 13px; font-weight: 700; color: var(--text-light);
            text-decoration: none; margin-top: 10px; transition: color 0.2s;
        }
        .back-link:hover { color: var(--orange); }

        @media (max-width: 520px) {
            .card-body { padding: 24px 20px 28px; }
            .card-header { padding: 28px 20px 22px; }
            .grid-2 { grid-template-columns: 1fr; gap: 0; }
        }
    </style>
</head>
<body>

    <div class="bg-icons">
        <span class="bg-icon" style="top:8%;left:5%;animation-delay:0s">🥐</span>
        <span class="bg-icon" style="top:15%;left:88%;animation-delay:1.2s">🍞</span>
        <span class="bg-icon" style="top:40%;left:3%;animation-delay:2.4s">🧁</span>
        <span class="bg-icon" style="top:60%;left:92%;animation-delay:0.8s">🥖</span>
        <span class="bg-icon" style="top:80%;left:10%;animation-delay:1.8s">🍩</span>
        <span class="bg-icon" style="top:88%;left:80%;animation-delay:3s">🥐</span>
        <span class="bg-icon" style="top:25%;left:50%;animation-delay:2s">🧇</span>
        <span class="bg-icon" style="top:70%;left:45%;animation-delay:0.5s">🍰</span>
    </div>

    <nav class="top-nav">
        <div class="nav-logo">🥐</div>
        <div class="nav-name">Pan<span>App</span></div>
    </nav>

    <div class="card">
        <div class="card-header">
            <div class="icon-wrap">👤</div>
            <h2>Crear una cuenta</h2>
            <p>Únete a PanApp y gestiona tu panadería</p>
        </div>

        <div class="card-body">

            <?php if ($alert && $alert['icon'] === 'error'): ?>
            <div class="alert">
                <span>⚠️</span>
                <span><?= htmlspecialchars($alert['text']) ?></span>
            </div>
            <?php endif; ?>

            <form action="../../controllers/UsuarioController.php" method="POST">

                <!-- ✅ rol correcto según la BD -->
                <input type="hidden" name="rol" value="CAJERO">

                <div class="grid-2">
                    <div class="field">
                        <label>Nombres</label>
                        <div class="input-wrap">
                            <i class="input-icon fas fa-user"></i>
                            <!-- ✅ name="nombres" con S -->
                            <input type="text" name="nombres" required maxlength="100" placeholder="Ej. Juan Carlos">
                        </div>
                    </div>
                    <div class="field">
                        <label>Apellidos</label>
                        <div class="input-wrap">
                            <i class="input-icon fas fa-user"></i>
                            <input type="text" name="apellidos" required maxlength="100" placeholder="Ej. Pérez Rodríguez">
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label>Correo Electrónico</label>
                    <div class="input-wrap">
                        <i class="input-icon fas fa-envelope"></i>
                        <input type="email" name="email" required maxlength="150" placeholder="correo@ejemplo.com">
                    </div>
                </div>

                <div class="grid-2">
                    <div class="field">
                        <label>Contraseña</label>
                        <div class="input-wrap">
                            <i class="input-icon fas fa-lock"></i>
                            <input type="password" name="password" id="pass1" required placeholder="••••••••">
                            <button type="button" class="toggle-pass" onclick="togglePass('pass1', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="field">
                        <label>Confirmar</label>
                        <div class="input-wrap">
                            <i class="input-icon fas fa-check-double"></i>
                            <input type="password" name="confirmar_password" id="pass2" required placeholder="••••••••">
                            <button type="button" class="toggle-pass" onclick="togglePass('pass2', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="terms">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">
                        Acepto los <a href="#">términos de servicio</a> y la <a href="#">política de privacidad</a> de PanApp.
                    </label>
                </div>

                <button type="submit" class="btn-submit">
                    Crear mi cuenta <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="divider">o</div>

            <div class="footer-link">
                ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
            </div>
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Volver al inicio
            </a>
        </div>
    </div>

    <script>
        function togglePass(id, btn) {
            const input = document.getElementById(id);
            const icon  = btn.querySelector('i');
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
        }).then(() => {
            <?php if (!empty($alert['redirect'])): ?>
                window.location.href = '<?= htmlspecialchars($alert['redirect']) ?>';
            <?php endif; ?>
        });
    </script>
    <?php endif; ?>

</body>
</html>