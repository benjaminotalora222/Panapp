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
    <title>PanApp | Registro de Cajero</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🥐</text></svg>">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --orange: #F97316; --orange-dark: #EA6A0A; --orange-light: #FED7AA;
            --orange-soft: #FFF7ED; --cream: #FEF3E8;
            --text-dark: #1C0A00; --text-mid: #6B4F3A; --text-light: #A87D5C;
            --border: #F3D5B5;
        }
        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--cream);
            min-height: 100vh;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 24px 16px;
            background-image: radial-gradient(circle at 15% 20%, rgba(249,115,22,0.07) 0%, transparent 50%),
                              radial-gradient(circle at 85% 80%, rgba(249,115,22,0.05) 0%, transparent 50%);
        }
        .bg-pattern { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
        .bg-icon { position: absolute; font-size: 32px; opacity: 0.07; animation: floatIcon 9s ease-in-out infinite; user-select: none; }
        @keyframes floatIcon { 0%,100%{transform:translateY(0) rotate(0deg);} 50%{transform:translateY(-14px) rotate(10deg);} }

        .top-nav { position: relative; z-index: 10; display: flex; align-items: center; gap: 10px; margin-bottom: 28px; animation: fadeDown 0.5s ease both; }
        @keyframes fadeDown { from{opacity:0;transform:translateY(-16px);} to{opacity:1;transform:translateY(0);} }
        .nav-logo { width: 42px; height: 42px; background: var(--orange); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; box-shadow: 0 4px 14px rgba(249,115,22,0.35); }
        .nav-name { font-family: 'Playfair Display', serif; font-size: 24px; color: var(--text-dark); }
        .nav-name span { color: var(--orange); }

        .card {
            position: relative; z-index: 10; width: 100%; max-width: 520px;
            background: #fff; border-radius: 28px;
            box-shadow: 0 8px 40px rgba(249,115,22,0.10), 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid var(--border); overflow: hidden;
            animation: fadeUp 0.55s ease both 0.1s;
        }
        @keyframes fadeUp { from{opacity:0;transform:translateY(24px);} to{opacity:1;transform:translateY(0);} }

        .card-header {
            background: linear-gradient(135deg, #F97316, #FB923C);
            padding: 28px 32px 24px;
            text-align: center;
        }
        .card-header .icon-wrap {
            width: 60px; height: 60px; background: rgba(255,255,255,0.2);
            border-radius: 18px; display: flex; align-items: center; justify-content: center;
            font-size: 28px; margin: 0 auto 14px; border: 1.5px solid rgba(255,255,255,0.3);
        }
        .card-header h1 { font-family: 'Playfair Display', serif; font-size: 22px; color: #fff; margin-bottom: 4px; }
        .card-header p { font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.82); }

        .card-body { padding: 28px 32px; }

        .field { display: flex; flex-direction: column; gap: 5px; margin-bottom: 16px; }
        label { font-size: 12px; font-weight: 800; color: var(--text-mid); letter-spacing: 0.3px; text-transform: uppercase; }
        .input-wrap { position: relative; }
        .input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 14px; pointer-events: none; }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; padding: 11px 14px 11px 40px;
            background: var(--orange-soft); border: 1.5px solid var(--border);
            border-radius: 12px; font-family: 'Nunito', sans-serif;
            font-size: 14px; font-weight: 600; color: var(--text-dark);
            outline: none; transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        }
        input::placeholder { color: #C9A880; font-weight: 600; }
        input:focus { border-color: var(--orange); background: #fff; box-shadow: 0 0 0 3px rgba(249,115,22,0.12); }
        .toggle-pass { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: var(--text-light); cursor: pointer; font-size: 15px; background: none; border: none; padding: 0; transition: color 0.2s; }
        .toggle-pass:hover { color: var(--orange); }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        .role-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: #EDE9FE; color: #5B21B6; border: 1px solid #C4B5FD;
            border-radius: 99px; padding: 5px 14px;
            font-size: 12px; font-weight: 900; margin-bottom: 20px;
        }

        .btn-submit {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #F97316, #FB923C); color: #fff;
            font-family: 'Nunito', sans-serif; font-size: 15px; font-weight: 900;
            border: none; border-radius: 12px; cursor: pointer;
            box-shadow: 0 6px 20px rgba(249,115,22,0.35);
            transition: transform 0.15s, box-shadow 0.15s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            margin-top: 8px;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(249,115,22,0.42); }

        .footer-link { text-align: center; font-size: 13px; font-weight: 700; color: var(--text-light); margin-top: 16px; }
        .footer-link a { color: var(--orange); text-decoration: none; font-weight: 900; }
        .footer-link a:hover { text-decoration: underline; }

        .alert-box {
            display: flex; align-items: center; gap: 10px;
            background: #FFF1F0; border: 1px solid #FFD3CC;
            border-radius: 12px; padding: 11px 16px; margin-bottom: 16px;
            font-size: 13px; font-weight: 700; color: #D94F2E;
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
    </div>

    <nav class="top-nav">
        <div class="nav-logo">🥐</div>
        <div class="nav-name">Pan<span>App</span></div>
    </nav>

    <div class="card">

        <div class="card-header">
            <div class="icon-wrap">👨‍🍳</div>
            <h1>Registro de Cajero</h1>
            <p>Crea tu cuenta para acceder al sistema</p>
        </div>

        <div class="card-body">

            <div style="text-align:center;margin-bottom:18px;">
                <span class="role-badge">
                    <i class="fas fa-cash-register"></i> Rol: Cajero
                </span>
            </div>

            <?php if ($alert && $alert['icon'] === 'error'): ?>
            <div class="alert-box">
                <span>⚠️</span>
                <span><?= htmlspecialchars($alert['text']) ?></span>
            </div>
            <?php endif; ?>

            <form action="../../controllers/RegistroController.php" method="POST">

                <div class="grid-2">
                    <div class="field">
                        <label>Nombres</label>
                        <div class="input-wrap">
                            <i class="input-icon fas fa-user"></i>
                            <input type="text" name="nombres" placeholder="Juan Carlos" required maxlength="100">
                        </div>
                    </div>
                    <div class="field">
                        <label>Apellidos</label>
                        <div class="input-wrap">
                            <i class="input-icon fas fa-user"></i>
                            <input type="text" name="apellidos" placeholder="Pérez" required maxlength="100">
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label>Correo Electrónico</label>
                    <div class="input-wrap">
                        <i class="input-icon fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="correo@ejemplo.com" required maxlength="150">
                    </div>
                </div>

                <div class="grid-2">
                    <div class="field">
                        <label>Contraseña</label>
                        <div class="input-wrap">
                            <i class="input-icon fas fa-lock"></i>
                            <input type="password" name="password" id="pass1" placeholder="Mín. 6 caracteres" required>
                            <button type="button" class="toggle-pass" onclick="toggleP('pass1','eye1')">
                                <i class="fas fa-eye" id="eye1"></i>
                            </button>
                        </div>
                    </div>
                    <div class="field">
                        <label>Confirmar</label>
                        <div class="input-wrap">
                            <i class="input-icon fas fa-check-double"></i>
                            <input type="password" name="confirmar_password" id="pass2" placeholder="Repite" required>
                            <button type="button" class="toggle-pass" onclick="toggleP('pass2','eye2')">
                                <i class="fas fa-eye" id="eye2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Rol fijo: CAJERO -->
                <input type="hidden" name="rol" value="CAJERO">

                <button type="submit" class="btn-submit">
                    <i class="fas fa-user-plus"></i> Crear cuenta
                </button>

            </form>

            <div class="footer-link">
                ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
            </div>

        </div>
    </div>

    <script>
        function toggleP(inputId, iconId) {
            var input = document.getElementById(inputId);
            var icon  = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>

    <?php if ($alert && $alert['icon'] !== 'error'): ?>
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
