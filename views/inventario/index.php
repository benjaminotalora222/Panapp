<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$db       = $database->conectar();

// Obtener inventario con nombre del insumo y proveedor
$stmt = $db->prepare("
    SELECT ii.id_inventario, ii.cantidad_actual, ii.fecha_actualizacion,
           i.id_insumo, i.nombre AS insumo, i.unidad_medida, i.descripcion,
           p.nombre AS proveedor
    FROM inventario_insumos ii
    LEFT JOIN insumos i ON ii.id_insumo = i.id_insumo
    LEFT JOIN proveedores p ON i.id_proveedor = p.id_proveedor
    ORDER BY i.nombre ASC
");
$stmt->execute();
$inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Conteos
$totalItems    = count($inventario);
$stockBajo     = count(array_filter($inventario, fn($i) => $i['cantidad_actual'] <= 5));
$stockSuficiente = $totalItems - $stockBajo;

$titulo = "Inventario";
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
    <span style="color:#F97316;">Inventario</span>
</div>

<!-- Tarjetas resumen -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">

    <div class="bg-white rounded-2xl p-5 border flex items-center gap-4" style="border-color:#F3D5B5;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;">📦</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Total Insumos</p>
            <p class="text-3xl font-black" style="color:#1C0A00;"><?= $totalItems ?></p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border flex items-center gap-4" style="border-color:#F3D5B5;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#D1FAE5;">✅</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Stock suficiente</p>
            <p class="text-3xl font-black" style="color:#065F46;"><?= $stockSuficiente ?></p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border flex items-center gap-4" style="border-color:#F3D5B5;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FEE2E2;">⚠️</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Stock bajo (≤5)</p>
            <p class="text-3xl font-black" style="color:#DC2626;"><?= $stockBajo ?></p>
        </div>
    </div>

</div>

<!-- Tabla -->
<div class="bg-white rounded-2xl border" style="border-color:#F3D5B5;">

    <div class="flex items-center justify-between px-6 py-5 border-b" style="border-color:#F3D5B5;">
        <h2 class="text-xl font-black" style="color:#1C0A00;">
            Control de <span style="color:#F97316;">Inventario</span>
        </h2>
        <?php if (strtoupper($usuario['rol']) === 'ADMIN'): ?>
        <button type="button" onclick="abrirModalAjuste()"
           class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-black text-white transition-all"
           style="background:#F97316;box-shadow:0 4px 12px rgba(249,115,22,0.3);"
           onmouseover="this.style.background='#EA6A0A';this.style.transform='translateY(-1px)';"
           onmouseout="this.style.background='#F97316';this.style.transform='';">
            <i class="fas fa-plus"></i> Ajustar Stock
        </button>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['alert'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: '<?= htmlspecialchars($_SESSION['alert']['icon']) ?>',
                title: '<?= htmlspecialchars($_SESSION['alert']['title']) ?>',
                text: '<?= htmlspecialchars($_SESSION['alert']['text']) ?>',
                confirmButtonColor: '#F97316'
            });
        });
    </script>
    <?php unset($_SESSION['alert']); endif; ?>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr style="background:#FFF7ED;">
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">#</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Insumo</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Proveedor</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Unidad</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Cantidad</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Estado</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Actualizado</th>
                    <?php if (strtoupper($usuario['rol']) === 'ADMIN'): ?>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider text-center" style="color:#A87D5C;">Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>

                <?php if (empty($inventario)): ?>
                <tr>
                    <td colspan="8" class="px-5 py-8 text-center font-bold" style="color:#A87D5C;">
                        📦 No hay insumos en el inventario aún.
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($inventario as $idx => $item): ?>
                <tr class="border-t transition-colors" style="border-color:#F3D5B5;"
                    onmouseover="this.style.background='#FFF7ED';"
                    onmouseout="this.style.background='';">

                    <td class="px-5 py-3 font-bold" style="color:#A87D5C;"><?= $idx + 1 ?></td>

                    <td class="px-5 py-3">
                        <p class="font-black text-sm" style="color:#1C0A00;"><?= htmlspecialchars($item['insumo']) ?></p>
                        <?php if (!empty($item['descripcion'])): ?>
                        <p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;"><?= htmlspecialchars($item['descripcion']) ?></p>
                        <?php endif; ?>
                    </td>

                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;">
                        <?= htmlspecialchars($item['proveedor'] ?? '—') ?>
                    </td>

                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;">
                        <?= htmlspecialchars($item['unidad_medida'] ?? '—') ?>
                    </td>

                    <td class="px-5 py-3 font-black text-lg" style="color:#1C0A00;">
                        <?= $item['cantidad_actual'] ?>
                    </td>

                    <td class="px-5 py-3">
                        <?php if ($item['cantidad_actual'] <= 0): ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;">
                                🔴 Sin stock
                            </span>
                        <?php elseif ($item['cantidad_actual'] <= 5): ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#FEF3C7;color:#92400E;border:1px solid #FCD34D;">
                                ⚠️ Stock bajo
                            </span>
                        <?php else: ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#D1FAE5;color:#065F46;border:1px solid #6EE7B7;">
                                ✅ OK
                            </span>
                        <?php endif; ?>
                    </td>

                    <td class="px-5 py-3 font-semibold text-xs" style="color:#A87D5C;">
                        <?= $item['fecha_actualizacion'] ? date('d/m/Y H:i', strtotime($item['fecha_actualizacion'])) : '—' ?>
                    </td>

                    <?php if (strtoupper($usuario['rol']) === 'ADMIN'): ?>
                    <td class="px-5 py-3 text-center">
                        <button type="button"
                           onclick="abrirModalAjuste(<?= $item['id_insumo'] ?>, '<?= htmlspecialchars(addslashes($item['insumo'])) ?>', <?= $item['cantidad_actual'] ?>)"
                           class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all mx-auto"
                           style="border-color:#F3D5B5;color:#6B4F3A;"
                           onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';this.style.color='#F97316';"
                           onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';this.style.color='#6B4F3A';"
                           title="Ajustar stock">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                    <?php endif; ?>

                </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<!-- ══ MODAL AJUSTAR STOCK ══ -->
