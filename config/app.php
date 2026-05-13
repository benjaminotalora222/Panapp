<?php
/**
 * Configuración central — URL base del proyecto
 *
 * Detecta automáticamente la ruta base sin importar
 * si el proyecto está en /PanApp, /public_html, o la raíz.
 *
 * Para forzar manualmente, descomenta y ajusta:
 * define('APP_BASE', '');          // Raíz del dominio
 * define('APP_BASE', '/PanApp');   // Subcarpeta local
 */

if (!defined('APP_BASE')) {
    // Detectar automáticamente subiendo desde el archivo actual
    // config/app.php está en /config/, la raíz del proyecto es un nivel arriba
    $root = dirname(__DIR__); // Ruta absoluta del servidor al proyecto

    // Obtener la ruta web relativa al document root
    $docRoot  = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $projPath = str_replace('\\', '/', $root);
    $base     = str_replace($docRoot, '', $projPath);

    define('APP_BASE', rtrim($base, '/'));
}
