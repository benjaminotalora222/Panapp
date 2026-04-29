<?php
session_start();

if (!isset($_SESSION['usuario']) || strtoupper($_SESSION['usuario']['rol']) !== 'CAJERO') {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$db       = $database->conectar();

// Ventas de hoy del cajero
$stmtHoy = $db->prepare("SELECT COUNT(*) as total, COALESCE(SUM(total),0) as ingresos
                          FROM ventas
                          WHERE DATE(fecha) = CURDATE()
                          AND estado = 'completada'
                          AND id_usuario = :id");
$stmtHoy->bindParam(':id', $_SESSION['usuario']['id_usuario'], PDO::PARAM_INT);
$stmtHoy->execute();
$hoy = $stmtHoy->fetch(PDO::FETCH_ASSOC);

// Total productos disponibles
$stmtProd = $db->prepare("SELECT COUNT(*) as total FROM productos");
$stmtProd->execute();
$productos = $stmtProd->fetch(PDO::FETCH_ASSOC);

// Últimas 5 ventas del cajero
$stmtVentas = $db->prepare("SELECT v.id_venta, v.fecha, v.total, v.estado, mp.nombre as metodo_pago
                             FROM ventas v
                             LEFT JOIN metodos_pago mp ON v.id_metodo_pago = mp.id_metodo_pago
                             WHERE v.id_usuario = :id
                             ORDER BY v.fecha DESC LIMIT 5");
$stmtVentas->bindParam(':id', $_SESSION['usuario']['id_usuario'], PDO::PARAM_INT);
$stmtVentas->execute();
$ultimasVentas = $stmtVentas->fetchAll(PDO::FETCH_ASSOC);

$titulo = "Dashboard";
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- BIENVENIDA -->
<div class="rounded-2xl p-6 mb-6 flex items-center justify-between flex-wrap gap-4"
     style="background:linear-gradient(135deg,#F97316,#FB923C);box-shadow:0 6px 24px rgba(249,115,22,0.3);">
    <div>
        <h2 class="text-2xl font-black text-white mb-1">
            ¡Bienvenido, <?= htmlspecialchars(explode(' ', $usuario['nombres'])[0]) ?>! 🥐
        </h2>
        <p class="text-sm font-semibold" style="color:rgba(255,255,255,0.85);">
            Panel de cajero · PanApp
        </p>
    </div>
    <div class="text-4xl">🛒</div>
</div>

<!-- TARJETAS RESUMEN -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">

    <div class="bg-white rounded-2xl p-5 border flex items-center gap-4" style="border-color:#F3D5B5;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;">🧾</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Ventas hoy</p>
            <p class="text-3xl font-black" style="color:#1C0A00;"><?= $hoy['total'] ?></p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border flex items-center gap-4" style="border-color:#F3D5B5;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;">💰</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Ingresos hoy</p>
            <p class="text-3xl font-black" style="color:#1C0A00;">$<?= number_format($hoy['ingresos'], 0, ',', '.') ?></p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border flex items-center gap-4" style="border-color:#F3D5B5;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;">🍞</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Productos</p>
            <p class="text-3xl font-black" style="color:#1C0A00;"><?= $productos['total'] ?></p>
        </div>
    </div>

</div>

<!-- ACCIONES RÁPIDAS -->
<div class="bg-white rounded-2xl border mb-6" style="border-color:#F3D5B5;">

    <div class="px-6 py-5 border-b" style="border-color:#F3D5B5;">
        <h3 class="text-xl font-black" style="color:#1C0A00;">
            Acciones <span style="color:#F97316;">Rápidas</span>
        </h3>
    </div>

    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Registrar venta -->
        <a href="../ventas/crear.php"
           class="flex items-center gap-4 border rounded-xl p-4 transition-all no-underline"
           style="border-color:#F3D5B5;"
           onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';this.style.boxShadow='0 4px 16px rgba(249,115,22,0.15)';"
           onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';this.style.boxShadow='';">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;border:1.5px solid #F3D5B5;">🛒</div>
            <div class="flex-1">
                <p class="font-black text-sm mb-0.5" style="color:#1C0A00;">Registrar Venta</p>
                <p class="text-xs font-semibold" style="color:#A87D5C;">Agrega productos y cobra</p>
            </div>
            <i class="fas fa-arrow-right" style="color:#A87D5C;"></i>
        </a>

        <!-- Consultar productos -->
        <a href="../productos/index.php"
           class="flex items-center gap-4 border rounded-xl p-4 transition-all no-underline"
           style="border-color:#F3D5B5;"
           onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';this.style.boxShadow='0 4px 16px rgba(249,115,22,0.15)';"
           onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';this.style.boxShadow='';">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;border:1.5px solid #F3D5B5;">🍞</div>
            <div class="flex-1">
                <p class="font-black text-sm mb-0.5" style="color:#1C0A00;">Consultar Productos</p>
                <p class="text-xs font-semibold" style="color:#A87D5C;">Ver catálogo y precios</p>
            </div>
            <i class="fas fa-arrow-right" style="color:#A87D5C;"></i>
        </a>

<!-- Reporte de ventas -->
<a href="/PanApp/views/reportes/index.php"
   class="flex items-center gap-4 border rounded-xl p-4 transition-all no-underline"
   style="border-color:#F3D5B5;"
   onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';this.style.boxShadow='0 4px 16px rgba(249,115,22,0.15)';"
   onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';this.style.boxShadow='';">
    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;border:1.5px solid #F3D5B5;">📊</div>
    <div class="flex-1">
        <p class="font-black text-sm mb-0.5" style="color:#1C0A00;">Reporte de Ventas</p>
        <p class="text-xs font-semibold" style="color:#A87D5C;">Diario, semanal y mensual</p>
    </div>
    <i class="fas fa-arrow-right" style="color:#A87D5C;"></i>
</a>


        <!-- Reporte de productos -->
        <a href="/PanApp/views/reportes/index.php"
           class="flex items-center gap-4 border rounded-xl p-4 transition-all no-underline"
           style="border-color:#F3D5B5;"
           onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';this.style.boxShadow='0 4px 16px rgba(249,115,22,0.15)';"
           onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';this.style.boxShadow='';">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;border:1.5px solid #F3D5B5;">📋</div>
            <div class="flex-1">
                <p class="font-black text-sm mb-0.5" style="color:#1C0A00;">Reporte de Productos</p>
                <p class="text-xs font-semibold" style="color:#A87D5C;">Más vendidos y stock</p>
            </div>
            <i class="fas fa-arrow-right" style="color:#A87D5C;"></i>
        </a>

    </div>
</div>

<!-- ÚLTIMAS VENTAS -->
<div class="bg-white rounded-2xl border" style="border-color:#F3D5B5;">

    <div class="flex items-center justify-between px-6 py-5 border-b" style="border-color:#F3D5B5;">
        <h3 class="text-xl font-black" style="color:#1C0A00;">
            Mis últimas <span style="color:#F97316;">Ventas</span>
        </h3>
        <a href="../ventas/index.php"
           class="text-xs font-black px-4 py-2 rounded-xl no-underline transition-all"
           style="background:#FFF7ED;border:1px solid #F3D5B5;color:#EA6A0A;"
           onmouseover="this.style.background='#FED7AA';"
           onmouseout="this.style.background='#FFF7ED';">
            Ver todas <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr style="background:#FFF7ED;">
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;"># Venta</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Fecha</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Método pago</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Total</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Estado</th>
                </tr>
            </thead>
            <tbody>

                <?php if (empty($ultimasVentas)): ?>
                <tr>
                    <td colspan="5" class="px-5 py-8 text-center font-bold" style="color:#A87D5C;">
                        🍞 Aún no has registrado ventas.
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($ultimasVentas as $v): ?>
                <tr class="border-t transition-colors" style="border-color:#F3D5B5;"
                    onmouseover="this.style.background='#FFF7ED';"
                    onmouseout="this.style.background='';">

                    <td class="px-5 py-3 font-black" style="color:#F97316;">
                        #<?= str_pad($v['id_venta'], 4, '0', STR_PAD_LEFT) ?>
                    </td>
                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;">
                        <?= date('d/m/Y H:i', strtotime($v['fecha'])) ?>
                    </td>
                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;">
                        <?= htmlspecialchars($v['metodo_pago'] ?? '—') ?>
                    </td>
                    <td class="px-5 py-3 font-black" style="color:#1C0A00;">
                        $<?= number_format($v['total'], 0, ',', '.') ?>
                    </td>
                    <td class="px-5 py-3">
                        <?php if ($v['estado'] === 'completada'): ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#D1FAE5;color:#065F46;border:1px solid #6EE7B7;">✅ Completada</span>
                        <?php elseif ($v['estado'] === 'pendiente'): ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#FEF3C7;color:#92400E;border:1px solid #FCD34D;">⏳ Pendiente</span>
                        <?php else: ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;">❌ Anulada</span>
                        <?php endif; ?>
                    </td>

                </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
