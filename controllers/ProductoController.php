<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../views/usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db       = $database->conectar();

$accion = $_GET['accion'] ?? '';

switch ($accion) {

    // ════════════════════════════════
    // CREAR — todos los roles
    // ════════════════════════════════
    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/productos/crear.php"); exit;
        }

        $nombre      = trim($_POST['nombre']        ?? '');
        $descripcion = trim($_POST['descripcion']   ?? '');
        $categoria   = trim($_POST['categoria']     ?? '');
        $precio      = floatval($_POST['precio']    ?? 0);
        $unidad      = trim($_POST['unidad_medida'] ?? '');

        if (empty($nombre) || empty($categoria) || $precio <= 0 || empty($unidad)) {
            $_SESSION['alert'] = ['icon' => 'warning', 'title' => 'Campos incompletos', 'text' => 'Completa todos los campos obligatorios.'];
            header("Location: ../views/productos/crear.php"); exit;
        }

        try {
            $sql = "INSERT INTO productos (nombre, descripcion, categoria, precio, unidad_medida)
                    VALUES (:nombre, :descripcion, :categoria, :precio, :unidad_medida)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':nombre',        $nombre);
            $stmt->bindParam(':descripcion',   $descripcion);
            $stmt->bindParam(':categoria',     $categoria);
            $stmt->bindParam(':precio',        $precio);
            $stmt->bindParam(':unidad_medida', $unidad);
            $stmt->execute();

            $_SESSION['alert'] = ['icon' => 'success', 'title' => '¡Creado!', 'text' => 'Producto creado correctamente.'];
            header("Location: ../views/productos/index.php"); exit;

        } catch (Exception $e) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => $e->getMessage()];
            header("Location: ../views/productos/crear.php"); exit;
        }

    // ════════════════════════════════
    // EDITAR — todos los roles
    // ════════════════════════════════
    case 'editar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/productos/index.php"); exit;
        }

        $id          = intval($_GET['id']           ?? 0);
        $nombre      = trim($_POST['nombre']        ?? '');
        $descripcion = trim($_POST['descripcion']   ?? '');
        $categoria   = trim($_POST['categoria']     ?? '');
        $precio      = floatval($_POST['precio']    ?? 0);
        $unidad      = trim($_POST['unidad_medida'] ?? '');

        if ($id <= 0 || empty($nombre) || empty($categoria) || $precio <= 0 || empty($unidad)) {
            $_SESSION['alert'] = ['icon' => 'warning', 'title' => 'Campos incompletos', 'text' => 'Completa todos los campos obligatorios.'];
            header("Location: ../views/productos/editar.php?id=$id"); exit;
        }

        try {
            $sql = "UPDATE productos
                    SET nombre = :nombre, descripcion = :descripcion, categoria = :categoria,
                        precio = :precio, unidad_medida = :unidad_medida
                    WHERE id_producto = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':nombre',        $nombre);
            $stmt->bindParam(':descripcion',   $descripcion);
            $stmt->bindParam(':categoria',     $categoria);
            $stmt->bindParam(':precio',        $precio);
            $stmt->bindParam(':unidad_medida', $unidad);
            $stmt->bindParam(':id',            $id, PDO::PARAM_INT);
            $stmt->execute();

            $_SESSION['alert'] = ['icon' => 'success', 'title' => '¡Actualizado!', 'text' => 'Producto actualizado correctamente.'];
            header("Location: ../views/productos/index.php"); exit;

        } catch (Exception $e) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => $e->getMessage()];
            header("Location: ../views/productos/editar.php?id=$id"); exit;
        }

    // ════════════════════════════════
    // ELIMINAR — solo ADMIN
    // ════════════════════════════════
    case 'eliminar':
        if (strtoupper($_SESSION['usuario']['rol']) !== 'ADMIN') {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Sin permiso', 'text' => 'Solo el administrador puede eliminar productos.'];
            header("Location: ../views/productos/index.php"); exit;
        }

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => 'Producto no válido.'];
            header("Location: ../views/productos/index.php"); exit;
        }

        try {
            $stmt = $db->prepare("DELETE FROM productos WHERE id_producto = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $_SESSION['alert'] = ['icon' => 'success', 'title' => '¡Eliminado!', 'text' => 'Producto eliminado correctamente.'];
        } catch (Exception $e) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'No se puede eliminar', 'text' => 'El producto está asociado a ventas existentes.'];
        }

        header("Location: ../views/productos/index.php"); exit;

    default:
        header("Location: ../views/productos/index.php"); exit;
}
?>
