<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php"); exit;
}

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$db       = $database->conectar();

$rol     = strtoupper($_SESSION['usuario']['rol']);
$periodo = $_GET['periodo'] ?? 'diario';
$tab     = $_GET['tab']     ?? 'ventas'; // ventas | productos

// ─── Rango de fechas ───
switch ($periodo) {
    case 'semanal':
        $fechaDesde   = date('Y-m-d', strtotime('monday this week'));
        $fechaHasta   = date('Y-m-d', strtotime('sunday this week'));
        $labelPeriodo = 'Esta semana';
        break;
    case 'mensual':
        $fechaDesde   = date('Y-m-01');
        $fechaHasta   = date('Y-m-t');
        $labelPeriodo = 'Este mes — ' . date('F Y');
        break;
    default:
        $fechaDesde   = date('Y-m-d');
        $fechaHasta   = date('Y-m-d');
        $labelPeriodo = 'Hoy — ' . date('d/m/Y');
        break;
}

// ─── Condición de usuario ───
$condUsuario = ($rol !== 'ADMIN') ? "AND v.id_usuario = " . intval($_SESSION['usuario']['id_usuario']) : '';

// ─── Reporte de VENTAS ───
$stmtV = $db->prepare("
    SELECT v.id_venta, v.fecha, v.total, v.estado,
           mp.nombre AS metodo_pago,
           u.nombres, u.apellidos
    FROM ventas v
    LEFT JOIN metodos_pago mp ON v.id_metodo_pago = mp.id_metodo_pago
    LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
    WHERE DATE(v.fecha) BETWEEN :desde AND :hasta
    AND v.estado = 'completada'
    $condUsuario
    ORDER BY v.fecha DESC
");
$stmtV->bindParam(':desde', $fechaDesde);
$stmtV->bindParam(':hasta', $fechaHasta);
$stmtV->execute();
$ventas = $stmtV->fetchAll(PDO::FETCH_ASSOC);

$totalVentas   = count($ventas);
$totalIngresos = array_sum(array_column($ventas, 'total'));
$promedio      = $totalVentas > 0 ? $totalIngresos / $totalVentas : 0;

$porMetodo = [];
foreach ($ventas as $v) {
    $mp = $v['metodo_pago'] ?? 'Sin método';
    $porMetodo[$mp] = ($porMetodo[$mp] ?? 0) + $v['total'];
}

// ─── Reporte de PRODUCTOS más vendidos ───
$stmtP = $db->prepare("
    SELECT p.nombre, p.categoria,
           SUM(dv.cantidad) AS total_unidades,
           SUM(dv.subtotal) AS total_ingresos,
           COUNT(DISTINCT dv.id_venta) AS num_ventas
    FROM detalle_venta dv
    LEFT JOIN productos p ON dv.id_producto = p.id_producto
    LEFT JOIN ventas v ON dv.id_venta = v.id_venta
    WHERE DATE(v.fecha) BETWEEN :desde AND :hasta
    AND v.estado = 'completada'
    $condUsuario
    GROUP BY dv.id_producto
    ORDER BY total_unidades DESC
    LIMIT 20
");
$stmtP->bindParam(':desde', $fechaDesde);
$stmtP->bindParam(':hasta', $fechaHasta);
$stmtP->execute();
$productosRanking = $stmtP->fetchAll(PDO::FETCH_ASSOC);

$titulo = "Reportes";
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 mb-6 text-sm font-bold" style="color:#A87D5C;">
    <a href="../dashboard/<?= strtolower($usuario['rol']) ?>.php" class="no-underline" style="color:#A87D5C;"
       onmouseover="this.style.color='#F97316';" onmouseout="this.style.color='#A87D5C';">
        <i class="fas fa-home mr-1"></i> Dashboard
    </a>
    <span>/</span>
    <span style="color:#F97316;">Reportes</span>
</div>

<!-- Filtros período + exportar -->
<div class="bg-white rounded-2xl border mb-6 px-5 py-4 flex items-center justify-between flex-wrap gap-3" style="border-color:#F3D5B5;">
    <div>
        <h2 class="text-xl font-black" style="color:#1C0A00;">
            Reportes <span style="color:#F97316;"><?= $rol === 'ADMIN' ? 'Generales' : 'Mis Reportes' ?></span>
        </h2>
        <p class="text-sm font-semibold mt-0.5" style="color:#A87D5C;">📅 <?= $labelPeriodo ?></p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <?php foreach (['diario'=>'Hoy','semanal'=>'Esta semana','mensual'=>'Este mes'] as $key=>$label): ?>
        <a href="?periodo=<?= $key ?>&tab=<?= $tab ?>"
           class="px-4 py-2 rounded-xl text-sm font-black no-underline transition-all"
           style="<?= $periodo===$key ? 'background:#F97316;color:#fff;box-shadow:0 4px 12px rgba(249,115,22,0.3);' : 'background:#FFF7ED;border:1px solid #F3D5B5;color:#6B4F3A;' ?>"
           <?= $periodo!==$key ? 'onmouseover="this.style.background=\'#FED7AA\';" onmouseout="this.style.background=\'#FFF7ED\';"' : '' ?>>
            <?= $label ?>
        </a>
        <?php endforeach; ?>
        <a href="exportar_pdf.php?periodo=<?= $periodo ?>&tab=<?= $tab ?>"
           class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black no-underline transition-all"
           style="background:#1C0A00;color:#fff;"
           onmouseover="this.style.background='#3D1A00';"
           onmouseout="this.style.background='#1C0A00';">
            <i class="fas fa-file-pdf"></i> Exportar PDF
        </a>
    </div>
</div>

<!-- Tabs Ventas / Productos -->
<div class="flex gap-2 mb-5">
    <a href="?periodo=<?= $periodo ?>&tab=ventas"
       class="px-5 py-2.5 rounded-xl text-sm font-black no-underline transition-all"
       style="<?= $tab==='ventas' ? 'background:#F97316;color:#fff;box-shadow:0 4px 12px rgba(249,115,22,0.3);' : 'background:#fff;border:1.5px solid #F3D5B5;color:#6B4F3A;' ?>">
        🧾 Reporte de Ventas
    </a>
    <a href="?periodo=<?= $periodo ?>&tab=productos"
       class="px-5 py-2.5 rounded-xl text-sm font-black no-underline transition-all"
       style="<?= $tab==='productos' ? 'background:#F97316;color:#fff;box-shadow:0 4px 12px rgba(249,115,22,0.3);' : 'background:#fff;border:1.5px solid #F3D5B5;color:#6B4F3A;' ?>">
        🍞 Productos más vendidos
    </a>
</div>

<?php if ($tab === 'ventas'): ?>

<!-- ══ TAB VENTAS ══ -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
    <div class="bg-white rounded-2xl p-5 border flex items-center gap-4" style="border-color:#F3D5B5;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;">🧾</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Ventas completadas</p>
            <p class="text-3xl font-black" style="color:#1C0A00;"><?= $totalVentas ?></p>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-5 border flex items-center gap-4" style="border-color:#F3D5B5;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;">💰</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Total ingresos</p>
            <p class="text-3xl font-black" style="color:#F97316;">$<?= number_format($totalIngresos, 0, ',', '.') ?></p>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-5 border flex items-center gap-4" style="border-color:#F3D5B5;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;">📊</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Promedio por venta</p>
            <p class="text-3xl font-black" style="color:#1C0A00;">$<?= number_format($promedio, 0, ',', '.') ?></p>
        </div>
    </div>
</div>

<!-- Por método de pago -->
<?php if (!empty($porMetodo)): ?>
<div class="bg-white rounded-2xl border mb-5 p-5" style="border-color:#F3D5B5;">
    <p class="text-sm font-black mb-3" style="color:#1C0A00;">Por método de <span style="color:#F97316;">pago</span></p>
    <div class="flex gap-3 flex-wrap">
        <?php foreach ($porMetodo as $mp => $monto): ?>
        <div class="rounded-xl px-4 py-3 text-center border" style="background:#FFF7ED;border-color:#F3D5B5;min-width:120px;">
            <p class="text-xs font-black uppercase" style="color:#A87D5C;"><?= htmlspecialchars($mp) ?></p>
            <p class="text-lg font-black mt-1" style="color:#F97316;">$<?= number_format($monto, 0, ',', '.') ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Tabla ventas -->
<div class="bg-white rounded-2xl border" style="border-color:#F3D5B5;">
    <div class="px-6 py-4 border-b" style="border-color:#F3D5B5;">
        <h3 class="text-lg font-black" style="color:#1C0A00;">Detalle de <span style="color:#F97316;">Ventas</span></h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr style="background:#FFF7ED;">
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;"># Venta</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Fecha</th>
                    <?php if ($rol === 'ADMIN'): ?>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Cajero</th>
                    <?php endif; ?>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Método Pago</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ventas)): ?>
                <tr><td colspan="5" class="px-5 py-8 text-center font-bold" style="color:#A87D5C;">📊 No hay ventas en este período.</td></tr>
                <?php endif; ?>
                <?php foreach ($ventas as $v): ?>
                <tr class="border-t transition-colors" style="border-color:#F3D5B5;"
                    onmouseover="this.style.background='#FFF7ED';" onmouseout="this.style.background='';">
                    <td class="px-5 py-3 font-black" style="color:#F97316;">#<?= str_pad($v['id_venta'],4,'0',STR_PAD_LEFT) ?></td>
                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;"><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></td>
                    <?php if ($rol === 'ADMIN'): ?>
                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;"><?= htmlspecialchars($v['nombres'].' '.$v['apellidos']) ?></td>
                    <?php endif; ?>
                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;"><?= htmlspecialchars($v['metodo_pago'] ?? '—') ?></td>
                    <td class="px-5 py-3 font-black" style="color:#1C0A00;">$<?= number_format($v['total'],0,',','.') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (!empty($ventas)): ?>
                <tr style="background:#FFF7ED;border-top:2px solid #F3D5B5;">
                    <td colspan="<?= $rol==='ADMIN' ? 4 : 3 ?>" class="px-5 py-3 font-black text-right" style="color:#6B4F3A;">TOTAL</td>
                    <td class="px-5 py-3 font-black text-lg" style="color:#F97316;">$<?= number_format($totalIngresos,0,',','.') ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>

