<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['error' => 'No autorizado']); exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { echo json_encode(['error' => 'ID inválido']); exit; }

require_once __DIR__ . '/../config/database.php';
$db = (new Database())->conectar();

$stmtV = $db->prepare("
    SELECT v.id_venta, v.fecha, v.total, v.estado, v.id_usuario,
           mp.nombre AS metodo_pago,
           u.nombres, u.apellidos
    FROM ventas v
    LEFT JOIN metodos_pago mp ON v.id_metodo_pago = mp.id_metodo_pago
    LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
    WHERE v.id_venta = :id
");
$stmtV->bindParam(':id', $id, PDO::PARAM_INT);
$stmtV->execute();
$venta = $stmtV->fetch(PDO::FETCH_ASSOC);

if (!$venta) { echo json_encode(['error' => 'Venta no encontrada']); exit; }

// Todos los usuarios autenticados pueden ver el detalle de cualquier venta

$stmtD = $db->prepare("
    SELECT dv.cantidad, dv.precio_unitario, dv.subtotal, p.nombre
    FROM detalle_venta dv
    LEFT JOIN productos p ON dv.id_producto = p.id_producto
    WHERE dv.id_venta = :id
");
$stmtD->bindParam(':id', $id, PDO::PARAM_INT);
$stmtD->execute();
$detalle = $stmtD->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'venta'   => $venta,
    'detalle' => $detalle
]);
