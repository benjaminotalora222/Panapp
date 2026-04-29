<?php
session_start();

if (!isset($_SESSION['usuario']) || strtoupper($_SESSION['usuario']['rol']) !== 'ADMIN') {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Usuario.php';

$database     = new Database();
$db           = $database->conectar();
$usuarioModel = new Usuario($db);
$usuarios     = $usuarioModel->obtenerTodos();

// ─── Conteos rápidos ───
$totalUsuarios  = count($usuarios);
$totalAdmins    = count(array_filter($usuarios, fn($u) => strtoupper($u['rol']) === 'ADMIN'));
$totalCajeros   = count(array_filter($usuarios, fn($u) => strtoupper($u['rol']) === 'CAJERO'));

$titulo = "Dashboard";
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- ══════════════════════════════
     BIENVENIDA
══════════════════════════════ -->
<div class="rounded-2xl p-6 mb-6 flex items-center justify-between flex-wrap gap-4"
     style="background:linear-gradient(135deg,#F97316,#FB923C);box-shadow:0 6px 24px rgba(249,115,22,0.3);">
    <div>
        <h2 class="text-2xl font-black text-white mb-1">
            ¡Bienvenido, <?= htmlspecialchars(explode(' ', $usuario['nombres'])[0]) ?>! 🥐
        </h2>
        <p class="text-sm font-semibold" style="color:rgba(255,255,255,0.85);">
            Panel de administración · PanApp
        </p>
    </div>
    <div class="text-4xl">🏪</div>
</div>

<!-- ══════════════════════════════
     TARJETAS DE RESUMEN
══════════════════════════════ -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">

    <div class="bg-white rounded-2xl p-5 border flex items-center gap-4" style="border-color:#F3D5B5;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0"
             style="background:#FFF7ED;">👥</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Total Usuarios</p>
            <p class="text-3xl font-black" style="color:#1C0A00;"><?= $totalUsuarios ?></p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border flex items-center gap-4" style="border-color:#F3D5B5;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0"
             style="background:#FFF7ED;">🛡️</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Administradores</p>
            <p class="text-3xl font-black" style="color:#1C0A00;"><?= $totalAdmins ?></p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border flex items-center gap-4" style="border-color:#F3D5B5;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0"
             style="background:#FFF7ED;">👨‍🍳</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Cajeros</p>
            <p class="text-3xl font-black" style="color:#1C0A00;"><?= $totalCajeros ?></p>
        </div>
    </div>

</div>

<!-- ══════════════════════════════
     TABLA DE USUARIOS
══════════════════════════════ -->
<div class="bg-white rounded-2xl border mb-6" style="border-color:#F3D5B5;">

    <div class="flex items-center justify-between px-6 py-5 border-b" style="border-color:#F3D5B5;">
        <h2 class="text-xl font-black" style="color:#1C0A00;">
            Gestión de <span style="color:#F97316;">Usuarios</span>
        </h2>
        <a href="usuario_crear.php"
           class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-black text-white transition-all"
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
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#F97316'
                });
            });
        </script>
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr style="background:#FFF7ED;">
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">#</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Nombre completo</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Correo</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Rol</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Estado</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider text-center" style="color:#A87D5C;">Acciones</th>
                </tr>
            </thead>
            <tbody>

                <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center font-bold" style="color:#A87D5C;">
                        🍞 No hay usuarios registrados aún.
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($usuarios as $i => $u): ?>
                <tr class="border-t transition-colors" style="border-color:#F3D5B5;"
                    onmouseover="this.style.background='#FFF7ED';"
                    onmouseout="this.style.background='';">

                    <td class="px-5 py-3 font-bold" style="color:#A87D5C;"><?= $i + 1 ?></td>

                    <td class="px-5 py-3 font-black" style="color:#1C0A00;">
                        <?= htmlspecialchars($u['nombres'] . ' ' . $u['apellidos']) ?>
                    </td>

                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;">
                        <?= htmlspecialchars($u['email']) ?>
                    </td>

                    <td class="px-5 py-3">
                        <?php if (strtoupper($u['rol']) === 'ADMIN'): ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#D1FAE5;color:#065F46;border:1px solid #6EE7B7;">
                                🛡️ Admin
                            </span>
                        <?php else: ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#EDE9FE;color:#5B21B6;border:1px solid #C4B5FD;">
                                👨‍🍳 Cajero
                            </span>
                        <?php endif; ?>
                    </td>

                    <td class="px-5 py-3">
                        <?php if ($u['activo']): ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#D1FAE5;color:#065F46;border:1px solid #6EE7B7;">
                                Activo
                            </span>
                        <?php else: ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;">
                                Inactivo
                            </span>
                        <?php endif; ?>
                    </td>

                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">

                            <!-- Editar -->
                            <a href="usuario_editar.php?id=<?= $u['id_usuario'] ?>"
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
                                   onclick="return confirm('¿Seguro que deseas desactivar este usuario?');">
                                    <i class="fas fa-ban"></i>
                                </a>
                            <?php else: ?>
                                <a href="../../controllers/AdminUsuarioController.php?accion=toggleEstado&id=<?= $u['id_usuario'] ?>&estado=0"
                                   class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all"
                                   style="border-color:#6EE7B7;color:#065F46;"
                                   onmouseover="this.style.background='#D1FAE5';"
                                   onmouseout="this.style.background='';"
                                   title="Activar"
                                   onclick="return confirm('¿Seguro que deseas activar este usuario?');">
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
                               onclick="return confirm('¿Seguro que deseas eliminar este usuario? Esta acción no se puede deshacer.');">
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

