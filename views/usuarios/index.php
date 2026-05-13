<?php
session_start();

if (!isset($_SESSION['usuario']) || strtoupper($_SESSION['usuario']['rol']) !== 'ADMIN') {
    header("Location: login.php"); exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/usuario.php';

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
        <button type="button" onclick="abrirModalCrearU()"
           class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-black text-white transition-all"
           style="background:#F97316;box-shadow:0 4px 12px rgba(249,115,22,0.3);"
           onmouseover="this.style.background='#EA6A0A';this.style.transform='translateY(-1px)';"
           onmouseout="this.style.background='#F97316';this.style.transform='';">
            <i class="fas fa-user-plus"></i> Agregar Usuario
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
                            <button type="button"
                               onclick="abrirModalEditarU(<?= $u['id_usuario'] ?>, '<?= htmlspecialchars(addslashes($u['nombres'])) ?>', '<?= htmlspecialchars(addslashes($u['apellidos'])) ?>', '<?= htmlspecialchars(addslashes($u['email'])) ?>', '<?= strtoupper($u['rol']) ?>')"
                               class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all"
                               style="border-color:#F3D5B5;color:#6B4F3A;"
                               onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';this.style.color='#F97316';"
                               onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';this.style.color='#6B4F3A';"
                               title="Editar">
                                <i class="fas fa-pen"></i>
                            </button>

                            <!-- Activar / Desactivar -->
                            <?php if ($u['activo']): ?>
                            <button type="button"
                               onclick="confirmarToggle(<?= $u['id_usuario'] ?>, '<?= htmlspecialchars(addslashes($u['nombres'])) ?>', 1)"
                               class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all"
                               style="border-color:#FCA5A5;color:#DC2626;"
                               onmouseover="this.style.background='#FEE2E2';"
                               onmouseout="this.style.background='';"
                               title="Desactivar">
                                <i class="fas fa-ban"></i>
                            </button>
                            <?php else: ?>
                            <button type="button"
                               onclick="confirmarToggle(<?= $u['id_usuario'] ?>, '<?= htmlspecialchars(addslashes($u['nombres'])) ?>', 0)"
                               class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all"
                               style="border-color:#6EE7B7;color:#065F46;"
                               onmouseover="this.style.background='#D1FAE5';"
                               onmouseout="this.style.background='';"
                               title="Activar">
                                <i class="fas fa-check"></i>
                            </button>
                            <?php endif; ?>

                            <!-- Eliminar -->
                            <button type="button"
                               onclick="confirmarEliminar(<?= $u['id_usuario'] ?>, '<?= htmlspecialchars(addslashes($u['nombres'])) ?>')"
                               class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all"
                               style="border-color:#FCA5A5;color:#DC2626;"
                               onmouseover="this.style.background='#FEE2E2';"
                               onmouseout="this.style.background='';"
                               title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>

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

<!-- ══ MODAL CREAR USUARIO ══ -->
<div id="modal-crear-u" class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(28,10,0,0.45);backdrop-filter:blur(4px);display:none!important;">
    <div id="modal-crear-u-box" class="bg-white rounded-2xl w-full shadow-2xl"
         style="max-width:520px;border:1px solid #F3D5B5;transform:scale(0.92) translateY(16px);opacity:0;transition:transform 0.25s cubic-bezier(0.34,1.56,0.64,1),opacity 0.2s ease;">
        <div class="flex items-center justify-between px-6 py-5 border-b" style="border-color:#F3D5B5;">
            <div>
                <h3 class="text-lg font-black" style="color:#1C0A00;">Nuevo <span style="color:#F97316;">Usuario</span></h3>
                <p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;">Completa los datos para registrar un nuevo usuario</p>
            </div>
            <button onclick="cerrarModal('modal-crear-u','modal-crear-u-box')" class="w-8 h-8 rounded-xl flex items-center justify-center text-sm transition-all" style="background:#FFF7ED;border:1px solid #F3D5B5;color:#A87D5C;" onmouseover="this.style.background='#FEE2E2';this.style.color='#DC2626';" onmouseout="this.style.background='#FFF7ED';this.style.color='#A87D5C';">✕</button>
        </div>
        <form id="form-crear-u" class="px-6 py-5 flex flex-col gap-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Nombres <span style="color:#F97316;">*</span></label>
                    <div class="relative"><i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <input type="text" name="nombres" required maxlength="100" placeholder="Juan Carlos" class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;" onfocus="this.style.borderColor='#F97316';this.style.background='#fff';" onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';"></div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Apellidos <span style="color:#F97316;">*</span></label>
                    <div class="relative"><i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <input type="text" name="apellidos" required maxlength="100" placeholder="Pérez" class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;" onfocus="this.style.borderColor='#F97316';this.style.background='#fff';" onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';"></div>
                </div>
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Correo <span style="color:#F97316;">*</span></label>
                <div class="relative"><i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                <input type="email" name="email" required maxlength="150" placeholder="correo@ejemplo.com" class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;" onfocus="this.style.borderColor='#F97316';this.style.background='#fff';" onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';"></div>
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Rol <span style="color:#F97316;">*</span></label>
                <div class="relative"><i class="fas fa-shield-alt absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                <select name="rol" required class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none appearance-none" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;" onfocus="this.style.borderColor='#F97316';" onblur="this.style.borderColor='#F3D5B5';">
                    <option value="">-- Selecciona --</option>
                    <option value="ADMIN">🛡️ Administrador</option>
                    <option value="CAJERO">👨‍🍳 Cajero</option>
                </select></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Contraseña <span style="color:#F97316;">*</span></label>
                    <div class="relative"><i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <input type="password" name="password" id="cu-pass1" required placeholder="Mín. 6 caracteres" class="w-full pl-8 pr-8 py-2.5 rounded-xl text-sm font-semibold outline-none" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;" onfocus="this.style.borderColor='#F97316';this.style.background='#fff';" onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';">
                    <button type="button" onclick="togglePassU('cu-pass1','cu-eye1')" class="absolute right-2.5 top-1/2 -translate-y-1/2 border-none bg-transparent cursor-pointer text-xs" style="color:#A87D5C;"><i class="fas fa-eye" id="cu-eye1"></i></button></div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Confirmar <span style="color:#F97316;">*</span></label>
                    <div class="relative"><i class="fas fa-check-double absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <input type="password" name="confirmar_password" id="cu-pass2" required placeholder="Repite" class="w-full pl-8 pr-8 py-2.5 rounded-xl text-sm font-semibold outline-none" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;" onfocus="this.style.borderColor='#F97316';this.style.background='#fff';" onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';">
                    <button type="button" onclick="togglePassU('cu-pass2','cu-eye2')" class="absolute right-2.5 top-1/2 -translate-y-1/2 border-none bg-transparent cursor-pointer text-xs" style="color:#A87D5C;"><i class="fas fa-eye" id="cu-eye2"></i></button></div>
                </div>
            </div>
            <div id="error-crear-u" class="hidden px-4 py-3 rounded-xl text-sm font-bold" style="background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;"></div>
            <div class="flex gap-3 pt-1">
                <button type="submit" id="btn-crear-u" class="flex-1 py-2.5 rounded-xl text-sm font-black text-white flex items-center justify-center gap-2" style="background:#F97316;" onmouseover="this.style.background='#EA6A0A';" onmouseout="this.style.background='#F97316';"><i class="fas fa-user-plus"></i> <span id="btn-crear-u-txt">Crear Usuario</span></button>
                <button type="button" onclick="cerrarModal('modal-crear-u','modal-crear-u-box')" class="px-5 py-2.5 rounded-xl text-sm font-black" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#6B4F3A;" onmouseover="this.style.background='#F3D5B5';" onmouseout="this.style.background='#FFF7ED';">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ MODAL EDITAR USUARIO ══ -->
<div id="modal-editar-u" class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(28,10,0,0.45);backdrop-filter:blur(4px);display:none!important;">
    <div id="modal-editar-u-box" class="bg-white rounded-2xl w-full shadow-2xl"
         style="max-width:520px;border:1px solid #F3D5B5;transform:scale(0.92) translateY(16px);opacity:0;transition:transform 0.25s cubic-bezier(0.34,1.56,0.64,1),opacity 0.2s ease;">
        <div class="flex items-center justify-between px-6 py-5 border-b" style="border-color:#F3D5B5;">
            <div>
                <h3 class="text-lg font-black" style="color:#1C0A00;">Editar <span style="color:#F97316;">Usuario</span></h3>
                <p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;">Deja la contraseña vacía para no cambiarla</p>
            </div>
            <button onclick="cerrarModal('modal-editar-u','modal-editar-u-box')" class="w-8 h-8 rounded-xl flex items-center justify-center text-sm transition-all" style="background:#FFF7ED;border:1px solid #F3D5B5;color:#A87D5C;" onmouseover="this.style.background='#FEE2E2';this.style.color='#DC2626';" onmouseout="this.style.background='#FFF7ED';this.style.color='#A87D5C';">✕</button>
        </div>
        <form id="form-editar-u" class="px-6 py-5 flex flex-col gap-4">
            <input type="hidden" id="eu-id">
            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Nombres <span style="color:#F97316;">*</span></label>
                    <div class="relative"><i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <input type="text" name="nombres" id="eu-nombres" required maxlength="100" class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;" onfocus="this.style.borderColor='#F97316';this.style.background='#fff';" onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';"></div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Apellidos <span style="color:#F97316;">*</span></label>
                    <div class="relative"><i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <input type="text" name="apellidos" id="eu-apellidos" required maxlength="100" class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;" onfocus="this.style.borderColor='#F97316';this.style.background='#fff';" onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';"></div>
                </div>
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Correo <span style="color:#F97316;">*</span></label>
                <div class="relative"><i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                <input type="email" name="email" id="eu-email" required maxlength="150" class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;" onfocus="this.style.borderColor='#F97316';this.style.background='#fff';" onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';"></div>
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Rol <span style="color:#F97316;">*</span></label>
                <div class="relative"><i class="fas fa-shield-alt absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                <select name="rol" id="eu-rol" required class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none appearance-none" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;" onfocus="this.style.borderColor='#F97316';" onblur="this.style.borderColor='#F3D5B5';">
                    <option value="ADMIN">🛡️ Administrador</option>
                    <option value="CAJERO">👨‍🍳 Cajero</option>
                </select></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Nueva contraseña <span class="font-semibold normal-case" style="color:#A87D5C;">(opcional)</span></label>
                    <div class="relative"><i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <input type="password" name="password" id="eu-pass1" placeholder="Dejar vacío" class="w-full pl-8 pr-8 py-2.5 rounded-xl text-sm font-semibold outline-none" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;" onfocus="this.style.borderColor='#F97316';this.style.background='#fff';" onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';">
                    <button type="button" onclick="togglePassU('eu-pass1','eu-eye1')" class="absolute right-2.5 top-1/2 -translate-y-1/2 border-none bg-transparent cursor-pointer text-xs" style="color:#A87D5C;"><i class="fas fa-eye" id="eu-eye1"></i></button></div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Confirmar</label>
                    <div class="relative"><i class="fas fa-check-double absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <input type="password" name="confirmar_password" id="eu-pass2" placeholder="Repite" class="w-full pl-8 pr-8 py-2.5 rounded-xl text-sm font-semibold outline-none" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;" onfocus="this.style.borderColor='#F97316';this.style.background='#fff';" onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';">
                    <button type="button" onclick="togglePassU('eu-pass2','eu-eye2')" class="absolute right-2.5 top-1/2 -translate-y-1/2 border-none bg-transparent cursor-pointer text-xs" style="color:#A87D5C;"><i class="fas fa-eye" id="eu-eye2"></i></button></div>
                </div>
            </div>
            <div id="error-editar-u" class="hidden px-4 py-3 rounded-xl text-sm font-bold" style="background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;"></div>
            <div class="flex gap-3 pt-1">
                <button type="submit" id="btn-editar-u" class="flex-1 py-2.5 rounded-xl text-sm font-black text-white flex items-center justify-center gap-2" style="background:#F97316;" onmouseover="this.style.background='#EA6A0A';" onmouseout="this.style.background='#F97316';"><i class="fas fa-save"></i> <span id="btn-editar-u-txt">Guardar Cambios</span></button>
                <button type="button" onclick="cerrarModal('modal-editar-u','modal-editar-u-box')" class="px-5 py-2.5 rounded-xl text-sm font-black" style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#6B4F3A;" onmouseover="this.style.background='#F3D5B5';" onmouseout="this.style.background='#FFF7ED';">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassU(inputId, iconId) {
    var input = document.getElementById(inputId);
    var icon  = document.getElementById(iconId);
    if (input.type === 'password') { input.type = 'text'; icon.classList.replace('fa-eye','fa-eye-slash'); }
    else { input.type = 'password'; icon.classList.replace('fa-eye-slash','fa-eye'); }
}

