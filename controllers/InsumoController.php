<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../views/usuarios/login.php"); exit;
}

require_once __DIR__ . '/../config/database.php';

$db     = (new Database())->conectar();
$accion = $_GET['accion'] ?? '';
$rol    = strtoupper($_SESSION['usuario']['rol']);

// Solo admin puede crear, editar y eliminar
if ($accion !== '' && $rol !== 'ADMIN') {
    $_SESSION['alert'] = ['icon'=>'error','title'=>'Sin permiso','text'=>'Solo el administrador puede gestionar insumos.'];
    header("Location: ../views/insumos/index.php"); exit;
}

switch ($accion) {

    // ════════════════════════════════
    // CREAR
    // ════════════════════════════════
    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/insumos/crear.php"); exit;
        }

        $nombre       = trim($_POST['nombre']        ?? '');
        $descripcion  = trim($_POST['descripcion']   ?? '');
        $unidad       = trim($_POST['unidad_medida'] ?? '');
        $id_proveedor = intval($_POST['id_proveedor'] ?? 0) ?: null;

        if (empty($nombre) || empty($unidad)) {
            $_SESSION['alert'] = ['icon'=>'warning','title'=>'Campos incompletos',
                'text'=>'El nombre y la unidad de medida son obligatorios.'];
            header("Location: ../views/insumos/crear.php"); exit;
        }

        try {
            $stmt = $db->prepare("INSERT INTO insumos (nombre, descripcion, unidad_medida, id_proveedor)
                                  VALUES (:nombre, :descripcion, :unidad_medida, :id_proveedor)");
            $stmt->bindParam(':nombre',       $nombre);
            $stmt->bindParam(':descripcion',  $descripcion);
            $stmt->bindParam(':unidad_medida',$unidad);
            $stmt->bindParam(':id_proveedor', $id_proveedor, PDO::PARAM_INT);
            $stmt->execute();

            // Crear registro en inventario_insumos con cantidad 0
            $id_insumo = $db->lastInsertId();
            $stmtInv = $db->prepare("INSERT INTO inventario_insumos (id_insumo, cantidad_actual, fecha_actualizacion)
                                     VALUES (:id_insumo, 0, NOW())");
            $stmtInv->bindParam(':id_insumo', $id_insumo, PDO::PARAM_INT);
            $stmtInv->execute();

            $_SESSION['alert'] = ['icon'=>'success','title'=>'¡Creado!',
                'text'=>'Insumo creado y agregado al inventario con stock 0.'];
            header("Location: ../views/insumos/index.php"); exit;

        } catch (Exception $e) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>$e->getMessage()];
            header("Location: ../views/insumos/crear.php"); exit;
        }

    // ════════════════════════════════
    // EDITAR
    // ════════════════════════════════
    case 'editar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/insumos/index.php"); exit;
        }

        $id           = intval($_GET['id']           ?? 0);
        $nombre       = trim($_POST['nombre']        ?? '');
        $descripcion  = trim($_POST['descripcion']   ?? '');
        $unidad       = trim($_POST['unidad_medida'] ?? '');
        $id_proveedor = intval($_POST['id_proveedor'] ?? 0) ?: null;

        if ($id <= 0 || empty($nombre) || empty($unidad)) {
            $_SESSION['alert'] = ['icon'=>'warning','title'=>'Campos incompletos',
                'text'=>'El nombre y la unidad de medida son obligatorios.'];
            header("Location: ../views/insumos/editar.php?id=$id"); exit;
        }

        try {
            $stmt = $db->prepare("UPDATE insumos
                                  SET nombre = :nombre, descripcion = :descripcion,
                                      unidad_medida = :unidad_medida, id_proveedor = :id_proveedor
                                  WHERE id_insumo = :id");
            $stmt->bindParam(':nombre',       $nombre);
            $stmt->bindParam(':descripcion',  $descripcion);
            $stmt->bindParam(':unidad_medida',$unidad);
            $stmt->bindParam(':id_proveedor', $id_proveedor, PDO::PARAM_INT);
            $stmt->bindParam(':id',           $id, PDO::PARAM_INT);
            $stmt->execute();

            $_SESSION['alert'] = ['icon'=>'success','title'=>'¡Actualizado!',
                'text'=>'Insumo actualizado correctamente.'];
            header("Location: ../views/insumos/index.php"); exit;

        } catch (Exception $e) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>$e->getMessage()];
            header("Location: ../views/insumos/editar.php?id=$id"); exit;
        }

    // ════════════════════════════════
    // ELIMINAR
    // ════════════════════════════════
    case 'eliminar':
        $id = intval($_GET['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'Insumo no válido.'];
            header("Location: ../views/insumos/index.php"); exit;
        }

        try {
            $db->beginTransaction();

            // Eliminar movimientos de inventario
            $db->prepare("DELETE FROM movimientos_inventario WHERE id_insumo = :id")
               ->execute([':id' => $id]);

            // Eliminar registro de inventario
            $db->prepare("DELETE FROM inventario_insumos WHERE id_insumo = :id")
               ->execute([':id' => $id]);

            // Eliminar relación producto_insumo
            $db->prepare("DELETE FROM producto_insumo WHERE id_insumo = :id")
               ->execute([':id' => $id]);

            // Eliminar insumo
            $db->prepare("DELETE FROM insumos WHERE id_insumo = :id")
               ->execute([':id' => $id]);

            $db->commit();
            $_SESSION['alert'] = ['icon'=>'success','title'=>'¡Eliminado!',
                'text'=>'Insumo eliminado correctamente.'];

        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error',
                'text'=>'No se pudo eliminar el insumo: ' . $e->getMessage()];
        }

        header("Location: ../views/insumos/index.php"); exit;

    default:
        header("Location: ../views/insumos/index.php"); exit;
}
?>
