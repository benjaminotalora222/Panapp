<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$db       = $database->conectar();

$stmt = $db->prepare("SELECT * FROM proveedores ORDER BY nombre ASC");
$stmt->execute();
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo = "Proveedores";
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="flex items-center gap-2 mb-6 text-sm font-bold" style="color:#A87D5C;">
    <a href="../dashboard/<?= strtolower($usuario['rol']) ?>.php" class="no-underline" style="color:#A87D5C;"
       onmouseover="this.style.color='#F97316';" onmouseout="this.style.color='#A87D5C';">
        <i class="fas fa-home mr-1"></i> Dashboard
    </a>
    <span>/</span>
    <span style="color:#F97316;">Proveedores</span>
</div>

<div class="bg-white rounded-2xl border" style="border-color:#F3D5B5;">

    <div class="flex items-center justify-between px-6 py-5 border-b" style="border-color:#F3D5B5;">
        <h2 class="text-xl font-black" style="color:#1C0A00;">
            Gestión de <span style="color:#F97316;">Proveedores</span>
        </h2>
        <?php if (strtoupper($usuario['rol']) === 'ADMIN'): ?>
        <button type="button" onclick="abrirModalProveedor()"
           class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-black text-white transition-all"
           style="background:#F97316;box-shadow:0 4px 12px rgba(249,115,22,0.3);"
           onmouseover="this.style.background='#EA6A0A';this.style.transform='translateY(-1px)';"
           onmouseout="this.style.background='#F97316';this.style.transform='';">
            <i class="fas fa-plus"></i> Agregar Proveedor
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
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Teléfono</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Correo</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Dirección</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Estado</th>
                    <?php if (strtoupper($usuario['rol']) === 'ADMIN'): ?>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider text-center" style="color:#A87D5C;">Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($proveedores)): ?>
                <tr>
                    <td colspan="7" class="px-5 py-8 text-center font-bold" style="color:#A87D5C;">
                        🚚 No hay proveedores registrados aún.
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($proveedores as $i => $p): ?>
                <tr class="border-t transition-colors" style="border-color:#F3D5B5;"
                    onmouseover="this.style.background='#FFF7ED';"
                    onmouseout="this.style.background='';">
                    <td class="px-5 py-3 font-bold" style="color:#A87D5C;"><?= $i + 1 ?></td>
                    <td class="px-5 py-3 font-black" style="color:#1C0A00;"><?= htmlspecialchars($p['nombre']) ?></td>
                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;"><?= htmlspecialchars($p['telefono'] ?? '—') ?></td>
                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;"><?= htmlspecialchars($p['correo'] ?? '—') ?></td>
                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;"><?= htmlspecialchars($p['direccion'] ?? '—') ?></td>
                    <td class="px-5 py-3">
                        <?php if ($p['estado'] === 'activo'): ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#D1FAE5;color:#065F46;border:1px solid #6EE7B7;">Activo</span>
                        <?php else: ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <?php if (strtoupper($usuario['rol']) === 'ADMIN'): ?>
                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="editar.php?id=<?= $p['id_proveedor'] ?>"
                               class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all"
                               style="border-color:#F3D5B5;color:#6B4F3A;"
                               onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';this.style.color='#F97316';"
                               onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';this.style.color='#6B4F3A';"
                               title="Editar"><i class="fas fa-pen"></i></a>
                            <a href="../../controllers/ProveedorController.php?accion=eliminar&id=<?= $p['id_proveedor'] ?>"
                               class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all"
                               style="border-color:#FCA5A5;color:#DC2626;"
                               onmouseover="this.style.background='#FEE2E2';"
                               onmouseout="this.style.background='';"
                               title="Eliminar"
                               onclick="return confirm('¿Eliminar este proveedor?');">
                                <i class="fas fa-trash"></i></a>
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