function abrirModalCrearU() {
    abrirModal('modal-crear-u','modal-crear-u-box');
    document.getElementById('form-crear-u').reset();
    document.getElementById('error-crear-u').classList.add('hidden');
}

function abrirModalEditarU(id, nombres, apellidos, email, rol) {
    abrirModal('modal-editar-u','modal-editar-u-box');
    document.getElementById('eu-id').value        = id;
    document.getElementById('eu-nombres').value   = nombres;
    document.getElementById('eu-apellidos').value = apellidos;
    document.getElementById('eu-email').value     = email;
    document.getElementById('eu-rol').value       = rol;
    document.getElementById('eu-pass1').value     = '';
    document.getElementById('eu-pass2').value     = '';
    document.getElementById('error-editar-u').classList.add('hidden');
}

function confirmarToggle(id, nombre, estadoActual) {
    var accion = estadoActual == 1 ? 'desactivar' : 'activar';
    var color  = estadoActual == 1 ? '#F59E0B' : '#10B981';
    Swal.fire({
        icon: estadoActual == 1 ? 'warning' : 'question',
        title: '¿' + accion.charAt(0).toUpperCase() + accion.slice(1) + ' usuario?',
        html: '<strong>' + nombre + '</strong> será ' + accion + 'do.',
        showCancelButton: true,
        confirmButtonText: 'Sí, ' + accion,
        cancelButtonText: 'Cancelar',
        confirmButtonColor: color
    }).then(function(r) {
        if (r.isConfirmed) window.location.href = '/PanApp/controllers/AdminUsuarioController.php?accion=toggleEstado&id=' + id + '&estado=' + estadoActual;
    });
}

