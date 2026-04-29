<?php
session_start();

if (!isset($_SESSION['usuario']) || strtoupper($_SESSION['usuario']['rol']) !== 'ADMIN') {
    header("Location: index.php"); exit;
}

require_once __DIR__ . '/../../config/database.php';

$db = (new Database())->conectar();

// Detectar si es editar o crear
$id       = intval($_GET['id'] ?? 0);
$esEditar = $id > 0;
$ins      = ['nombre' => '', 'descripcion' => '', 'unidad_medida' => '', 'id_proveedor' => ''];

if ($esEditar) {
    $stmt = $db->prepare("SELECT * FROM insumos WHERE id_insumo = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $ins = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$ins) { header("Location: index.php"); exit; }
}

// Cargar proveedores activos
$stmtP = $db->prepare("SELECT id_proveedor, nombre FROM proveedores WHERE estado = 'activo' ORDER BY nombre ASC");
$stmtP->execute();
$proveedores = $stmtP->fetchAll(PDO::FETCH_ASSOC);

$titulo = $esEditar ? "Editar Insumo" : "Crear Insumo";
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
       onmouseover="this.style.color='#F97316';" onmouseout="this.style.color='#A87D5C';">Insumos</a>
    <span>/</span>
    <span style="color:#F97316;"><?= $esEditar ? 'Editar' : 'Crear' ?></span>
</div>

<div class="bg-white rounded-2xl border" style="border-color:#F3D5B5; max-width:580px;">

    <div class="px-6 py-5 border-b" style="border-color:#F3D5B5;">
        <h2 class="text-xl font-black" style="color:#1C0A00;">
            <?= $esEditar ? 'Editar' : 'Nuevo' ?> <span style="color:#F97316;">Insumo</span>
        </h2>
        <p class="text-sm font-semibold mt-1" style="color:#A87D5C;">
            <?= $esEditar ? 'Modifica los datos del insumo.' : 'Completa los datos para registrar un nuevo insumo.' ?>
        </p>
    </div>

    <form action="../../controllers/InsumoController.php?accion=<?= $esEditar ? 'editar&id=' . $id : 'crear' ?>"
          method="POST" class="px-6 py-6 flex flex-col gap-5">

        <!-- Nombre -->
        <div class="flex flex-col gap-1">
            <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Nombre del Insumo</label>
            <div class="relative">
                <i class="fas fa-seedling absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
                <input type="text" name="nombre" required maxlength="100"
                       value="<?= htmlspecialchars($ins['nombre']) ?>"
                       placeholder="Ej. Harina de trigo"
                       class="w-full pl-9 pr-4 py-3 rounded-xl text-sm font-semibold outline-none transition-all"
                       style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                       onfocus="this.style.borderColor='#F97316';this.style.background='#fff';this.style.boxShadow='0 0 0 3px rgba(249,115,22,0.12)';"
                       onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';this.style.boxShadow='';">
            </div>
        </div>

        <!-- Descripción -->
        <div class="flex flex-col gap-1">
            <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">
                Descripción <span class="font-semibold normal-case" style="color:#A87D5C;">(opcional)</span>
            </label>
            <textarea name="descripcion" rows="2" maxlength="150"
                      placeholder="Ej. Harina para pan y pasteles..."
                      class="w-full px-4 py-3 rounded-xl text-sm font-semibold outline-none transition-all resize-none"
                      style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                      onfocus="this.style.borderColor='#F97316';this.style.background='#fff';this.style.boxShadow='0 0 0 3px rgba(249,115,22,0.12)';"
                      onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';this.style.boxShadow='';'"><?= htmlspecialchars($ins['descripcion'] ?? '') ?></textarea>
        </div>

        <!-- Unidad de medida -->
        <div class="flex flex-col gap-1">
            <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Unidad de Medida</label>
            <div class="relative">
                <i class="fas fa-ruler absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
                <select name="unidad_medida" required
                        class="w-full pl-9 pr-4 py-3 rounded-xl text-sm font-semibold outline-none appearance-none"
                        style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                        onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                        onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';">
                    <option value="">-- Selecciona --</option>
                    <?php
                    $unidades = ['Kilo','Gramo','Libra','Litro','Mililitro','Unidad','Docena','Bolsa','Caja','Arroba'];
                    foreach ($unidades as $u):
                    ?>
                    <option value="<?= $u ?>" <?= ($ins['unidad_medida'] ?? '') === $u ? 'selected' : '' ?>>
                        <?= $u ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Proveedor -->
        <div class="flex flex-col gap-1">
            <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">
                Proveedor <span class="font-semibold normal-case" style="color:#A87D5C;">(opcional)</span>
            </label>
            <div class="relative">
                <i class="fas fa-truck absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
                <select name="id_proveedor"
                        class="w-full pl-9 pr-4 py-3 rounded-xl text-sm font-semibold outline-none appearance-none"
                        style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                        onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                        onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';">
                    <option value="">-- Sin proveedor --</option>
                    <?php foreach ($proveedores as $p): ?>
                    <option value="<?= $p['id_proveedor'] ?>"
                            <?= ($ins['id_proveedor'] ?? '') == $p['id_proveedor'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (empty($proveedores)): ?>
            <p class="text-xs font-semibold mt-1" style="color:#A87D5C;">
                ⚠️ No hay proveedores activos.
                <a href="../proveedores/crear.php" style="color:#F97316;">Crear uno aquí</a>
            </p>
            <?php endif; ?>
        </div>

        <!-- Botones -->
        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="flex-1 py-3 rounded-xl text-sm font-black text-white transition-all"
                    style="background:#F97316;box-shadow:0 4px 14px rgba(249,115,22,0.35);"
                    onmouseover="this.style.background='#EA6A0A';"
                    onmouseout="this.style.background='#F97316';">
                <i class="fas <?= $esEditar ? 'fa-save' : 'fa-plus' ?> mr-2"></i>
                <?= $esEditar ? 'Guardar Cambios' : 'Crear Insumo' ?>
            </button>
            <a href="index.php"
               class="px-6 py-3 rounded-xl text-sm font-black no-underline transition-all flex items-center"
               style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#6B4F3A;"
               onmouseover="this.style.background='#F3D5B5';"
               onmouseout="this.style.background='#FFF7ED';">
                Cancelar
            </a>
        </div>

    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
