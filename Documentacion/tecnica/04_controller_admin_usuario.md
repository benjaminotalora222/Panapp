# 📄 controllers/AdminUsuarioController.php

## Descripción General

Controlador exclusivo para que el **Administrador** gestione los usuarios del sistema. Permite crear, editar, activar/desactivar y eliminar usuarios. Requiere sesión activa con rol `ADMIN`.

---

## Ubicación

```
controllers/AdminUsuarioController.php
```

---

## Dependencias

| Archivo | Descripción |
|---|---|
| `config/database.php` | Conexión a la base de datos |
| `models/Usuario.php` | Operaciones CRUD sobre usuarios |

---

## Control de Acceso

```php
if (!isset($_SESSION['usuario']) || strtoupper($_SESSION['usuario']['rol']) !== 'ADMIN') {
    header("Location: ../views/usuarios/login.php");
    exit;
}
```

Cualquier intento de acceso sin sesión de Admin redirige al login.

---

## Acciones Disponibles

La acción se determina por `$_GET['accion']`.

---

### `crear` — POST

**Validaciones:**
1. Todos los campos obligatorios presentes
2. Email con formato válido
3. Contraseñas coinciden
4. Contraseña mínimo 6 caracteres
5. Rol válido (`ADMIN` o `CAJERO`)
6. Email no duplicado en BD

**Proceso:**
- Hashea la contraseña con `password_hash($password, PASSWORD_DEFAULT)`
- Llama a `$usuarioModel->registrar()`
- Redirige a `admin.php` con alerta de éxito o error

---

### `editar` — POST + GET `?id=`

**Validaciones:**
1. ID válido (> 0)
2. Campos obligatorios presentes
3. Email válido
4. Rol válido
5. Si el email cambió, verificar que no esté en uso por otro usuario
6. Si se ingresó contraseña nueva: coinciden y mínimo 6 caracteres

**Proceso:**
- La contraseña es **opcional** — solo se actualiza si se ingresó
- Llama a `$usuarioModel->actualizar($id, $datos)`

---

### `toggleEstado` — GET `?id=&estado=`

Invierte el estado activo/inactivo de un usuario.

**Lógica:**
```
estado=1 (activo)   → nuevoEstado = 0 (desactivar)
estado=0 (inactivo) → nuevoEstado = 1 (activar)
```

Llama a `$usuarioModel->cambiarEstado($id, $nuevoEstado)`.

---

### `eliminar` — GET `?id=`

**Protección especial:**
```php
if ($id === intval($_SESSION['usuario']['id_usuario'])) {
    // No puedes eliminarte a ti mismo
}
```

Llama a `$usuarioModel->eliminar($id)`.

---

## Flujo de Redirecciones

```
Todas las acciones → $_SESSION['alert'] → header("Location: admin.php")
```

Los errores de validación en `editar` redirigen a `usuario_editar.php?id=`.

---

## ⚠️ Posibles Fallos

| Escenario | Consecuencia | Solución |
|---|---|---|
| Eliminar usuario con ventas asociadas | Error de FK en BD | El modelo retorna el error como string |
| Editar email a uno ya existente | Error detectado y mostrado | Ya validado con `existeCorreo()` |
| Acceso directo sin sesión | Redirige a login | Ya protegido |
| Sin validación CSRF | Vulnerable a ataques cross-site | Agregar token CSRF en formularios |