function confirmarEliminar(id, nombre) {
    Swal.fire({
        icon: 'error',
        title: '¿Eliminar usuario?',
        html: '<strong>' + nombre + '</strong> será eliminado permanentemente.',
        showCancelButton: true,
        confirmButtonText: '🗑️ Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#DC2626'
    }).then(function(r) {
        if (r.isConfirmed) window.location.href = '/PanApp/controllers/AdminUsuarioController.php?accion=eliminar&id=' + id;
    });
}

document.getElementById('form-crear-u').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('btn-crear-u');
    var txt = document.getElementById('btn-crear-u-txt');
    var err = document.getElementById('error-crear-u');
    var fd  = new FormData(this);
    if (fd.get('password') !== fd.get('confirmar_password')) { err.textContent = '⚠️ Las contraseñas no coinciden.'; err.classList.remove('hidden'); return; }
    if (fd.get('password').length < 6) { err.textContent = '⚠️ Mínimo 6 caracteres.'; err.classList.remove('hidden'); return; }
    err.classList.add('hidden');
    btn.disabled = true; txt.textContent = 'Creando...'; btn.style.opacity = '0.75';
    fetch('/PanApp/controllers/AdminUsuarioController.php?accion=crear', { method:'POST', body: fd })
    .then(function() { Swal.fire({ icon:'success', title:'¡Usuario creado!', confirmButtonColor:'#F97316' }).then(function() { window.location.reload(); }); })
    .catch(function() { err.textContent = '⚠️ Error de conexión.'; err.classList.remove('hidden'); })
    .finally(function() { btn.disabled = false; txt.textContent = 'Crear Usuario'; btn.style.opacity = '1'; });
});

