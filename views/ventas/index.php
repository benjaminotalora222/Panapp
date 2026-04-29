<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$db       = $database->conectar();

$rol = strtoupper($_SESSION['usuario']['rol']);

// ─── Filtro de fecha ───
$filtro     = $_GET['filtro'] ?? 'todo';
$condFecha  = '';

switch ($filtro) {
    case 'hoy':
        $condFecha = "AND DATE(v.fecha) = CURDATE()";
        break;
    case 'semana':
        $condFecha = "AND DATE(v.fecha) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)";
        break;
    case 'mes':
        $condFecha = "AND MONTH(v.fecha) = MONTH(NOW()) AND YEAR(v.fecha) = YEAR(NOW())";
        break;
    default:
        $condFecha = '';
        break;
}

// ─── Consultar ventas ───
$condUsuario = ($rol !== 'ADMIN') ? "AND v.id_usuario = {$_SESSION['usuario']['id_usuario']}" : '';

$stmt = $db->prepare("
    SELECT v.id_venta, v.fecha, v.total, v.estado,
           mp.nombre AS metodo_pago,
           u.nombres, u.apellidos
    FROM ventas v
    LEFT JOIN metodos_pago mp ON v.id_metodo_pago = mp.id_metodo_pago
    LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
    WHERE 1=1 $condFecha $condUsuario
    ORDER BY v.fecha DESC
");
$stmt->execute();
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ─── Agrupar por día ───
$ventasPorDia = [];
foreach ($ventas as $v) {
    $dia = date('Y-m-d', strtotime($v['fecha']));
    $ventasPorDia[$dia][] = $v;
}

// ─── Totales generales ───
$totalVentas   = count($ventas);
$totalIngresos = array_sum(array_column(
    array_filter($ventas, fn($v) => $v['estado'] === 'completada'),
    'total'
));

$titulo = "Ventas";
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 mb-6 text-sm font-bold" style="color:#A87D5C;">
    <a href="../dashboard/<?= strtolower($usuario['rol']) ?>.php"
       class="no-underline" style="color:#A87D5C;"
       onmouseover="this.style.color='#F97316';" onmouseout="this.style.color='#A87D5C';">
        <i class="fas fa-home mr-1"></i> Dashboard
    </a>
    <span>/</span>
    <span style="color:#F97316;">Ventas</span>
</div>

<!-- Tarjetas resumen -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
    <div class="bg-white rounded-2xl p-5 border flex items-center gap-4" style="border-color:#F3D5B5;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;">🧾</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">
                <?= $rol === 'ADMIN' ? 'Total Ventas' : 'Mis Ventas' ?>
            </p>
            <p class="text-3xl font-black" style="color:#1C0A00;"><?= $totalVentas ?></p>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-5 border flex items-center gap-4" style="border-color:#F3D5B5;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;">💰</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Ingresos completados</p>
            <p class="text-3xl font-black" style="color:#F97316;">$<?= number_format($totalIngresos, 0, ',', '.') ?></p>
        </div>
    </div>
</div>

<!-- Filtros + botón nueva venta -->
<div class="bg-white rounded-2xl border mb-6 px-5 py-4 flex items-center justify-between flex-wrap gap-3" style="border-color:#F3D5B5;">

    <div class="flex items-center gap-2 flex-wrap">
        <?php
        $filtros = ['todo' => 'Todas', 'hoy' => 'Hoy', 'semana' => 'Esta semana', 'mes' => 'Este mes'];
        foreach ($filtros as $key => $label):
        ?>
        <a href="?filtro=<?= $key ?>"
           class="px-4 py-2 rounded-xl text-sm font-black no-underline transition-all"
           style="<?= $filtro === $key
               ? 'background:#F97316;color:#fff;box-shadow:0 4px 12px rgba(249,115,22,0.3);'
               : 'background:#FFF7ED;border:1px solid #F3D5B5;color:#6B4F3A;' ?>"
           <?= $filtro !== $key ? 'onmouseover="this.style.background=\'#FED7AA\';" onmouseout="this.style.background=\'#FFF7ED\';"' : '' ?>>
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <a href="crear.php"
       class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-black text-white transition-all no-underline"
       style="background:#F97316;box-shadow:0 4px 12px rgba(249,115,22,0.3);"
       onmouseover="this.style.background='#EA6A0A';"
       onmouseout="this.style.background='#F97316';">
        <i class="fas fa-plus"></i> Nueva Venta
    </a>
</div>

<!-- Ventas agrupadas por día -->
<?php if (empty($ventasPorDia)): ?>
<div class="bg-white rounded-2xl border px-6 py-10 text-center" style="border-color:#F3D5B5;">
    <p class="font-bold text-lg" style="color:#A87D5C;">🍞 No hay ventas en este período.</p>
</div>

<?php else: ?>
<?php foreach ($ventasPorDia as $dia => $ventasDelDia):
    $subtotalDia = array_sum(array_column(
        array_filter($ventasDelDia, fn($v) => $v['estado'] === 'completada'),
        'total'
    ));
    $cantidadDia = count($ventasDelDia);

    // Label del día
    $hoy      = date('Y-m-d');
    $ayer     = date('Y-m-d', strtotime('-1 day'));
    if ($dia === $hoy)       $labelDia = '📅 Hoy — ' . date('d/m/Y', strtotime($dia));
    elseif ($dia === $ayer)  $labelDia = '📅 Ayer — ' . date('d/m/Y', strtotime($dia));
    else                     $labelDia = '📅 ' . date('l d/m/Y', strtotime($dia));
?>

<div class="bg-white rounded-2xl border mb-4" style="border-color:#F3D5B5;">

    <!-- Cabecera del día -->
    <div class="flex items-center justify-between px-6 py-4 border-b" style="border-color:#F3D5B5;background:#FFF7ED;border-radius:16px 16px 0 0;">
        <div class="flex items-center gap-3">
            <span class="text-sm font-black" style="color:#1C0A00;"><?= $labelDia ?></span>
            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                  style="background:#F97316;color:#fff;">
                <?= $cantidadDia ?> venta<?= $cantidadDia !== 1 ? 's' : '' ?>
            </span>
        </div>
        <span class="font-black text-base" style="color:#F97316;">
            $<?= number_format($subtotalDia, 0, ',', '.') ?>
        </span>
    </div>

    <!-- Tabla del día -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr style="background:#FFFDF9;">
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;"># Venta</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Hora</th>
                    <?php if ($rol === 'ADMIN'): ?>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Cajero</th>
                    <?php endif; ?>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Método Pago</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Total</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Estado</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider text-center" style="color:#A87D5C;">Detalle</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventasDelDia as $v): ?>
                <tr class="border-t transition-colors" style="border-color:#F3D5B5;"
                    onmouseover="this.style.background='#FFF7ED';"
                    onmouseout="this.style.background='';">

                    <td class="px-5 py-3 font-black" style="color:#F97316;">
                        #<?= str_pad($v['id_venta'], 4, '0', STR_PAD_LEFT) ?>
                    </td>

                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;">
                        <?= date('H:i', strtotime($v['fecha'])) ?>
                    </td>

                    <?php if ($rol === 'ADMIN'): ?>
                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;">
                        <?= htmlspecialchars($v['nombres'] . ' ' . $v['apellidos']) ?>
                    </td>
                    <?php endif; ?>

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

                    <td class="px-5 py-3 text-center">
                        <a href="detalle.php?id=<?= $v['id_venta'] ?>"
                           class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all mx-auto"
                           style="border-color:#F3D5B5;color:#6B4F3A;"
                           onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';this.style.color='#F97316';"
                           onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';this.style.color='#6B4F3A';"
                           title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
