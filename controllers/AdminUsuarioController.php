<?php
session_start();

if (!isset($_SESSION['usuario']) || strtoupper($_SESSION['usuario']['rol']) !== 'ADMIN') {
    header("Location: ../views/usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';

$database     = new Database();
$db           = $database->conectar();
$usuarioModel = new Usuario($db);

$accion = $_GET['accion'] ?? '';

switch ($accion) {

    // ════════════════════════════════
    // CREAR
    // ════════════════════════════════
    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/dashboard/usuario_crear.php");
            exit;
        }

        $nombres            = trim($_POST['nombres']            ?? '');
        $apellidos          = trim($_POST['apellidos']          ?? '');
        $email              = trim($_POST['email']              ?? '');
        $password           = trim($_POST['password']           ?? '');
        $confirmar_password = trim($_POST['confirmar_password'] ?? '');
        $rol                = trim($_POST['rol']                ?? '');

        if (empty($nombres) || empty($apellidos) || empty($email) || empty($password) || empty($confirmar_password) || empty($rol)) {
            $_SESSION['alert'] = ['icon' => 'warning', 'title' => 'Campos incompletos', 'text' => 'Completa todos los campos.'];
            header("Location: ../views/dashboard/usuario_crear.php");
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Correo inválido', 'text' => 'Ingresa un correo válido.'];
            header("Location: ../views/dashboard/usuario_crear.php");
            exit;
        }
        if ($password !== $confirmar_password) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Contraseñas distintas', 'text' => 'Las contraseñas no coinciden.'];
            header("Location: ../views/dashboard/usuario_crear.php");
            exit;
        }
        if (strlen($password) < 6) {
            $_SESSION['alert'] = ['icon' => 'warning', 'title' => 'Contraseña corta', 'text' => 'Mínimo 6 caracteres.'];
            header("Location: ../views/dashboard/usuario_crear.php");
            exit;
        }
        if (!in_array(strtoupper($rol), ['ADMIN', 'CAJERO'])) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Rol inválido', 'text' => 'Selecciona un rol válido.'];
            header("Location: ../views/dashboard/usuario_crear.php");
            exit;
        }
        if ($usuarioModel->existeCorreo($email)) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Correo registrado', 'text' => 'Este correo ya está en uso.'];
            header("Location: ../views/dashboard/usuario_crear.php");
            exit;
        }

        $resultado = $usuarioModel->registrar([
            'nombres'       => $nombres,
            'apellidos'     => $apellidos,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'rol'           => strtoupper($rol),
        ]);

        $_SESSION['alert'] = $resultado === true
            ? ['icon' => 'success', 'title' => '¡Creado!',  'text' => 'El usuario fue creado correctamente.']
            : ['icon' => 'error',   'title' => 'Error',      'text' => $resultado];

        header("Location: ../views/dashboard/admin.php");
        exit;

    // ════════════════════════════════
    // EDITAR
    // ════════════════════════════════
    case 'editar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/dashboard/admin.php");
            exit;
        }

        $id                 = intval($_GET['id']                ?? 0);
        $nombres            = trim($_POST['nombres']            ?? '');
        $apellidos          = trim($_POST['apellidos']          ?? '');
        $email              = trim($_POST['email']              ?? '');
        $password           = trim($_POST['password']           ?? '');
        $confirmar_password = trim($_POST['confirmar_password'] ?? '');
        $rol                = trim($_POST['rol']                ?? '');

        if ($id <= 0) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => 'Usuario no válido.'];
            header("Location: ../views/dashboard/admin.php");
            exit;
        }
        if (empty($nombres) || empty($apellidos) || empty($email) || empty($rol)) {
            $_SESSION['alert'] = ['icon' => 'warning', 'title' => 'Campos incompletos', 'text' => 'Completa todos los campos obligatorios.'];
            header("Location: ../views/dashboard/usuario_editar.php?id=$id");
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Correo inválido', 'text' => 'Ingresa un correo válido.'];
            header("Location: ../views/dashboard/usuario_editar.php?id=$id");
            exit;
        }
        if (!in_array(strtoupper($rol), ['ADMIN', 'CAJERO'])) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Rol inválido', 'text' => 'Selecciona un rol válido.'];
            header("Location: ../views/dashboard/usuario_editar.php?id=$id");
            exit;
        }

        // Verificar correo duplicado solo si cambió
        $usuarioActual = $usuarioModel->obtenerPorId($id);
        if ($email !== $usuarioActual['email'] && $usuarioModel->existeCorreo($email)) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Correo registrado', 'text' => 'Este correo ya está en uso por otro usuario.'];
            header("Location: ../views/dashboard/usuario_editar.php?id=$id");
            exit;
        }

        // Contraseña opcional — solo validar si se escribió algo
        $datos = [
            'nombres'   => $nombres,
            'apellidos' => $apellidos,
            'email'     => $email,
            'rol'       => strtoupper($rol),
        ];

        if (!empty($password)) {
            if ($password !== $confirmar_password) {
                $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Contraseñas distintas', 'text' => 'Las contraseñas no coinciden.'];
                header("Location: ../views/dashboard/usuario_editar.php?id=$id");
                exit;
            }
            if (strlen($password) < 6) {
                $_SESSION['alert'] = ['icon' => 'warning', 'title' => 'Contraseña corta', 'text' => 'Mínimo 6 caracteres.'];
                header("Location: ../views/dashboard/usuario_editar.php?id=$id");
                exit;
            }
            $datos['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $resultado = $usuarioModel->actualizar($id, $datos);

        $_SESSION['alert'] = $resultado === true
            ? ['icon' => 'success', 'title' => '¡Actualizado!', 'text' => 'El usuario fue actualizado correctamente.']
            : ['icon' => 'error',   'title' => 'Error',          'text' => $resultado];

        header("Location: ../views/dashboard/admin.php");
        exit;

    // ════════════════════════════════
    // TOGGLE ESTADO
    // ════════════════════════════════
    case 'toggleEstado':
        $id     = intval($_GET['id']     ?? 0);
        $estado = intval($_GET['estado'] ?? 0);

        if ($id <= 0) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => 'Usuario no válido.'];
            header("Location: ../views/dashboard/admin.php");
            exit;
        }

        $nuevoEstado = ($estado == 1) ? 0 : 1;
        $resultado   = $usuarioModel->cambiarEstado($id, $nuevoEstado);
        $msg         = ($nuevoEstado == 1) ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.';

        $_SESSION['alert'] = $resultado === true
            ? ['icon' => 'success', 'title' => '¡Listo!', 'text' => $msg]
            : ['icon' => 'error',   'title' => 'Error',   'text' => $resultado];

        header("Location: ../views/dashboard/admin.php");
        exit;

    // ════════════════════════════════
    // ELIMINAR
    // ════════════════════════════════
    case 'eliminar':
        $id = intval($_GET['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => 'Usuario no válido.'];
            header("Location: ../views/dashboard/admin.php");
            exit;
        }
        if ($id === intval($_SESSION['usuario']['id_usuario'])) {
            $_SESSION['alert'] = ['icon' => 'warning', 'title' => 'Acción no permitida', 'text' => 'No puedes eliminar tu propio usuario.'];
            header("Location: ../views/dashboard/admin.php");
            exit;
        }

        $resultado = $usuarioModel->eliminar($id);

        $_SESSION['alert'] = $resultado === true
            ? ['icon' => 'success', 'title' => '¡Eliminado!', 'text' => 'El usuario fue eliminado correctamente.']
            : ['icon' => 'error',   'title' => 'Error',        'text' => $resultado];

        header("Location: ../views/dashboard/admin.php");
        exit;

    default:
        header("Location: ../views/dashboard/admin.php");
        exit;
}
?>
