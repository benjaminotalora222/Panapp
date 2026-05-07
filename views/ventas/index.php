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

    <button type="button" onclick="abrirModalVenta()"
       class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-black text-white transition-all"
       style="background:#F97316;box-shadow:0 4px 12px rgba(249,115,22,0.3);"
       onmouseover="this.style.background='#EA6A0A';this.style.transform='translateY(-1px)';"
       onmouseout="this.style.background='#F97316';this.style.transform='';">
        <i class="fas fa-plus"></i> Nueva Venta
    </button>
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
                        <button type="button"
                           onclick="verDetalleVentaModal(<?= $v['id_venta'] ?>)"
                           class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all mx-auto"
                           style="border-color:#F3D5B5;color:#6B4F3A;"
                           onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';this.style.color='#F97316';"
                           onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';this.style.color='#6B4F3A';"
                           title="Ver detalle">
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
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<!-- ══════════════════════════════
     MODAL NUEVA VENTA
══════════════════════════════ -->
<?php
// Cargar productos y métodos de pago para el modal
$stmtProd = $db->prepare("SELECT id_producto, nombre, precio, categoria, unidad_medida FROM productos ORDER BY categoria, nombre");
$stmtProd->execute();
$productosModal = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

$stmtPago = $db->prepare("SELECT id_metodo_pago, nombre FROM metodos_pago ORDER BY nombre");
$stmtPago->execute();
$metodosPagoModal = $stmtPago->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    #modal-venta-box {
        max-width: 900px;
        width: 100%;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
    }
    .mv-productos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 10px;
    }
    .mv-prod-card {
        border: 1.5px solid #F3D5B5;
        border-radius: 12px;
        padding: 12px;
        cursor: pointer;
        transition: border-color 0.15s, background 0.15s, transform 0.1s;
        user-select: none;
    }
    .mv-prod-card:hover {
        border-color: #F97316;
        background: #FFF7ED;
        transform: translateY(-2px);
    }
    .mv-prod-card.en-carrito {
        border-color: #F97316;
        background: #FFF7ED;
    }
    #mv-carrito-items {
        max-height: 260px;
        overflow-y: auto;
    }
    #mv-carrito-items::-webkit-scrollbar { width: 3px; }
    #mv-carrito-items::-webkit-scrollbar-thumb { background: #F3D5B5; border-radius: 3px; }
    #mv-productos-scroll {
        max-height: 340px;
        overflow-y: auto;
        padding-right: 4px;
    }
    #mv-productos-scroll::-webkit-scrollbar { width: 3px; }
    #mv-productos-scroll::-webkit-scrollbar-thumb { background: #F3D5B5; border-radius: 3px; }
</style>

