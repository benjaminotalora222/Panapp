<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php"); exit;
}

require_once __DIR__ . '/../../config/database.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

$db   = (new Database())->conectar();
$stmt = $db->prepare("SELECT * FROM productos WHERE id_producto = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$p = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$p) { header("Location: index.php"); exit; }

$titulo = "Editar Producto";
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="flex items-center gap-2 mb-6 text-sm font-bold" style="color:#A87D5C;">
    <a href="../dashboard/<?= strtolower($usuario['rol']) ?>.php" class="no-underline" style="color:#A87D5C;"
       onmouseover="this.style.color='#F97316';" onmouseout="this.style.color='#A87D5C';">
        <i class="fas fa-home mr-1"></i> Dashboard
    </a>
    <span>/</span>
    <a href="index.php" class="no-underline" style="color:#A87D5C;"
       onmouseover="this.style.color='#F97316';" onmouseout="this.style.color='#A87D5C';">Productos</a>
    <span>/</span>
    <span style="color:#F97316;">Editar</span>
</div>

<div class="bg-white rounded-2xl border" style="border-color:#F3D5B5; max-width:600px;">
    <div class="px-6 py-5 border-b" style="border-color:#F3D5B5;">
        <h2 class="text-xl font-black" style="color:#1C0A00;">
            Editar <span style="color:#F97316;">Producto</span>
        </h2>
        <p class="text-sm font-semibold mt-1" style="color:#A87D5C;">Modifica los datos del producto.</p>
    </div>

    <form action="../../controllers/ProductoController.php?accion=editar&id=<?= $id ?>" method="POST" class="px-6 py-6 flex flex-col gap-5">

        <div class="flex flex-col gap-1">
            <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Nombre del Producto</label>
            <div class="relative">
                <i class="fas fa-bread-slice absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
                <input type="text" name="nombre" required maxlength="255"
                       value="<?= htmlspecialchars($p['nombre']) ?>"
                       class="w-full pl-9 pr-4 py-3 rounded-xl text-sm font-semibold outline-none transition-all"
                       style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                       onfocus="this.style.borderColor='#F97316';this.style.background='#fff';this.style.boxShadow='0 0 0 3px rgba(249,115,22,0.12)';"
                       onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';this.style.boxShadow='';">
            </div>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">
                Descripción <span class="font-semibold normal-case" style="color:#A87D5C;">(opcional)</span>
            </label>
            <textarea name="descripcion" rows="3" maxlength="255"
                      class="w-full px-4 py-3 rounded-xl text-sm font-semibold outline-none transition-all resize-none"
                      style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                      onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                      onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';"><?= htmlspecialchars($p['descripcion'] ?? '') ?></textarea>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Categoría</label>
            <div class="relative">
                <i class="fas fa-tag absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
                <select name="categoria" required
                        class="w-full pl-9 pr-4 py-3 rounded-xl text-sm font-semibold outline-none appearance-none"
                        style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                        onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                        onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';">
                    <?php
                    $cats = ['Pan'=>'🍞 Pan','Pastel'=>'🎂 Pastel','Galleta'=>'🍪 Galleta','Torta'=>'🎂 Torta','Bebida'=>'☕ Bebida','Otro'=>'📦 Otro'];
                    foreach ($cats as $val => $label):
                    ?>
                    <option value="<?= $val ?>" <?= $p['categoria'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Precio ($)</label>
                <div class="relative">
                    <i class="fas fa-dollar-sign absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
                    <input type="number" name="precio" required min="0" step="0.01"
                           value="<?= $p['precio'] ?>"
                           class="w-full pl-9 pr-4 py-3 rounded-xl text-sm font-semibold outline-none transition-all"
                           style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                           onfocus="this.style.borderColor='#F97316';this.style.background='#fff';this.style.boxShadow='0 0 0 3px rgba(249,115,22,0.12)';"
                           onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';this.style.boxShadow='';">
                </div>
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Unidad de Medida</label>
                <div class="relative">
                    <i class="fas fa-ruler absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
                    <select name="unidad_medida" required
                            class="w-full pl-9 pr-4 py-3 rounded-xl text-sm font-semibold outline-none appearance-none"
                            style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                            onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                            onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';">
                        <?php foreach (['Unidad','Docena','Libra','Kilo','Porción','Litro'] as $u): ?>
                        <option value="<?= $u ?>" <?= $p['unidad_medida'] === $u ? 'selected' : '' ?>><?= $u ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="flex-1 py-3 rounded-xl text-sm font-black text-white transition-all"
                    style="background:#F97316;box-shadow:0 4px 14px rgba(249,115,22,0.35);"
                    onmouseover="this.style.background='#EA6A0A';"
                    onmouseout="this.style.background='#F97316';">
                <i class="fas fa-save mr-2"></i> Guardar Cambios
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
