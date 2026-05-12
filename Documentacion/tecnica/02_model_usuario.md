# 📄 models/usuario.php

## Descripción General

Modelo de la entidad `Usuario`. Encapsula todas las operaciones CRUD sobre la tabla `usuarios` de la base de datos. Sigue el patrón **Active Record simplificado** dentro de la arquitectura MVC del proyecto.

---

## Ubicación

```
models/usuario.php
```

---

## Dependencias

| Dependencia | Tipo | Descripción |
|---|---|---|
| `config/database.php` | Archivo interno | Debe instanciarse antes y pasarse al constructor |
| Tabla `usuarios` (MySQL) | Base de datos | Tabla sobre la que opera el modelo |

---

## Clase: `Usuario`

### Constructor

```php
public function __construct($db)
```

Recibe una instancia PDO activa y la almacena en `$this->conn`.

---

## Métodos

### `existeCorreo($email): bool`

Verifica si un correo ya está registrado en la base de datos.

**Uso:** Validación antes de crear o editar un usuario.

**Retorna:** `true` si existe, `false` si no.

---

### `obtenerPorEmail($email): array|false`

Busca un usuario activo por su correo electrónico.

**Uso:** Proceso de login en `AuthController`.

**Retorna:** Array asociativo con todos los campos del usuario, o `false` si no existe.

---

### `registrar($datos): true|string`

Inserta un nuevo usuario en la tabla `usuarios`.

**Parámetros esperados en `$datos`:**

| Clave | Tipo | Descripción |
|---|---|---|
| `nombres` | string | Nombres del usuario |
| `apellidos` | string | Apellidos del usuario |
| `email` | string | Correo electrónico único |
| `password_hash` | string | Hash bcrypt de la contraseña |
| `rol` | string | `ADMIN` o `CAJERO` |

**Retorna:** `true` si fue exitoso, o `string` con el mensaje de error.

> ⚠️ El campo `activo` se establece en `1` automáticamente al registrar.

---

### `obtenerTodos(): array`

Retorna todos los usuarios ordenados por fecha de creación descendente.

**Campos retornados:** `id_usuario`, `nombres`, `apellidos`, `email`, `rol`, `activo`, `created_at`.

> ⚠️ No retorna `password_hash` por seguridad.

---

### `obtenerPorId($id_usuario): array|false`

Busca un usuario por su ID primario.

**Uso:** Edición de usuario en `AdminUsuarioController`.

---

### `actualizar($id_usuario, $datos): true|string`

Actualiza los datos de un usuario existente. La contraseña es **opcional** — solo se actualiza si `$datos['password_hash']` está presente y no vacío.

**Flujo:**

```
1. Construye SQL base con nombres, apellidos, email, rol
2. Si $datos['password_hash'] existe → agrega SET password_hash
3. Ejecuta UPDATE con bindParam
```

---

### `cambiarEstado($id_usuario, $activo): true|string`

Activa (`1`) o desactiva (`0`) un usuario sin eliminarlo.

---

### `eliminar($id_usuario): true|string`

Elimina permanentemente un usuario de la base de datos.

> ⚠️ No hay soft delete. La eliminación es irreversible.

---

## Flujo de Uso Típico

```php
$database = new Database();
$db = $database->conectar();
$usuarioModel = new Usuario($db);

// Login
$usuario = $usuarioModel->obtenerPorEmail('admin@panapp.com');

// Crear
$usuarioModel->registrar([
    'nombres'       => 'Juan',
    'apellidos'     => 'Pérez',
    'email'         => 'juan@panapp.com',
    'password_hash' => password_hash('123456', PASSWORD_DEFAULT),
    'rol'           => 'CAJERO',
]);
```

---

## ⚠️ Posibles Fallos

| Escenario | Consecuencia | Solución |
|---|---|---|
| Correo duplicado sin verificar | Error de BD por UNIQUE constraint | Usar `existeCorreo()` antes de `registrar()` |
| `$datos` incompleto en `actualizar()` | Error de binding PDO | Validar en el controlador antes de llamar |
| Eliminar usuario con ventas asociadas | Error de FK en BD | Verificar dependencias antes de eliminar |
| `obtenerPorEmail()` retorna usuario inactivo | Login exitoso de cuenta desactivada | Verificar `$usuario['activo']` en el controlador |