<div id="modal-venta" class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(28,10,0,0.5);backdrop-filter:blur(4px);display:none!important;">
    <div id="modal-venta-box" class="bg-white rounded-2xl shadow-2xl overflow-hidden"
         style="border:1px solid #F3D5B5;transform:scale(0.92) translateY(16px);opacity:0;transition:transform 0.25s cubic-bezier(0.34,1.56,0.64,1),opacity 0.2s ease;">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 flex-shrink-0"
             style="background:linear-gradient(135deg,#F97316,#EA6A0A);">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg"
                     style="background:rgba(255,255,255,0.2);">🛒</div>
                <div>
                    <h3 class="text-base font-black text-white">Nueva Venta</h3>
                    <p class="text-xs font-semibold" style="color:rgba(255,255,255,0.8);">
                        Selecciona productos y registra el cobro
                    </p>
                </div>
            </div>
            <button onclick="cerrarModalVenta()"
                    class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-black text-white transition-all"
                    style="background:rgba(255,255,255,0.15);"
                    onmouseover="this.style.background='rgba(255,255,255,0.25)';"
                    onmouseout="this.style.background='rgba(255,255,255,0.15)';">✕</button>
        </div>

        <!-- Body: 2 columnas -->
        <div class="flex flex-1 overflow-hidden" style="min-height:0;">

            <!-- Columna izquierda: Productos -->
            <div class="flex-1 flex flex-col border-r overflow-hidden" style="border-color:#F3D5B5;">
                <!-- Buscador -->
                <div class="px-5 py-3 border-b flex-shrink-0" style="border-color:#F3D5B5;background:#FDFAF7;">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                        <input type="text" id="mv-buscador" placeholder="Buscar producto..."
                               class="w-full pl-8 pr-3 py-2 rounded-xl text-sm font-semibold outline-none"
                               style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                               onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                               onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';"
                               oninput="mvFiltrar(this.value)">
                    </div>
                </div>
                <!-- Grid productos -->
                <div id="mv-productos-scroll" class="p-4 flex-1">
                    <?php if (empty($productosModal)): ?>
                    <p class="text-center text-sm font-bold py-8" style="color:#A87D5C;">🍞 No hay productos registrados.</p>
                    <?php else: ?>
                    <div class="mv-productos-grid" id="mv-grid">
                        <?php foreach ($productosModal as $p): ?>
                        <div class="mv-prod-card"
                             data-id="<?= $p['id_producto'] ?>"
                             data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                             data-precio="<?= $p['precio'] ?>"
                             data-cat="<?= htmlspecialchars(strtolower($p['categoria'] ?? '')) ?>"
                             onclick="mvAgregar(this)">
                            <p class="font-black text-xs leading-tight mb-1" style="color:#1C0A00;">
                                <?= htmlspecialchars($p['nombre']) ?>
                            </p>
                            <p class="text-xs font-semibold" style="color:#A87D5C;">
                                <?= htmlspecialchars($p['categoria'] ?? '—') ?>
                            </p>
                            <p class="font-black text-sm mt-2" style="color:#F97316;">
                                $<?= number_format($p['precio'], 0, ',', '.') ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Columna derecha: Carrito + pago -->
            <div class="flex flex-col flex-shrink-0" style="width:280px;">

                <!-- Carrito header -->
                <div class="px-4 py-3 border-b flex-shrink-0 flex items-center justify-between"
                     style="border-color:#F3D5B5;background:#FDFAF7;">
                    <span class="text-sm font-black" style="color:#1C0A00;">🧾 Carrito</span>
                    <button onclick="mvLimpiarCarrito()"
                            class="text-xs font-black px-2.5 py-1 rounded-lg transition-all"
                            style="background:#FEE2E2;color:#DC2626;border:1px solid #FCA5A5;"
                            onmouseover="this.style.background='#FECACA';"
                            onmouseout="this.style.background='#FEE2E2';">
                        Limpiar
                    </button>
                </div>

                <!-- Items del carrito -->
                <div id="mv-carrito-items" class="flex-1 px-4 py-3">
                    <p id="mv-empty" class="text-xs font-bold text-center py-6" style="color:#A87D5C;">
                        Selecciona productos del catálogo
                    </p>
                </div>

                <!-- Total + método + botón -->
                <div class="px-4 py-4 border-t flex-shrink-0" style="border-color:#F3D5B5;background:#FDFAF7;">

                    <!-- Total -->
                    <div class="flex items-center justify-between mb-3 pb-3 border-b" style="border-color:#F3D5B5;">
                        <span class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Total</span>
                        <span class="text-xl font-black" style="color:#F97316;" id="mv-total">$0</span>
                    </div>

                    <!-- Método de pago -->
                    <div class="mb-3">
                        <label class="text-xs font-black uppercase tracking-wider block mb-1.5" style="color:#6B4F3A;">
                            Método de Pago
                        </label>
                        <div class="relative">
                            <i class="fas fa-credit-card absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                            <select id="mv-metodo"
                                    class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none appearance-none"
                                    style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                                    onfocus="this.style.borderColor='#F97316';"
                                    onblur="this.style.borderColor='#F3D5B5';">
                                <option value="">-- Selecciona --</option>
                                <?php foreach ($metodosPagoModal as $mp): ?>
                                <option value="<?= $mp['id_metodo_pago'] ?>"><?= htmlspecialchars($mp['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Botón registrar -->
                    <button onclick="mvConfirmar()"
                            class="w-full py-2.5 rounded-xl text-sm font-black text-white transition-all flex items-center justify-center gap-2"
                            style="background:#F97316;box-shadow:0 4px 14px rgba(249,115,22,0.35);"
                            onmouseover="this.style.background='#EA6A0A';"
                            onmouseout="this.style.background='#F97316';">
                        <i class="fas fa-check"></i> Registrar Venta
                    </button>

                </div>
            </div>
        </div>

    </div>
</div>

<!-- Form oculto para enviar -->
<form id="mv-form" action="/PanApp/controllers/VentaController.php" method="POST" style="display:none;">
    <input type="hidden" name="id_metodo_pago" id="mv-input-metodo">
    <input type="hidden" name="total"          id="mv-input-total">
    <input type="hidden" name="items"          id="mv-input-items">
</form>

<script>
(function() {
    var carrito = {};

    // ── Abrir / cerrar ──
    window.abrirModalVenta = function() {
        var modal = document.getElementById('modal-venta');
        var box   = document.getElementById('modal-venta-box');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        setTimeout(function() {
            box.style.transform = 'scale(1) translateY(0)';
            box.style.opacity   = '1';
        }, 10);
        // Limpiar al abrir
        carrito = {};
        mvRenderCarrito();
        document.getElementById('mv-buscador').value = '';
        mvFiltrar('');
        document.getElementById('mv-metodo').value = '';
    };

    window.cerrarModalVenta = function() {
        var modal = document.getElementById('modal-venta');
        var box   = document.getElementById('modal-venta-box');
        box.style.transform = 'scale(0.92) translateY(16px)';
        box.style.opacity   = '0';
        setTimeout(function() {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 220);
    };

    // Cerrar con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('modal-venta').style.display === 'flex') {
            cerrarModalVenta();
        }
    });

    // ── Agregar producto ──
    window.mvAgregar = function(el) {
        var id     = el.dataset.id;
        var nombre = el.dataset.nombre;
        var precio = parseFloat(el.dataset.precio);
        if (carrito[id]) {
            carrito[id].cantidad++;
        } else {
            carrito[id] = { nombre: nombre, precio: precio, cantidad: 1 };
        }
        el.classList.add('en-carrito');
        mvRenderCarrito();
    };

    // ── Cambiar cantidad ──
    window.mvCambiarCantidad = function(id, delta) {
        if (!carrito[id]) return;
        carrito[id].cantidad += delta;
        if (carrito[id].cantidad <= 0) {
            delete carrito[id];
            // Quitar clase en-carrito de la card
            var card = document.querySelector('.mv-prod-card[data-id="' + id + '"]');
            if (card) card.classList.remove('en-carrito');
        }
        mvRenderCarrito();
    };

    // ── Limpiar carrito ──
    window.mvLimpiarCarrito = function() {
        carrito = {};
        document.querySelectorAll('.mv-prod-card').forEach(function(c) { c.classList.remove('en-carrito'); });
        mvRenderCarrito();
    };

    // ── Render carrito ──
    function mvRenderCarrito() {
        var container = document.getElementById('mv-carrito-items');
        var emptyMsg  = document.getElementById('mv-empty');
        var totalEl   = document.getElementById('mv-total');
        var keys      = Object.keys(carrito);

        if (keys.length === 0) {
            container.innerHTML = '<p id="mv-empty" class="text-xs font-bold text-center py-6" style="color:#A87D5C;">Selecciona productos del catálogo</p>';
            totalEl.textContent = '$0';
            return;
        }

        var html  = '';
        var total = 0;

        keys.forEach(function(id) {
            var item     = carrito[id];
            var subtotal = item.precio * item.cantidad;
            total += subtotal;
            html += '<div class="flex items-center gap-2 py-2 border-b" style="border-color:#F3D5B5;">' +
                '<div class="flex-1 min-w-0">' +
                    '<p class="text-xs font-black truncate" style="color:#1C0A00;">' + item.nombre + '</p>' +
                    '<p class="text-xs font-semibold" style="color:#A87D5C;">$' + mvFmt(item.precio) + ' c/u</p>' +
                '</div>' +
                '<div class="flex items-center gap-1">' +
                    '<button onclick="mvCambiarCantidad(\'' + id + '\',-1)" class="w-5 h-5 rounded text-xs font-black flex items-center justify-center border" style="border-color:#F3D5B5;color:#DC2626;" onmouseover="this.style.background=\'#FEE2E2\';" onmouseout="this.style.background=\'\';">−</button>' +
                    '<span class="w-5 text-center text-xs font-black" style="color:#1C0A00;">' + item.cantidad + '</span>' +
                    '<button onclick="mvCambiarCantidad(\'' + id + '\',1)" class="w-5 h-5 rounded text-xs font-black flex items-center justify-center border" style="border-color:#F3D5B5;color:#F97316;" onmouseover="this.style.background=\'#FFF7ED\';" onmouseout="this.style.background=\'\';">+</button>' +
                '</div>' +
                '<p class="text-xs font-black" style="color:#F97316;min-width:52px;text-align:right;">$' + mvFmt(subtotal) + '</p>' +
            '</div>';
        });

        container.innerHTML = html;
        totalEl.textContent = '$' + mvFmt(total);
    }

    // ── Filtrar productos ──
    window.mvFiltrar = function(q) {
        q = q.toLowerCase();
        document.querySelectorAll('.mv-prod-card').forEach(function(card) {
            var nombre = card.dataset.nombre.toLowerCase();
            var cat    = card.dataset.cat || '';
            card.style.display = (!q || nombre.includes(q) || cat.includes(q)) ? '' : 'none';
        });
    };

    // ── Confirmar venta ──
    window.mvConfirmar = function() {
        var keys = Object.keys(carrito);
        if (keys.length === 0) {
            Swal.fire({ icon:'warning', title:'Carrito vacío', text:'Agrega al menos un producto.', confirmButtonColor:'#F97316' });
            return;
        }
        var metodo = document.getElementById('mv-metodo').value;
        if (!metodo) {
            Swal.fire({ icon:'warning', title:'Método de pago', text:'Selecciona un método de pago.', confirmButtonColor:'#F97316' });
            return;
        }

        var total = 0;
        keys.forEach(function(id) { total += carrito[id].precio * carrito[id].cantidad; });

        var items = keys.map(function(id) {
            return {
                id_producto:     id,
                cantidad:        carrito[id].cantidad,
                precio_unitario: carrito[id].precio,
                subtotal:        carrito[id].precio * carrito[id].cantidad
            };
        });

        Swal.fire({
            icon: 'question',
            title: '¿Confirmar venta?',
            html: '<strong style="color:#F97316;font-size:1.3rem;">$' + mvFmt(total) + '</strong><br><span style="color:#6B4F3A;font-size:0.85rem;">' + keys.length + ' producto(s)</span>',
            showCancelButton: true,
            confirmButtonText: '✅ Registrar',
            cancelButtonText:  'Cancelar',
            confirmButtonColor: '#F97316'
        }).then(function(result) {
            if (result.isConfirmed) {
                document.getElementById('mv-input-metodo').value = metodo;
                document.getElementById('mv-input-total').value  = total;
                document.getElementById('mv-input-items').value  = JSON.stringify(items);
                document.getElementById('mv-form').submit();
            }
        });
    };

    function mvFmt(n) {
        return new Intl.NumberFormat('es-CO').format(n);
    }
})();
</script>

