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

        // ─── Subida de imagen ───
        $imagen = null;
        if (!empty($_FILES['imagen']['name'])) {
            $ext       = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array($ext, $permitidos)) {
                $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Formato inválido', 'text' => 'Solo se permiten imágenes JPG, PNG, WEBP o GIF.'];
                header("Location: ../views/productos/crear.php"); exit;
            }
            if ($_FILES['imagen']['size'] > 2 * 1024 * 1024) {
                $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Imagen muy grande', 'text' => 'La imagen no debe superar 2 MB.'];
                header("Location: ../views/productos/crear.php"); exit;
            }
            $nombreArchivo = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destino = __DIR__ . '/../public/uploads/productos/' . $nombreArchivo;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
                $imagen = $nombreArchivo;
            }
        }

        try {
            $sql = "INSERT INTO productos (nombre, descripcion, categoria, precio, unidad_medida, imagen)
                    VALUES (:nombre, :descripcion, :categoria, :precio, :unidad_medida, :imagen)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':nombre',        $nombre);
            $stmt->bindParam(':descripcion',   $descripcion);
            $stmt->bindParam(':categoria',     $categoria);
            $stmt->bindParam(':precio',        $precio);
            $stmt->bindParam(':unidad_medida', $unidad);
            $stmt->bindParam(':imagen',        $imagen);
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

        // ─── Subida de imagen ───
        $imagenActual = trim($_POST['imagen_actual'] ?? '');
        $imagen       = $imagenActual; // mantener la existente por defecto

        if (!empty($_FILES['imagen']['name'])) {
            $ext        = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array($ext, $permitidos)) {
                $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Formato inválido', 'text' => 'Solo se permiten imágenes JPG, PNG, WEBP o GIF.'];
                header("Location: ../views/productos/editar.php?id=$id"); exit;
            }
            if ($_FILES['imagen']['size'] > 2 * 1024 * 1024) {
                $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Imagen muy grande', 'text' => 'La imagen no debe superar 2 MB.'];
                header("Location: ../views/productos/editar.php?id=$id"); exit;
            }
            $nombreArchivo = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destino = __DIR__ . '/../public/uploads/productos/' . $nombreArchivo;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
                // Borrar imagen anterior si existe
                if (!empty($imagenActual)) {
                    $anterior = __DIR__ . '/../public/uploads/productos/' . $imagenActual;
                    if (file_exists($anterior)) { unlink($anterior); }
                }
                $imagen = $nombreArchivo;
            }
        }

        try {
            $sql = "UPDATE productos
                    SET nombre = :nombre, descripcion = :descripcion, categoria = :categoria,
                        precio = :precio, unidad_medida = :unidad_medida, imagen = :imagen
                    WHERE id_producto = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':nombre',        $nombre);
            $stmt->bindParam(':descripcion',   $descripcion);
            $stmt->bindParam(':categoria',     $categoria);
            $stmt->bindParam(':precio',        $precio);
            $stmt->bindParam(':unidad_medida', $unidad);
            $stmt->bindParam(':imagen',        $imagen);
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
            // Obtener imagen antes de eliminar para borrarla del disco
            $stmtImg = $db->prepare("SELECT imagen FROM productos WHERE id_producto = :id");
            $stmtImg->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtImg->execute();
            $imgRow = $stmtImg->fetch(PDO::FETCH_ASSOC);

            $stmt = $db->prepare("DELETE FROM productos WHERE id_producto = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Borrar archivo de imagen si existe
            if (!empty($imgRow['imagen'])) {
                $archivo = __DIR__ . '/../public/uploads/productos/' . $imgRow['imagen'];
                if (file_exists($archivo)) { unlink($archivo); }
            }

            $_SESSION['alert'] = ['icon' => 'success', 'title' => '¡Eliminado!', 'text' => 'Producto eliminado correctamente.'];
        } catch (Exception $e) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error al eliminar', 'text' => $e->getMessage()];
        }

        header("Location: ../views/productos/index.php"); exit;

    default:
        header("Location: ../views/productos/index.php"); exit;
}
?>
