<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$db       = $database->conectar();

$stmt = $db->prepare("SELECT * FROM productos ORDER BY categoria, nombre");
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo = "Productos";
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
    <span style="color:#F97316;">Productos</span>
</div>

<div class="bg-white rounded-2xl border" style="border-color:#F3D5B5;">

    <div class="flex items-center justify-between px-6 py-5 border-b" style="border-color:#F3D5B5;">
        <h2 class="text-xl font-black" style="color:#1C0A00;">
            Gestión de <span style="color:#F97316;">Productos</span>
        </h2>
        <button type="button" onclick="abrirModalProducto()"
           class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-black text-white transition-all"
           style="background:#F97316;box-shadow:0 4px 12px rgba(249,115,22,0.3);"
           onmouseover="this.style.background='#EA6A0A';this.style.transform='translateY(-1px)';"
           onmouseout="this.style.background='#F97316';this.style.transform='';">
            <i class="fas fa-plus"></i> Agregar Producto
        </button>
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
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Nombre</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Categoría</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Precio</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Unidad</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider text-center" style="color:#A87D5C;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($productos)): ?>
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center font-bold" style="color:#A87D5C;">
                        🍞 No hay productos registrados aún.
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($productos as $i => $p): ?>
                <tr class="border-t transition-colors" style="border-color:#F3D5B5;"
                    onmouseover="this.style.background='#FFF7ED';"
                    onmouseout="this.style.background='';">

                    <td class="px-5 py-3 font-bold" style="color:#A87D5C;"><?= $i + 1 ?></td>

                    <td class="px-5 py-3">
                        <p class="font-black text-sm" style="color:#1C0A00;"><?= htmlspecialchars($p['nombre']) ?></p>
                        <?php if (!empty($p['descripcion'])): ?>
                        <p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;"><?= htmlspecialchars($p['descripcion']) ?></p>
                        <?php endif; ?>
                    </td>

                    <td class="px-5 py-3">
                        <span class="text-xs font-black px-2.5 py-1 rounded-full"
                              style="background:#FFF7ED;border:1px solid #F3D5B5;color:#EA6A0A;">
                            <?= htmlspecialchars($p['categoria'] ?? '—') ?>
                        </span>
                    </td>

                    <td class="px-5 py-3 font-black" style="color:#F97316;">
                        $<?= number_format($p['precio'], 0, ',', '.') ?>
                    </td>

                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;">
                        <?= htmlspecialchars($p['unidad_medida'] ?? '—') ?>
                    </td>

                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <!-- Editar — todos los roles -->
                            <a href="editar.php?id=<?= $p['id_producto'] ?>"
                               class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all"
                               style="border-color:#F3D5B5;color:#6B4F3A;"
                               onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';this.style.color='#F97316';"
                               onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';this.style.color='#6B4F3A';"
                               title="Editar">
                                <i class="fas fa-pen"></i>
                            </a>
                            <!-- Eliminar — solo ADMIN -->
                            <?php if (strtoupper($usuario['rol']) === 'ADMIN'): ?>
                            <a href="../../controllers/ProductoController.php?accion=eliminar&id=<?= $p['id_producto'] ?>"
                               class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all"
                               style="border-color:#FCA5A5;color:#DC2626;"
                               onmouseover="this.style.background='#FEE2E2';"
                               onmouseout="this.style.background='';"
                               title="Eliminar"
                               onclick="return confirm('¿Eliminar este producto?');">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<!-- ══ MODAL NUEVO PRODUCTO ══ -->
