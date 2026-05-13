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
$filtro    = $_GET['filtro'] ?? 'todo';
$condFecha = '';

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
$condUsuario = '';

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

// ─── KPI metrics ───
$totalVentas    = count($ventas);
$totalIngresos  = array_sum(array_column(
    array_filter($ventas, fn($v) => $v['estado'] === 'completada'), 'total'
));
$ventasHoy      = count(array_filter($ventas, fn($v) => date('Y-m-d', strtotime($v['fecha'])) === date('Y-m-d')));
$ventasAnuladas = count(array_filter($ventas, fn($v) => $v['estado'] === 'anulada'));

$titulo = "Ventas";
require_once __DIR__ . '/../layouts/header.php';
?>

<?php if (isset($_SESSION['alert'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: '<?= htmlspecialchars($_SESSION['alert']['icon']) ?>',
            title: '<?= htmlspecialchars($_SESSION['alert']['title']) ?>',
            text: '<?= htmlspecialchars($_SESSION['alert']['text']) ?>',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#F97316'
        });
    });
</script>
<?php unset($_SESSION['alert']); endif; ?>

<style>
/* ── Page styles ── */
.kpi-card {
    background: #fff;
    border: 1.5px solid #F3D5B5;
    border-radius: 20px;
    padding: 20px 22px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: box-shadow 0.2s, transform 0.2s;
    position: relative;
    overflow: hidden;
}
.kpi-card::before {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 80px; height: 80px;
    border-radius: 0 20px 0 80px;
    opacity: 0.06;
}
.kpi-card:hover { box-shadow: 0 8px 28px rgba(249,115,22,0.12); transform: translateY(-2px); }
.kpi-icon {
    width: 52px; height: 52px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}
