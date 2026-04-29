<?php
session_start();

if (!isset($_SESSION['usuario']) || strtoupper($_SESSION['usuario']['rol']) !== 'ADMIN') {
    header("Location: login.php"); exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Usuario.php';

$db           = (new Database())->conectar();
$usuarioModel = new Usuario($db);
$usuarios     = $usuarioModel->obtenerTodos();

$totalUsuarios  = count($usuarios);
$totalAdmins    = count(array_filter($usuarios, fn($u) => strtoupper($u['rol']) === 'ADMIN'));
$totalCajeros   = count(array_filter($usuarios, fn($u) => strtoupper($u['rol']) === 'CAJERO'));
$totalActivos   = count(array_filter($usuarios, fn($u) => $u['activo'] == 1));

$titulo = "Usuarios";
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 mb-6 text-sm font-bold" style="color:#A87D5C;">
    <a href="../dashboard/admin.php" class="no-underline" style="color:#A87D5C;"
       onmouseover="this.style.color='#F97316';" onmouseout="this.style.color='#A87D5C';">
        <i class="fas fa-home mr-1"></i> Dashboard
    </a>
    <span>/</span>
    <span style="color:#F97316;">Usuarios</span>
</div>

<!-- Tarjetas resumen -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded-2xl p-5 border flex items-center gap-3" style="border-color:#F3D5B5;">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl flex-shrink-0" style="background:#FFF7ED;">👥</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Total</p>
            <p class="text-2xl font-black" style="color:#1C0A00;"><?= $totalUsuarios ?></p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border flex items-center gap-3" style="border-color:#F3D5B5;">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl flex-shrink-0" style="background:#D1FAE5;">🛡️</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Admins</p>
            <p class="text-2xl font-black" style="color:#065F46;"><?= $totalAdmins ?></p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border flex items-center gap-3" style="border-color:#F3D5B5;">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl flex-shrink-0" style="background:#EDE9FE;">👨‍🍳</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Cajeros</p>
            <p class="text-2xl font-black" style="color:#5B21B6;"><?= $totalCajeros ?></p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border flex items-center gap-3" style="border-color:#F3D5B5;">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl flex-shrink-0" style="background:#FFF7ED;">✅</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Activos</p>
            <p class="text-2xl font-black" style="color:#F97316;"><?= $totalActivos ?></p>
        </div>
    </div>

</div>

<!-- Tabla principal -->
<div class="bg-white rounded-2xl border" style="border-color:#F3D5B5;">

    <div class="flex items-center justify-between px-6 py-5 border-b" style="border-color:#F3D5B5;">
        <h2 class="text-xl font-black" style="color:#1C0A00;">
            Gestión de <span style="color:#F97316;">Usuarios</span>
        </h2>
        <a href="../dashboard/usuario_crear.php"
           class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-black text-white transition-all no-underline"
           style="background:#F97316;box-shadow:0 4px 12px rgba(249,115,22,0.3);"
           onmouseover="this.style.background='#EA6A0A';"
           onmouseout="this.style.background='#F97316';">
            <i class="fas fa-plus"></i> Agregar Usuario
        </a>
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

    <!-- Buscador -->
    <div class="px-6 py-3 border-b" style="border-color:#F3D5B5;">
        <div class="relative" style="max-width:320px;">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
            <input type="text" id="buscador" placeholder="Buscar por nombre o correo..."
                   class="w-full pl-9 pr-4 py-2.5 rounded-xl text-sm font-semibold outline-none"
                   style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                   onfocus="this.style.borderColor='#F97316';" onblur="this.style.borderColor='#F3D5B5';"
                   oninput="filtrar(this.value)">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm" id="tablaUsuarios">
            <thead>
                <tr style="background:#FFF7ED;">
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">#</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Nombre completo</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Correo</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Rol</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Estado</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Registrado</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider text-center" style="color:#A87D5C;">Acciones</th>
                </tr>
            </thead>
            <tbody id="tbodyUsuarios">

                <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="7" class="px-5 py-8 text-center font-bold" style="color:#A87D5C;">
                        👤 No hay usuarios registrados.
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($usuarios as $i => $u): ?>
                <tr class="fila-usuario border-t transition-colors" style="border-color:#F3D5B5;"
                    data-nombre="<?= strtolower($u['nombres'].' '.$u['apellidos']) ?>"
                    data-correo="<?= strtolower($u['email']) ?>"
                    onmouseover="this.style.background='#FFF7ED';"
                    onmouseout="this.style.background='';">

                    <td class="px-5 py-3 font-bold" style="color:#A87D5C;"><?= $i + 1 ?></td>

                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm flex-shrink-0"
                                 style="background:#FFF7ED;border:1.5px solid #F3D5B5;">
                                <?= strtoupper($u['rol']) === 'ADMIN' ? '🛡️' : '👨‍🍳' ?>
                            </div>
                            <span class="font-black" style="color:#1C0A00;">
                                <?= htmlspecialchars($u['nombres'] . ' ' . $u['apellidos']) ?>
                            </span>
                        </div>
                    </td>

                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;">
                        <?= htmlspecialchars($u['email']) ?>
                    </td>

                    <td class="px-5 py-3">
                        <?php if (strtoupper($u['rol']) === 'ADMIN'): ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#D1FAE5;color:#065F46;border:1px solid #6EE7B7;">🛡️ Admin</span>
                        <?php else: ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#EDE9FE;color:#5B21B6;border:1px solid #C4B5FD;">👨‍🍳 Cajero</span>
                        <?php endif; ?>
                    </td>

                    <td class="px-5 py-3">
                        <?php if ($u['activo']): ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#D1FAE5;color:#065F46;border:1px solid #6EE7B7;">Activo</span>
                        <?php else: ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;">Inactivo</span>
                        <?php endif; ?>
                    </td>

                    <td class="px-5 py-3 text-xs font-semibold" style="color:#A87D5C;">
                        <?= $u['created_at'] ? date('d/m/Y', strtotime($u['created_at'])) : '—' ?>
                    </td>

                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">

                            <!-- Editar -->
                            <a href="../dashboard/usuario_editar.php?id=<?= $u['id_usuario'] ?>"
                               class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all"
                               style="border-color:#F3D5B5;color:#6B4F3A;"
                               onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';this.style.color='#F97316';"
                               onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';this.style.color='#6B4F3A';"
                               title="Editar">
                                <i class="fas fa-pen"></i>
                            </a>

                            <!-- Activar / Desactivar -->
                            <?php if ($u['activo']): ?>
                            <a href="../../controllers/AdminUsuarioController.php?accion=toggleEstado&id=<?= $u['id_usuario'] ?>&estado=1"
                               class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all"
                               style="border-color:#FCA5A5;color:#DC2626;"
                               onmouseover="this.style.background='#FEE2E2';"
                               onmouseout="this.style.background='';"
                               title="Desactivar"
                               onclick="return confirm('¿Desactivar este usuario?');">
                                <i class="fas fa-ban"></i>
                            </a>
                            <?php else: ?>
                            <a href="../../controllers/AdminUsuarioController.php?accion=toggleEstado&id=<?= $u['id_usuario'] ?>&estado=0"
                               class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all"
                               style="border-color:#6EE7B7;color:#065F46;"
                               onmouseover="this.style.background='#D1FAE5';"
                               onmouseout="this.style.background='';"
                               title="Activar"
                               onclick="return confirm('¿Activar este usuario?');">
                                <i class="fas fa-check"></i>
                            </a>
                            <?php endif; ?>

                            <!-- Eliminar -->
                            <a href="../../controllers/AdminUsuarioController.php?accion=eliminar&id=<?= $u['id_usuario'] ?>"
                               class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all"
                               style="border-color:#FCA5A5;color:#DC2626;"
                               onmouseover="this.style.background='#FEE2E2';"
                               onmouseout="this.style.background='';"
                               title="Eliminar"
                               onclick="return confirm('¿Eliminar este usuario? Esta acción no se puede deshacer.');">
                                <i class="fas fa-trash"></i>
                            </a>

                        </div>
                    </td>

                </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</div>

<script>
    function filtrar(q) {
        const filas = document.querySelectorAll('.fila-usuario');
        q = q.toLowerCase();
        filas.forEach(fila => {
            const nombre = fila.dataset.nombre;
            const correo = fila.dataset.correo;
            fila.style.display = (nombre.includes(q) || correo.includes(q)) ? '' : 'none';
        });
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
