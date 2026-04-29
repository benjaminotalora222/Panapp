<?php
session_start();

if (!isset($_SESSION['usuario']) || strtoupper($_SESSION['usuario']['rol']) !== 'ADMIN') {
    header("Location: index.php"); exit;
}

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$db       = $database->conectar();

// Detectar si es editar o crear
$id = intval($_GET['id'] ?? 0);
$esEditar = $id > 0;
$p = ['nombre' => '', 'telefono' => '', 'correo' => '', 'direccion' => '', 'estado' => 'activo'];

if ($esEditar) {
    $stmt = $db->prepare("SELECT * FROM proveedores WHERE id_proveedor = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $p = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$p) { header("Location: index.php"); exit; }
}

$titulo = $esEditar ? "Editar Proveedor" : "Crear Proveedor";
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="flex items-center gap-2 mb-6 text-sm font-bold" style="color:#A87D5C;">
    <a href="../dashboard/admin.php" class="no-underline" style="color:#A87D5C;"
       onmouseover="this.style.color='#F97316';" onmouseout="this.style.color='#A87D5C';">
        <i class="fas fa-home mr-1"></i> Dashboard
    </a>
    <span>/</span>
    <a href="index.php" class="no-underline" style="color:#A87D5C;"
       onmouseover="this.style.color='#F97316';" onmouseout="this.style.color='#A87D5C';">Proveedores</a>
    <span>/</span>
    <span style="color:#F97316;"><?= $esEditar ? 'Editar' : 'Crear' ?></span>
</div>

<div class="bg-white rounded-2xl border" style="border-color:#F3D5B5; max-width:560px;">
    <div class="px-6 py-5 border-b" style="border-color:#F3D5B5;">
        <h2 class="text-xl font-black" style="color:#1C0A00;">
            <?= $esEditar ? 'Editar' : 'Nuevo' ?> <span style="color:#F97316;">Proveedor</span>
        </h2>
    </div>

    <form action="../../controllers/ProveedorController.php?accion=<?= $esEditar ? 'editar&id=' . $id : 'crear' ?>" method="POST"
          class="px-6 py-6 flex flex-col gap-5">

        <?php
        $campos = [
            ['nombre', 'Nombre', 'fa-truck', 'Ej. Harinera del Valle', 'text', true],
            ['telefono', 'Teléfono', 'fa-phone', 'Ej. 3001234567', 'text', false],
            ['correo', 'Correo', 'fa-envelope', 'proveedor@ejemplo.com', 'email', false],
            ['direccion', 'Dirección', 'fa-map-marker-alt', 'Ej. Calle 10 # 5-20', 'text', false],
        ];
        foreach ($campos as [$campo, $label, $icon, $placeholder, $type, $required]):
        ?>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">
                <?= $label ?><?= !$required ? ' <span class="font-semibold normal-case" style="color:#A87D5C;">(opcional)</span>' : '' ?>
            </label>
            <div class="relative">
                <i class="fas <?= $icon ?> absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
                <input type="<?= $type ?>" name="<?= $campo ?>"
                       value="<?= htmlspecialchars($p[$campo] ?? '') ?>"
                       <?= $required ? 'required' : '' ?> placeholder="<?= $placeholder ?>"
                       class="w-full pl-9 pr-4 py-3 rounded-xl text-sm font-semibold outline-none transition-all"
                       style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                       onfocus="this.style.borderColor='#F97316';this.style.background='#fff';this.style.boxShadow='0 0 0 3px rgba(249,115,22,0.12)';"
                       onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';this.style.boxShadow='';">
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Estado -->
        <div class="flex flex-col gap-1">
            <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Estado</label>
            <div class="relative">
                <i class="fas fa-toggle-on absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
                <select name="estado" class="w-full pl-9 pr-4 py-3 rounded-xl text-sm font-semibold outline-none appearance-none"
                        style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                        onfocus="this.style.borderColor='#F97316';" onblur="this.style.borderColor='#F3D5B5';">
                    <option value="activo"   <?= ($p['estado'] ?? '') === 'activo'   ? 'selected' : '' ?>>✅ Activo</option>
                    <option value="inactivo" <?= ($p['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>❌ Inactivo</option>
                </select>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="flex-1 py-3 rounded-xl text-sm font-black text-white transition-all"
                    style="background:#F97316;box-shadow:0 4px 14px rgba(249,115,22,0.35);"
                    onmouseover="this.style.background='#EA6A0A';"
                    onmouseout="this.style.background='#F97316';">
                <i class="fas <?= $esEditar ? 'fa-save' : 'fa-plus' ?> mr-2"></i>
                <?= $esEditar ? 'Guardar Cambios' : 'Crear Proveedor' ?>
            </button>
            <a href="index.php"
               class="px-6 py-3 rounded-xl text-sm font-black no-underline transition-all flex items-center"
               style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#6B4F3A;"
               onmouseover="this.style.background='#F3D5B5';"
               onmouseout="this.style.background='#FFF7ED';">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