<div id="modal-producto" class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(28,10,0,0.45);backdrop-filter:blur(4px);display:none!important;">
    <div id="modal-producto-box" class="bg-white rounded-2xl w-full shadow-2xl"
         style="max-width:520px;border:1px solid #F3D5B5;transform:scale(0.92) translateY(16px);opacity:0;transition:transform 0.25s cubic-bezier(0.34,1.56,0.64,1),opacity 0.2s ease;">

        <div class="flex items-center justify-between px-6 py-5 border-b" style="border-color:#F3D5B5;">
            <div>
                <h3 class="text-lg font-black" style="color:#1C0A00;">Nuevo <span style="color:#F97316;">Producto</span></h3>
                <p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;">Completa los datos del producto</p>
            </div>
            <button onclick="cerrarModal('modal-producto','modal-producto-box')"
                    class="w-8 h-8 rounded-xl flex items-center justify-center text-sm transition-all"
                    style="background:#FFF7ED;border:1px solid #F3D5B5;color:#A87D5C;"
                    onmouseover="this.style.background='#FEE2E2';this.style.color='#DC2626';"
                    onmouseout="this.style.background='#FFF7ED';this.style.color='#A87D5C';">✕</button>
        </div>

        <form id="form-producto" class="px-6 py-5 flex flex-col gap-4">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Nombre <span style="color:#F97316;">*</span></label>
                <div class="relative">
                    <i class="fas fa-bread-slice absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <input type="text" name="nombre" required maxlength="255" placeholder="Ej. Pan de queso"
                           class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none transition-all"
                           style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                           onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                           onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';">
                </div>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Descripción <span class="font-semibold normal-case" style="color:#A87D5C;">(opcional)</span></label>
                <textarea name="descripcion" rows="2" maxlength="255" placeholder="Descripción breve..."
                          class="w-full px-3 py-2.5 rounded-xl text-sm font-semibold outline-none transition-all resize-none"
                          style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                          onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                          onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Categoría <span style="color:#F97316;">*</span></label>
                    <div class="relative">
                        <i class="fas fa-tag absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                        <select name="categoria" required class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none appearance-none"
                                style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                                onfocus="this.style.borderColor='#F97316';" onblur="this.style.borderColor='#F3D5B5';">
                            <option value="">-- Selecciona --</option>
                            <option value="Pan">🍞 Pan</option>
                            <option value="Pastel">🎂 Pastel</option>
                            <option value="Galleta">🍪 Galleta</option>
                            <option value="Torta">🎂 Torta</option>
                            <option value="Bebida">☕ Bebida</option>
                            <option value="Otro">📦 Otro</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Unidad <span style="color:#F97316;">*</span></label>
                    <div class="relative">
                        <i class="fas fa-ruler absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                        <select name="unidad_medida" required class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none appearance-none"
                                style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                                onfocus="this.style.borderColor='#F97316';" onblur="this.style.borderColor='#F3D5B5';">
                            <option value="">-- Selecciona --</option>
                            <option value="Unidad">Unidad</option>
                            <option value="Docena">Docena</option>
                            <option value="Libra">Libra</option>
                            <option value="Kilo">Kilo</option>
                            <option value="Porción">Porción</option>
                            <option value="Litro">Litro</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Precio ($) <span style="color:#F97316;">*</span></label>
                <div class="relative">
                    <i class="fas fa-dollar-sign absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <input type="number" name="precio" required min="0" step="0.01" placeholder="0"
                           class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none transition-all"
                           style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                           onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                           onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';">
                </div>
            </div>

            <div id="error-producto" class="hidden px-4 py-3 rounded-xl text-sm font-bold"
                 style="background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;"></div>

            <div class="flex gap-3 pt-1">
                <button type="submit" id="btn-producto"
                        class="flex-1 py-2.5 rounded-xl text-sm font-black text-white flex items-center justify-center gap-2"
                        style="background:#F97316;box-shadow:0 4px 14px rgba(249,115,22,0.3);"
                        onmouseover="this.style.background='#EA6A0A';" onmouseout="this.style.background='#F97316';">
                    <i class="fas fa-plus"></i> <span id="btn-producto-txt">Crear Producto</span>
                </button>
                <button type="button" onclick="cerrarModal('modal-producto','modal-producto-box')"
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
function abrirModalProducto() { abrirModal('modal-producto','modal-producto-box'); document.getElementById('form-producto').reset(); document.getElementById('error-producto').classList.add('hidden'); }

document.getElementById('form-producto').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('btn-producto');
    var txt = document.getElementById('btn-producto-txt');
    var err = document.getElementById('error-producto');
    btn.disabled = true; txt.textContent = 'Guardando...'; btn.style.opacity = '0.75';
    fetch('/PanApp/controllers/ProductoController.php?accion=crear', { method:'POST', body: new FormData(this) })
    .then(function() {
        Swal.fire({ icon:'success', title:'¡Producto creado!', text:'El producto fue agregado al catálogo.', confirmButtonColor:'#F97316' })
        .then(function() { window.location.reload(); });
    })
    .catch(function() { err.textContent = '⚠️ Error de conexión.'; err.classList.remove('hidden'); })
    .finally(function() { btn.disabled = false; txt.textContent = 'Crear Producto'; btn.style.opacity = '1'; });
});
</script>