<!-- ══════════════════════════════
     ACCESOS RÁPIDOS (según diagrama de flujo Admin)
══════════════════════════════ -->
<div class="bg-white rounded-2xl border" style="border-color:#F3D5B5;">

    <div class="px-6 py-5 border-b" style="border-color:#F3D5B5;">
        <h3 class="text-xl font-black" style="color:#1C0A00;">
            Accesos <span style="color:#F97316;">Rápidos</span>
        </h3>
    </div>

    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        <a href="../productos/index.php"
           class="flex items-center gap-4 border rounded-xl p-4 transition-all no-underline"
           style="border-color:#F3D5B5;"
           onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';"
           onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;">🍞</div>
            <div class="flex-1">
                <p class="font-black text-sm" style="color:#1C0A00;">Gestionar Productos</p>
                <p class="text-xs font-semibold" style="color:#A87D5C;">Agregar, editar y eliminar productos</p>
            </div>
            <i class="fas fa-arrow-right" style="color:#A87D5C;"></i>
        </a>

        <a href="../inventario/index.php"
           class="flex items-center gap-4 border rounded-xl p-4 transition-all no-underline"
           style="border-color:#F3D5B5;"
           onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';"
           onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;">📦</div>
            <div class="flex-1">
                <p class="font-black text-sm" style="color:#1C0A00;">Gestionar Inventario</p>
                <p class="text-xs font-semibold" style="color:#A87D5C;">Consulta y actualiza el stock</p>
            </div>
            <i class="fas fa-arrow-right" style="color:#A87D5C;"></i>
        </a>

        <a href="../reportes/index.php"
           class="flex items-center gap-4 border rounded-xl p-4 transition-all no-underline"
           style="border-color:#F3D5B5;"
           onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';"
           onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;">📊</div>
            <div class="flex-1">
                <p class="font-black text-sm" style="color:#1C0A00;">Ver Reportes de Ventas</p>
                <p class="text-xs font-semibold" style="color:#A87D5C;">Diarios, semanales y mensuales</p>
            </div>
            <i class="fas fa-arrow-right" style="color:#A87D5C;"></i>
        </a>

        <a href="../proveedores/index.php"
           class="flex items-center gap-4 border rounded-xl p-4 transition-all no-underline"
           style="border-color:#F3D5B5;"
           onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';"
           onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;">🚚</div>
            <div class="flex-1">
                <p class="font-black text-sm" style="color:#1C0A00;">Gestionar Proveedores</p>
                <p class="text-xs font-semibold" style="color:#A87D5C;">Administra tus proveedores</p>
            </div>
            <i class="fas fa-arrow-right" style="color:#A87D5C;"></i>
        </a>

        <a href="../insumos/index.php"
           class="flex items-center gap-4 border rounded-xl p-4 transition-all no-underline"
           style="border-color:#F3D5B5;"
           onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';"
           onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;">🌾</div>
            <div class="flex-1">
                <p class="font-black text-sm" style="color:#1C0A00;">Gestionar Insumos</p>
                <p class="text-xs font-semibold" style="color:#A87D5C;">Controla materias primas</p>
            </div>
            <i class="fas fa-arrow-right" style="color:#A87D5C;"></i>
        </a>

        <a href="../usuarios/index.php"
           class="flex items-center gap-4 border rounded-xl p-4 transition-all no-underline"
           style="border-color:#F3D5B5;"
           onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';"
           onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;">👥</div>
            <div class="flex-1">
                <p class="font-black text-sm" style="color:#1C0A00;">Gestionar Usuarios</p>
                <p class="text-xs font-semibold" style="color:#A87D5C;">Admins y cajeros del sistema</p>
            </div>
            <i class="fas fa-arrow-right" style="color:#A87D5C;"></i>
        </a>

    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>