<!-- ══ MODAL DETALLE VENTA ══ -->
<div id="modal-detalle-v" class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(28,10,0,0.45);backdrop-filter:blur(4px);display:none!important;">
    <div id="modal-detalle-v-box" class="bg-white rounded-2xl w-full shadow-2xl"
         style="max-width:520px;border:1px solid #F3D5B5;transform:scale(0.92) translateY(16px);opacity:0;transition:transform 0.25s cubic-bezier(0.34,1.56,0.64,1),opacity 0.2s ease;">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b" style="border-color:#F3D5B5;">
            <div>
                <h3 class="text-lg font-black" style="color:#1C0A00;">
                    Venta <span style="color:#F97316;" id="mdv2-numero">#0000</span>
                </h3>
                <p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;" id="mdv2-fecha">—</p>
            </div>
            <div class="flex items-center gap-2">
                <span id="mdv2-estado-badge" class="text-xs font-black px-3 py-1.5 rounded-full"></span>
                <button onclick="cerrarModal('modal-detalle-v','modal-detalle-v-box')"
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
                <p class="text-sm font-black" style="color:#1C0A00;" id="mdv2-cajero">—</p>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-wider mb-1" style="color:#A87D5C;">Método de Pago</p>
                <p class="text-sm font-black" style="color:#1C0A00;" id="mdv2-metodo">—</p>
            </div>
        </div>

        <!-- Loading -->
        <div id="mdv2-loading" class="px-6 py-8 text-center">
            <div class="inline-flex items-center gap-2" style="color:#A87D5C;">
                <i class="fas fa-spinner fa-spin"></i>
                <span class="text-sm font-semibold">Cargando detalle...</span>
            </div>
        </div>

        <!-- Productos -->
        <div id="mdv2-productos" class="px-6 py-4 hidden">
            <p class="text-xs font-black uppercase tracking-wider mb-3" style="color:#A87D5C;">Productos</p>
            <div id="mdv2-lista" class="flex flex-col gap-0 max-h-52 overflow-y-auto"></div>
            <div class="flex items-center justify-between pt-4 mt-2 border-t" style="border-color:#F3D5B5;">
                <span class="font-black text-base" style="color:#6B4F3A;">TOTAL</span>
                <span class="font-black text-2xl" style="color:#F97316;" id="mdv2-total">$0</span>
            </div>
        </div>

        <!-- Botones -->
        <div class="px-6 pb-5 flex gap-3">
            <button onclick="cerrarModal('modal-detalle-v','modal-detalle-v-box')"
                    class="flex-1 py-2.5 rounded-xl text-sm font-black transition-all"
                    style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#6B4F3A;"
                    onmouseover="this.style.background='#F3D5B5';" onmouseout="this.style.background='#FFF7ED';">
                <i class="fas fa-times mr-1"></i> Cerrar
            </button>
            <button id="mdv2-btn-anular" onclick="anularVentaModal2()"
                    class="flex-1 py-2.5 rounded-xl text-sm font-black transition-all hidden"
                    style="background:#FEE2E2;border:1.5px solid #FCA5A5;color:#DC2626;"
                    onmouseover="this.style.background='#FECACA';" onmouseout="this.style.background='#FEE2E2';">
                <i class="fas fa-ban mr-1"></i> Anular Venta
            </button>
        </div>
    </div>
