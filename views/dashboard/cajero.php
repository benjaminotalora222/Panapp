<?php
session_start();

if (!isset($_SESSION['usuario']) || strtoupper($_SESSION['usuario']['rol']) !== 'CAJERO') {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$db       = $database->conectar();

// Ventas de hoy — todas
$stmtHoy = $db->prepare("SELECT COUNT(*) as total, COALESCE(SUM(total),0) as ingresos
                          FROM ventas
                          WHERE DATE(fecha) = CURDATE()
                          AND estado = 'completada'");
$stmtHoy->execute();
$hoy = $stmtHoy->fetch(PDO::FETCH_ASSOC);

// Ventas de esta semana — todas
$stmtSemana = $db->prepare("SELECT COUNT(*) as total, COALESCE(SUM(total),0) as ingresos
                             FROM ventas
                             WHERE DATE(fecha) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                             AND estado = 'completada'");
$stmtSemana->execute();
$semana = $stmtSemana->fetch(PDO::FETCH_ASSOC);

// Total ventas — todas
$stmtTotal = $db->prepare("SELECT COUNT(*) as total FROM ventas WHERE estado = 'completada'");
$stmtTotal->execute();
$totalVentas = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

// Total productos disponibles
$stmtProd = $db->prepare("SELECT COUNT(*) as total FROM productos");
$stmtProd->execute();
$productos = $stmtProd->fetch(PDO::FETCH_ASSOC);

// Últimas 5 ventas — todas
$stmtVentas = $db->prepare("SELECT v.id_venta, v.fecha, v.total, v.estado, mp.nombre as metodo_pago
                             FROM ventas v
                             LEFT JOIN metodos_pago mp ON v.id_metodo_pago = mp.id_metodo_pago
                             ORDER BY v.fecha DESC LIMIT 5");
$stmtVentas->execute();
$ultimasVentas = $stmtVentas->fetchAll(PDO::FETCH_ASSOC);

$titulo = "Dashboard";
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- BIENVENIDA -->
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
            Panel de cajero · PanApp
        </p>
    </div>
    <div class="flex items-center gap-3">
        <button type="button" onclick="abrirModalVentaDash()"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-black no-underline transition-all"
                style="background:rgba(255,255,255,0.2);color:#fff;border:1.5px solid rgba(255,255,255,0.3);"
                onmouseover="this.style.background='rgba(255,255,255,0.3)';"
                onmouseout="this.style.background='rgba(255,255,255,0.2)';">
            <i class="fas fa-plus"></i> Nueva Venta
        </button>
        <div class="text-5xl">🛒</div>
    </div>
</div>

<!-- KPIs -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded-2xl p-5 border relative overflow-hidden" style="border-color:#F3D5B5;">
        <div class="absolute top-0 right-0 w-16 h-16 rounded-full opacity-5" style="background:#F97316;transform:translate(30%,-30%);"></div>
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0" style="background:#FFF7ED;">🛒</div>
            <span class="text-xs font-black px-2 py-0.5 rounded-full" style="background:#D1FAE5;color:#065F46;">Hoy</span>
        </div>
        <p class="text-3xl font-black mb-0.5" style="color:#1C0A00;"><?= $hoy['total'] ?></p>
        <p class="text-xs font-bold" style="color:#A87D5C;">Ventas del día</p>
    </div>

    <div class="bg-white rounded-2xl p-5 border relative overflow-hidden" style="border-color:#F3D5B5;">
        <div class="absolute top-0 right-0 w-16 h-16 rounded-full opacity-5" style="background:#F97316;transform:translate(30%,-30%);"></div>
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0" style="background:#FFF7ED;">💰</div>
            <span class="text-xs font-black px-2 py-0.5 rounded-full" style="background:#FEF3C7;color:#92400E;">Hoy</span>
        </div>
        <p class="text-2xl font-black mb-0.5" style="color:#F97316;">$<?= number_format($hoy['ingresos'], 0, ',', '.') ?></p>
        <p class="text-xs font-bold" style="color:#A87D5C;">Ingresos del día</p>
    </div>

    <div class="bg-white rounded-2xl p-5 border relative overflow-hidden" style="border-color:#F3D5B5;">
        <div class="absolute top-0 right-0 w-16 h-16 rounded-full opacity-5" style="background:#F97316;transform:translate(30%,-30%);"></div>
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0" style="background:#EFF6FF;">📅</div>
            <span class="text-xs font-black px-2 py-0.5 rounded-full" style="background:#DBEAFE;color:#1E40AF;">Semana</span>
        </div>
        <p class="text-3xl font-black mb-0.5" style="color:#1C0A00;"><?= $semana['total'] ?></p>
        <p class="text-xs font-bold" style="color:#A87D5C;">Ventas esta semana</p>
    </div>

    <div class="bg-white rounded-2xl p-5 border relative overflow-hidden" style="border-color:#F3D5B5;">
        <div class="absolute top-0 right-0 w-16 h-16 rounded-full opacity-5" style="background:#F97316;transform:translate(30%,-30%);"></div>
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0" style="background:#FFF7ED;">🍞</div>
            <span class="text-xs font-black px-2 py-0.5 rounded-full" style="background:#EDE9FE;color:#5B21B6;">Total</span>
        </div>
        <p class="text-3xl font-black mb-0.5" style="color:#1C0A00;"><?= $productos['total'] ?></p>
        <p class="text-xs font-bold" style="color:#A87D5C;">Productos disponibles</p>
    </div>

</div>

<!-- FILA: Acciones rápidas + Resumen semana -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

    <!-- Acciones rápidas (2/3) -->
    <div class="lg:col-span-2 bg-white rounded-2xl border" style="border-color:#F3D5B5;">
        <div class="px-6 py-4 border-b" style="border-color:#F3D5B5;">
            <h3 class="text-base font-black" style="color:#1C0A00;">Acciones <span style="color:#F97316;">Rápidas</span></h3>
            <p class="text-xs font-semibold" style="color:#A87D5C;">Accede a las funciones principales</p>
        </div>
        <div class="p-5 grid grid-cols-2 gap-3">
            <?php
            $acciones = [
                ['#', 'onclick="abrirModalVentaDash()"', '🛒', 'Nueva Venta', 'Registrar cobro', '#FFF7ED', '#F97316'],
                ['/PanApp/views/ventas/index.php', '', '🧾', 'Ver Ventas', 'Historial completo', '#FFF7ED', '#F97316'],
                ['/PanApp/views/productos/index.php', '', '🍞', 'Productos', 'Catálogo y precios', '#FFF7ED', '#F97316'],
                ['/PanApp/views/reportes/index.php', '', '📊', 'Reportes', 'Ventas y productos', '#FEF3C7', '#D97706'],
            ];
            foreach ($acciones as [$url, $extra, $emoji, $nombre, $desc, $bg, $color]):
                if ($url === '#'):
            ?>
            <button type="button" <?= $extra ?>
               class="flex flex-col gap-2 p-4 rounded-xl border text-left transition-all"
               style="border-color:#F3D5B5;"
               onmouseover="this.style.borderColor='<?= $color ?>';this.style.background='<?= $bg ?>';this.style.transform='translateY(-2px)';"
               onmouseout="this.style.borderColor='#F3D5B5';this.style.background='';this.style.transform='';">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" style="background:<?= $bg ?>;"><?= $emoji ?></div>
                <div>
                    <p class="font-black text-sm" style="color:#1C0A00;"><?= $nombre ?></p>
                    <p class="text-xs font-semibold" style="color:#A87D5C;"><?= $desc ?></p>
                </div>
            </button>
            <?php else: ?>
            <a href="<?= $url ?>" <?= $extra ?>
               class="flex flex-col gap-2 p-4 rounded-xl border no-underline transition-all"
               style="border-color:#F3D5B5;"
               onmouseover="this.style.borderColor='<?= $color ?>';this.style.background='<?= $bg ?>';this.style.transform='translateY(-2px)';"
               onmouseout="this.style.borderColor='#F3D5B5';this.style.background='';this.style.transform='';">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" style="background:<?= $bg ?>;"><?= $emoji ?></div>
                <div>
                    <p class="font-black text-sm" style="color:#1C0A00;"><?= $nombre ?></p>
                    <p class="text-xs font-semibold" style="color:#A87D5C;"><?= $desc ?></p>
                </div>
            </a>
            <?php endif; endforeach; ?>
        </div>
    </div>

    <!-- Resumen semana (1/3) -->
    <div class="bg-white rounded-2xl border" style="border-color:#F3D5B5;">
        <div class="px-5 py-4 border-b" style="border-color:#F3D5B5;">
            <h3 class="text-base font-black" style="color:#1C0A00;">Mi <span style="color:#F97316;">Semana</span></h3>
            <p class="text-xs font-semibold" style="color:#A87D5C;">Últimos 7 días</p>
        </div>
        <div class="p-5 flex flex-col gap-4">
            <div class="flex items-center gap-3 p-3 rounded-xl" style="background:#FFF7ED;border:1px solid #F3D5B5;">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-base flex-shrink-0" style="background:#FEF3C7;">🧾</div>
                <div class="flex-1">
                    <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Ventas</p>
                    <p class="text-xl font-black" style="color:#1C0A00;"><?= $semana['total'] ?></p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 rounded-xl" style="background:#FFF7ED;border:1px solid #F3D5B5;">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-base flex-shrink-0" style="background:#D1FAE5;">💵</div>
                <div class="flex-1">
                    <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Ingresos</p>
                    <p class="text-xl font-black" style="color:#059669;">$<?= number_format($semana['ingresos'], 0, ',', '.') ?></p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 rounded-xl" style="background:#FFF7ED;border:1px solid #F3D5B5;">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-base flex-shrink-0" style="background:#EDE9FE;">📈</div>
                <div class="flex-1">
                    <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Total histórico</p>
                    <p class="text-xl font-black" style="color:#5B21B6;"><?= $totalVentas ?></p>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ÚLTIMAS VENTAS -->
<div class="bg-white rounded-2xl border" style="border-color:#F3D5B5;">

    <div class="flex items-center justify-between px-6 py-4 border-b" style="border-color:#F3D5B5;">
        <div>
            <h3 class="text-base font-black" style="color:#1C0A00;">Actividad <span style="color:#F97316;">Reciente</span></h3>
            <p class="text-xs font-semibold" style="color:#A87D5C;">Tus últimas 5 ventas registradas</p>
        </div>
        <a href="/PanApp/views/ventas/index.php"
           class="text-xs font-black px-3 py-1.5 rounded-lg no-underline transition-all"
           style="background:#FFF7ED;border:1px solid #F3D5B5;color:#EA6A0A;"
           onmouseover="this.style.background='#FED7AA';"
           onmouseout="this.style.background='#FFF7ED';">
            Ver todas <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>

    <?php if (empty($ultimasVentas)): ?>
    <div class="px-6 py-12 text-center">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-3" style="background:#FFF7ED;">🍞</div>
        <p class="font-black text-sm mb-1" style="color:#1C0A00;">Aún no has registrado ventas</p>
        <p class="text-xs font-semibold mb-4" style="color:#A87D5C;">Registra tu primera venta para verla aquí</p>
        <button type="button" onclick="abrirModalVentaDash()"
                class="text-xs font-black px-4 py-2 rounded-xl text-white"
                style="background:#F97316;">
            <i class="fas fa-plus mr-1"></i> Registrar primera venta
        </button>
    </div>
    <?php else: ?>
    <div class="divide-y" style="--tw-divide-color:#F3D5B5;">
        <?php foreach ($ultimasVentas as $v): ?>
        <div class="flex items-center gap-4 px-6 py-3.5 transition-colors"
             onmouseover="this.style.background='#FFFBF7';"
             onmouseout="this.style.background='';">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm flex-shrink-0"
                 style="background:<?= $v['estado']==='completada' ? '#D1FAE5' : ($v['estado']==='anulada' ? '#FEE2E2' : '#FEF3C7') ?>;">
                <?= $v['estado']==='completada' ? '✅' : ($v['estado']==='anulada' ? '❌' : '⏳') ?>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="font-black text-sm" style="color:#F97316;">#<?= str_pad($v['id_venta'],4,'0',STR_PAD_LEFT) ?></span>
                    <span class="text-xs font-semibold" style="color:#A87D5C;">·</span>
                    <span class="text-xs font-semibold" style="color:#A87D5C;"><?= htmlspecialchars($v['metodo_pago'] ?? '—') ?></span>
                </div>
                <p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;">
                    <?= date('d/m/Y H:i', strtotime($v['fecha'])) ?>
                </p>
            </div>
            <span class="font-black text-sm flex-shrink-0" style="color:#1C0A00;">
                $<?= number_format($v['total'],0,',','.') ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<!-- ══ MODAL NUEVA VENTA ══ -->
<?php
$stmtProdDash = $db->prepare("SELECT id_producto, nombre, precio, categoria, unidad_medida FROM productos ORDER BY categoria, nombre");
$stmtProdDash->execute();
$productosDash = $stmtProdDash->fetchAll(PDO::FETCH_ASSOC);

$stmtPagoDash = $db->prepare("SELECT id_metodo_pago, nombre FROM metodos_pago ORDER BY nombre");
$stmtPagoDash->execute();
$metodosPagoDash = $stmtPagoDash->fetchAll(PDO::FETCH_ASSOC);
?>

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
                <div><h3 class="text-base font-black text-white">Nueva Venta</h3><p class="text-xs font-semibold" style="color:rgba(255,255,255,0.8);">Selecciona productos y registra el cobro</p></div>
            </div>
            <button onclick="cerrarModalVentaDash()" class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-black text-white" style="background:rgba(255,255,255,0.15);" onmouseover="this.style.background='rgba(255,255,255,0.25)';" onmouseout="this.style.background='rgba(255,255,255,0.15)';">✕</button>
        </div>
        <div class="flex flex-1 overflow-hidden" style="min-height:0;">
            <div class="flex-1 flex flex-col border-r overflow-hidden" style="border-color:#F3D5B5;">
                <div class="px-5 py-3 border-b flex-shrink-0" style="border-color:#F3D5B5;background:#FDFAF7;">
                    <div class="relative"><i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <input type="text" id="mvd-buscador" placeholder="Buscar producto..." class="w-full pl-8 pr-3 py-2 rounded-xl text-sm font-semibold outline-none" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;" onfocus="this.style.borderColor='#F97316';this.style.background='#fff';" onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';" oninput="mvdFiltrar(this.value)"></div>
                </div>
                <div id="mvd-prods-scroll" class="p-4 flex-1">
                    <?php if (empty($productosDash)): ?>
                    <p class="text-center text-sm font-bold py-8" style="color:#A87D5C;">🍞 No hay productos registrados.</p>
                    <?php else: ?>
                    <div class="mvd-grid">
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
                    <p class="text-xs font-bold text-center py-6" style="color:#A87D5C;">Selecciona productos del catálogo</p>
                </div>
                <div class="px-4 py-4 border-t flex-shrink-0" style="border-color:#F3D5B5;background:#FDFAF7;">
                    <div class="flex items-center justify-between mb-3 pb-3 border-b" style="border-color:#F3D5B5;">
                        <span class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Total</span>
                        <span class="text-xl font-black" style="color:#F97316;" id="mvd-total">$0</span>
                    </div>
                    <div class="mb-3">
                        <label class="text-xs font-black uppercase tracking-wider block mb-1.5" style="color:#6B4F3A;">Método de Pago</label>
                        <div class="relative"><i class="fas fa-credit-card absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                        <select id="mvd-metodo" class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none appearance-none" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;" onfocus="this.style.borderColor='#F97316';" onblur="this.style.borderColor='#F3D5B5';">
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($metodosPagoDash as $mp): ?>
                            <option value="<?= $mp['id_metodo_pago'] ?>"><?= htmlspecialchars($mp['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    </div>
                    <button onclick="mvdConfirmar()" class="w-full py-2.5 rounded-xl text-sm font-black text-white flex items-center justify-center gap-2" style="background:#F97316;box-shadow:0 4px 14px rgba(249,115,22,0.35);" onmouseover="this.style.background='#EA6A0A';" onmouseout="this.style.background='#F97316';"><i class="fas fa-check"></i> Registrar Venta</button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="mvd-form" action="/PanApp/controllers/VentaController.php" method="POST" style="display:none;">
    <input type="hidden" name="id_metodo_pago" id="mvd-input-metodo">
    <input type="hidden" name="total" id="mvd-input-total">
    <input type="hidden" name="items" id="mvd-input-items">
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
