<?php
session_start();

if (!isset($_SESSION['usuario']) || strtoupper($_SESSION['usuario']['rol']) !== 'ADMIN') {
    header("Location: ../views/inventario/index.php"); exit;
}

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db       = $database->conectar();

$accion = $_GET['accion'] ?? '';

switch ($accion) {

    // ════════════════════════════════
    // AJUSTAR STOCK
    // ════════════════════════════════
    case 'ajustar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/inventario/ajuste.php"); exit;
        }

        $id_insumo = intval($_POST['id_insumo'] ?? 0);
        $tipo      = trim($_POST['tipo']         ?? '');
        $cantidad  = intval($_POST['cantidad']   ?? 0);
        $motivo    = trim($_POST['motivo']       ?? '');

        if ($id_insumo <= 0 || !in_array($tipo, ['entrada', 'salida']) || $cantidad <= 0) {
            $_SESSION['alert'] = ['icon' => 'warning', 'title' => 'Datos inválidos', 'text' => 'Completa todos los campos correctamente.'];
            header("Location: ../views/inventario/ajuste.php?id=$id_insumo"); exit;
        }

        try {
            $db->beginTransaction();

            // Verificar si ya existe registro en inventario_insumos
            $stmtCheck = $db->prepare("SELECT id_inventario, cantidad_actual FROM inventario_insumos WHERE id_insumo = :id");
            $stmtCheck->bindParam(':id', $id_insumo, PDO::PARAM_INT);
            $stmtCheck->execute();
            $registro = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($tipo === 'salida' && $registro && $registro['cantidad_actual'] < $cantidad) {
                $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Stock insuficiente', 'text' => 'No hay suficiente stock para registrar la salida.'];
                header("Location: ../views/inventario/ajuste.php?id=$id_insumo"); exit;
            }

            if ($registro) {
                // Actualizar stock existente
                $nuevaCantidad = ($tipo === 'entrada')
                    ? $registro['cantidad_actual'] + $cantidad
                    : $registro['cantidad_actual'] - $cantidad;

                $stmtUpd = $db->prepare("UPDATE inventario_insumos
                                         SET cantidad_actual = :cantidad, fecha_actualizacion = NOW()
                                         WHERE id_insumo = :id");
                $stmtUpd->bindParam(':cantidad', $nuevaCantidad, PDO::PARAM_INT);
                $stmtUpd->bindParam(':id',       $id_insumo,    PDO::PARAM_INT);
                $stmtUpd->execute();
            } else {
                // Crear nuevo registro
                if ($tipo === 'salida') {
                    $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Sin stock', 'text' => 'Este insumo no tiene stock registrado. Primero registra una entrada.'];
                    header("Location: ../views/inventario/ajuste.php?id=$id_insumo"); exit;
                }
                $stmtIns = $db->prepare("INSERT INTO inventario_insumos (id_insumo, cantidad_actual, fecha_actualizacion)
                                         VALUES (:id, :cantidad, NOW())");
                $stmtIns->bindParam(':id',       $id_insumo, PDO::PARAM_INT);
                $stmtIns->bindParam(':cantidad', $cantidad,  PDO::PARAM_INT);
                $stmtIns->execute();
            }

            // Registrar movimiento
            $stmtMov = $db->prepare("INSERT INTO movimientos_inventario (id_insumo, tipo, cantidad, motivo, fecha)
                                     VALUES (:id_insumo, :tipo, :cantidad, :motivo, NOW())");
            $stmtMov->bindParam(':id_insumo', $id_insumo, PDO::PARAM_INT);
            $stmtMov->bindParam(':tipo',      $tipo);
            $stmtMov->bindParam(':cantidad',  $cantidad,  PDO::PARAM_INT);
            $stmtMov->bindParam(':motivo',    $motivo);
            $stmtMov->execute();

            $db->commit();

            $msg = ($tipo === 'entrada') ? "Se agregaron $cantidad unidades al stock." : "Se restaron $cantidad unidades del stock.";
            $_SESSION['alert'] = ['icon' => 'success', 'title' => '¡Ajuste registrado!', 'text' => $msg];
            header("Location: ../views/inventario/index.php"); exit;

        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => $e->getMessage()];
            header("Location: ../views/inventario/ajuste.php?id=$id_insumo"); exit;
        }

    default:
        header("Location: ../views/inventario/index.php"); exit;
}
?>
