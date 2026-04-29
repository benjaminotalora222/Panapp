<?php
session_start();

if (!isset($_SESSION['usuario']) || strtoupper($_SESSION['usuario']['rol']) !== 'ADMIN') {
    header("Location: index.php"); exit;
}

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$db       = $database->conectar();

$id_insumo = intval($_GET['id'] ?? 0);
$insumo    = null;
$stockActual = 0;

if ($id_insumo > 0) {
    $stmt = $db->prepare("
        SELECT i.id_insumo, i.nombre, i.unidad_medida,
               COALESCE(ii.id_inventario, 0) as id_inventario,
               COALESCE(ii.cantidad_actual, 0) as cantidad_actual
        FROM insumos i
        LEFT JOIN inventario_insumos ii ON i.id_insumo = ii.id_insumo
        WHERE i.id_insumo = :id
    ");
    $stmt->bindParam(':id', $id_insumo, PDO::PARAM_INT);
    $stmt->execute();
    $insumo = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($insumo) $stockActual = $insumo['cantidad_actual'];
}

// Cargar todos los insumos para el select
$stmtI = $db->prepare("SELECT id_insumo, nombre, unidad_medida FROM insumos ORDER BY nombre");
$stmtI->execute();
$insumos = $stmtI->fetchAll(PDO::FETCH_ASSOC);

$titulo = "Ajustar Stock";
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 mb-6 text-sm font-bold" style="color:#A87D5C;">
    <a href="../dashboard/admin.php" class="no-underline" style="color:#A87D5C;"
       onmouseover="this.style.color='#F97316';" onmouseout="this.style.color='#A87D5C';">
        <i class="fas fa-home mr-1"></i> Dashboard
    </a>
    <span>/</span>
    <a href="index.php" class="no-underline" style="color:#A87D5C;"
       onmouseover="this.style.color='#F97316';" onmouseout="this.style.color='#A87D5C';">Inventario</a>
    <span>/</span>
    <span style="color:#F97316;">Ajustar Stock</span>
</div>

<div class="bg-white rounded-2xl border" style="border-color:#F3D5B5; max-width:560px;">

    <div class="px-6 py-5 border-b" style="border-color:#F3D5B5;">
        <h2 class="text-xl font-black" style="color:#1C0A00;">
            Ajustar <span style="color:#F97316;">Stock</span>
        </h2>
        <p class="text-sm font-semibold mt-1" style="color:#A87D5C;">
            Registra entradas o salidas de insumos del inventario.
        </p>
    </div>

    <form action="../../controllers/InventarioController.php?accion=ajustar" method="POST" class="px-6 py-6 flex flex-col gap-5">

        <!-- Insumo -->
        <div class="flex flex-col gap-1">
            <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Insumo</label>
            <div class="relative">
                <i class="fas fa-seedling absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
                <select name="id_insumo" id="selectInsumo" required
                        class="w-full pl-9 pr-4 py-3 rounded-xl text-sm font-semibold outline-none appearance-none"
                        style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                        onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                        onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';"
                        onchange="this.form.submit()">
                    <option value="">-- Selecciona un insumo --</option>
                    <?php foreach ($insumos as $ins): ?>
                    <option value="<?= $ins['id_insumo'] ?>"
                            <?= ($insumo && $insumo['id_insumo'] == $ins['id_insumo']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ins['nombre']) ?> (<?= htmlspecialchars($ins['unidad_medida']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if ($insumo): ?>

        <!-- Stock actual -->
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl" style="background:#FFF7ED;border:1.5px solid #F3D5B5;">
            <span class="text-2xl">📦</span>
            <div>
                <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Stock actual</p>
                <p class="text-2xl font-black" style="color:#1C0A00;">
                    <?= $stockActual ?> <span class="text-sm font-semibold" style="color:#A87D5C;"><?= htmlspecialchars($insumo['unidad_medida']) ?></span>
                </p>
            </div>
        </div>

        <!-- Tipo de movimiento -->
        <div class="flex flex-col gap-1">
            <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Tipo de Movimiento</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-3 px-4 py-3 rounded-xl cursor-pointer border transition-all"
                       style="border-color:#F3D5B5;"
                       onmouseover="this.style.borderColor='#F97316';this.style.background='#FFF7ED';"
                       onmouseout="this.style.borderColor='#F3D5B5';this.style.background='';">
                    <input type="radio" name="tipo" value="entrada" required
                           class="accent-orange-500" style="accent-color:#F97316;">
                    <div>
                        <p class="text-sm font-black" style="color:#065F46;">📥 Entrada</p>
                        <p class="text-xs font-semibold" style="color:#A87D5C;">Suma al stock</p>
                    </div>
                </label>
                <label class="flex items-center gap-3 px-4 py-3 rounded-xl cursor-pointer border transition-all"
                       style="border-color:#F3D5B5;"
                       onmouseover="this.style.borderColor='#F97316';this.style.background='#FFF7ED';"
                       onmouseout="this.style.borderColor='#F3D5B5';this.style.background='';">
                    <input type="radio" name="tipo" value="salida" required
                           style="accent-color:#F97316;">
                    <div>
                        <p class="text-sm font-black" style="color:#991B1B;">📤 Salida</p>
                        <p class="text-xs font-semibold" style="color:#A87D5C;">Resta del stock</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Cantidad -->
        <div class="flex flex-col gap-1">
            <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Cantidad</label>
            <div class="relative">
                <i class="fas fa-hashtag absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
                <input type="number" name="cantidad" required min="1" placeholder="Ej. 10"
                       class="w-full pl-9 pr-4 py-3 rounded-xl text-sm font-semibold outline-none transition-all"
                       style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                       onfocus="this.style.borderColor='#F97316';this.style.background='#fff';this.style.boxShadow='0 0 0 3px rgba(249,115,22,0.12)';"
                       onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';this.style.boxShadow='';">
            </div>
        </div>

        <!-- Motivo -->
        <div class="flex flex-col gap-1">
            <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">
                Motivo <span class="font-semibold normal-case" style="color:#A87D5C;">(opcional)</span>
            </label>
            <div class="relative">
                <i class="fas fa-comment absolute left-3 top-3.5 text-sm" style="color:#A87D5C;"></i>
                <textarea name="motivo" rows="2" maxlength="255" placeholder="Ej. Compra a proveedor, uso en producción..."
                          class="w-full pl-9 pr-4 py-3 rounded-xl text-sm font-semibold outline-none transition-all resize-none"
                          style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                          onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                          onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';"></textarea>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="flex-1 py-3 rounded-xl text-sm font-black text-white transition-all"
                    style="background:#F97316;box-shadow:0 4px 14px rgba(249,115,22,0.35);"
                    onmouseover="this.style.background='#EA6A0A';"
                    onmouseout="this.style.background='#F97316';">
                <i class="fas fa-save mr-2"></i> Guardar Ajuste
            </button>
            <a href="index.php"
               class="px-6 py-3 rounded-xl text-sm font-black no-underline transition-all flex items-center"
               style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#6B4F3A;"
               onmouseover="this.style.background='#F3D5B5';"
               onmouseout="this.style.background='#FFF7ED';">
                Cancelar
            </a>
        </div>

        <?php else: ?>
        <p class="text-sm font-bold text-center py-4" style="color:#A87D5C;">
            👆 Selecciona un insumo para ajustar su stock.
        </p>
        <?php endif; ?>

    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