<!-- ══ TAB PRODUCTOS ══ -->
<div class="bg-white rounded-2xl border" style="border-color:#F3D5B5;">
    <div class="px-6 py-4 border-b" style="border-color:#F3D5B5;">
        <h3 class="text-lg font-black" style="color:#1C0A00;">
            Productos más <span style="color:#F97316;">vendidos</span>
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr style="background:#FFF7ED;">
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Pos.</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Producto</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Categoría</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Unidades vendidas</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">En # ventas</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Total ingresos</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($productosRanking)): ?>
                <tr><td colspan="6" class="px-5 py-8 text-center font-bold" style="color:#A87D5C;">🍞 No hay datos de productos en este período.</td></tr>
                <?php endif; ?>
                <?php foreach ($productosRanking as $idx => $p): ?>
                <tr class="border-t transition-colors" style="border-color:#F3D5B5;"
                    onmouseover="this.style.background='#FFF7ED';" onmouseout="this.style.background='';">
                    <td class="px-5 py-3">
                        <?php
                        $medallas = ['🥇','🥈','🥉'];
                        echo isset($medallas[$idx]) ? "<span class='text-lg'>{$medallas[$idx]}</span>" : "<span class='font-black' style='color:#A87D5C;'>".($idx+1)."</span>";
                        ?>
                    </td>
                    <td class="px-5 py-3 font-black" style="color:#1C0A00;"><?= htmlspecialchars($p['nombre']) ?></td>
                    <td class="px-5 py-3">
                        <span class="text-xs font-black px-2.5 py-1 rounded-full"
                              style="background:#FFF7ED;border:1px solid #F3D5B5;color:#EA6A0A;">
                            <?= htmlspecialchars($p['categoria'] ?? '—') ?>
                        </span>
                    </td>
                    <td class="px-5 py-3 font-black text-lg" style="color:#F97316;"><?= $p['total_unidades'] ?></td>
                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;"><?= $p['num_ventas'] ?></td>
                    <td class="px-5 py-3 font-black" style="color:#1C0A00;">$<?= number_format($p['total_ingresos'],0,',','.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
