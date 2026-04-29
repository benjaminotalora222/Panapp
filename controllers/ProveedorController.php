<?php
session_start();

if (!isset($_SESSION['usuario']) || strtoupper($_SESSION['usuario']['rol']) !== 'ADMIN') {
    header("Location: ../views/proveedores/index.php"); exit;
}

require_once __DIR__ . '/../config/database.php';
$db = (new Database())->conectar();
$accion = $_GET['accion'] ?? '';

switch ($accion) {

    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ../views/proveedores/crear.php"); exit; }
        $nombre    = trim($_POST['nombre']    ?? '');
        $telefono  = trim($_POST['telefono']  ?? '');
        $correo    = trim($_POST['correo']    ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $estado    = trim($_POST['estado']    ?? 'activo');

        if (empty($nombre)) {
            $_SESSION['alert'] = ['icon'=>'warning','title'=>'Campo requerido','text'=>'El nombre del proveedor es obligatorio.'];
            header("Location: ../views/proveedores/crear.php"); exit;
        }
        try {
            $stmt = $db->prepare("INSERT INTO proveedores (nombre, telefono, correo, direccion, estado, fecha_registro)
                                  VALUES (:nombre,:telefono,:correo,:direccion,:estado,NOW())");
            $stmt->execute([':nombre'=>$nombre,':telefono'=>$telefono,':correo'=>$correo,':direccion'=>$direccion,':estado'=>$estado]);
            $_SESSION['alert'] = ['icon'=>'success','title'=>'¡Creado!','text'=>'Proveedor creado correctamente.'];
        } catch (Exception $e) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>$e->getMessage()];
        }
        header("Location: ../views/proveedores/index.php"); exit;

    case 'editar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ../views/proveedores/index.php"); exit; }
        $id        = intval($_GET['id'] ?? 0);
        $nombre    = trim($_POST['nombre']    ?? '');
        $telefono  = trim($_POST['telefono']  ?? '');
        $correo    = trim($_POST['correo']    ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $estado    = trim($_POST['estado']    ?? 'activo');

        if ($id <= 0 || empty($nombre)) {
            $_SESSION['alert'] = ['icon'=>'warning','title'=>'Error','text'=>'Datos inválidos.'];
            header("Location: ../views/proveedores/editar.php?id=$id"); exit;
        }
        try {
            $stmt = $db->prepare("UPDATE proveedores SET nombre=:nombre,telefono=:telefono,correo=:correo,
                                  direccion=:direccion,estado=:estado WHERE id_proveedor=:id");
            $stmt->execute([':nombre'=>$nombre,':telefono'=>$telefono,':correo'=>$correo,
                            ':direccion'=>$direccion,':estado'=>$estado,':id'=>$id]);
            $_SESSION['alert'] = ['icon'=>'success','title'=>'¡Actualizado!','text'=>'Proveedor actualizado correctamente.'];
        } catch (Exception $e) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>$e->getMessage()];
        }
        header("Location: ../views/proveedores/index.php"); exit;

    case 'eliminar':
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) { $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'Proveedor inválido.']; header("Location: ../views/proveedores/index.php"); exit; }
        try {
            $db->prepare("DELETE FROM proveedores WHERE id_proveedor=:id")->execute([':id'=>$id]);
            $_SESSION['alert'] = ['icon'=>'success','title'=>'¡Eliminado!','text'=>'Proveedor eliminado correctamente.'];
        } catch (Exception $e) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'No se puede eliminar','text'=>'Este proveedor tiene insumos asociados.'];
        }
        header("Location: ../views/proveedores/index.php"); exit;

    default:
        header("Location: ../views/proveedores/index.php"); exit;
}
?>
