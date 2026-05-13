<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['reply' => 'Método no permitido.']);
    exit;
}

$input   = json_decode(file_get_contents('php://input'), true);
$mensaje = trim($input['mensaje'] ?? '');

if (empty($mensaje)) {
    echo json_encode(['reply' => 'Por favor escribe tu pregunta. 😊']);
    exit;
}

$GEMINI_API_KEY = 'AIzaSyD_Bjn5vr-T_kc5tc-zRrhO7y221ndcFCk';

// ─── Contexto según si hay sesión o no ───
$estaLogueado = isset($_SESSION['usuario']);
$rol    = $estaLogueado ? strtoupper($_SESSION['usuario']['rol']) : 'VISITANTE';
$nombre = $estaLogueado ? explode(' ', $_SESSION['usuario']['nombres'] ?? 'Usuario')[0] : 'visitante';

if ($estaLogueado) {
    $systemContext = "Eres PanBot, el asistente virtual de PanApp, sistema de gestión para panaderías colombianas.
Usuario: {$nombre}, Rol: {$rol}.

MÓDULOS:
1. VENTAS (Admin+Cajero): registrar venta > carrito > método de pago > confirmar. Ver historial con filtros. Anular desde detalle.
2. INVENTARIO (Admin): ver stock, ajustar entradas/salidas. Alerta si stock ≤5.
3. PRODUCTOS (Admin+Cajero): crear, editar, eliminar (Admin). Categorías: Pan,Pastel,Galleta,Torta,Bebida,Otro.
4. PROVEEDORES (Admin): gestionar desde menú Proveedores.
5. INSUMOS (Admin): gestionar desde menú Insumos, vinculados al inventario.
6. REPORTES: ventas y productos, exportar PDF por período (hoy/semana/mes/todo).
7. USUARIOS (Admin): crear, editar, activar/desactivar, eliminar desde menú Usuarios.
8. DASHBOARD Admin: KPIs del día, actividad reciente, top productos, accesos rápidos.

REGLAS: Responde en español, amigable, con emojis 🥐, pasos numerados, máximo 5 oraciones. Usa **negritas**.";
} else {
    $systemContext = "Eres PanBot, el asistente virtual de PanApp, sistema de gestión para panaderías colombianas.
Estás hablando con un visitante que aún no ha iniciado sesión.

INFORMACIÓN DE PANAPP:
- Sistema de gestión integral para panaderías colombianas
- Módulos: Ventas, Inventario, Productos, Proveedores, Insumos, Reportes, Usuarios
- Roles: Administrador (acceso total) y Cajero (ventas y productos)
- Para acceder al sistema se necesita una cuenta creada por el Administrador
- El registro público está deshabilitado por seguridad; solo el Admin puede crear cuentas

REGLAS: Responde en español, amigable, con emojis 🥐, máximo 5 oraciones. Usa **negritas**. Si preguntan cómo ingresar, indica que deben iniciar sesión con sus credenciales o contactar al administrador.";
}

// ── Modelos a intentar en orden ──
$modelos = [
    'gemini-1.5-flash',
    'gemini-1.5-flash-latest',
    'gemini-pro',
    'gemini-1.0-pro',
];

$body = json_encode([
    'contents' => [[
        'parts' => [['text' => $systemContext . "\n\nUsuario pregunta: " . $mensaje]]
    ]],
    'generationConfig' => [
        'temperature'     => 0.7,
        'maxOutputTokens' => 400,
    ]
]);

$reply    = null;
$lastCode = 0;
$lastErr  = '';

foreach ($modelos as $modelo) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key={$GEMINI_API_KEY}";
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $lastErr  = curl_error($ch);
    $lastCode = $httpCode;
    curl_close($ch);

    if ($response !== false && $httpCode === 200) {
        $data = json_decode($response, true);
        if (!isset($data['error'])) {
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if ($text) { $reply = $text; break; }
        }
    }
}

// ── Si Gemini falla, usar respuestas locales inteligentes ──
if (!$reply) {
    $reply = respuestaLocal(strtolower($mensaje), $rol, $nombre);
}

// Formatear markdown a HTML
$reply = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $reply);
$reply = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $reply);
$reply = str_replace("\n", '<br>', $reply);

echo json_encode(['reply' => $reply]);

