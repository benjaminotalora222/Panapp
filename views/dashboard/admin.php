<?php
session_start();

if (!isset($_SESSION['usuario']) || strtoupper($_SESSION['usuario']['rol']) !== 'ADMIN') {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Usuario.php';

$database     = new Database();
$db           = $database->conectar();
$usuarioModel = new Usuario($db);
$usuarios     = $usuarioModel->obtenerTodos();

// ─── Métricas de usuarios ───
$totalUsuarios = count($usuarios);
$totalAdmins   = count(array_filter($usuarios, fn($u) => strtoupper($u['rol']) === 'ADMIN'));
$totalCajeros  = count(array_filter($usuarios, fn($u) => strtoupper($u['rol']) === 'CAJERO'));

// ─── Métricas de ventas ───
$stmtVentas = $db->prepare("
    SELECT
        COUNT(*) as total,
        COALESCE(SUM(total), 0) as ingresos,
        COUNT(CASE WHEN DATE(fecha) = CURDATE() THEN 1 END) as hoy,
        COALESCE(SUM(CASE WHEN DATE(fecha) = CURDATE() THEN total ELSE 0 END), 0) as ingresos_hoy
    FROM ventas WHERE estado = 'completada'
");
$stmtVentas->execute();
$metricasVentas = $stmtVentas->fetch(PDO::FETCH_ASSOC);

// ─── Métricas de productos ───
$stmtProds = $db->prepare("SELECT COUNT(*) as total FROM productos");
$stmtProds->execute();
$totalProductos = $stmtProds->fetch(PDO::FETCH_ASSOC)['total'];

// ─── Stock bajo ───
$stmtStock = $db->prepare("SELECT COUNT(*) as total FROM inventario_insumos WHERE cantidad_actual <= 5");
$stmtStock->execute();
$stockBajo = $stmtStock->fetch(PDO::FETCH_ASSOC)['total'];

// ─── Últimas 5 ventas ───
$stmtUltimas = $db->prepare("
    SELECT v.id_venta, v.fecha, v.total, v.estado,
           mp.nombre AS metodo_pago,
           u.nombres, u.apellidos
    FROM ventas v
    LEFT JOIN metodos_pago mp ON v.id_metodo_pago = mp.id_metodo_pago
    LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
    ORDER BY v.fecha DESC LIMIT 5
");
$stmtUltimas->execute();
$ultimasVentas = $stmtUltimas->fetchAll(PDO::FETCH_ASSOC);

// ─── Top 3 productos más vendidos (este mes) ───
$stmtTop = $db->prepare("
    SELECT p.nombre, SUM(dv.cantidad) as unidades, SUM(dv.subtotal) as ingresos
    FROM detalle_venta dv
    JOIN productos p ON dv.id_producto = p.id_producto
    JOIN ventas v ON dv.id_venta = v.id_venta
    WHERE v.estado = 'completada'
      AND MONTH(v.fecha) = MONTH(NOW())
      AND YEAR(v.fecha) = YEAR(NOW())
    GROUP BY dv.id_producto
    ORDER BY unidades DESC
    LIMIT 3
");
$stmtTop->execute();
$topProductos = $stmtTop->fetchAll(PDO::FETCH_ASSOC);

// ─── Ventas por día (últimos 7 días) para mini gráfica ───
$stmtDias = $db->prepare("
    SELECT DATE(fecha) as dia, COUNT(*) as cantidad, COALESCE(SUM(total),0) as total
    FROM ventas
    WHERE estado = 'completada'
      AND DATE(fecha) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(fecha)
    ORDER BY dia ASC
");
$stmtDias->execute();
$ventasDias = $stmtDias->fetchAll(PDO::FETCH_ASSOC);

$titulo = "Dashboard";
require_once __DIR__ . '/../layouts/header.php';
?>

<?php
// ─── Datos para modal Nueva Venta ───
$stmtProdDash = $db->prepare("SELECT id_producto, nombre, precio, categoria, unidad_medida FROM productos ORDER BY categoria, nombre");
$stmtProdDash->execute();
$productosDash = $stmtProdDash->fetchAll(PDO::FETCH_ASSOC);

$stmtPagoDash = $db->prepare("SELECT id_metodo_pago, nombre FROM metodos_pago ORDER BY nombre");
$stmtPagoDash->execute();
$metodosPagoDash = $stmtPagoDash->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- ══ BIENVENIDA ══ -->
<div class="rounded-2xl p-6 mb-6 flex items-center justify-between flex-wrap gap-4"
     style="background:linear-gradient(135deg,#F97316,#FB923C);box-shadow:0 6px 24px rgba(249,115,22,0.3);">
    <div>
        <p class="text-sm font-bold mb-1" style="color:rgba(255,255,255,0.75);">
            <?php
            $diasES   = ['Sunday'=>'Domingo','Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado'];
            $mesesES  = ['January'=>'Enero','February'=>'Febrero','March'=>'Marzo','April'=>'Abril','May'=>'Mayo','June'=>'Junio','July'=>'Julio','August'=>'Agosto','September'=>'Septiembre','October'=>'Octubre','November'=>'Noviembre','December'=>'Diciembre'];
            echo $diasES[date('l')] . ', ' . date('d') . ' de ' . $mesesES[date('F')] . ' de ' . date('Y');
            ?>
        </p>
        <h2 class="text-2xl font-black text-white mb-1">
            ¡Bienvenido, <?= htmlspecialchars(explode(' ', $usuario['nombres'])[0]) ?>! 🥐
        </h2>
        <p class="text-sm font-semibold" style="color:rgba(255,255,255,0.85);">
            Panel de administración · PanApp
        </p>
    </div>
    <div class="flex items-center gap-3">
        <button type="button" onclick="abrirModalVentaDash()"
           class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-black transition-all"
           style="background:rgba(255,255,255,0.2);color:#fff;border:1.5px solid rgba(255,255,255,0.3);"
           onmouseover="this.style.background='rgba(255,255,255,0.3)';"
           onmouseout="this.style.background='rgba(255,255,255,0.2)';">
            <i class="fas fa-plus"></i> Nueva Venta
        </button>
        <div class="text-5xl">🏪</div>
    </div>
</div>

<!-- ══ KPIs PRINCIPALES ══ -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <!-- Ventas hoy -->
    <div class="bg-white rounded-2xl p-5 border relative overflow-hidden" style="border-color:#F3D5B5;">
        <div class="absolute top-0 right-0 w-20 h-20 rounded-full opacity-5" style="background:#F97316;transform:translate(30%,-30%);"></div>
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0" style="background:#FFF7ED;">🛒</div>
            <span class="text-xs font-black px-2 py-0.5 rounded-full" style="background:#D1FAE5;color:#065F46;">Hoy</span>
        </div>
        <p class="text-3xl font-black mb-0.5" style="color:#1C0A00;"><?= $metricasVentas['hoy'] ?></p>
        <p class="text-xs font-bold" style="color:#A87D5C;">Ventas del día</p>
    </div>

    <!-- Ingresos hoy -->
    <div class="bg-white rounded-2xl p-5 border relative overflow-hidden" style="border-color:#F3D5B5;">
        <div class="absolute top-0 right-0 w-20 h-20 rounded-full opacity-5" style="background:#F97316;transform:translate(30%,-30%);"></div>
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0" style="background:#FFF7ED;">💰</div>
            <span class="text-xs font-black px-2 py-0.5 rounded-full" style="background:#FEF3C7;color:#92400E;">Hoy</span>
        </div>
        <p class="text-2xl font-black mb-0.5" style="color:#F97316;">$<?= number_format($metricasVentas['ingresos_hoy'], 0, ',', '.') ?></p>
        <p class="text-xs font-bold" style="color:#A87D5C;">Ingresos del día</p>
    </div>

    <!-- Total ventas -->
    <div class="bg-white rounded-2xl p-5 border relative overflow-hidden" style="border-color:#F3D5B5;">
        <div class="absolute top-0 right-0 w-20 h-20 rounded-full opacity-5" style="background:#F97316;transform:translate(30%,-30%);"></div>
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0" style="background:#FFF7ED;">📊</div>
            <span class="text-xs font-black px-2 py-0.5 rounded-full" style="background:#EDE9FE;color:#5B21B6;">Total</span>
        </div>
        <p class="text-3xl font-black mb-0.5" style="color:#1C0A00;"><?= $metricasVentas['total'] ?></p>
        <p class="text-xs font-bold" style="color:#A87D5C;">Ventas completadas</p>
    </div>

    <!-- Stock bajo -->
    <div class="bg-white rounded-2xl p-5 border relative overflow-hidden" style="border-color:<?= $stockBajo > 0 ? '#FCA5A5' : '#F3D5B5' ?>;">
        <div class="absolute top-0 right-0 w-20 h-20 rounded-full opacity-5" style="background:<?= $stockBajo > 0 ? '#EF4444' : '#F97316' ?>;transform:translate(30%,-30%);"></div>
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0"
                 style="background:<?= $stockBajo > 0 ? '#FEE2E2' : '#FFF7ED' ?>;">
                <?= $stockBajo > 0 ? '⚠️' : '📦' ?>
            </div>
            <span class="text-xs font-black px-2 py-0.5 rounded-full"
                  style="background:<?= $stockBajo > 0 ? '#FEE2E2' : '#D1FAE5' ?>;color:<?= $stockBajo > 0 ? '#991B1B' : '#065F46' ?>;">
                <?= $stockBajo > 0 ? 'Alerta' : 'OK' ?>
            </span>
        </div>
        <p class="text-3xl font-black mb-0.5" style="color:<?= $stockBajo > 0 ? '#DC2626' : '#1C0A00' ?>;"><?= $stockBajo ?></p>
        <p class="text-xs font-bold" style="color:#A87D5C;">Insumos con stock bajo</p>
    </div>

</div>

<!-- ══ FILA MEDIA: Actividad + Top productos ══ -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

    <!-- Últimas ventas (2/3) -->
    <div class="lg:col-span-2 bg-white rounded-2xl border" style="border-color:#F3D5B5;">
        <div class="flex items-center justify-between px-6 py-4 border-b" style="border-color:#F3D5B5;">
            <div>
                <h3 class="text-base font-black" style="color:#1C0A00;">Actividad <span style="color:#F97316;">Reciente</span></h3>
                <p class="text-xs font-semibold" style="color:#A87D5C;">Últimas 5 ventas del sistema</p>
            </div>
            <a href="../ventas/index.php"
               class="text-xs font-black px-3 py-1.5 rounded-lg no-underline transition-all"
               style="background:#FFF7ED;border:1px solid #F3D5B5;color:#EA6A0A;"
               onmouseover="this.style.background='#FED7AA';"
               onmouseout="this.style.background='#FFF7ED';">
                Ver todas <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <?php if (empty($ultimasVentas)): ?>
        <div class="px-6 py-10 text-center">
            <p class="text-sm font-bold" style="color:#A87D5C;">🍞 No hay ventas registradas aún.</p>
        </div>
        <?php else: ?>
        <div class="divide-y" style="--tw-divide-opacity:1;">
            <?php foreach ($ultimasVentas as $v): ?>
            <div class="flex items-center gap-4 px-6 py-3.5 transition-colors"
                 onmouseover="this.style.background='#FFFBF7';"
                 onmouseout="this.style.background='';">
                <!-- Ícono estado -->
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm flex-shrink-0"
                     style="background:<?= $v['estado']==='completada' ? '#D1FAE5' : ($v['estado']==='anulada' ? '#FEE2E2' : '#FEF3C7') ?>;">
                    <?= $v['estado']==='completada' ? '✅' : ($v['estado']==='anulada' ? '❌' : '⏳') ?>
                </div>
                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-black text-sm" style="color:#F97316;">#<?= str_pad($v['id_venta'],4,'0',STR_PAD_LEFT) ?></span>
                        <span class="text-xs font-semibold" style="color:#A87D5C;">·</span>
                        <span class="text-xs font-semibold truncate" style="color:#6B4F3A;">
                            <?= htmlspecialchars($v['nombres'] . ' ' . $v['apellidos']) ?>
                        </span>
                    </div>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-xs font-semibold" style="color:#A87D5C;">
                            <?= date('d/m/Y H:i', strtotime($v['fecha'])) ?>
                        </span>
                        <span class="text-xs font-semibold" style="color:#A87D5C;">·</span>
                        <span class="text-xs font-semibold" style="color:#A87D5C;">
                            <?= htmlspecialchars($v['metodo_pago'] ?? '—') ?>
                        </span>
                    </div>
                </div>
                <!-- Total -->
                <span class="font-black text-sm flex-shrink-0" style="color:#1C0A00;">
                    $<?= number_format($v['total'],0,',','.') ?>
                </span>
                <!-- Ver detalle -->
                <button type="button"
                   onclick="verDetalleVenta(<?= $v['id_venta'] ?>)"
                   class="w-7 h-7 rounded-lg border flex items-center justify-center text-xs transition-all flex-shrink-0"
                   style="border-color:#F3D5B5;color:#A87D5C;"
                   onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';this.style.color='#F97316';"
                   onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';this.style.color='#A87D5C';">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Top productos (1/3) -->
    <div class="bg-white rounded-2xl border" style="border-color:#F3D5B5;">
        <div class="px-5 py-4 border-b" style="border-color:#F3D5B5;">
            <h3 class="text-base font-black" style="color:#1C0A00;">Top <span style="color:#F97316;">Productos</span></h3>
            <p class="text-xs font-semibold" style="color:#A87D5C;">Más vendidos este mes</p>
        </div>

        <?php if (empty($topProductos)): ?>
        <div class="px-5 py-8 text-center">
            <p class="text-sm font-bold" style="color:#A87D5C;">Sin datos este mes.</p>
        </div>
        <?php else: ?>
        <div class="p-5 flex flex-col gap-4">
            <?php
            $medallas = ['🥇','🥈','🥉'];
            $maxUnidades = max(array_column($topProductos, 'unidades'));
            foreach ($topProductos as $idx => $p):
                $pct = $maxUnidades > 0 ? round(($p['unidades'] / $maxUnidades) * 100) : 0;
            ?>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <span class="text-base"><?= $medallas[$idx] ?? ($idx+1) ?></span>
                        <span class="text-sm font-black truncate" style="color:#1C0A00;max-width:130px;">
                            <?= htmlspecialchars($p['nombre']) ?>
                        </span>
                    </div>
                    <span class="text-xs font-black" style="color:#F97316;"><?= $p['unidades'] ?> uds</span>
                </div>
                <!-- Barra de progreso -->
                <div class="h-1.5 rounded-full overflow-hidden" style="background:#F3D5B5;">
                    <div class="h-full rounded-full transition-all"
                         style="width:<?= $pct ?>%;background:linear-gradient(90deg,#F97316,#FB923C);"></div>
                </div>
                <p class="text-xs font-semibold mt-1" style="color:#A87D5C;">
                    $<?= number_format($p['ingresos'],0,',','.') ?> en ingresos
                </p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="px-5 pb-4">
            <a href="../reportes/index.php?tab=productos"
               class="w-full py-2 rounded-xl text-xs font-black text-center no-underline block transition-all"
               style="background:#FFF7ED;border:1px solid #F3D5B5;color:#EA6A0A;"
               onmouseover="this.style.background='#FED7AA';"
               onmouseout="this.style.background='#FFF7ED';">
                Ver reporte completo <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

</div>

<!-- ══ FILA INFERIOR: Resumen del equipo + Accesos rápidos ══ -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <!-- Resumen del equipo (1/3) -->
    <div class="bg-white rounded-2xl border" style="border-color:#F3D5B5;">
        <div class="px-5 py-4 border-b" style="border-color:#F3D5B5;">
            <h3 class="text-base font-black" style="color:#1C0A00;">Tu <span style="color:#F97316;">Equipo</span></h3>
            <p class="text-xs font-semibold" style="color:#A87D5C;"><?= $totalUsuarios ?> usuarios registrados</p>
        </div>
        <div class="p-5 flex flex-col gap-3">

            <!-- Admins -->
            <div class="flex items-center gap-3 p-3 rounded-xl" style="background:#F0FDF4;border:1px solid #BBF7D0;">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-base flex-shrink-0" style="background:#D1FAE5;">🛡️</div>
                <div class="flex-1">
                    <p class="text-sm font-black" style="color:#065F46;">Administradores</p>
                    <p class="text-xs font-semibold" style="color:#6EE7B7;">Acceso completo al sistema</p>
                </div>
                <span class="text-2xl font-black" style="color:#065F46;"><?= $totalAdmins ?></span>
            </div>

            <!-- Cajeros -->
            <div class="flex items-center gap-3 p-3 rounded-xl" style="background:#F5F3FF;border:1px solid #DDD6FE;">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-base flex-shrink-0" style="background:#EDE9FE;">👨‍🍳</div>
                <div class="flex-1">
                    <p class="text-sm font-black" style="color:#5B21B6;">Cajeros</p>
                    <p class="text-xs font-semibold" style="color:#C4B5FD;">Ventas y productos</p>
                </div>
                <span class="text-2xl font-black" style="color:#5B21B6;"><?= $totalCajeros ?></span>
            </div>

            <!-- Activos -->
            <?php $activos = count(array_filter($usuarios, fn($u) => $u['activo'])); ?>
            <div class="flex items-center justify-between px-3 py-2 rounded-xl" style="background:#FFF7ED;border:1px solid #F3D5B5;">
                <span class="text-xs font-black" style="color:#6B4F3A;">Usuarios activos</span>
                <span class="text-sm font-black" style="color:#F97316;"><?= $activos ?> / <?= $totalUsuarios ?></span>
            </div>

            <a href="../usuarios/index.php"
               class="w-full py-2 rounded-xl text-xs font-black text-center no-underline block transition-all mt-1"
               style="background:#F97316;color:#fff;box-shadow:0 4px 12px rgba(249,115,22,0.25);"
               onmouseover="this.style.background='#EA6A0A';"
               onmouseout="this.style.background='#F97316';">
                <i class="fas fa-users mr-1"></i> Gestionar Usuarios
            </a>
        </div>
    </div>

    <!-- Accesos rápidos (2/3) -->
    <div class="lg:col-span-2 bg-white rounded-2xl border" style="border-color:#F3D5B5;">
        <div class="px-6 py-4 border-b" style="border-color:#F3D5B5;">
            <h3 class="text-base font-black" style="color:#1C0A00;">Accesos <span style="color:#F97316;">Rápidos</span></h3>
            <p class="text-xs font-semibold" style="color:#A87D5C;">Navega a cualquier módulo del sistema</p>
        </div>
        <div class="p-5 grid grid-cols-2 sm:grid-cols-3 gap-3">

            <?php
            $modulos = [
                ['../ventas/index.php',      '🛒', 'Ventas',      'Registrar y ver ventas',       '#FFF7ED', '#F97316'],
                ['../inventario/index.php',  '📦', 'Inventario',  'Control de stock',             '#F0FDF4', '#10B981'],
                ['../productos/index.php',   '🍞', 'Productos',   'Catálogo de productos',        '#FFF7ED', '#F97316'],
                ['../proveedores/index.php', '🚚', 'Proveedores', 'Gestión de proveedores',       '#EFF6FF', '#3B82F6'],
                ['../insumos/index.php',     '🌾', 'Insumos',     'Materias primas',              '#F5F3FF', '#8B5CF6'],
                ['../reportes/index.php',    '📊', 'Reportes',    'Análisis y estadísticas',      '#FEF3C7', '#D97706'],
            ];
            foreach ($modulos as [$url, $emoji, $nombre, $desc, $bg, $color]):
            ?>
            <a href="<?= $url ?>"
               class="flex flex-col gap-2 p-4 rounded-xl border no-underline transition-all group"
               style="border-color:#F3D5B5;"
               onmouseover="this.style.borderColor='<?= $color ?>';this.style.background='<?= $bg ?>';this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 16px rgba(0,0,0,0.08)';"
               onmouseout="this.style.borderColor='#F3D5B5';this.style.background='';this.style.transform='';this.style.boxShadow='';">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl flex-shrink-0"
                     style="background:<?= $bg ?>;">
                    <?= $emoji ?>
                </div>
                <div>
                    <p class="font-black text-sm" style="color:#1C0A00;"><?= $nombre ?></p>
                    <p class="text-xs font-semibold" style="color:#A87D5C;"><?= $desc ?></p>
                </div>
            </a>
            <?php endforeach; ?>

        </div>
    </div>

</div>

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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
<div id="modal-detalle-venta" class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(28,10,0,0.45);backdrop-filter:blur(4px);display:none!important;">
    <div id="modal-detalle-venta-box" class="bg-white rounded-2xl w-full shadow-2xl"
         style="max-width:520px;border:1px solid #F3D5B5;transform:scale(0.92) translateY(16px);opacity:0;transition:transform 0.25s cubic-bezier(0.34,1.56,0.64,1),opacity 0.2s ease;">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b" style="border-color:#F3D5B5;">
            <div>
                <h3 class="text-lg font-black" style="color:#1C0A00;">
                    Venta <span style="color:#F97316;" id="mdv-numero">#0000</span>
                </h3>
                <p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;" id="mdv-fecha">—</p>
            </div>
            <div class="flex items-center gap-2">
                <span id="mdv-estado-badge" class="text-xs font-black px-3 py-1.5 rounded-full"></span>
                <button onclick="cerrarModal('modal-detalle-venta','modal-detalle-venta-box')"
                        class="w-8 h-8 rounded-xl flex items-center justify-center text-sm transition-all"
                        style="background:#FFF7ED;border:1px solid #F3D5B5;color:#A87D5C;"
                        onmouseover="this.style.background='#FEE2E2';this.style.color='#DC2626';"
                        onmouseout="this.style.background='#FFF7ED';this.style.color='#A87D5C';">✕</button>
            </div>
        </div>

        <!-- Info cajero + método -->
        <div class="px-6 py-4 grid grid-cols-2 gap-4 border-b" style="border-color:#F3D5B5;">
            <div>
                <p class="text-xs font-black uppercase tracking-wider mb-1" style="color:#A87D5C;">Cajero</p>
                <p class="text-sm font-black" style="color:#1C0A00;" id="mdv-cajero">—</p>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-wider mb-1" style="color:#A87D5C;">Método de Pago</p>
                <p class="text-sm font-black" style="color:#1C0A00;" id="mdv-metodo">—</p>
            </div>
        </div>

        <!-- Loading -->
        <div id="mdv-loading" class="px-6 py-8 text-center">
            <div class="inline-flex items-center gap-2" style="color:#A87D5C;">
                <i class="fas fa-spinner fa-spin"></i>
                <span class="text-sm font-semibold">Cargando detalle...</span>
            </div>
        </div>

        <!-- Productos -->
        <div id="mdv-productos" class="px-6 py-4 hidden">
            <p class="text-xs font-black uppercase tracking-wider mb-3" style="color:#A87D5C;">Productos</p>
            <div id="mdv-lista" class="flex flex-col gap-0 max-h-52 overflow-y-auto"></div>
            <div class="flex items-center justify-between pt-4 mt-2 border-t" style="border-color:#F3D5B5;">
                <span class="font-black text-base" style="color:#6B4F3A;">TOTAL</span>
                <span class="font-black text-2xl" style="color:#F97316;" id="mdv-total">$0</span>
            </div>
        </div>

        <!-- Footer botones -->
        <div class="px-6 pb-5 flex gap-3" id="mdv-footer">
            <button onclick="cerrarModal('modal-detalle-venta','modal-detalle-venta-box')"
                    class="flex-1 py-2.5 rounded-xl text-sm font-black transition-all"
                    style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#6B4F3A;"
                    onmouseover="this.style.background='#F3D5B5';" onmouseout="this.style.background='#FFF7ED';">
                <i class="fas fa-times mr-1"></i> Cerrar
            </button>
            <a id="mdv-btn-ver" href="#"
               class="flex-1 py-2.5 rounded-xl text-sm font-black text-center no-underline transition-all"
               style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#EA6A0A;"
               onmouseover="this.style.background='#FED7AA';" onmouseout="this.style.background='#FFF7ED';">
                <i class="fas fa-external-link-alt mr-1"></i> Ver completo
            </a>
            <button id="mdv-btn-anular" onclick="anularVentaModal()"
                    class="flex-1 py-2.5 rounded-xl text-sm font-black transition-all hidden"
                    style="background:#FEE2E2;border:1.5px solid #FCA5A5;color:#DC2626;"
                    onmouseover="this.style.background='#FECACA';" onmouseout="this.style.background='#FEE2E2';">
                <i class="fas fa-ban mr-1"></i> Anular
            </button>
        </div>
    </div>
</div>

<script>
var _ventaIdActual = null;

function verDetalleVenta(id) {
    _ventaIdActual = id;
    abrirModal('modal-detalle-venta', 'modal-detalle-venta-box');

    // Reset
    document.getElementById('mdv-numero').textContent  = '#' + String(id).padStart(4,'0');
    document.getElementById('mdv-fecha').textContent   = '—';
    document.getElementById('mdv-cajero').textContent  = '—';
    document.getElementById('mdv-metodo').textContent  = '—';
    document.getElementById('mdv-total').textContent   = '$0';
    document.getElementById('mdv-lista').innerHTML     = '';
    document.getElementById('mdv-loading').classList.remove('hidden');
    document.getElementById('mdv-productos').classList.add('hidden');
    document.getElementById('mdv-btn-anular').classList.add('hidden');
    document.getElementById('mdv-btn-ver').href = '/PanApp/views/ventas/detalle.php?id=' + id;

    fetch('/PanApp/controllers/VentaDetalleController.php?id=' + id)
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.error) { alert(data.error); return; }

        var v = data.venta;
        var d = data.detalle;

        // Número y fecha
        document.getElementById('mdv-numero').textContent = '#' + String(v.id_venta).padStart(4,'0');
        document.getElementById('mdv-fecha').textContent  = formatFecha(v.fecha);
        document.getElementById('mdv-cajero').textContent = v.nombres + ' ' + v.apellidos;
        document.getElementById('mdv-metodo').textContent = v.metodo_pago || '—';

        // Badge estado
        var badge = document.getElementById('mdv-estado-badge');
        if (v.estado === 'completada') {
            badge.textContent = '✅ Completada';
            badge.style.cssText = 'background:#D1FAE5;color:#065F46;border:1px solid #6EE7B7;';
        } else if (v.estado === 'anulada') {
            badge.textContent = '❌ Anulada';
            badge.style.cssText = 'background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;';
        } else {
            badge.textContent = '⏳ Pendiente';
            badge.style.cssText = 'background:#FEF3C7;color:#92400E;border:1px solid #FCD34D;';
        }

        // Productos
        var lista = '';
        d.forEach(function(item) {
            lista += '<div class="flex items-center justify-between py-2.5 border-b" style="border-color:#F3D5B5;">' +
                '<div>' +
                    '<p class="text-sm font-black" style="color:#1C0A00;">' + item.nombre + '</p>' +
                    '<p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;">' + item.cantidad + ' x $' + fmt(item.precio_unitario) + '</p>' +
                '</div>' +
                '<p class="font-black text-sm" style="color:#F97316;">$' + fmt(item.subtotal) + '</p>' +
            '</div>';
        });
        document.getElementById('mdv-lista').innerHTML = lista;
        document.getElementById('mdv-total').textContent = '$' + fmt(v.total);

        // Botón anular
        if (v.estado !== 'anulada') {
            document.getElementById('mdv-btn-anular').classList.remove('hidden');
        }

        document.getElementById('mdv-loading').classList.add('hidden');
        document.getElementById('mdv-productos').classList.remove('hidden');
    })
    .catch(function() {
        document.getElementById('mdv-loading').innerHTML = '<p class="text-sm font-bold" style="color:#DC2626;">⚠️ Error al cargar el detalle.</p>';
    });
}

function anularVentaModal() {
    if (!_ventaIdActual) return;
    Swal.fire({
        icon: 'warning',
        title: '¿Anular venta?',
        html: 'La venta <strong>#' + String(_ventaIdActual).padStart(4,'0') + '</strong> será anulada.',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#DC2626'
    }).then(function(r) {
        if (r.isConfirmed) {
            window.location.href = '/PanApp/controllers/VentaController.php?accion=anular&id=' + _ventaIdActual;
        }
    });
}

function formatFecha(str) {
    var d = new Date(str.replace(' ', 'T'));
    return d.toLocaleDateString('es-CO', { day:'2-digit', month:'2-digit', year:'numeric' }) + ' ' +
           d.toLocaleTimeString('es-CO', { hour:'2-digit', minute:'2-digit' });
}

function fmt(n) {
    return new Intl.NumberFormat('es-CO').format(n);
}
</script>

<!-- ══ MODAL NUEVA VENTA (Dashboard Admin) ══ -->
<style>
    #modal-venta-dash-box{max-width:900px;width:100%;max-height:90vh;display:flex;flex-direction:column;}
    .mvd-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:10px;}
    .mvd-card{border:1.5px solid #F3D5B5;border-radius:12px;padding:12px;cursor:pointer;transition:border-color .15s,background .15s,transform .1s;user-select:none;}
    .mvd-card:hover{border-color:#F97316;background:#FFF7ED;transform:translateY(-2px);}
    .mvd-card.en-carrito{border-color:#F97316;background:#FFF7ED;}
    #mvd-carrito-items{max-height:260px;overflow-y:auto;}
    #mvd-carrito-items::-webkit-scrollbar{width:3px;}
    #mvd-carrito-items::-webkit-scrollbar-thumb{background:#F3D5B5;border-radius:3px;}
    #mvd-prods-scroll{max-height:340px;overflow-y:auto;padding-right:4px;}
    #mvd-prods-scroll::-webkit-scrollbar{width:3px;}
    #mvd-prods-scroll::-webkit-scrollbar-thumb{background:#F3D5B5;border-radius:3px;}
</style>

<div id="modal-venta-dash" class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(28,10,0,0.5);backdrop-filter:blur(4px);display:none!important;">
    <div id="modal-venta-dash-box" class="bg-white rounded-2xl shadow-2xl overflow-hidden"
         style="border:1px solid #F3D5B5;transform:scale(0.92) translateY(16px);opacity:0;transition:transform 0.25s cubic-bezier(0.34,1.56,0.64,1),opacity 0.2s ease;">

        <div class="flex items-center justify-between px-6 py-4 flex-shrink-0" style="background:linear-gradient(135deg,#F97316,#EA6A0A);">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg" style="background:rgba(255,255,255,0.2);">🛒</div>
                <div>
                    <h3 class="text-base font-black text-white">Nueva Venta</h3>
                    <p class="text-xs font-semibold" style="color:rgba(255,255,255,0.8);">Selecciona productos y registra el cobro</p>
                </div>
            </div>
            <button onclick="cerrarModalVentaDash()" class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-black text-white" style="background:rgba(255,255,255,0.15);" onmouseover="this.style.background='rgba(255,255,255,0.25)';" onmouseout="this.style.background='rgba(255,255,255,0.15)';">✕</button>
        </div>

        <div class="flex flex-1 overflow-hidden" style="min-height:0;">
            <div class="flex-1 flex flex-col border-r overflow-hidden" style="border-color:#F3D5B5;">
                <div class="px-5 py-3 border-b flex-shrink-0" style="border-color:#F3D5B5;background:#FDFAF7;">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                        <input type="text" id="mvd-buscador" placeholder="Buscar producto..." class="w-full pl-8 pr-3 py-2 rounded-xl text-sm font-semibold outline-none" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;" onfocus="this.style.borderColor='#F97316';this.style.background='#fff';" onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';" oninput="mvdFiltrar(this.value)">
                    </div>
                </div>
                <div id="mvd-prods-scroll" class="p-4 flex-1">
                    <?php if (empty($productosDash)): ?>
                    <p class="text-center text-sm font-bold py-8" style="color:#A87D5C;">🍞 No hay productos registrados.</p>
                    <?php else: ?>
                    <div class="mvd-grid" id="mvd-grid">
                        <?php foreach ($productosDash as $p): ?>
                        <div class="mvd-card" data-id="<?= $p['id_producto'] ?>" data-nombre="<?= htmlspecialchars($p['nombre']) ?>" data-precio="<?= $p['precio'] ?>" data-cat="<?= htmlspecialchars(strtolower($p['categoria'] ?? '')) ?>" onclick="mvdAgregar(this)">
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
                    <button onclick="mvdLimpiar()" class="text-xs font-black px-2.5 py-1 rounded-lg" style="background:#FEE2E2;color:#DC2626;border:1px solid #FCA5A5;" onmouseover="this.style.background='#FECACA';" onmouseout="this.style.background='#FEE2E2';">Limpiar</button>
                </div>
                <div id="mvd-carrito-items" class="flex-1 px-4 py-3">
                    <p id="mvd-empty" class="text-xs font-bold text-center py-6" style="color:#A87D5C;">Selecciona productos del catálogo</p>
                </div>
                <div class="px-4 py-4 border-t flex-shrink-0" style="border-color:#F3D5B5;background:#FDFAF7;">
                    <div class="flex items-center justify-between mb-3 pb-3 border-b" style="border-color:#F3D5B5;">
                        <span class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Total</span>
                        <span class="text-xl font-black" style="color:#F97316;" id="mvd-total">$0</span>
                    </div>
                    <div class="mb-3">
                        <label class="text-xs font-black uppercase tracking-wider block mb-1.5" style="color:#6B4F3A;">Método de Pago</label>
                        <div class="relative">
                            <i class="fas fa-credit-card absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                            <select id="mvd-metodo" class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none appearance-none" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;" onfocus="this.style.borderColor='#F97316';" onblur="this.style.borderColor='#F3D5B5';">
                                <option value="">-- Selecciona --</option>
                                <?php foreach ($metodosPagoDash as $mp): ?>
                                <option value="<?= $mp['id_metodo_pago'] ?>"><?= htmlspecialchars($mp['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button onclick="mvdConfirmar()" class="w-full py-2.5 rounded-xl text-sm font-black text-white flex items-center justify-center gap-2" style="background:#F97316;box-shadow:0 4px 14px rgba(249,115,22,0.35);" onmouseover="this.style.background='#EA6A0A';" onmouseout="this.style.background='#F97316';"><i class="fas fa-check"></i> Registrar Venta</button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="mvd-form" action="/PanApp/controllers/VentaController.php" method="POST" style="display:none;">
    <input type="hidden" name="id_metodo_pago" id="mvd-input-metodo">
    <input type="hidden" name="total"          id="mvd-input-total">
    <input type="hidden" name="items"          id="mvd-input-items">
</form>

<script>
(function(){
    var carrito={};
    window.abrirModalVentaDash=function(){
        abrirModal('modal-venta-dash','modal-venta-dash-box');
        carrito={};mvdRender();
        document.getElementById('mvd-buscador').value='';mvdFiltrar('');
        document.getElementById('mvd-metodo').value='';
    };
    window.cerrarModalVentaDash=function(){ cerrarModal('modal-venta-dash','modal-venta-dash-box'); };
    window.mvdAgregar=function(el){
        var id=el.dataset.id;
        if(carrito[id]) carrito[id].cantidad++;
        else carrito[id]={nombre:el.dataset.nombre,precio:parseFloat(el.dataset.precio),cantidad:1};
        el.classList.add('en-carrito'); mvdRender();
    };
    window.mvdCambiar=function(id,delta){
        if(!carrito[id]) return;
        carrito[id].cantidad+=delta;
        if(carrito[id].cantidad<=0){ delete carrito[id]; var c=document.querySelector('.mvd-card[data-id="'+id+'"]'); if(c) c.classList.remove('en-carrito'); }
        mvdRender();
    };
    window.mvdLimpiar=function(){ carrito={}; document.querySelectorAll('.mvd-card').forEach(function(c){c.classList.remove('en-carrito');}); mvdRender(); };
    function mvdRender(){
        var cont=document.getElementById('mvd-carrito-items'),tot=document.getElementById('mvd-total'),keys=Object.keys(carrito);
        if(!keys.length){ cont.innerHTML='<p class="text-xs font-bold text-center py-6" style="color:#A87D5C;">Selecciona productos del catálogo</p>'; tot.textContent='$0'; return; }
        var html='',total=0;
        keys.forEach(function(id){ var it=carrito[id],sub=it.precio*it.cantidad; total+=sub;
            html+='<div class="flex items-center gap-2 py-2 border-b" style="border-color:#F3D5B5;"><div class="flex-1 min-w-0"><p class="text-xs font-black truncate" style="color:#1C0A00;">'+it.nombre+'</p><p class="text-xs font-semibold" style="color:#A87D5C;">$'+mvdFmt(it.precio)+' c/u</p></div><div class="flex items-center gap-1"><button onclick="mvdCambiar(\''+id+'\',-1)" class="w-5 h-5 rounded text-xs font-black flex items-center justify-center border" style="border-color:#F3D5B5;color:#DC2626;" onmouseover="this.style.background=\'#FEE2E2\';" onmouseout="this.style.background=\'\';">−</button><span class="w-5 text-center text-xs font-black" style="color:#1C0A00;">'+it.cantidad+'</span><button onclick="mvdCambiar(\''+id+'\',1)" class="w-5 h-5 rounded text-xs font-black flex items-center justify-center border" style="border-color:#F3D5B5;color:#F97316;" onmouseover="this.style.background=\'#FFF7ED\';" onmouseout="this.style.background=\'\';">+</button></div><p class="text-xs font-black" style="color:#F97316;min-width:52px;text-align:right;">$'+mvdFmt(sub)+'</p></div>';
        });
        cont.innerHTML=html; tot.textContent='$'+mvdFmt(total);
    }
    window.mvdFiltrar=function(q){ q=q.toLowerCase(); document.querySelectorAll('.mvd-card').forEach(function(c){ c.style.display=(!q||c.dataset.nombre.toLowerCase().includes(q)||(c.dataset.cat||'').includes(q))?'':'none'; }); };
    window.mvdConfirmar=function(){
        var keys=Object.keys(carrito);
        if(!keys.length){ Swal.fire({icon:'warning',title:'Carrito vacío',text:'Agrega al menos un producto.',confirmButtonColor:'#F97316'}); return; }
        var metodo=document.getElementById('mvd-metodo').value;
        if(!metodo){ Swal.fire({icon:'warning',title:'Método de pago',text:'Selecciona un método de pago.',confirmButtonColor:'#F97316'}); return; }
        var total=0; keys.forEach(function(id){ total+=carrito[id].precio*carrito[id].cantidad; });
        var items=keys.map(function(id){ return{id_producto:id,cantidad:carrito[id].cantidad,precio_unitario:carrito[id].precio,subtotal:carrito[id].precio*carrito[id].cantidad}; });
        Swal.fire({icon:'question',title:'¿Confirmar venta?',html:'<strong style="color:#F97316;font-size:1.3rem;">$'+mvdFmt(total)+'</strong><br><span style="color:#6B4F3A;font-size:.85rem;">'+keys.length+' producto(s)</span>',showCancelButton:true,confirmButtonText:'✅ Registrar',cancelButtonText:'Cancelar',confirmButtonColor:'#F97316'})
        .then(function(r){ if(r.isConfirmed){ document.getElementById('mvd-input-metodo').value=metodo; document.getElementById('mvd-input-total').value=total; document.getElementById('mvd-input-items').value=JSON.stringify(items); document.getElementById('mvd-form').submit(); } });
    };
    function mvdFmt(n){ return new Intl.NumberFormat('es-CO').format(n); }
})();
</script>