</div>

<script>
var _ventaIdModal2 = null;

function verDetalleVentaModal(id) {
    _ventaIdModal2 = id;
    abrirModal('modal-detalle-v', 'modal-detalle-v-box');

    // Reset
    document.getElementById('mdv2-numero').textContent = '#' + String(id).padStart(4,'0');
    document.getElementById('mdv2-fecha').textContent  = '—';
    document.getElementById('mdv2-cajero').textContent = '—';
    document.getElementById('mdv2-metodo').textContent = '—';
    document.getElementById('mdv2-total').textContent  = '$0';
    document.getElementById('mdv2-lista').innerHTML    = '';
    document.getElementById('mdv2-loading').classList.remove('hidden');
    document.getElementById('mdv2-productos').classList.add('hidden');
    document.getElementById('mdv2-btn-anular').classList.add('hidden');

    fetch('/PanApp/controllers/VentaDetalleController.php?id=' + id)
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.error) { alert(data.error); return; }
        var v = data.venta, d = data.detalle;

        document.getElementById('mdv2-numero').textContent = '#' + String(v.id_venta).padStart(4,'0');
        document.getElementById('mdv2-fecha').textContent  = mdv2Fecha(v.fecha);
        document.getElementById('mdv2-cajero').textContent = v.nombres + ' ' + v.apellidos;
        document.getElementById('mdv2-metodo').textContent = v.metodo_pago || '—';

        var badge = document.getElementById('mdv2-estado-badge');
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

        var lista = '';
        d.forEach(function(item) {
            lista += '<div class="flex items-center justify-between py-2.5 border-b" style="border-color:#F3D5B5;">' +
                '<div><p class="text-sm font-black" style="color:#1C0A00;">' + item.nombre + '</p>' +
                '<p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;">' + item.cantidad + ' x $' + mdv2Fmt(item.precio_unitario) + '</p></div>' +
                '<p class="font-black text-sm" style="color:#F97316;">$' + mdv2Fmt(item.subtotal) + '</p></div>';
        });
        document.getElementById('mdv2-lista').innerHTML = lista;
        document.getElementById('mdv2-total').textContent = '$' + mdv2Fmt(v.total);

        if (v.estado !== 'anulada') {
            document.getElementById('mdv2-btn-anular').classList.remove('hidden');
        }

        document.getElementById('mdv2-loading').classList.add('hidden');
        document.getElementById('mdv2-productos').classList.remove('hidden');
    })
    .catch(function() {
        document.getElementById('mdv2-loading').innerHTML = '<p class="text-sm font-bold" style="color:#DC2626;">⚠️ Error al cargar el detalle.</p>';
    });
}

function anularVentaModal2() {
    if (!_ventaIdModal2) return;
    Swal.fire({
        icon: 'warning',
        title: '¿Anular venta?',
        html: 'La venta <strong>#' + String(_ventaIdModal2).padStart(4,'0') + '</strong> será anulada.',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#DC2626'
    }).then(function(r) {
        if (r.isConfirmed) {
            window.location.href = '/PanApp/controllers/VentaController.php?accion=anular&id=' + _ventaIdModal2;
        }
    });
}

function mdv2Fecha(str) {
    var d = new Date(str.replace(' ','T'));
    return d.toLocaleDateString('es-CO',{day:'2-digit',month:'2-digit',year:'numeric'}) + ' ' +
           d.toLocaleTimeString('es-CO',{hour:'2-digit',minute:'2-digit'});
}

function mdv2Fmt(n) { return new Intl.NumberFormat('es-CO').format(n); }
</script>
