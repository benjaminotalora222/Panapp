<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php"); exit;
}

require_once __DIR__ . '/../../config/database.php';

$db = (new Database())->conectar();

$stmt = $db->prepare("
    SELECT i.*, p.nombre AS proveedor_nombre
    FROM insumos i
    LEFT JOIN proveedores p ON i.id_proveedor = p.id_proveedor
    ORDER BY i.nombre ASC
");
$stmt->execute();
$insumos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo = "Insumos";
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 mb-6 text-sm font-bold" style="color:#A87D5C;">
    <a href="../dashboard/<?= strtolower($usuario['rol']) ?>.php" class="no-underline" style="color:#A87D5C;"
       onmouseover="this.style.color='#F97316';" onmouseout="this.style.color='#A87D5C';">
        <i class="fas fa-home mr-1"></i> Dashboard
    </a>
    <span>/</span>
    <span style="color:#F97316;">Insumos</span>
</div>

<div class="bg-white rounded-2xl border" style="border-color:#F3D5B5;">

    <div class="flex items-center justify-between px-6 py-5 border-b" style="border-color:#F3D5B5;">
        <h2 class="text-xl font-black" style="color:#1C0A00;">
            Gestión de <span style="color:#F97316;">Insumos</span>
        </h2>
        <?php if (strtoupper($usuario['rol']) === 'ADMIN'): ?>
        <button type="button" onclick="abrirModalInsumo()"
           class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-black text-white transition-all"
           style="background:#F97316;box-shadow:0 4px 12px rgba(249,115,22,0.3);"
           onmouseover="this.style.background='#EA6A0A';this.style.transform='translateY(-1px)';"
           onmouseout="this.style.background='#F97316';this.style.transform='';">
            <i class="fas fa-plus"></i> Agregar Insumo
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
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Nombre</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Descripción</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Unidad de Medida</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Proveedor</th>
                    <?php if (strtoupper($usuario['rol']) === 'ADMIN'): ?>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider text-center" style="color:#A87D5C;">Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($insumos)): ?>
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center font-bold" style="color:#A87D5C;">
                        🌾 No hay insumos registrados aún.
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($insumos as $i => $ins): ?>
                <tr class="border-t transition-colors" style="border-color:#F3D5B5;"
                    onmouseover="this.style.background='#FFF7ED';"
                    onmouseout="this.style.background='';">

                    <td class="px-5 py-3 font-bold" style="color:#A87D5C;"><?= $i + 1 ?></td>

                    <td class="px-5 py-3 font-black" style="color:#1C0A00;">
                        <?= htmlspecialchars($ins['nombre']) ?>
                    </td>

                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;">
                        <?= htmlspecialchars($ins['descripcion'] ?? '—') ?>
                    </td>

                    <td class="px-5 py-3">
                        <span class="text-xs font-black px-2.5 py-1 rounded-full"
                              style="background:#FFF7ED;border:1px solid #F3D5B5;color:#EA6A0A;">
                            <?= htmlspecialchars($ins['unidad_medida'] ?? '—') ?>
                        </span>
                    </td>

                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;">
                        <?php if (!empty($ins['proveedor_nombre'])): ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#D1FAE5;color:#065F46;border:1px solid #6EE7B7;">
                                🚚 <?= htmlspecialchars($ins['proveedor_nombre']) ?>
                            </span>
                        <?php else: ?>
                            <span style="color:#A87D5C;">Sin proveedor</span>
                        <?php endif; ?>
                    </td>

                    <?php if (strtoupper($usuario['rol']) === 'ADMIN'): ?>
                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="editar.php?id=<?= $ins['id_insumo'] ?>"
                               class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all"
                               style="border-color:#F3D5B5;color:#6B4F3A;"
                               onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';this.style.color='#F97316';"
                               onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';this.style.color='#6B4F3A';"
                               title="Editar">
                                <i class="fas fa-pen"></i>
                            </a>
                            <a href="../../controllers/InsumoController.php?accion=eliminar&id=<?= $ins['id_insumo'] ?>"
                               class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all"
                               style="border-color:#FCA5A5;color:#DC2626;"
                               onmouseover="this.style.background='#FEE2E2';"
                               onmouseout="this.style.background='';"
                               title="Eliminar"
                               onclick="return confirm('¿Eliminar este insumo?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                    <?php endif; ?>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<!-- ══ MODAL NUEVO INSUMO ══ -->
<div id="modal-insumo" class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(28,10,0,0.45);backdrop-filter:blur(4px);display:none!important;">
    <div id="modal-insumo-box" class="bg-white rounded-2xl w-full shadow-2xl"
         style="max-width:500px;border:1px solid #F3D5B5;transform:scale(0.92) translateY(16px);opacity:0;transition:transform 0.25s cubic-bezier(0.34,1.56,0.64,1),opacity 0.2s ease;">

        <div class="flex items-center justify-between px-6 py-5 border-b" style="border-color:#F3D5B5;">
            <div>
                <h3 class="text-lg font-black" style="color:#1C0A00;">Nuevo <span style="color:#F97316;">Insumo</span></h3>
                <p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;">Registra un nuevo insumo</p>
            </div>
            <button onclick="cerrarModal('modal-insumo','modal-insumo-box')"
                    class="w-8 h-8 rounded-xl flex items-center justify-center text-sm transition-all"
                    style="background:#FFF7ED;border:1px solid #F3D5B5;color:#A87D5C;"
                    onmouseover="this.style.background='#FEE2E2';this.style.color='#DC2626';"
                    onmouseout="this.style.background='#FFF7ED';this.style.color='#A87D5C';">✕</button>
        </div>

        <form id="form-insumo" class="px-6 py-5 flex flex-col gap-4">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Nombre <span style="color:#F97316;">*</span></label>
                <div class="relative">
                    <i class="fas fa-seedling absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <input type="text" name="nombre" required maxlength="100" placeholder="Ej. Harina de trigo"
                           class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none transition-all"
                           style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                           onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                           onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';">
                </div>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Descripción <span class="font-semibold normal-case" style="color:#A87D5C;">(opcional)</span></label>
                <textarea name="descripcion" rows="2" maxlength="150" placeholder="Descripción breve..."
                          class="w-full px-3 py-2.5 rounded-xl text-sm font-semibold outline-none transition-all resize-none"
                          style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                          onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                          onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Unidad <span style="color:#F97316;">*</span></label>
                    <div class="relative">
                        <i class="fas fa-ruler absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                        <select name="unidad_medida" required class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none appearance-none"
                                style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                                onfocus="this.style.borderColor='#F97316';" onblur="this.style.borderColor='#F3D5B5';">
                            <option value="">-- Selecciona --</option>
                            <?php foreach (['Kilo','Gramo','Libra','Litro','Mililitro','Unidad','Docena','Bolsa','Caja','Arroba'] as $u): ?>
                            <option value="<?= $u ?>"><?= $u ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Proveedor <span class="font-semibold normal-case" style="color:#A87D5C;">(opcional)</span></label>
                    <div class="relative">
                        <i class="fas fa-truck absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                        <select name="id_proveedor" class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none appearance-none"
                                style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                                onfocus="this.style.borderColor='#F97316';" onblur="this.style.borderColor='#F3D5B5';">
                            <option value="">-- Sin proveedor --</option>
                            <?php foreach ($insumos as $ins):
                                // Reutilizamos la query de proveedores activos
                            endforeach; ?>
                            <?php
                            $stmtProvModal = $db->prepare("SELECT id_proveedor, nombre FROM proveedores WHERE estado = 'activo' ORDER BY nombre ASC");
                            $stmtProvModal->execute();
                            foreach ($stmtProvModal->fetchAll(PDO::FETCH_ASSOC) as $prov):
                            ?>
                            <option value="<?= $prov['id_proveedor'] ?>"><?= htmlspecialchars($prov['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div id="error-insumo" class="hidden px-4 py-3 rounded-xl text-sm font-bold"
                 style="background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;"></div>

            <div class="flex gap-3 pt-1">
                <button type="submit" id="btn-insumo"
                        class="flex-1 py-2.5 rounded-xl text-sm font-black text-white flex items-center justify-center gap-2"
                        style="background:#F97316;box-shadow:0 4px 14px rgba(249,115,22,0.3);"
                        onmouseover="this.style.background='#EA6A0A';" onmouseout="this.style.background='#F97316';">
                    <i class="fas fa-plus"></i> <span id="btn-insumo-txt">Crear Insumo</span>
                </button>
                <button type="button" onclick="cerrarModal('modal-insumo','modal-insumo-box')"
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
function abrirModalInsumo() { abrirModal('modal-insumo','modal-insumo-box'); document.getElementById('form-insumo').reset(); document.getElementById('error-insumo').classList.add('hidden'); }

document.getElementById('form-insumo').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('btn-insumo');
    var txt = document.getElementById('btn-insumo-txt');
    var err = document.getElementById('error-insumo');
    btn.disabled = true; txt.textContent = 'Guardando...'; btn.style.opacity = '0.75';
    fetch('/PanApp/controllers/InsumoController.php?accion=crear', { method:'POST', body: new FormData(this) })
    .then(function() {
        Swal.fire({ icon:'success', title:'¡Insumo creado!', text:'El insumo fue registrado correctamente.', confirmButtonColor:'#F97316' })
        .then(function() { window.location.reload(); });
    })
    .catch(function() { err.textContent = '⚠️ Error de conexión.'; err.classList.remove('hidden'); })
    .finally(function() { btn.disabled = false; txt.textContent = 'Crear Insumo'; btn.style.opacity = '1'; });
});
</script>
