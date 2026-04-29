<?php
session_start();

if (!isset($_SESSION['usuario']) || strtoupper($_SESSION['usuario']['rol']) !== 'ADMIN') {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Usuario.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: admin.php");
    exit;
}

$database     = new Database();
$db           = $database->conectar();
$usuarioModel = new Usuario($db);
$u            = $usuarioModel->obtenerPorId($id);

if (!$u) {
    $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => 'Usuario no encontrado.'];
    header("Location: admin.php");
    exit;
}

$titulo = "Editar Usuario";
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 mb-6 text-sm font-bold" style="color:#A87D5C;">
    <a href="admin.php" class="no-underline transition-colors" style="color:#A87D5C;"
       onmouseover="this.style.color='#F97316';" onmouseout="this.style.color='#A87D5C';">
        <i class="fas fa-home mr-1"></i> Dashboard
    </a>
    <span>/</span>
    <span style="color:#F97316;">Editar Usuario</span>
</div>

<!-- Card formulario -->
<div class="bg-white rounded-2xl border" style="border-color:#F3D5B5; max-width:600px;">

    <div class="px-6 py-5 border-b" style="border-color:#F3D5B5;">
        <h2 class="text-xl font-black" style="color:#1C0A00;">
            Editar <span style="color:#F97316;">Usuario</span>
        </h2>
        <p class="text-sm font-semibold mt-1" style="color:#A87D5C;">
            Modifica los datos del usuario. Deja la contraseña vacía para no cambiarla.
        </p>
    </div>

    <form action="../../controllers/AdminUsuarioController.php?accion=editar&id=<?= $id ?>" method="POST" class="px-6 py-6 flex flex-col gap-5">

        <!-- Nombres -->
        <div class="flex flex-col gap-1">
            <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Nombres</label>
            <div class="relative">
                <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
                <input type="text" name="nombres" required maxlength="100"
                       value="<?= htmlspecialchars($u['nombres']) ?>"
                       class="w-full pl-9 pr-4 py-3 rounded-xl text-sm font-semibold outline-none transition-all"
                       style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                       onfocus="this.style.borderColor='#F97316';this.style.background='#fff';this.style.boxShadow='0 0 0 3px rgba(249,115,22,0.12)';"
                       onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';this.style.boxShadow='';">
            </div>
        </div>

        <!-- Apellidos -->
        <div class="flex flex-col gap-1">
            <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Apellidos</label>
            <div class="relative">
                <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
                <input type="text" name="apellidos" required maxlength="100"
                       value="<?= htmlspecialchars($u['apellidos']) ?>"
                       class="w-full pl-9 pr-4 py-3 rounded-xl text-sm font-semibold outline-none transition-all"
                       style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                       onfocus="this.style.borderColor='#F97316';this.style.background='#fff';this.style.boxShadow='0 0 0 3px rgba(249,115,22,0.12)';"
                       onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';this.style.boxShadow='';">
            </div>
        </div>

        <!-- Correo -->
        <div class="flex flex-col gap-1">
            <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Correo Electrónico</label>
            <div class="relative">
                <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
                <input type="email" name="email" required maxlength="150"
                       value="<?= htmlspecialchars($u['email']) ?>"
                       class="w-full pl-9 pr-4 py-3 rounded-xl text-sm font-semibold outline-none transition-all"
                       style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                       onfocus="this.style.borderColor='#F97316';this.style.background='#fff';this.style.boxShadow='0 0 0 3px rgba(249,115,22,0.12)';"
                       onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';this.style.boxShadow='';">
            </div>
        </div>

        <!-- Rol -->
        <div class="flex flex-col gap-1">
            <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Rol</label>
            <div class="relative">
                <i class="fas fa-shield-alt absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
                <select name="rol" required
                        class="w-full pl-9 pr-4 py-3 rounded-xl text-sm font-semibold outline-none transition-all appearance-none"
                        style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                        onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                        onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';">
                    <option value="ADMIN"   <?= strtoupper($u['rol']) === 'ADMIN'   ? 'selected' : '' ?>>🛡️ Administrador</option>
                    <option value="CAJERO"  <?= strtoupper($u['rol']) === 'CAJERO'  ? 'selected' : '' ?>>👨‍🍳 Cajero</option>
                </select>
            </div>
        </div>

        <!-- Nueva contraseña (opcional) -->
        <div class="flex flex-col gap-1">
            <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">
                Nueva Contraseña <span class="font-semibold normal-case" style="color:#A87D5C;">(opcional)</span>
            </label>
            <div class="relative">
                <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
                <input type="password" name="password" id="pass1" placeholder="Dejar vacío para no cambiar"
                       class="w-full pl-9 pr-10 py-3 rounded-xl text-sm font-semibold outline-none transition-all"
                       style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                       onfocus="this.style.borderColor='#F97316';this.style.background='#fff';this.style.boxShadow='0 0 0 3px rgba(249,115,22,0.12)';"
                       onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';this.style.boxShadow='';">
                <button type="button" onclick="togglePass('pass1','eye1')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-sm border-none bg-transparent cursor-pointer"
                        style="color:#A87D5C;">
                    <i class="fas fa-eye" id="eye1"></i>
                </button>
            </div>
        </div>

        <!-- Confirmar contraseña (opcional) -->
        <div class="flex flex-col gap-1">
            <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">
                Confirmar Contraseña <span class="font-semibold normal-case" style="color:#A87D5C;">(opcional)</span>
            </label>
            <div class="relative">
                <i class="fas fa-check-double absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
                <input type="password" name="confirmar_password" id="pass2" placeholder="Repite la nueva contraseña"
                       class="w-full pl-9 pr-10 py-3 rounded-xl text-sm font-semibold outline-none transition-all"
                       style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                       onfocus="this.style.borderColor='#F97316';this.style.background='#fff';this.style.boxShadow='0 0 0 3px rgba(249,115,22,0.12)';"
                       onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';this.style.boxShadow='';">
                <button type="button" onclick="togglePass('pass2','eye2')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-sm border-none bg-transparent cursor-pointer"
                        style="color:#A87D5C;">
                    <i class="fas fa-eye" id="eye2"></i>
                </button>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="flex-1 py-3 rounded-xl text-sm font-black text-white transition-all"
                    style="background:#F97316;box-shadow:0 4px 14px rgba(249,115,22,0.35);"
                    onmouseover="this.style.background='#EA6A0A';"
                    onmouseout="this.style.background='#F97316';">
                <i class="fas fa-save mr-2"></i> Guardar Cambios
            </button>
            <a href="admin.php"
               class="px-6 py-3 rounded-xl text-sm font-black no-underline transition-all flex items-center"
               style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#6B4F3A;"
               onmouseover="this.style.background='#F3D5B5';"
               onmouseout="this.style.background='#FFF7ED';">
                Cancelar
            </a>
        </div>

    </form>
</div>

<script>
    function togglePass(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
