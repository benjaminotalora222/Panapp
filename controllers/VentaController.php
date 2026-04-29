<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../views/usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db       = $database->conectar();

$accion = $_GET['accion'] ?? 'registrar';

// ════════════════════════════════
// REGISTRAR VENTA
// ════════════════════════════════
if ($accion === 'registrar') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../views/ventas/crear.php"); exit;
    }

    $id_usuario = intval($_SESSION['usuario']['id_usuario']);
    $id_metodo  = intval($_POST['id_metodo_pago'] ?? 0);
    $total      = floatval($_POST['total']         ?? 0);
    $itemsJson  = $_POST['items']                  ?? '[]';

    if ($id_metodo <= 0 || $total <= 0) {
        $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'Datos de venta inválidos.'];
        header("Location: ../views/ventas/crear.php"); exit;
    }

    $items = json_decode($itemsJson, true);
    if (empty($items) || !is_array($items)) {
        $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'No hay productos en la venta.'];
        header("Location: ../views/ventas/crear.php"); exit;
    }

    try {
        $db->beginTransaction();

        $stmtV = $db->prepare("INSERT INTO ventas (fecha, total, id_usuario, id_metodo_pago, estado)
                                VALUES (NOW(), :total, :id_usuario, :id_metodo_pago, 'completada')");
        $stmtV->bindParam(':total',          $total,      PDO::PARAM_STR);
        $stmtV->bindParam(':id_usuario',     $id_usuario, PDO::PARAM_INT);
        $stmtV->bindParam(':id_metodo_pago', $id_metodo,  PDO::PARAM_INT);
        $stmtV->execute();
        $id_venta = $db->lastInsertId();

        $stmtD = $db->prepare("INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario, subtotal)
                                VALUES (:id_venta, :id_producto, :cantidad, :precio_unitario, :subtotal)");

        foreach ($items as $item) {
            $id_producto     = intval($item['id_producto']);
            $cantidad        = intval($item['cantidad']);
            $precio_unitario = floatval($item['precio_unitario']);
            $subtotal        = floatval($item['subtotal']);
            if ($id_producto <= 0 || $cantidad <= 0) continue;
            $stmtD->execute([
                ':id_venta'        => $id_venta,
                ':id_producto'     => $id_producto,
                ':cantidad'        => $cantidad,
                ':precio_unitario' => $precio_unitario,
                ':subtotal'        => $subtotal,
            ]);
        }

        $db->commit();
        $_SESSION['alert'] = ['icon'=>'success','title'=>'¡Venta registrada!',
            'text' => 'Venta #' . str_pad($id_venta, 4, '0', STR_PAD_LEFT) . ' registrada correctamente.'];
        header("Location: ../views/dashboard/" . strtolower($_SESSION['usuario']['rol']) . ".php"); exit;

    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>$e->getMessage()];
        header("Location: ../views/ventas/crear.php"); exit;
    }
}

// ════════════════════════════════
// ANULAR VENTA
// ════════════════════════════════
if ($accion === 'anular') {
    $id  = intval($_GET['id'] ?? 0);
    $rol = strtoupper($_SESSION['usuario']['rol']);

    if ($id <= 0) {
        $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'Venta inválida.'];
        header("Location: ../views/ventas/index.php"); exit;
    }

    $stmtCheck = $db->prepare("SELECT id_venta, estado, id_usuario FROM ventas WHERE id_venta = :id");
    $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtCheck->execute();
    $venta = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'Venta no encontrada.'];
        header("Location: ../views/ventas/index.php"); exit;
    }

    if ($rol !== 'ADMIN' && $venta['id_usuario'] != $_SESSION['usuario']['id_usuario']) {
        $_SESSION['alert'] = ['icon'=>'error','title'=>'Sin permiso','text'=>'No puedes anular ventas de otro cajero.'];
        header("Location: ../views/ventas/index.php"); exit;
    }

    if ($venta['estado'] === 'anulada') {
        $_SESSION['alert'] = ['icon'=>'warning','title'=>'Ya anulada','text'=>'Esta venta ya fue anulada anteriormente.'];
        header("Location: ../views/ventas/detalle.php?id=$id"); exit;
    }

    try {
        $stmt = $db->prepare("UPDATE ventas SET estado = 'anulada' WHERE id_venta = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $_SESSION['alert'] = ['icon'=>'success','title'=>'Venta anulada',
            'text' => 'La venta #' . str_pad($id, 4, '0', STR_PAD_LEFT) . ' fue anulada correctamente.'];
    } catch (Exception $e) {
        $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>$e->getMessage()];
    }

    header("Location: ../views/ventas/index.php"); exit;
}

header("Location: ../views/ventas/index.php"); exit;
?>