document.getElementById('form-editar-u').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('btn-editar-u');
    var txt = document.getElementById('btn-editar-u-txt');
    var err = document.getElementById('error-editar-u');
    var fd  = new FormData(this);
    var id  = document.getElementById('eu-id').value;
    var p1  = fd.get('password');
    var p2  = fd.get('confirmar_password');
    if (p1 && p1 !== p2) { err.textContent = '⚠️ Las contraseñas no coinciden.'; err.classList.remove('hidden'); return; }
    if (p1 && p1.length < 6) { err.textContent = '⚠️ Mínimo 6 caracteres.'; err.classList.remove('hidden'); return; }
    err.classList.add('hidden');
    btn.disabled = true; txt.textContent = 'Guardando...'; btn.style.opacity = '0.75';
    fetch('/PanApp/controllers/AdminUsuarioController.php?accion=editar&id=' + id, { method:'POST', body: fd })
    .then(function() { Swal.fire({ icon:'success', title:'¡Cambios guardados!', confirmButtonColor:'#F97316' }).then(function() { window.location.reload(); }); })
    .catch(function() { err.textContent = '⚠️ Error de conexión.'; err.classList.remove('hidden'); })
    .finally(function() { btn.disabled = false; txt.textContent = 'Guardar Cambios'; btn.style.opacity = '1'; });
});
</script>