<div id="modal-ajuste" class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(28,10,0,0.45);backdrop-filter:blur(4px);display:none!important;">
    <div id="modal-ajuste-box" class="bg-white rounded-2xl w-full shadow-2xl"
         style="max-width:500px;border:1px solid #F3D5B5;transform:scale(0.92) translateY(16px);opacity:0;transition:transform 0.25s cubic-bezier(0.34,1.56,0.64,1),opacity 0.2s ease;">

        <div class="flex items-center justify-between px-6 py-5 border-b" style="border-color:#F3D5B5;">
            <div>
                <h3 class="text-lg font-black" style="color:#1C0A00;">Ajustar <span style="color:#F97316;">Stock</span></h3>
                <p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;">Registra entradas o salidas de inventario</p>
            </div>
            <button onclick="cerrarModal('modal-ajuste','modal-ajuste-box')"
                    class="w-8 h-8 rounded-xl flex items-center justify-center text-sm transition-all"
                    style="background:#FFF7ED;border:1px solid #F3D5B5;color:#A87D5C;"
                    onmouseover="this.style.background='#FEE2E2';this.style.color='#DC2626';"
                    onmouseout="this.style.background='#FFF7ED';this.style.color='#A87D5C';">✕</button>
        </div>

        <form id="form-ajuste" class="px-6 py-5 flex flex-col gap-4">

            <!-- Insumo -->
            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Insumo <span style="color:#F97316;">*</span></label>
                <div class="relative">
                    <i class="fas fa-seedling absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <select name="id_insumo" id="modal-ajuste-insumo" required
                            class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none appearance-none"
                            style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                            onfocus="this.style.borderColor='#F97316';" onblur="this.style.borderColor='#F3D5B5';"
                            onchange="actualizarStockInfo(this.value)">
                        <option value="">-- Selecciona un insumo --</option>
                        <?php
                        $stmtInvModal = $db->prepare("
                            SELECT i.id_insumo, i.nombre, i.unidad_medida,
                                   COALESCE(ii.cantidad_actual, 0) as cantidad_actual
                            FROM insumos i
                            LEFT JOIN inventario_insumos ii ON i.id_insumo = ii.id_insumo
                            ORDER BY i.nombre ASC
                        ");
                        $stmtInvModal->execute();
                        $insumosModal = $stmtInvModal->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($insumosModal as $ins):
                        ?>
                        <option value="<?= $ins['id_insumo'] ?>"
                                data-stock="<?= $ins['cantidad_actual'] ?>"
                                data-unidad="<?= htmlspecialchars($ins['unidad_medida']) ?>">
                            <?= htmlspecialchars($ins['nombre']) ?> (<?= htmlspecialchars($ins['unidad_medida']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Stock actual (dinámico) -->
            <div id="stock-info" class="hidden flex items-center gap-3 px-4 py-3 rounded-xl" style="background:#FFF7ED;border:1.5px solid #F3D5B5;">
                <span class="text-xl">📦</span>
                <div>
                    <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Stock actual</p>
                    <p class="text-xl font-black" style="color:#1C0A00;">
                        <span id="stock-cantidad">0</span>
                        <span class="text-sm font-semibold" style="color:#A87D5C;" id="stock-unidad"></span>
                    </p>
                </div>
            </div>

            <!-- Tipo -->
            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Tipo de Movimiento <span style="color:#F97316;">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2 px-3 py-2.5 rounded-xl cursor-pointer border transition-all"
                           style="border-color:#F3D5B5;"
                           onmouseover="this.style.borderColor='#10B981';this.style.background='#F0FDF4';"
                           onmouseout="this.style.borderColor='#F3D5B5';this.style.background='';">
                        <input type="radio" name="tipo" value="entrada" required style="accent-color:#10B981;">
                        <div>
                            <p class="text-sm font-black" style="color:#065F46;">📥 Entrada</p>
                            <p class="text-xs font-semibold" style="color:#A87D5C;">Suma al stock</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-2 px-3 py-2.5 rounded-xl cursor-pointer border transition-all"
                           style="border-color:#F3D5B5;"
                           onmouseover="this.style.borderColor='#EF4444';this.style.background='#FEF2F2';"
                           onmouseout="this.style.borderColor='#F3D5B5';this.style.background='';">
                        <input type="radio" name="tipo" value="salida" style="accent-color:#EF4444;">
                        <div>
                            <p class="text-sm font-black" style="color:#991B1B;">📤 Salida</p>
                            <p class="text-xs font-semibold" style="color:#A87D5C;">Resta del stock</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Cantidad y Motivo -->
            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Cantidad <span style="color:#F97316;">*</span></label>
                    <div class="relative">
                        <i class="fas fa-hashtag absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                        <input type="number" name="cantidad" required min="1" placeholder="Ej. 10"
                               class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none transition-all"
                               style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                               onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                               onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';">
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Motivo <span class="font-semibold normal-case" style="color:#A87D5C;">(opcional)</span></label>
                    <input type="text" name="motivo" maxlength="255" placeholder="Ej. Compra a proveedor"
                           class="w-full px-3 py-2.5 rounded-xl text-sm font-semibold outline-none transition-all"
                           style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                           onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                           onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';">
                </div>
            </div>

            <div id="error-ajuste" class="hidden px-4 py-3 rounded-xl text-sm font-bold"
                 style="background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;"></div>

            <div class="flex gap-3 pt-1">
                <button type="submit" id="btn-ajuste"
                        class="flex-1 py-2.5 rounded-xl text-sm font-black text-white flex items-center justify-center gap-2"
                        style="background:#F97316;box-shadow:0 4px 14px rgba(249,115,22,0.3);"
                        onmouseover="this.style.background='#EA6A0A';" onmouseout="this.style.background='#F97316';">
                    <i class="fas fa-save"></i> <span id="btn-ajuste-txt">Guardar Ajuste</span>
                </button>
                <button type="button" onclick="cerrarModal('modal-ajuste','modal-ajuste-box')"
                        class="px-5 py-2.5 rounded-xl text-sm font-black transition-all"
                        style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#6B4F3A;"
                        onmouseover="this.style.background='#F3D5B5';" onmouseout="this.style.background='#FFF7ED';">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalAjuste(idInsumo, nombreInsumo, stockActual) {
    abrirModal('modal-ajuste','modal-ajuste-box');
    document.getElementById('form-ajuste').reset();
    document.getElementById('error-ajuste').classList.add('hidden');
    document.getElementById('stock-info').classList.add('hidden');
    if (idInsumo) {
        var sel = document.getElementById('modal-ajuste-insumo');
        sel.value = idInsumo;
        actualizarStockInfo(idInsumo);
    }
}

function actualizarStockInfo(idInsumo) {
    var sel = document.getElementById('modal-ajuste-insumo');
    var opt = sel.options[sel.selectedIndex];
    var info = document.getElementById('stock-info');
    if (idInsumo && opt) {
        document.getElementById('stock-cantidad').textContent = opt.dataset.stock || '0';
        document.getElementById('stock-unidad').textContent   = opt.dataset.unidad || '';
        info.classList.remove('hidden');
    } else {
        info.classList.add('hidden');
    }
}

document.getElementById('form-ajuste').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('btn-ajuste');
    var txt = document.getElementById('btn-ajuste-txt');
    var err = document.getElementById('error-ajuste');
    btn.disabled = true; txt.textContent = 'Guardando...'; btn.style.opacity = '0.75';
    fetch('/PanApp/controllers/InventarioController.php?accion=ajustar', { method:'POST', body: new FormData(this) })
    .then(function() {
        Swal.fire({ icon:'success', title:'¡Ajuste registrado!', text:'El stock fue actualizado correctamente.', confirmButtonColor:'#F97316' })
        .then(function() { window.location.reload(); });
    })
    .catch(function() { err.textContent = '⚠️ Error de conexión.'; err.classList.remove('hidden'); })
    .finally(function() { btn.disabled = false; txt.textContent = 'Guardar Ajuste'; btn.style.opacity = '1'; });
});
</script>
