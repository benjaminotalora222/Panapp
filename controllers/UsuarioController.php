<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController {

    public function registrar() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        // ─── Recoger y limpiar campos ───
        $nombres            = trim($_POST['nombres']            ?? '');
        $apellidos          = trim($_POST['apellidos']          ?? '');
        $email              = trim($_POST['email']              ?? '');
        $password           = trim($_POST['password']           ?? '');
        $confirmar_password = trim($_POST['confirmar_password'] ?? '');
        $rol                = trim($_POST['rol']                ?? '');

        // ─── Validar campos obligatorios ───
        if (empty($nombres) || empty($apellidos) || empty($email) || empty($password) || empty($confirmar_password) || empty($rol)) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Campos incompletos',
                'text'  => 'Debe completar todos los campos obligatorios.'
            ];
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        // ─── Validar formato de correo ───
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Correo inválido',
                'text'  => 'Ingrese un correo electrónico válido.'
            ];
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        // ─── Validar que las contraseñas coincidan ───
        if ($password !== $confirmar_password) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Contraseñas distintas',
                'text'  => 'Las contraseñas no coinciden.'
            ];
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        // ─── Validar longitud mínima de contraseña ───
        if (strlen($password) < 6) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Contraseña muy corta',
                'text'  => 'La contraseña debe tener al menos 6 caracteres.'
            ];
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        // ─── Validar rol permitido ───
        $rolesPermitidos = ['ADMIN', 'CAJERO'];
        if (!in_array(strtoupper($rol), $rolesPermitidos)) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Rol inválido',
                'text'  => 'El rol seleccionado no es válido.'
            ];
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        // ─── Conectar a la base de datos ───
        $database = new Database();
        $db       = $database->conectar();
        $usuario  = new Usuario($db);

        // ─── Verificar correo duplicado ───
        if ($usuario->existeCorreo($email)) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Correo registrado',
                'text'  => 'Este correo ya está registrado en el sistema.'
            ];
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        // ─── Armar datos y registrar ───
        $datos = [
            'nombres'       => $nombres,
            'apellidos'     => $apellidos,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'rol'           => strtoupper($rol),
        ];

        $resultado = $usuario->registrar($datos);

        if ($resultado === true) {
            $_SESSION['alert'] = [
                'icon'     => 'success',
                'title'    => '¡Registro exitoso!',
                'text'     => 'El usuario fue creado correctamente.',
                'redirect' => 'login.php'
            ];
            header("Location: ../views/usuarios/registre.php");
            exit;

        } else {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error al registrar',
                'text'  => $resultado
            ];
            header("Location: ../views/usuarios/registre.php");
            exit;
        }
    }
}

$controller = new UsuarioController();
$controller->registrar();
?>