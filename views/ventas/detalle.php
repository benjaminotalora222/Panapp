<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php"); exit;
}

require_once __DIR__ . '/../../config/database.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

$db = (new Database())->conectar();

$stmtV = $db->prepare("
    SELECT v.*, mp.nombre AS metodo_pago, u.nombres, u.apellidos
    FROM ventas v
    LEFT JOIN metodos_pago mp ON v.id_metodo_pago = mp.id_metodo_pago
    LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
    WHERE v.id_venta = :id
");
$stmtV->bindParam(':id', $id, PDO::PARAM_INT);
$stmtV->execute();
$venta = $stmtV->fetch(PDO::FETCH_ASSOC);

if (!$venta) { header("Location: index.php"); exit; }

$rol = strtoupper($_SESSION['usuario']['rol']);
if ($rol !== 'ADMIN' && $venta['id_usuario'] != $_SESSION['usuario']['id_usuario']) {
    header("Location: index.php"); exit;
}

$stmtD = $db->prepare("
    SELECT dv.cantidad, dv.precio_unitario, dv.subtotal, p.nombre
    FROM detalle_venta dv
    LEFT JOIN productos p ON dv.id_producto = p.id_producto
    WHERE dv.id_venta = :id
");
$stmtD->bindParam(':id', $id, PDO::PARAM_INT);
$stmtD->execute();
$detalle = $stmtD->fetchAll(PDO::FETCH_ASSOC);

$titulo = "Detalle Venta";
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="flex items-center gap-2 mb-6 text-sm font-bold" style="color:#A87D5C;">
    <a href="../dashboard/<?= strtolower($usuario['rol']) ?>.php" class="no-underline" style="color:#A87D5C;"
       onmouseover="this.style.color='#F97316';" onmouseout="this.style.color='#A87D5C';">
        <i class="fas fa-home mr-1"></i> Dashboard
    </a>
    <span>/</span>
    <a href="index.php" class="no-underline" style="color:#A87D5C;"
       onmouseover="this.style.color='#F97316';" onmouseout="this.style.color='#A87D5C';">Ventas</a>
    <span>/</span>
    <span style="color:#F97316;">Venta #<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?></span>
</div>

<div class="bg-white rounded-2xl border" style="border-color:#F3D5B5; max-width:640px;">

    <div class="px-6 py-5 border-b flex items-center justify-between" style="border-color:#F3D5B5;">
        <div>
            <h2 class="text-xl font-black" style="color:#1C0A00;">
                Venta <span style="color:#F97316;">#<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?></span>
            </h2>
            <p class="text-sm font-semibold mt-0.5" style="color:#A87D5C;">
                <?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?>
            </p>
        </div>
        <?php if ($venta['estado'] === 'completada'): ?>
            <span class="text-xs font-black px-3 py-1.5 rounded-full"
                  style="background:#D1FAE5;color:#065F46;border:1px solid #6EE7B7;">✅ Completada</span>
        <?php elseif ($venta['estado'] === 'pendiente'): ?>
            <span class="text-xs font-black px-3 py-1.5 rounded-full"
                  style="background:#FEF3C7;color:#92400E;border:1px solid #FCD34D;">⏳ Pendiente</span>
        <?php else: ?>
            <span class="text-xs font-black px-3 py-1.5 rounded-full"
                  style="background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;">❌ Anulada</span>
        <?php endif; ?>
    </div>

    <div class="px-6 py-4 grid grid-cols-2 gap-4 border-b" style="border-color:#F3D5B5;">
        <div>
            <p class="text-xs font-black uppercase tracking-wider mb-1" style="color:#A87D5C;">Cajero</p>
            <p class="text-sm font-black" style="color:#1C0A00;">
                <?= htmlspecialchars($venta['nombres'] . ' ' . $venta['apellidos']) ?>
            </p>
        </div>
        <div>
            <p class="text-xs font-black uppercase tracking-wider mb-1" style="color:#A87D5C;">Método de Pago</p>
            <p class="text-sm font-black" style="color:#1C0A00;">
                <?= htmlspecialchars($venta['metodo_pago'] ?? '—') ?>
            </p>
        </div>
    </div>

    <div class="px-6 py-4">
        <p class="text-xs font-black uppercase tracking-wider mb-3" style="color:#A87D5C;">Productos</p>
        <div class="flex flex-col gap-3">
            <?php foreach ($detalle as $d): ?>
            <div class="flex items-center justify-between py-2 border-b" style="border-color:#F3D5B5;">
                <div>
                    <p class="text-sm font-black" style="color:#1C0A00;"><?= htmlspecialchars($d['nombre']) ?></p>
                    <p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;">
                        <?= $d['cantidad'] ?> x $<?= number_format($d['precio_unitario'], 0, ',', '.') ?>
                    </p>
                </div>
                <p class="font-black text-sm" style="color:#F97316;">
                    $<?= number_format($d['subtotal'], 0, ',', '.') ?>
                </p>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="flex items-center justify-between pt-4 mt-2">
            <span class="font-black text-base" style="color:#6B4F3A;">TOTAL</span>
            <span class="font-black text-2xl" style="color:#F97316;">
                $<?= number_format($venta['total'], 0, ',', '.') ?>
            </span>
        </div>
    </div>

    <div class="px-6 pb-6 flex gap-3">
        <a href="index.php"
           class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-black no-underline transition-all"
           style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#6B4F3A;"
           onmouseover="this.style.background='#F3D5B5';"
           onmouseout="this.style.background='#FFF7ED';">
            <i class="fas fa-arrow-left"></i> Volver
        </a>

        <?php if ($venta['estado'] !== 'anulada'): ?>
        <a href="../../controllers/VentaController.php?accion=anular&id=<?= $id ?>"
           class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-black no-underline transition-all"
           style="background:#FEE2E2;border:1.5px solid #FCA5A5;color:#DC2626;"
           onmouseover="this.style.background='#FECACA';"
           onmouseout="this.style.background='#FEE2E2';"
           onclick="return confirm('¿Anular esta venta? Esta acción no se puede deshacer.');">
            <i class="fas fa-ban"></i> Anular Venta
        </a>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
