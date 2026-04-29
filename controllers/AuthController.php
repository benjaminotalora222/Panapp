<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';

class AuthController {

    public function login() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/usuarios/login.php");
            exit;
        }

        // ─── Recoger campos ───
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        // ─── Validar que no estén vacíos ───
        if (empty($email) || empty($password)) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Campos incompletos',
                'text'  => 'Debe ingresar su correo y contraseña.'
            ];
            header("Location: ../views/usuarios/login.php");
            exit;
        }

        // ─── Validar formato de correo ───
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Correo inválido',
                'text'  => 'Ingrese un correo electrónico válido.'
            ];
            header("Location: ../views/usuarios/login.php");
            exit;
        }

        // ─── Conectar y buscar usuario ───
        $database     = new Database();
        $db           = $database->conectar();
        $usuarioModel = new Usuario($db);

        $usuario = $usuarioModel->obtenerPorEmail($email);

        // ─── Verificar que el usuario exista ───
        if (!$usuario) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Usuario no encontrado',
                'text'  => 'El correo ingresado no está registrado.'
            ];
            header("Location: ../views/usuarios/login.php");
            exit;
        }

        // ─── Verificar que el usuario esté activo ───
        if (!$usuario['activo']) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Cuenta inactiva',
                'text'  => 'Su cuenta está desactivada. Contacte al administrador.'
            ];
            header("Location: ../views/usuarios/login.php");
            exit;
        }

        // ─── Verificar contraseña ───
        if (!password_verify($password, $usuario['password_hash'])) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Contraseña incorrecta',
                'text'  => 'La contraseña ingresada no es correcta.'
            ];
            header("Location: ../views/usuarios/login.php");
            exit;
        }

        // ─── Crear sesión ───
        session_regenerate_id(true);

        $_SESSION['usuario'] = [
            'id_usuario' => $usuario['id_usuario'],
            'nombres'    => $usuario['nombres'],
            'apellidos'  => $usuario['apellidos'],
            'email'      => $usuario['email'],
            'rol'        => $usuario['rol'],
            'activo'     => $usuario['activo'],
        ];

        // ─── Redirigir según rol ───
        switch (strtoupper($usuario['rol'])) {
            case 'ADMIN':
                header("Location: ../views/dashboard/admin.php");
                exit;

            case 'CAJERO':
                header("Location: ../views/dashboard/cajero.php");
                exit;

            default:
                $_SESSION['alert'] = [
                    'icon'  => 'error',
                    'title' => 'Rol no válido',
                    'text'  => 'No se pudo determinar el acceso del usuario.'
                ];
                header("Location: ../views/usuarios/login.php");
                exit;
        }
    }

    // ─── Cerrar sesión ───
    public function logout() {
        session_unset();
        session_destroy();
        header("Location: ../views/usuarios/login.php");
        exit;
    }
}

$controller = new AuthController();
$accion     = $_GET['accion'] ?? 'login';

if ($accion === 'logout') {
    $controller->logout();
} else {
    $controller->login();
}
?>