<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$db       = $database->conectar();

// Obtener inventario con nombre del insumo y proveedor
$stmt = $db->prepare("
    SELECT ii.id_inventario, ii.cantidad_actual, ii.fecha_actualizacion,
           i.id_insumo, i.nombre AS insumo, i.unidad_medida, i.descripcion,
           p.nombre AS proveedor
    FROM inventario_insumos ii
    LEFT JOIN insumos i ON ii.id_insumo = i.id_insumo
    LEFT JOIN proveedores p ON i.id_proveedor = p.id_proveedor
    ORDER BY i.nombre ASC
");
$stmt->execute();
$inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Conteos
$totalItems    = count($inventario);
$stockBajo     = count(array_filter($inventario, fn($i) => $i['cantidad_actual'] <= 5));
$stockSuficiente = $totalItems - $stockBajo;

$titulo = "Inventario";
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
    <span style="color:#F97316;">Inventario</span>
</div>

<!-- Tarjetas resumen -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">

    <div class="bg-white rounded-2xl p-5 border flex items-center gap-4" style="border-color:#F3D5B5;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FFF7ED;">📦</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Total Insumos</p>
            <p class="text-3xl font-black" style="color:#1C0A00;"><?= $totalItems ?></p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border flex items-center gap-4" style="border-color:#F3D5B5;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#D1FAE5;">✅</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Stock suficiente</p>
            <p class="text-3xl font-black" style="color:#065F46;"><?= $stockSuficiente ?></p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border flex items-center gap-4" style="border-color:#F3D5B5;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0" style="background:#FEE2E2;">⚠️</div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider" style="color:#A87D5C;">Stock bajo (≤5)</p>
            <p class="text-3xl font-black" style="color:#DC2626;"><?= $stockBajo ?></p>
        </div>
    </div>

</div>

<!-- Tabla -->
<div class="bg-white rounded-2xl border" style="border-color:#F3D5B5;">

    <div class="flex items-center justify-between px-6 py-5 border-b" style="border-color:#F3D5B5;">
        <h2 class="text-xl font-black" style="color:#1C0A00;">
            Control de <span style="color:#F97316;">Inventario</span>
        </h2>
        <?php if (strtoupper($usuario['rol']) === 'ADMIN'): ?>
        <a href="ajuste.php"
           class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-black text-white transition-all no-underline"
           style="background:#F97316;box-shadow:0 4px 12px rgba(249,115,22,0.3);"
           onmouseover="this.style.background='#EA6A0A';"
           onmouseout="this.style.background='#F97316';">
            <i class="fas fa-plus"></i> Ajustar Stock
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
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Insumo</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Proveedor</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Unidad</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Cantidad</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Estado</th>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider" style="color:#A87D5C;">Actualizado</th>
                    <?php if (strtoupper($usuario['rol']) === 'ADMIN'): ?>
                    <th class="px-5 py-3 font-black text-xs uppercase tracking-wider text-center" style="color:#A87D5C;">Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>

                <?php if (empty($inventario)): ?>
                <tr>
                    <td colspan="8" class="px-5 py-8 text-center font-bold" style="color:#A87D5C;">
                        📦 No hay insumos en el inventario aún.
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($inventario as $idx => $item): ?>
                <tr class="border-t transition-colors" style="border-color:#F3D5B5;"
                    onmouseover="this.style.background='#FFF7ED';"
                    onmouseout="this.style.background='';">

                    <td class="px-5 py-3 font-bold" style="color:#A87D5C;"><?= $idx + 1 ?></td>

                    <td class="px-5 py-3">
                        <p class="font-black text-sm" style="color:#1C0A00;"><?= htmlspecialchars($item['insumo']) ?></p>
                        <?php if (!empty($item['descripcion'])): ?>
                        <p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;"><?= htmlspecialchars($item['descripcion']) ?></p>
                        <?php endif; ?>
                    </td>

                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;">
                        <?= htmlspecialchars($item['proveedor'] ?? '—') ?>
                    </td>

                    <td class="px-5 py-3 font-semibold" style="color:#6B4F3A;">
                        <?= htmlspecialchars($item['unidad_medida'] ?? '—') ?>
                    </td>

                    <td class="px-5 py-3 font-black text-lg" style="color:#1C0A00;">
                        <?= $item['cantidad_actual'] ?>
                    </td>

                    <td class="px-5 py-3">
                        <?php if ($item['cantidad_actual'] <= 0): ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;">
                                🔴 Sin stock
                            </span>
                        <?php elseif ($item['cantidad_actual'] <= 5): ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#FEF3C7;color:#92400E;border:1px solid #FCD34D;">
                                ⚠️ Stock bajo
                            </span>
                        <?php else: ?>
                            <span class="text-xs font-black px-2.5 py-1 rounded-full"
                                  style="background:#D1FAE5;color:#065F46;border:1px solid #6EE7B7;">
                                ✅ OK
                            </span>
                        <?php endif; ?>
                    </td>

                    <td class="px-5 py-3 font-semibold text-xs" style="color:#A87D5C;">
                        <?= $item['fecha_actualizacion'] ? date('d/m/Y H:i', strtotime($item['fecha_actualizacion'])) : '—' ?>
                    </td>

                    <?php if (strtoupper($usuario['rol']) === 'ADMIN'): ?>
                    <td class="px-5 py-3 text-center">
                        <a href="ajuste.php?id=<?= $item['id_insumo'] ?>"
                           class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all mx-auto"
                           style="border-color:#F3D5B5;color:#6B4F3A;"
                           onmouseover="this.style.background='#FFF7ED';this.style.borderColor='#F97316';this.style.color='#F97316';"
                           onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';this.style.color='#6B4F3A';"
                           title="Ajustar stock">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                    <?php endif; ?>

                </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
