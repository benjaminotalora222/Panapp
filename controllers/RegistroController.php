<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/usuarios/registre.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/usuario.php';

$nombres            = trim($_POST['nombres']            ?? '');
$apellidos          = trim($_POST['apellidos']          ?? '');
$email              = trim($_POST['email']              ?? '');
$password           = trim($_POST['password']           ?? '');
$confirmar_password = trim($_POST['confirmar_password'] ?? '');
$rol                = 'CAJERO'; // Siempre cajero en registro público

// Validaciones
if (empty($nombres) || empty($apellidos) || empty($email) || empty($password)) {
    $_SESSION['alert'] = ['icon'=>'error','title'=>'Campos incompletos','text'=>'Completa todos los campos.'];
    header("Location: ../views/usuarios/registre.php"); exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['alert'] = ['icon'=>'error','title'=>'Correo inválido','text'=>'Ingresa un correo electrónico válido.'];
    header("Location: ../views/usuarios/registre.php"); exit;
}

if ($password !== $confirmar_password) {
    $_SESSION['alert'] = ['icon'=>'error','title'=>'Contraseñas distintas','text'=>'Las contraseñas no coinciden.'];
    header("Location: ../views/usuarios/registre.php"); exit;
}

if (strlen($password) < 6) {
    $_SESSION['alert'] = ['icon'=>'error','title'=>'Contraseña corta','text'=>'La contraseña debe tener al menos 6 caracteres.'];
    header("Location: ../views/usuarios/registre.php"); exit;
}

$db           = (new Database())->conectar();
$usuarioModel = new Usuario($db);

// Verificar si el correo ya existe
if ($usuarioModel->existeCorreo($email)) {
    $_SESSION['alert'] = ['icon'=>'error','title'=>'Correo registrado','text'=>'Este correo ya está en uso. Intenta con otro.'];
    header("Location: ../views/usuarios/registre.php"); exit;
}

// Registrar usuario como CAJERO
$resultado = $usuarioModel->registrar([
    'nombres'       => $nombres,
    'apellidos'     => $apellidos,
    'email'         => $email,
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    'rol'           => 'CAJERO',
]);

if ($resultado === true) {
    $_SESSION['alert'] = [
        'icon'  => 'success',
        'title' => '¡Cuenta creada!',
        'text'  => 'Tu cuenta de cajero fue creada correctamente. Ya puedes iniciar sesión.'
    ];
    header("Location: ../views/usuarios/login.php"); exit;
} else {
    $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>$resultado];
    header("Location: ../views/usuarios/registre.php"); exit;
}