// ══════════════════════════════════════════════════════════
// RESPUESTAS LOCALES (fallback cuando Gemini no responde)
// ══════════════════════════════════════════════════════════
function respuestaLocal(string $msg, string $rol, string $nombre): string {

    $esVisitante = ($rol === 'VISITANTE');

    $respuestas = [
        [['hola','buenas','buenos días','buenas tardes','hey','saludos'],
         $esVisitante
            ? "¡Hola! 👋 Soy **PanBot**, el asistente de **PanApp**. Puedo contarte sobre el sistema de gestión para panaderías. ¿En qué te ayudo?"
            : "¡Hola **{$nombre}**! 👋 Soy **PanBot**, tu asistente de PanApp. Puedo ayudarte con ventas, inventario, productos, proveedores, insumos, reportes y usuarios. ¿Qué necesitas?"],

        [['qué es panapp','que es panapp','para qué sirve','para que sirve','información','informacion'],
         "**PanApp** es un sistema de gestión integral para panaderías colombianas 🥐\n\nPermite:\n- 🛒 Registrar ventas con carrito\n- 📦 Controlar inventario en tiempo real\n- 📊 Generar reportes de ventas\n- 👥 Gestionar usuarios y proveedores\n\n¿Quieres saber más sobre algún módulo?"],

        [['cómo ingreso','como ingreso','iniciar sesión','iniciar sesion','acceder','login','entrar'],
         "Para ingresar a **PanApp** 🗝️:\n1. Haz clic en **Iniciar sesión** en el menú\n2. Ingresa tu correo y contraseña\n3. Si no tienes cuenta, contacta al **Administrador** para que te cree una\n\nEl registro público está deshabilitado por seguridad."],

        [['registrar venta','nueva venta','cómo vendo','como vendo','cómo registro','como registro'],
         "Para registrar una venta 🛒:\n1. Ve a **Ventas** en el menú\n2. Haz clic en **Nueva Venta**\n3. Selecciona los productos del carrito\n4. Elige el **método de pago**\n5. Confirma con **Registrar Venta** ✅"],

        [['inventario','stock','ajustar','entrada','salida'],
         ($rol==='ADMIN')
            ? "Para ajustar el stock 📦:\n1. Ve a **Inventario**\n2. Haz clic en **Ajustar Stock** o el ícono ✏️\n3. Selecciona el insumo\n4. Elige **Entrada** (suma) o **Salida** (resta)\n5. Ingresa la cantidad y guarda"
            : ($esVisitante ? "El módulo de **Inventario** permite controlar el stock de insumos en tiempo real 📦. Para acceder necesitas iniciar sesión como Administrador." : "El módulo de Inventario es exclusivo para Administradores. 🛡️")],

        [['producto','crear producto','agregar producto','nuevo producto'],
         "Para crear un producto 🍞:\n1. Ve a **Productos**\n2. Haz clic en **Agregar Producto**\n3. Completa: nombre, categoría, precio y unidad\n4. Guarda ✅"],

        [['proveedor','proveedores'],
         ($esVisitante) ? "El módulo de **Proveedores** permite gestionar los contactos de tus distribuidores 🚚. Disponible para Administradores." : (($rol==='ADMIN') ? "Para gestionar proveedores 🚚:\n→ Ve a **Proveedores** en el menú lateral\n→ Usa **Agregar Proveedor** para crear uno nuevo" : "Los proveedores son gestionados solo por Administradores. 🛡️")],

        [['insumo','insumos','materia prima'],
         ($esVisitante) ? "Los **Insumos** son las materias primas de tu panadería 🌾. El módulo permite gestionarlos y vincularlos al inventario." : (($rol==='ADMIN') ? "Para gestionar insumos 🌾:\n→ Ve a **Insumos** en el menú\n→ Crea, edita o elimina insumos vinculados a proveedores" : "Los insumos son gestionados solo por Administradores. 🛡️")],

        [['reporte','reportes','pdf','exportar'],
         "Para ver reportes 📊:\n1. Ve a **Reportes** en el menú\n2. Filtra por: Hoy, Esta semana, Este mes o Todas\n3. Usa **Exportar PDF** para descargar el reporte"],

        [['usuario','usuarios','crear usuario','cuenta'],
         ($esVisitante) ? "Las cuentas en **PanApp** son creadas por el **Administrador** 👥. Si necesitas acceso, contacta al admin de tu panadería." : (($rol==='ADMIN') ? "Para gestionar usuarios 👥:\n→ Ve a **Usuarios** en el menú\n→ Usa **Agregar Usuario** para crear uno nuevo\n→ Puedes editar, activar/desactivar o eliminar desde la tabla" : "La gestión de usuarios es exclusiva para Administradores. 🛡️")],

        [['cerrar sesión','cerrar sesion','salir','logout'],
         "Para cerrar sesión 🚪:\n→ Ve al **sidebar** (menú lateral)\n→ En la parte inferior haz clic en el ícono de salida →"],

        [['ayuda','help','qué puedes','que puedes','funciones','módulos','modulos'],
         $esVisitante
            ? "**PanApp** tiene estos módulos 🥐:\n🛒 **Ventas** · 📦 **Inventario** · 🍞 **Productos**\n🚚 **Proveedores** · 🌾 **Insumos** · 📊 **Reportes** · 👥 **Usuarios**\n\nPara usarlos necesitas iniciar sesión. ¿Te cuento más sobre alguno?"
            : "Puedo ayudarte con:\n🛒 **Ventas** · 📦 **Inventario** · 🍞 **Productos**\n🚚 **Proveedores** · 🌾 **Insumos** · 📊 **Reportes** · 👥 **Usuarios**\n\nPregúntame lo que necesites 😊"],

        [['gracias','adiós','adios','chao','bye'],
         $esVisitante
            ? "¡Con gusto! 🥐 Si decides usar **PanApp**, no dudes en volver. ¡Hasta pronto!"
            : "¡Con gusto, **{$nombre}**! 🥐 Si necesitas algo más, aquí estaré. ¡Que tengas un excelente día!"],
    ];

    $mejorRespuesta = null;
    $mejorPuntaje   = 0;

    foreach ($respuestas as [$palabras, $respuesta]) {
        $puntaje = 0;
        foreach ($palabras as $p) {
            if (strpos($msg, $p) !== false) $puntaje += strlen($p);
        }
        if ($puntaje > $mejorPuntaje) {
            $mejorPuntaje   = $puntaje;
            $mejorRespuesta = $respuesta;
        }
    }

    return $mejorRespuesta ?? "No estoy seguro de cómo ayudarte con eso 🤔\n\nPuedo contarte sobre **PanApp** y sus módulos: ventas, inventario, productos, proveedores, insumos, reportes y usuarios. ¿Puedes reformular tu pregunta?";
}
