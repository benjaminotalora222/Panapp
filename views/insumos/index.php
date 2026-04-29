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
        <a href="crear.php"
           class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-black text-white transition-all no-underline"
           style="background:#F97316;box-shadow:0 4px 12px rgba(249,115,22,0.3);"
           onmouseover="this.style.background='#EA6A0A';"
           onmouseout="this.style.background='#F97316';">
            <i class="fas fa-plus"></i> Agregar Insumo
        </a>
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