.pill-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 16px;
    border-radius: 50px;
    font-size: 13px; font-weight: 800;
    text-decoration: none;
    transition: all 0.18s;
    border: 1.5px solid transparent;
    cursor: pointer;
}
.pill-btn.active {
    background: #F97316;
    color: #fff;
    box-shadow: 0 4px 14px rgba(249,115,22,0.35);
}
.pill-btn.inactive {
    background: #FFF7ED;
    border-color: #F3D5B5;
    color: #6B4F3A;
}
.pill-btn.inactive:hover { background: #FED7AA; border-color: #F97316; color: #1C0A00; }
.search-input {
    background: #FFF7ED;
    border: 1.5px solid #F3D5B5;
    border-radius: 50px;
    padding: 7px 16px 7px 38px;
    font-size: 13px; font-weight: 600;
    color: #1C0A00;
    outline: none;
    transition: border-color 0.18s, background 0.18s;
    width: 220px;
}
.search-input:focus { border-color: #F97316; background: #fff; }
.day-header {
    background: linear-gradient(135deg, #F97316 0%, #EA6A0A 60%, #C2550A 100%);
    border-radius: 16px 16px 0 0;
    padding: 14px 22px;
    display: flex; align-items: center; justify-content: space-between;
}
.avatar-initials {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, #F97316, #EA6A0A);
    color: #fff;
    font-size: 12px; font-weight: 900;
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    letter-spacing: 0.5px;
}
.badge-pago {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px;
    border-radius: 50px;
    font-size: 11px; font-weight: 800;
}
.badge-efectivo  { background: #D1FAE5; color: #065F46; border: 1px solid #6EE7B7; }
.badge-tarjeta   { background: #DBEAFE; color: #1E40AF; border: 1px solid #93C5FD; }
.badge-transferencia { background: #EDE9FE; color: #5B21B6; border: 1px solid #C4B5FD; }
.badge-otro      { background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D; }
.badge-estado-completada { background:#D1FAE5; color:#065F46; border:1px solid #6EE7B7; }
.badge-estado-pendiente  { background:#FEF3C7; color:#92400E; border:1px solid #FCD34D; }
.badge-estado-anulada    { background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; }
.venta-row { transition: background 0.15s; }
.venta-row:hover { background: #FFFBF7 !important; }
.btn-detalle {
    width: 32px; height: 32px;
    border-radius: 10px;
    border: 1.5px solid #F3D5B5;
    background: transparent;
    color: #A87D5C;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.15s;
}
.btn-detalle:hover { background: #FFF7ED; border-color: #F97316; color: #F97316; transform: scale(1.08); }
</style>

<!-- ── Breadcrumb ── -->
<div class="flex items-center gap-2 mb-6 text-sm font-bold" style="color:#A87D5C;">
    <a href="../dashboard/<?= strtolower($usuario['rol']) ?>.php"
       class="no-underline transition-colors" style="color:#A87D5C;"
       onmouseover="this.style.color='#F97316';" onmouseout="this.style.color='#A87D5C';">
        <i class="fas fa-home mr-1"></i> Dashboard
    </a>
    <i class="fas fa-chevron-right text-xs" style="color:#F3D5B5;"></i>
    <span style="color:#F97316;">Ventas</span>
</div>

<!-- ── Page header ── -->
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
        <h1 class="text-2xl font-black" style="color:#1C0A00;">Gestión de Ventas</h1>
        <p class="text-sm font-semibold mt-0.5" style="color:#A87D5C;">
            Registro y seguimiento de todas las transacciones
        </p>
    </div>
    <button type="button" onclick="abrirModalVenta()"
            class="flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-black text-white transition-all"
            style="background:linear-gradient(135deg,#F97316,#EA6A0A);box-shadow:0 4px 16px rgba(249,115,22,0.35);"
            onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(249,115,22,0.45)';"
            onmouseout="this.style.transform='';this.style.boxShadow='0 4px 16px rgba(249,115,22,0.35)';">
        <i class="fas fa-plus"></i> Nueva Venta
    </button>
</div>

<!-- ── KPI Cards ── -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <!-- Total Ventas -->
    <div class="kpi-card" style="--kpi-color:#F97316;">
        <div class="kpi-icon" style="background:#FFF7ED;">
            <i class="fas fa-receipt" style="color:#F97316;"></i>
        </div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider mb-1" style="color:#A87D5C;">Total Ventas</p>
            <p class="text-3xl font-black leading-none" style="color:#1C0A00;"><?= $totalVentas ?></p>
            <p class="text-xs font-semibold mt-1" style="color:#A87D5C;">en el período</p>
        </div>
    </div>

    <!-- Ingresos Completados -->
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#ECFDF5;">
            <i class="fas fa-dollar-sign" style="color:#059669;"></i>
        </div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider mb-1" style="color:#A87D5C;">Ingresos</p>
            <p class="text-2xl font-black leading-none" style="color:#059669;">$<?= number_format($totalIngresos, 0, ',', '.') ?></p>
            <p class="text-xs font-semibold mt-1" style="color:#A87D5C;">completados</p>
        </div>
    </div>

    <!-- Ventas Hoy -->
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#EFF6FF;">
            <i class="fas fa-calendar-day" style="color:#3B82F6;"></i>
        </div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider mb-1" style="color:#A87D5C;">Ventas Hoy</p>
            <p class="text-3xl font-black leading-none" style="color:#3B82F6;"><?= $ventasHoy ?></p>
            <p class="text-xs font-semibold mt-1" style="color:#A87D5C;"><?= date('d/m/Y') ?></p>
        </div>
    </div>

    <!-- Ventas Anuladas -->
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#FEF2F2;">
            <i class="fas fa-ban" style="color:#EF4444;"></i>
        </div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider mb-1" style="color:#A87D5C;">Anuladas</p>
            <p class="text-3xl font-black leading-none" style="color:#EF4444;"><?= $ventasAnuladas ?></p>
            <p class="text-xs font-semibold mt-1" style="color:#A87D5C;">en el período</p>
        </div>
    </div>

</div>

<!-- ── Filter bar ── -->
<div class="bg-white rounded-2xl border mb-6 px-5 py-4 flex items-center justify-between flex-wrap gap-3"
     style="border-color:#F3D5B5;box-shadow:0 2px 12px rgba(249,115,22,0.06);">

    <!-- Pill date filters -->
    <div class="flex items-center gap-2 flex-wrap">
        <?php
        $filtros = [
            'todo'   => ['label' => 'Todas',       'icon' => 'fa-list'],
            'hoy'    => ['label' => 'Hoy',          'icon' => 'fa-sun'],
            'semana' => ['label' => 'Esta semana',  'icon' => 'fa-calendar-week'],
            'mes'    => ['label' => 'Este mes',     'icon' => 'fa-calendar-alt'],
        ];
        foreach ($filtros as $key => $meta):
            $isActive = ($filtro === $key);
        ?>
        <a href="?filtro=<?= $key ?>"
           class="pill-btn <?= $isActive ? 'active' : 'inactive' ?>">
            <i class="fas <?= $meta['icon'] ?> text-xs"></i>
            <?= $meta['label'] ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Search input -->
    <div class="relative">
        <i class="fas fa-search absolute text-xs" style="left:14px;top:50%;transform:translateY(-50%);color:#A87D5C;pointer-events:none;"></i>
        <input type="text"
               id="search-ventas"
               placeholder="Buscar # venta o cajero..."
               class="search-input"
               oninput="filtrarTabla(this.value)">
    </div>

</div>

<!-- ── Ventas agrupadas por día ── -->
<?php if (empty($ventasPorDia)): ?>
<div class="bg-white rounded-2xl border px-6 py-14 text-center" style="border-color:#F3D5B5;">
    <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4" style="background:#FFF7ED;">🍞</div>
    <p class="font-black text-lg mb-1" style="color:#1C0A00;">Sin ventas en este período</p>
    <p class="text-sm font-semibold" style="color:#A87D5C;">Prueba cambiando el filtro de fecha o registra una nueva venta.</p>
</div>

<?php else: ?>

<div id="ventas-container">
<?php foreach ($ventasPorDia as $dia => $ventasDelDia):
    $subtotalDia = array_sum(array_column(
        array_filter($ventasDelDia, fn($v) => $v['estado'] === 'completada'), 'total'
    ));
    $cantidadDia = count($ventasDelDia);

    $hoy  = date('Y-m-d');
    $ayer = date('Y-m-d', strtotime('-1 day'));
    $diasES = ['Sunday'=>'Domingo','Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado'];
    if ($dia === $hoy)      $labelDia = 'Hoy';
    elseif ($dia === $ayer) $labelDia = 'Ayer';
    else                    $labelDia = $diasES[date('l', strtotime($dia))] ?? date('l', strtotime($dia));
    $fechaFormateada = date('d/m/Y', strtotime($dia));
?>

<div class="rounded-2xl border mb-5 overflow-hidden day-group"
     style="border-color:#F3D5B5;box-shadow:0 2px 16px rgba(249,115,22,0.07);">

    <!-- Day header with gradient -->
    <div class="day-header">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:rgba(255,255,255,0.18);">
                <i class="fas fa-calendar-day text-white text-sm"></i>
            </div>
            <div>
                <p class="font-black text-white text-sm leading-tight"><?= $labelDia ?> — <?= $fechaFormateada ?></p>
                <p class="text-xs font-semibold" style="color:rgba(255,255,255,0.75);">
                    <?= $cantidadDia ?> venta<?= $cantidadDia !== 1 ? 's' : '' ?> registrada<?= $cantidadDia !== 1 ? 's' : '' ?>
                </p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-xs font-black uppercase tracking-wider" style="color:rgba(255,255,255,0.7);">Ingresos del día</p>
            <p class="text-xl font-black text-white">$<?= number_format($subtotalDia, 0, ',', '.') ?></p>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-white">
        <table class="w-full text-left text-sm" style="border-collapse:collapse;">
            <thead>
                <tr style="background:#FDFAF7;border-bottom:1.5px solid #F3D5B5;">
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;"># Venta</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Hora</th>
                    <?php if ($rol === 'ADMIN'): ?>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Cajero</th>
                    <?php endif; ?>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Método de Pago</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Total</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Estado</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider text-center" style="color:#A87D5C;">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventasDelDia as $v):
                    $nombreCajero = trim($v['nombres'] . ' ' . $v['apellidos']);
                    $partes       = explode(' ', $nombreCajero);
                    $iniciales    = strtoupper(
                        substr($partes[0] ?? '', 0, 1) .
                        substr($partes[1] ?? $partes[0] ?? '', 0, 1)
                    );

                    // Payment badge class
                    $mp = strtolower($v['metodo_pago'] ?? '');
                    if (str_contains($mp, 'efectivo'))       $badgePago = 'badge-efectivo';
                    elseif (str_contains($mp, 'tarjeta'))    $badgePago = 'badge-tarjeta';
                    elseif (str_contains($mp, 'transfer'))   $badgePago = 'badge-transferencia';
                    else                                     $badgePago = 'badge-otro';

                    // Payment icon
                    if (str_contains($mp, 'efectivo'))       $iconPago = 'fa-money-bill-wave';
                    elseif (str_contains($mp, 'tarjeta'))    $iconPago = 'fa-credit-card';
                    elseif (str_contains($mp, 'transfer'))   $iconPago = 'fa-exchange-alt';
                    else                                     $iconPago = 'fa-wallet';
                ?>
                <tr class="venta-row border-b venta-data-row"
                    style="border-color:#F3D5B5;"
                    data-id="<?= $v['id_venta'] ?>"
                    data-cajero="<?= htmlspecialchars(strtolower($nombreCajero)) ?>">

                    <!-- # Venta -->
                    <td class="px-5 py-3.5">
                        <span class="font-black text-sm" style="color:#F97316;">
                            #<?= str_pad($v['id_venta'], 4, '0', STR_PAD_LEFT) ?>
                        </span>
                    </td>

                    <!-- Hora -->
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-1.5">
                            <i class="fas fa-clock text-xs" style="color:#F3D5B5;"></i>
                            <span class="font-semibold text-sm" style="color:#6B4F3A;">
                                <?= date('H:i', strtotime($v['fecha'])) ?>
                            </span>
                        </div>
                    </td>

                    <!-- Cajero (admin only) -->
                    <?php if ($rol === 'ADMIN'): ?>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="avatar-initials"><?= $iniciales ?></div>
                            <span class="font-semibold text-sm" style="color:#1C0A00;">
                                <?= htmlspecialchars($nombreCajero) ?>
                            </span>
                        </div>
                    </td>
                    <?php endif; ?>

                    <!-- Método de pago -->
                    <td class="px-5 py-3.5">
                        <span class="badge-pago <?= $badgePago ?>">
                            <i class="fas <?= $iconPago ?> text-xs"></i>
                            <?= htmlspecialchars($v['metodo_pago'] ?? '—') ?>
                        </span>
                    </td>

                    <!-- Total -->
                    <td class="px-5 py-3.5">
                        <span class="font-black text-sm" style="color:#1C0A00;">
                            $<?= number_format($v['total'], 0, ',', '.') ?>
                        </span>
                    </td>

                    <!-- Estado -->
                    <td class="px-5 py-3.5">
                        <?php if ($v['estado'] === 'completada'): ?>
                            <span class="badge-pago badge-estado-completada">
                                <i class="fas fa-check-circle text-xs"></i> Completada
                            </span>
                        <?php elseif ($v['estado'] === 'pendiente'): ?>
                            <span class="badge-pago badge-estado-pendiente">
                                <i class="fas fa-hourglass-half text-xs"></i> Pendiente
                            </span>
                        <?php else: ?>
                            <span class="badge-pago badge-estado-anulada">
                                <i class="fas fa-times-circle text-xs"></i> Anulada
                            </span>
                        <?php endif; ?>
                    </td>

                    <!-- Acción -->
                    <td class="px-5 py-3.5 text-center">
                        <button type="button"
                                onclick="verDetalleVentaModal(<?= $v['id_venta'] ?>)"
                                class="btn-detalle"
                                title="Ver detalle de venta #<?= str_pad($v['id_venta'], 4, '0', STR_PAD_LEFT) ?>">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php endforeach; ?>
</div><!-- #ventas-container -->

<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<script>
// ── Búsqueda en tiempo real ──
function filtrarTabla(q) {
    q = q.toLowerCase().trim();
    document.querySelectorAll('.venta-data-row').forEach(function(row) {
        var id     = '#' + row.dataset.id.padStart(4,'0');
        var cajero = row.dataset.cajero || '';
        row.style.display = (!q || id.includes(q) || cajero.includes(q)) ? '' : 'none';
    });
    // Ocultar grupos vacíos
    document.querySelectorAll('.day-group').forEach(function(group) {
        var visible = Array.from(group.querySelectorAll('.venta-data-row')).some(function(r) { return r.style.display !== 'none'; });
        group.style.display = visible ? '' : 'none';
    });
}
</script>

<!-- ══ MODAL NUEVA VENTA ══ -->
<?php
$stmtProd = $db->prepare("SELECT id_producto, nombre, precio, categoria, unidad_medida FROM productos ORDER BY categoria, nombre");
$stmtProd->execute();
$productosModal = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

$stmtPago = $db->prepare("SELECT id_metodo_pago, nombre FROM metodos_pago ORDER BY nombre");
$stmtPago->execute();
$metodosPagoModal = $stmtPago->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
#modal-venta-box { max-width:900px;width:100%;max-height:90vh;display:flex;flex-direction:column; }
.mv-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:10px; }
.mv-card { border:1.5px solid #F3D5B5;border-radius:12px;padding:12px;cursor:pointer;transition:border-color .15s,background .15s,transform .1s;user-select:none; }
.mv-card:hover { border-color:#F97316;background:#FFF7ED;transform:translateY(-2px); }
.mv-card.en-carrito { border-color:#F97316;background:#FFF7ED; }
#mv-carrito-items { max-height:260px;overflow-y:auto; }
#mv-carrito-items::-webkit-scrollbar{width:3px;}
#mv-carrito-items::-webkit-scrollbar-thumb{background:#F3D5B5;border-radius:3px;}
#mv-prods-scroll { max-height:340px;overflow-y:auto;padding-right:4px; }
#mv-prods-scroll::-webkit-scrollbar{width:3px;}
#mv-prods-scroll::-webkit-scrollbar-thumb{background:#F3D5B5;border-radius:3px;}
</style>

<div id="modal-venta" class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(28,10,0,0.5);backdrop-filter:blur(4px);display:none!important;">
    <div id="modal-venta-box" class="bg-white rounded-2xl shadow-2xl overflow-hidden"
         style="border:1px solid #F3D5B5;transform:scale(0.92) translateY(16px);opacity:0;transition:transform 0.25s cubic-bezier(0.34,1.56,0.64,1),opacity 0.2s ease;">
        <div class="flex items-center justify-between px-6 py-4 flex-shrink-0" style="background:linear-gradient(135deg,#F97316,#EA6A0A);">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg" style="background:rgba(255,255,255,0.2);">🛒</div>
                <div><h3 class="text-base font-black text-white">Nueva Venta</h3><p class="text-xs font-semibold" style="color:rgba(255,255,255,0.8);">Selecciona productos y registra el cobro</p></div>
            </div>
            <button onclick="cerrarModalVenta()" class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-black text-white" style="background:rgba(255,255,255,0.15);" onmouseover="this.style.background='rgba(255,255,255,0.25)';" onmouseout="this.style.background='rgba(255,255,255,0.15)';">✕</button>
        </div>
        <div class="flex flex-1 overflow-hidden" style="min-height:0;">
            <div class="flex-1 flex flex-col border-r overflow-hidden" style="border-color:#F3D5B5;">
                <div class="px-5 py-3 border-b flex-shrink-0" style="border-color:#F3D5B5;background:#FDFAF7;">
                    <div class="relative"><i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <input type="text" id="mv-buscador" placeholder="Buscar producto..." class="w-full pl-8 pr-3 py-2 rounded-xl text-sm font-semibold outline-none" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;" onfocus="this.style.borderColor='#F97316';this.style.background='#fff';" onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';" oninput="mvFiltrar(this.value)"></div>
                </div>
                <div id="mv-prods-scroll" class="p-4 flex-1">
                    <?php if (empty($productosModal)): ?>
                    <p class="text-center text-sm font-bold py-8" style="color:#A87D5C;">🍞 No hay productos registrados.</p>
                    <?php else: ?>
                    <div class="mv-grid">
                        <?php foreach ($productosModal as $p): ?>
                        <div class="mv-card" data-id="<?= $p['id_producto'] ?>" data-nombre="<?= htmlspecialchars($p['nombre']) ?>" data-precio="<?= $p['precio'] ?>" data-cat="<?= htmlspecialchars(strtolower($p['categoria'] ?? '')) ?>" onclick="mvAgregar(this)">
                            <p class="font-black text-xs leading-tight mb-1" style="color:#1C0A00;"><?= htmlspecialchars($p['nombre']) ?></p>
                            <p class="text-xs font-semibold" style="color:#A87D5C;"><?= htmlspecialchars($p['categoria'] ?? '—') ?></p>
                            <p class="font-black text-sm mt-2" style="color:#F97316;">$<?= number_format($p['precio'],0,',','.') ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex flex-col flex-shrink-0" style="width:280px;">
                <div class="px-4 py-3 border-b flex-shrink-0 flex items-center justify-between" style="border-color:#F3D5B5;background:#FDFAF7;">
                    <span class="text-sm font-black" style="color:#1C0A00;">🧾 Carrito</span>
                    <button onclick="mvLimpiar()" class="text-xs font-black px-2.5 py-1 rounded-lg" style="background:#FEE2E2;color:#DC2626;border:1px solid #FCA5A5;" onmouseover="this.style.background='#FECACA';" onmouseout="this.style.background='#FEE2E2';">Limpiar</button>
                </div>
                <div id="mv-carrito-items" class="flex-1 px-4 py-3">
                    <p id="mv-empty" class="text-xs font-bold text-center py-6" style="color:#A87D5C;">Selecciona productos del catálogo</p>
                </div>
                <div class="px-4 py-4 border-t flex-shrink-0" style="border-color:#F3D5B5;background:#FDFAF7;">
                    <div class="flex items-center justify-between mb-3 pb-3 border-b" style="border-color:#F3D5B5;">
                        <span class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Total</span>
                        <span class="text-xl font-black" style="color:#F97316;" id="mv-total">$0</span>
                    </div>
                    <div class="mb-3">
                        <label class="text-xs font-black uppercase tracking-wider block mb-1.5" style="color:#6B4F3A;">Método de Pago</label>
                        <input type="hidden" id="mv-metodo" value="">
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" id="mv-btn-efectivo"
                                    onclick="mvElegirPago('Efectivo','mv-btn-efectivo','mv-btn-nequi')"
                                    class="py-2.5 rounded-xl text-sm font-black border transition-all"
                                    style="background:#FFF7ED;border-color:#F3D5B5;color:#6B4F3A;">
                                💵 Efectivo
                            </button>
                            <button type="button" id="mv-btn-nequi"
                                    onclick="mvElegirPago('Nequi','mv-btn-nequi','mv-btn-efectivo')"
                                    class="py-2.5 rounded-xl text-sm font-black border transition-all"
                                    style="background:#FFF7ED;border-color:#F3D5B5;color:#6B4F3A;">
                                📱 Nequi
                            </button>
                        </div>
                    </div>
                    <button onclick="mvConfirmar()" class="w-full py-2.5 rounded-xl text-sm font-black text-white flex items-center justify-center gap-2" style="background:#F97316;box-shadow:0 4px 14px rgba(249,115,22,0.35);" onmouseover="this.style.background='#EA6A0A';" onmouseout="this.style.background='#F97316';"><i class="fas fa-check"></i> Registrar Venta</button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="mv-form" action="/PanApp/controllers/VentaController.php" method="POST" style="display:none;">
    <input type="hidden" name="id_metodo_pago" id="mv-input-metodo">
    <input type="hidden" name="total" id="mv-input-total">
    <input type="hidden" name="items" id="mv-input-items">
</form>

<!-- ══ MODAL DETALLE VENTA ══ -->
<div id="modal-detalle-v" class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(28,10,0,0.45);backdrop-filter:blur(4px);display:none!important;">
    <div id="modal-detalle-v-box" class="bg-white rounded-2xl w-full shadow-2xl"
         style="max-width:520px;border:1px solid #F3D5B5;transform:scale(0.92) translateY(16px);opacity:0;transition:transform 0.25s cubic-bezier(0.34,1.56,0.64,1),opacity 0.2s ease;">
        <div class="flex items-center justify-between px-6 py-4 border-b" style="border-color:#F3D5B5;">
            <div>
                <h3 class="text-lg font-black" style="color:#1C0A00;">Venta <span style="color:#F97316;" id="mdv2-numero">#0000</span></h3>
                <p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;" id="mdv2-fecha">—</p>
            </div>
            <div class="flex items-center gap-2">
                <span id="mdv2-estado-badge" class="text-xs font-black px-3 py-1.5 rounded-full"></span>
                <button onclick="cerrarModal('modal-detalle-v','modal-detalle-v-box')" class="w-8 h-8 rounded-xl flex items-center justify-center text-sm transition-all" style="background:#FFF7ED;border:1px solid #F3D5B5;color:#A87D5C;" onmouseover="this.style.background='#FEE2E2';this.style.color='#DC2626';" onmouseout="this.style.background='#FFF7ED';this.style.color='#A87D5C';">✕</button>
            </div>
        </div>
        <div class="px-6 py-4 grid grid-cols-2 gap-4 border-b" style="border-color:#F3D5B5;">
            <div><p class="text-xs font-black uppercase tracking-wider mb-1" style="color:#A87D5C;">Cajero</p><p class="text-sm font-black" style="color:#1C0A00;" id="mdv2-cajero">—</p></div>
            <div><p class="text-xs font-black uppercase tracking-wider mb-1" style="color:#A87D5C;">Método de Pago</p><p class="text-sm font-black" style="color:#1C0A00;" id="mdv2-metodo">—</p></div>
        </div>
        <div id="mdv2-loading" class="px-6 py-8 text-center"><div class="inline-flex items-center gap-2" style="color:#A87D5C;"><i class="fas fa-spinner fa-spin"></i><span class="text-sm font-semibold">Cargando...</span></div></div>
        <div id="mdv2-productos" class="px-6 py-4 hidden">
            <p class="text-xs font-black uppercase tracking-wider mb-3" style="color:#A87D5C;">Productos</p>
            <div id="mdv2-lista" class="flex flex-col gap-0 max-h-52 overflow-y-auto"></div>
            <div class="flex items-center justify-between pt-4 mt-2 border-t" style="border-color:#F3D5B5;">
                <span class="font-black text-base" style="color:#6B4F3A;">TOTAL</span>
                <span class="font-black text-2xl" style="color:#F97316;" id="mdv2-total">$0</span>
            </div>
        </div>
        <div class="px-6 pb-5 flex gap-3">
            <button onclick="cerrarModal('modal-detalle-v','modal-detalle-v-box')" class="flex-1 py-2.5 rounded-xl text-sm font-black transition-all" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#6B4F3A;" onmouseover="this.style.background='#F3D5B5';" onmouseout="this.style.background='#FFF7ED';"><i class="fas fa-times mr-1"></i> Cerrar</button>
            <button id="mdv2-btn-anular" onclick="anularVentaModal2()" class="flex-1 py-2.5 rounded-xl text-sm font-black transition-all hidden" style="background:#FEE2E2;border:1.5px solid #FCA5A5;color:#DC2626;" onmouseover="this.style.background='#FECACA';" onmouseout="this.style.background='#FEE2E2';"><i class="fas fa-ban mr-1"></i> Anular Venta</button>
        </div>
    </div>
</div>

<script>
var _ventaIdModal2 = null;

// ── Modal Nueva Venta ──
(function(){
    var carrito={};
    window.abrirModalVenta=function(){
        abrirModal('modal-venta','modal-venta-box');
        carrito={};mvRender();
        document.getElementById('mv-buscador').value='';mvFiltrar('');
        document.getElementById('mv-metodo').value='';
        // Limpiar botones de pago
        var e=document.getElementById('mv-btn-efectivo'), n=document.getElementById('mv-btn-nequi');
        if(e){e.style.background='#FFF7ED';e.style.borderColor='#F3D5B5';e.style.color='#6B4F3A';}
        if(n){n.style.background='#FFF7ED';n.style.borderColor='#F3D5B5';n.style.color='#6B4F3A';}
    };
    window.cerrarModalVenta=function(){ cerrarModal('modal-venta','modal-venta-box'); };
    window.mvAgregar=function(el){
        var id=el.dataset.id;
        if(carrito[id]) carrito[id].cantidad++;
        else carrito[id]={nombre:el.dataset.nombre,precio:parseFloat(el.dataset.precio),cantidad:1};
        el.classList.add('en-carrito'); mvRender();
    };
    window.mvCambiar=function(id,delta){
        if(!carrito[id]) return;
        carrito[id].cantidad+=delta;
        if(carrito[id].cantidad<=0){ delete carrito[id]; var c=document.querySelector('.mv-card[data-id="'+id+'"]'); if(c) c.classList.remove('en-carrito'); }
        mvRender();
    };
    window.mvLimpiar=function(){ carrito={}; document.querySelectorAll('.mv-card').forEach(function(c){c.classList.remove('en-carrito');}); mvRender(); };
    function mvRender(){
        var cont=document.getElementById('mv-carrito-items'),tot=document.getElementById('mv-total'),keys=Object.keys(carrito);
        if(!keys.length){ cont.innerHTML='<p class="text-xs font-bold text-center py-6" style="color:#A87D5C;">Selecciona productos del catálogo</p>'; tot.textContent='$0'; return; }
        var html='',total=0;
        keys.forEach(function(id){ var it=carrito[id],sub=it.precio*it.cantidad; total+=sub;
            html+='<div class="flex items-center gap-2 py-2 border-b" style="border-color:#F3D5B5;"><div class="flex-1 min-w-0"><p class="text-xs font-black truncate" style="color:#1C0A00;">'+it.nombre+'</p><p class="text-xs font-semibold" style="color:#A87D5C;">$'+mvFmt(it.precio)+' c/u</p></div><div class="flex items-center gap-1"><button onclick="mvCambiar(\''+id+'\',-1)" class="w-5 h-5 rounded text-xs font-black flex items-center justify-center border" style="border-color:#F3D5B5;color:#DC2626;" onmouseover="this.style.background=\'#FEE2E2\';" onmouseout="this.style.background=\'\';">−</button><span class="w-5 text-center text-xs font-black" style="color:#1C0A00;">'+it.cantidad+'</span><button onclick="mvCambiar(\''+id+'\',1)" class="w-5 h-5 rounded text-xs font-black flex items-center justify-center border" style="border-color:#F3D5B5;color:#F97316;" onmouseover="this.style.background=\'#FFF7ED\';" onmouseout="this.style.background=\'\';">+</button></div><p class="text-xs font-black" style="color:#F97316;min-width:52px;text-align:right;">$'+mvFmt(sub)+'</p></div>';
        });
        cont.innerHTML=html; tot.textContent='$'+mvFmt(total);
    }
    window.mvFiltrar=function(q){ q=q.toLowerCase(); document.querySelectorAll('.mv-card').forEach(function(c){ c.style.display=(!q||c.dataset.nombre.toLowerCase().includes(q)||(c.dataset.cat||'').includes(q))?'':'none'; }); };
    window.mvConfirmar=function(){
        var keys=Object.keys(carrito);
        if(!keys.length){ Swal.fire({icon:'warning',title:'Carrito vacío',text:'Agrega al menos un producto.',confirmButtonColor:'#F97316'}); return; }
        var metodo=document.getElementById('mv-metodo').value;
        if(!metodo){ Swal.fire({icon:'warning',title:'Método de pago',text:'Selecciona un método de pago.',confirmButtonColor:'#F97316'}); return; }
        var total=0; keys.forEach(function(id){ total+=carrito[id].precio*carrito[id].cantidad; });
        var items=keys.map(function(id){ return{id_producto:id,cantidad:carrito[id].cantidad,precio_unitario:carrito[id].precio,subtotal:carrito[id].precio*carrito[id].cantidad}; });
        Swal.fire({icon:'question',title:'¿Confirmar venta?',html:'<strong style="color:#F97316;font-size:1.3rem;">$'+mvFmt(total)+'</strong><br><span style="color:#6B4F3A;font-size:.85rem;">'+keys.length+' producto(s)</span>',showCancelButton:true,confirmButtonText:'✅ Registrar',cancelButtonText:'Cancelar',confirmButtonColor:'#F97316'})
        .then(function(r){ if(r.isConfirmed){ document.getElementById('mv-input-metodo').value=metodo; document.getElementById('mv-input-total').value=total; document.getElementById('mv-input-items').value=JSON.stringify(items); document.getElementById('mv-form').submit(); } });
    };
    function mvFmt(n){ return new Intl.NumberFormat('es-CO').format(n); }

    // ── Botones de método de pago ──
    window.mvElegirPago = function(nombre, btnActivo, btnOtro) {
        var activo = document.getElementById(btnActivo);
        var otro   = document.getElementById(btnOtro);
        document.getElementById('mv-metodo').value = nombre;
        if (nombre === 'Efectivo') {
            activo.style.background='#D1FAE5'; activo.style.borderColor='#10B981'; activo.style.color='#065F46';
        } else {
            activo.style.background='#EDE9FE'; activo.style.borderColor='#8B5CF6'; activo.style.color='#5B21B6';
        }
        otro.style.background='#FFF7ED'; otro.style.borderColor='#F3D5B5'; otro.style.color='#6B4F3A';
    };
})();

// ── Modal Detalle Venta ──
function verDetalleVentaModal(id) {
    _ventaIdModal2=id;
    abrirModal('modal-detalle-v','modal-detalle-v-box');
    document.getElementById('mdv2-numero').textContent='#'+String(id).padStart(4,'0');
    document.getElementById('mdv2-fecha').textContent='—';
    document.getElementById('mdv2-cajero').textContent='—';
    document.getElementById('mdv2-metodo').textContent='—';
    document.getElementById('mdv2-total').textContent='$0';
    document.getElementById('mdv2-lista').innerHTML='';
    document.getElementById('mdv2-loading').classList.remove('hidden');
    document.getElementById('mdv2-productos').classList.add('hidden');
    document.getElementById('mdv2-btn-anular').classList.add('hidden');
    fetch('/PanApp/controllers/VentaDetalleController.php?id='+id)
    .then(function(r){return r.json();})
    .then(function(data){
        if(data.error){alert(data.error);return;}
        var v=data.venta,d=data.detalle;
        document.getElementById('mdv2-numero').textContent='#'+String(v.id_venta).padStart(4,'0');
        document.getElementById('mdv2-fecha').textContent=mdv2Fecha(v.fecha);
        document.getElementById('mdv2-cajero').textContent=v.nombres+' '+v.apellidos;
        document.getElementById('mdv2-metodo').textContent=v.metodo_pago||'—';
        var badge=document.getElementById('mdv2-estado-badge');
        if(v.estado==='completada'){badge.textContent='✅ Completada';badge.style.cssText='background:#D1FAE5;color:#065F46;border:1px solid #6EE7B7;';}
        else if(v.estado==='anulada'){badge.textContent='❌ Anulada';badge.style.cssText='background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;';}
        else{badge.textContent='⏳ Pendiente';badge.style.cssText='background:#FEF3C7;color:#92400E;border:1px solid #FCD34D;';}
        var lista='';
        d.forEach(function(item){lista+='<div class="flex items-center justify-between py-2.5 border-b" style="border-color:#F3D5B5;"><div><p class="text-sm font-black" style="color:#1C0A00;">'+item.nombre+'</p><p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;">'+item.cantidad+' x $'+mdv2Fmt(item.precio_unitario)+'</p></div><p class="font-black text-sm" style="color:#F97316;">$'+mdv2Fmt(item.subtotal)+'</p></div>';});
        document.getElementById('mdv2-lista').innerHTML=lista;
        document.getElementById('mdv2-total').textContent='$'+mdv2Fmt(v.total);
        if(v.estado!=='anulada') document.getElementById('mdv2-btn-anular').classList.remove('hidden');
        document.getElementById('mdv2-loading').classList.add('hidden');
        document.getElementById('mdv2-productos').classList.remove('hidden');
    })
    .catch(function(){document.getElementById('mdv2-loading').innerHTML='<p class="text-sm font-bold" style="color:#DC2626;">⚠️ Error al cargar el detalle.</p>';});
}

function anularVentaModal2(){
    if(!_ventaIdModal2) return;
    Swal.fire({icon:'warning',title:'¿Anular venta?',html:'La venta <strong>#'+String(_ventaIdModal2).padStart(4,'0')+'</strong> será anulada.',showCancelButton:true,confirmButtonText:'Sí, anular',cancelButtonText:'Cancelar',confirmButtonColor:'#DC2626'})
    .then(function(r){if(r.isConfirmed) window.location.href='/PanApp/controllers/VentaController.php?accion=anular&id='+_ventaIdModal2;});
}

function mdv2Fecha(str){var d=new Date(str.replace(' ','T'));return d.toLocaleDateString('es-CO',{day:'2-digit',month:'2-digit',year:'numeric'})+' '+d.toLocaleTimeString('es-CO',{hour:'2-digit',minute:'2-digit'});}
function mdv2Fmt(n){return new Intl.NumberFormat('es-CO').format(n);}
</script>