<!-- ══ MODAL NUEVO PROVEEDOR ══ -->
<div id="modal-proveedor" class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(28,10,0,0.45);backdrop-filter:blur(4px);display:none!important;">
    <div id="modal-proveedor-box" class="bg-white rounded-2xl w-full shadow-2xl"
         style="max-width:500px;border:1px solid #F3D5B5;transform:scale(0.92) translateY(16px);opacity:0;transition:transform 0.25s cubic-bezier(0.34,1.56,0.64,1),opacity 0.2s ease;">

        <div class="flex items-center justify-between px-6 py-5 border-b" style="border-color:#F3D5B5;">
            <div>
                <h3 class="text-lg font-black" style="color:#1C0A00;">Nuevo <span style="color:#F97316;">Proveedor</span></h3>
                <p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;">Registra un nuevo proveedor</p>
            </div>
            <button onclick="cerrarModal('modal-proveedor','modal-proveedor-box')"
                    class="w-8 h-8 rounded-xl flex items-center justify-center text-sm transition-all"
                    style="background:#FFF7ED;border:1px solid #F3D5B5;color:#A87D5C;"
                    onmouseover="this.style.background='#FEE2E2';this.style.color='#DC2626';"
                    onmouseout="this.style.background='#FFF7ED';this.style.color='#A87D5C';">✕</button>
        </div>

        <form id="form-proveedor" class="px-6 py-5 flex flex-col gap-4">
            <?php
            $camposProv = [
                ['nombre',    'Nombre',    'fa-truck',          'Ej. Harinera del Valle', 'text',  true],
                ['telefono',  'Teléfono',  'fa-phone',          'Ej. 3001234567',         'text',  false],
                ['correo',    'Correo',    'fa-envelope',       'proveedor@ejemplo.com',  'email', false],
                ['direccion', 'Dirección', 'fa-map-marker-alt', 'Ej. Calle 10 # 5-20',   'text',  false],
            ];
            foreach ($camposProv as [$campo, $label, $icon, $ph, $type, $req]):
            ?>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">
                    <?= $label ?><?= $req ? ' <span style="color:#F97316;">*</span>' : ' <span class="font-semibold normal-case" style="color:#A87D5C;">(opcional)</span>' ?>
                </label>
                <div class="relative">
                    <i class="fas <?= $icon ?> absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <input type="<?= $type ?>" name="<?= $campo ?>" <?= $req ? 'required' : '' ?> placeholder="<?= $ph ?>"
                           class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none transition-all"
                           style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                           onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                           onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';">
                </div>
            </div>
            <?php endforeach; ?>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Estado</label>
                <div class="relative">
                    <i class="fas fa-toggle-on absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <select name="estado" class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none appearance-none"
                            style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                            onfocus="this.style.borderColor='#F97316';" onblur="this.style.borderColor='#F3D5B5';">
                        <option value="activo">✅ Activo</option>
                        <option value="inactivo">❌ Inactivo</option>
                    </select>
                </div>
            </div>

            <div id="error-proveedor" class="hidden px-4 py-3 rounded-xl text-sm font-bold"
                 style="background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;"></div>

            <div class="flex gap-3 pt-1">
                <button type="submit" id="btn-proveedor"
                        class="flex-1 py-2.5 rounded-xl text-sm font-black text-white flex items-center justify-center gap-2"
                        style="background:#F97316;box-shadow:0 4px 14px rgba(249,115,22,0.3);"
                        onmouseover="this.style.background='#EA6A0A';" onmouseout="this.style.background='#F97316';">
                    <i class="fas fa-plus"></i> <span id="btn-proveedor-txt">Crear Proveedor</span>
                </button>
                <button type="button" onclick="cerrarModal('modal-proveedor','modal-proveedor-box')"
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
function abrirModalProveedor() { abrirModal('modal-proveedor','modal-proveedor-box'); document.getElementById('form-proveedor').reset(); document.getElementById('error-proveedor').classList.add('hidden'); }

document.getElementById('form-proveedor').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('btn-proveedor');
    var txt = document.getElementById('btn-proveedor-txt');
    var err = document.getElementById('error-proveedor');
    btn.disabled = true; txt.textContent = 'Guardando...'; btn.style.opacity = '0.75';
    fetch('/PanApp/controllers/ProveedorController.php?accion=crear', { method:'POST', body: new FormData(this) })
    .then(function() {
        Swal.fire({ icon:'success', title:'¡Proveedor creado!', text:'El proveedor fue registrado correctamente.', confirmButtonColor:'#F97316' })
        .then(function() { window.location.reload(); });
    })
    .catch(function() { err.textContent = '⚠️ Error de conexión.'; err.classList.remove('hidden'); })
    .finally(function() { btn.disabled = false; txt.textContent = 'Crear Proveedor'; btn.style.opacity = '1'; });
});
</script>
