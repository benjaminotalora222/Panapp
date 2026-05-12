# 📄 controllers/AuthController.php

## Descripción General

Controlador de autenticación. Gestiona el inicio y cierre de sesión de los usuarios del sistema. Es el punto de entrada principal para cualquier usuario que quiera acceder a PanApp.

---

## Ubicación

```
controllers/AuthController.php
```

---

## Dependencias

| Archivo | Descripción |
|---|---|
| `config/database.php` | Conexión a la base de datos |
| `models/Usuario.php` | Modelo para consultar usuarios |
| `views/usuarios/login.php` | Vista del formulario de login |
| `views/dashboard/admin.php` | Redirección post-login para Admin |
| `views/dashboard/cajero.php` | Redirección post-login para Cajero |

---

## Clase: `AuthController`

### Método: `login()`

**Acceso:** POST desde el formulario de `login.php`

**Flujo completo:**

```
1. Verifica que el método sea POST → si no, redirige a login.php
2. Recoge y limpia $email y $password del POST
3. Valida que no estén vacíos
4. Valida formato de email con filter_var()
5. Conecta a la BD y busca el usuario por email
6. Verifica que el usuario exista
7. Verifica que el usuario esté activo (activo = 1)
8. Verifica la contraseña con password_verify()
9. Regenera el ID de sesión (seguridad anti-fixation)
10. Guarda datos del usuario en $_SESSION['usuario']
11. Redirige según el rol: ADMIN → admin.php | CAJERO → cajero.php
```

**Datos guardados en sesión:**

```php
$_SESSION['usuario'] = [
    'id_usuario' => int,
    'nombres'    => string,
    'apellidos'  => string,
    'email'      => string,
    'rol'        => string,  // 'ADMIN' o 'CAJERO'
    'activo'     => int,     // 1
];
```

---

### Método: `logout()`

**Acceso:** GET con `?accion=logout`

**Flujo:**

```
1. session_unset() → elimina todas las variables de sesión
2. session_destroy() → destruye la sesión del servidor
3. Redirige a login.php
```

---

## Punto de Entrada (al final del archivo)

```php
$controller = new AuthController();
$accion = $_GET['accion'] ?? 'login';

if ($accion === 'logout') {
    $controller->logout();
} else {
    $controller->login();
}
```

El archivo se ejecuta directamente. La acción se determina por el parámetro GET `accion`.

---

## Rutas de Acceso

| URL | Acción |
|---|---|
| `controllers/AuthController.php` (POST) | Login |
| `controllers/AuthController.php?accion=logout` | Logout |

---

## ⚠️ Posibles Fallos

| Escenario | Consecuencia | Solución |
|---|---|---|
| Usuario con rol distinto a ADMIN/CAJERO | Redirige a login con error "Rol no válido" | Validar rol al crear usuarios |
| Sesión no destruida correctamente | Usuario puede seguir accediendo | `session_unset()` + `session_destroy()` ya lo maneja |
| Ataque de fijación de sesión | Robo de sesión | `session_regenerate_id(true)` ya lo previene |
| Contraseña en texto plano en BD | Vulnerabilidad crítica | Se usa `password_hash()` y `password_verify()` correctamente |
| Sin HTTPS | Credenciales expuestas en red | Configurar SSL en producción |

---

## 🔒 Seguridad Implementada

- ✅ `password_verify()` — nunca compara contraseñas en texto plano
- ✅ `session_regenerate_id(true)` — previene session fixation
- ✅ Validación de email con `filter_var()`
- ✅ Verificación de cuenta activa antes de permitir acceso
- ⚠️ Sin límite de intentos de login (vulnerable a fuerza bruta)
- ⚠️ Sin token CSRF en el formulario
