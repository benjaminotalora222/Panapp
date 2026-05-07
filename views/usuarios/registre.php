<?php
// Registro público deshabilitado por seguridad.
// Los usuarios solo pueden ser creados por un Administrador.
session_start();
$_SESSION['alert'] = [
    'icon'  => 'warning',
    'title' => 'Registro no disponible',
    'text'  => 'El registro público está deshabilitado. Contacta al administrador para obtener acceso.'
];
header("Location: login.php");
exit;
