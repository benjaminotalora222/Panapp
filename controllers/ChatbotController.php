<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['reply' => 'Debes iniciar sesión para usar el asistente. 🔐']);
    exit;
}

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

$rol    = strtoupper($_SESSION['usuario']['rol']);
$nombre = explode(' ', $_SESSION['usuario']['nombres'] ?? 'Usuario')[0];

// ── Contexto completo del sistema ──
$systemContext = "Eres PanBot, el asistente virtual inteligente de PanApp, un sistema de gestión para panaderías colombianas.
El usuario se llama {$nombre} y tiene el rol: {$rol}.

MÓDULOS Y FUNCIONES DEL SISTEMA:

1. VENTAS (Admin y Cajero)
   - Registrar venta: Ventas > Nueva Venta > agregar productos al carrito > seleccionar método de pago > Registrar Venta
   - Ver historial: Ventas, filtros por Hoy / Esta semana / Este mes / Todas
   - Ver detalle: ícono 👁️ en cada fila
   - Anular venta: desde el detalle, botón Anular (cajero solo sus ventas, Admin cualquiera)
   - Métodos de pago: Efectivo, Tarjeta, Transferencia

2. INVENTARIO (solo Admin)
   - Ver stock: Inventario en el menú
   - Estados: ✅ OK (>5), ⚠️ Stock bajo (≤5), 🔴 Sin stock (0)
   - Ajustar stock: botón Ajustar Stock o ícono ✏️ en cada fila
   - Tipos de ajuste: Entrada (suma) o Salida (resta)
   - No se puede registrar salida sin stock suficiente

3. PRODUCTOS (Admin y Cajero)
   - Ver: Productos en el menú
   - Crear: botón Agregar Producto > nombre, descripción, categoría, precio, unidad de medida
   - Categorías: Pan, Pastel, Galleta, Torta, Bebida, Otro
   - Unidades: Unidad, Docena, Libra, Kilo, Porción, Litro
   - Editar: ícono ✏️ (todos los roles)
   - Eliminar: ícono 🗑️ (solo Admin, no si tiene ventas asociadas)

4. PROVEEDORES (solo Admin)
   - Ver/Crear/Editar/Eliminar desde el menú Proveedores
   - Campos: nombre, teléfono, correo, dirección, estado (activo/inactivo)

5. INSUMOS (solo Admin)
   - Ver/Crear/Editar/Eliminar desde el menú Insumos
   - Campos: nombre, descripción, unidad de medida, proveedor (opcional)
   - Se vinculan al inventario para controlar stock

6. REPORTES
   - Admin: Reportes generales (ventas y productos), exportar PDF
   - Cajero: Reporte Ventas y Reporte Productos desde el menú

7. USUARIOS (solo Admin)
   - Ver y gestionar desde el Dashboard
   - Crear: botón Nuevo Usuario > nombres, apellidos, correo, rol, contraseña (mín. 6 caracteres)
   - Roles: Admin (acceso completo) o Cajero (acceso limitado)
   - Editar, activar/desactivar, eliminar (no puedes eliminarte a ti mismo)

8. DASHBOARD
   - Admin: resumen de usuarios, gestión de usuarios, accesos rápidos a todos los módulos
   - Cajero: ventas del día, ingresos, productos disponibles, últimas 5 ventas

9. CERRAR SESIÓN
   - Sidebar > parte inferior > ícono de salida (→)

REGLAS IMPORTANTES:
- Responde SIEMPRE en español
- Sé amigable, conciso y usa emojis ocasionalmente 🥐
- Si el usuario pregunta algo que no puede hacer por su rol ({$rol}), indícaselo amablemente
- Da pasos numerados cuando expliques cómo hacer algo
- No inventes funciones que no existen en el sistema
- Puedes responder preguntas generales sobre panadería o negocios si el usuario lo pide
- Máximo 5-6 oraciones o pasos por respuesta
- Usa **negritas** para resaltar términos importantes";

// ── Llamada a Gemini API ──
$url  = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$GEMINI_API_KEY}";
$body = json_encode([
    'contents' => [
        [
            'parts' => [
                ['text' => $systemContext . "\n\nPregunta del usuario: " . $mensaje]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature'     => 0.7,
        'maxOutputTokens' => 400,
        'topP'            => 0.9,
    ],
    'safetySettings' => [
        ['category' => 'HARM_CATEGORY_HARASSMENT',        'threshold' => 'BLOCK_NONE'],
        ['category' => 'HARM_CATEGORY_HATE_SPEECH',       'threshold' => 'BLOCK_NONE'],
        ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_NONE'],
        ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_NONE'],
    ]
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $body,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// ── Procesar respuesta ──
if ($response === false || $httpCode !== 200) {
    // Fallback: respuesta local si Gemini falla
    echo json_encode(['reply' => '⚠️ El asistente de IA no está disponible en este momento. Intenta de nuevo en unos segundos.', 'error' => $curlError]);
    exit;
}

$data = json_decode($response, true);

if (isset($data['error'])) {
    echo json_encode(['reply' => '⚠️ Error de la IA: ' . ($data['error']['message'] ?? 'Error desconocido')]);
    exit;
}

$reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

if (!$reply) {
    echo json_encode(['reply' => '⚠️ No obtuve respuesta. Intenta de nuevo.']);
    exit;
}

// Formatear markdown a HTML básico
$reply = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $reply);
$reply = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $reply);
$reply = str_replace("\n", '<br>', $reply);

echo json_encode(['reply' => $reply]);
