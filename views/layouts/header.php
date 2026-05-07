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
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🥐</text></svg>">
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

                <?php if (strtoupper($usuario['rol']) === 'ADMIN' && isset($totalStockBajo) && $totalStockBajo > 0): ?>
                <!-- Campana de alertas -->
                <div class="relative" id="alert-bell-wrap">
                    <button id="alert-bell-btn"
                            onclick="toggleAlertDropdown()"
                            class="relative w-9 h-9 rounded-xl flex items-center justify-center transition-all"
                            style="background:#FEE2E2;border:1px solid #FCA5A5;color:#DC2626;"
                            onmouseover="this.style.background='#FECACA';"
                            onmouseout="this.style.background='#FEE2E2';"
                            title="Alertas de stock bajo">
                        <i class="fas fa-bell text-sm"></i>
                        <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full text-white flex items-center justify-center"
                              style="background:#EF4444;font-size:9px;font-weight:900;border:1.5px solid #fff;">
                            <?= $totalStockBajo ?>
                        </span>
                    </button>

                    <!-- Dropdown de alertas -->
                    <div id="alert-dropdown"
                         class="absolute right-0 top-11 w-72 bg-white rounded-2xl shadow-2xl border hidden z-50"
                         style="border-color:#F3D5B5;">

                        <div class="px-4 py-3 border-b flex items-center justify-between" style="border-color:#F3D5B5;background:#FEF2F2;border-radius:16px 16px 0 0;">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-exclamation-triangle text-sm" style="color:#DC2626;"></i>
                                <span class="text-sm font-black" style="color:#991B1B;">Stock Bajo</span>
                            </div>
                            <span class="text-xs font-black px-2 py-0.5 rounded-full" style="background:#FEE2E2;color:#DC2626;">
                                <?= $totalStockBajo ?> alerta<?= $totalStockBajo !== 1 ? 's' : '' ?>
                            </span>
                        </div>

                        <div class="py-2 max-h-64 overflow-y-auto">
                            <?php foreach ($stockBajoItems as $item): ?>
                            <div class="flex items-center gap-3 px-4 py-2.5 transition-colors"
                                 onmouseover="this.style.background='#FFF7ED';"
                                 onmouseout="this.style.background='';">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                     style="background:<?= $item['cantidad_actual'] <= 0 ? '#FEE2E2' : '#FEF3C7' ?>;">
                                    <?= $item['cantidad_actual'] <= 0 ? '🔴' : '⚠️' ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-black truncate" style="color:#1C0A00;">
                                        <?= htmlspecialchars($item['nombre']) ?>
                                    </p>
                                    <p class="text-xs font-semibold" style="color:#A87D5C;">
                                        <?= $item['cantidad_actual'] <= 0 ? 'Sin stock' : $item['cantidad_actual'] . ' ' . htmlspecialchars($item['unidad_medida']) . ' restantes' ?>
                                    </p>
                                </div>
                                <span class="text-lg font-black flex-shrink-0"
                                      style="color:<?= $item['cantidad_actual'] <= 0 ? '#DC2626' : '#D97706' ?>;">
                                    <?= $item['cantidad_actual'] ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="px-4 py-3 border-t" style="border-color:#F3D5B5;">
                            <a href="/PanApp/views/inventario/index.php"
                               class="w-full py-2 rounded-xl text-xs font-black text-center no-underline block transition-all"
                               style="background:#F97316;color:#fff;"
                               onmouseover="this.style.background='#EA6A0A';"
                               onmouseout="this.style.background='#F97316';">
                                <i class="fas fa-boxes mr-1"></i> Ir al Inventario
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </header>

        <!-- Page body -->
        <main class="flex-1 p-6" style="background-color:#FEF3E8;">

<script>
function toggleAlertDropdown() {
    var dd = document.getElementById('alert-dropdown');
    if (dd) dd.classList.toggle('hidden');
}
// Cerrar al hacer clic fuera
document.addEventListener('click', function(e) {
    var wrap = document.getElementById('alert-bell-wrap');
    if (wrap && !wrap.contains(e.target)) {
        var dd = document.getElementById('alert-dropdown');
        if (dd) dd.classList.add('hidden');
    }
});
</script>
