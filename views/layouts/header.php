<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

$usuario = $_SESSION['usuario'];
$titulo  = $titulo ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PanApp | <?= htmlspecialchars($titulo) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        orange: {
                            50:  '#FFF7ED',
                            100: '#FFEDD5',
                            200: '#FED7AA',
                            400: '#FB923C',
                            500: '#F97316',
                            600: '#EA6A0A',
                        },
                        cream: '#FEF3E8',
                    },
                    fontFamily: {
                        sans: ['Nunito', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen" style="background-color:#FEF3E8;">
<div class="flex min-h-screen">

<?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- Mobile overlay -->
    <div id="overlay"
         class="fixed inset-0 bg-black/30 z-40 hidden"
         onclick="closeSidebar()">
    </div>

    <!-- ── MAIN CONTENT ── -->
    <div class="flex-1 flex flex-col min-h-screen md:ml-60">

        <!-- ── TOPBAR ── -->
        <header class="sticky top-0 z-30 flex items-center justify-between px-6 bg-white/90 backdrop-blur border-b"
                style="height:60px;border-color:#F3D5B5;">

            <div class="flex items-center gap-3">
                <!-- Hamburger mobile -->
                <button class="md:hidden text-xl p-1" style="color:#6B4F3A;" onclick="openSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="text-lg font-black" style="color:#1C0A00;">
                    <?= htmlspecialchars($titulo) ?>
                </h1>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-sm font-bold hidden sm:block" style="color:#6B4F3A;">
                    Hola, <?= htmlspecialchars(explode(' ', $usuario['nombres'])[0]) ?> 👋
                </span>
                <span class="text-xs font-black capitalize px-3 py-1 rounded-full"
                      style="background:#FFF7ED;border:1px solid #F3D5B5;color:#EA6A0A;">
                    <?= htmlspecialchars($usuario['rol']) ?>
                </span>
            </div>

        </header>

        <!-- Page body -->
        <main class="flex-1 p-6" style="background-color:#FEF3E8;">
